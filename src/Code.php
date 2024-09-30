<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class Code
{
    use Injection;
    use Singleton;

    private string $identifier = "A8";
    private array $dataTypes = ["boolean", "integer", "double", "string", "array", "object", "NULL"];
    public const string BASE_36_CHARSET = "0123456789abcdefghijklmnopqrstuvwxyz";
    public const string BASE_62_CHARSET = "0123456789abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ";
    // excludes characters that can be used as separators: | and ,
    public const string BASE_92_CHARSET = "!\"#$%&'()*+-./0123456789:;<=>?@ABCDEFGHIJKLMNOPQRSTUVWXYZ[\]^_`abcdefghijklmnopqrstuvwxyz{}~";

    /**
     * This function encodes an id to an obfuscated 12 character string (for id <= 999999999; for larger the output size will increase.)
     * This function will return false if the id <= 0.
     * @param string|int $id
     * @param bool $randomized
     * @return string|bool
     */
    public function encodeId(string|int|null $id, bool $randomized = false): string|null
    {
        $id = (int)$id;

        if ($id <= 0) {
            return null;
        }

        $hash = hash("sha256", $id);
        $salt = $randomized ? dechex(rand(16, 255)) : substr($hash, -2);
        $alphabet = $this->getShuffledAlphabet($salt);
        $id = dechex($id);
        $len = strlen($id);
        $id = ($len < 8 ? substr($hash, 0, 8 - $len) : "") . $id . dechex($len + 16);

        foreach (str_split($id) as $key => $ch) {
            $id[$key] = $alphabet[$ch];
        }

        return $id . $salt;
    }

    /**
     * Decodes the obfuscated id encoded by encodeId. Will return false if the string cannot be decoded.
     * @param string $string
     * @return bool|int
     */
    public function decodeId(string $string): int|null
    {
        if (empty($string)) {
            return null;
        }

        $salt = substr($string, -2);
        $string = substr($string, 0, -2);
        $alphabet = $this->getShuffledAlphabet($salt, true);

        foreach (str_split($string) as $key => $ch) {
            $string[$key] = $alphabet[$ch];
        }

        $len = hexdec(substr($string, -2)) - 16;
        $result = hexdec(substr($string, ($len + 2) * -1, $len));

        return is_float($result) ? null : $result;
    }

    public function encrypt($data, string $key, bool $hexEncode = true): string|bool
    {
        $type = gettype($data);

        switch ($type) {
            case "boolean":
                $data = $data ? "1" : "0";
                $compress = false;
                break;
            case "integer":
            case "double":
                $data = (string)$data;
                $compress = false;
                break;
            case "array":
            case "object":
                $data = json_encode($data);
                $compress = true;
                break;
            case "NULL":
                $data = "0";
                $compress = false;
                break;
            case "string":
                $data = (string)$data;
                if (empty($data)) {
                    return "";
                }
                $compress = true;
                break;
            default:
                return false;
        }

        $length = strlen($data);
        $compressed = 0;

        // we compress only if the variable is a text (or json) and if it's longer than 100 characters, which is the
        // approximate threshold of when the encrypted data is smaller when compressed
        if ($compress && $length > 100) {
            $compressedData = gzcompress($data, 9);
            $compressedLength = strlen($compressedData);
            if ($compressedLength < $length) {
                $data = $compressedData;
                $compressed = 1;
            }
        }

        $data =
            $this->identifier . // identifier: 2 characters
            str_pad(base_convert($length, 10, 36), 6, "0", STR_PAD_LEFT) . // string length: 6 characters
            (int)array_search($type, $this->dataTypes) . // data type: 1 character
            $compressed . // compressed: 1 character
            $data;

        $result = $this->encryptString($data, $key);

        return $hexEncode ? bin2hex($result) : $result;
    }

    /**
     * Decrypts data. It decodes the data and returns it in the original state, including the variable type.
     *
     * @param mixed $data
     * @param string $key If null, the app key is used (set in setup.php)
     * @param bool $hexEncoded
     * @return mixed
     */
    public function decrypt(string $data, string $key, bool $hexEncoded = true): mixed
    {
        if (empty($data)) {
            return "";
        }

        try {
            $data = $this->decryptString($hexEncoded ? @hex2bin($data) : $data, $key);
        } catch (\Exception) {
            return false;
        }

        // check the identifier
        if (substr($data, 0, 2) != $this->identifier) {
            return false;
        }

        // get the length
        if (!($length = (int)base_convert(substr($data, 2, 6), 36, 10))) {
            return "";
        }

        // get the type
        $type = substr($data, 8, 1);

        if (!array_key_exists($type, $this->dataTypes)) {
            return false;
        }

        // get the data and decompress if needed
        if (substr($data, 9, 1)) {
            $data = gzuncompress(substr($data, 10, $length));
        } else {
            $data = substr($data, 10, $length);
        }

        return match ($this->dataTypes[$type]) {
            "boolean" => $data == "1",
            "integer" => (int)$data,
            "double" => (double)$data,
            "array" => json_decode($data, true),
            "object" => json_decode($data),
            "NULL" => null,
            default => $data,
        };
    }

    public function encryptString(string $string, string $key): bool|string
    {
        if (empty($string) || !function_exists("openssl_encrypt")) {
            return false;
        }

        if (empty($key)) {
            return false;
        }

        $iv = openssl_random_pseudo_bytes(16);
        return $iv . openssl_encrypt($string, "AES-256-CFB", $key, OPENSSL_RAW_DATA, $iv);
	}

	public function decryptString(string $string, string $key): bool|string
    {
		if (empty($string) || !function_exists("openssl_decrypt")) {
			return false;
        }

        if (empty($key)) {
            return false;
        }

        return openssl_decrypt(substr($string, 16), "AES-256-CFB", $key, OPENSSL_RAW_DATA, substr($string, 0, 16));
	}

    /**
     * Returns an array of shuffled alphabet [0-F] in the format [originalCharacter => shuffledCharacter].
     * @param string $salt
     * @param bool $reverse
     * @return array
     */
    private function getShuffledAlphabet(string $salt, bool $reverse = false): array
    {
        $alphabet = $original = "0123456789abcdef";
        $length = 16;
        $result = [];

        for ($i = $length - 1, $v = 0, $p = 0; $i > 0; $i--, $v++) {
            $v %= strlen($salt);
            $p += $int = ord($salt[$v]);
            $j = ($int + $v + $p) % $i;
            $temp = $alphabet[$j];
            $alphabet[$j] = $alphabet[$i];
            $alphabet[$i] = $temp;
        }

        for ($i = 0; $i < $length; $i++) {
            if ($reverse) {
                $result[$alphabet[$i]] = $original[$i];
            } else {
                $result[$original[$i]] = $alphabet[$i];
            }
        }

        return $result;
    }

    /**
     * Converts encoded text to binary data.
     *
     * @param string $data
     * @return bool|string
     */
    public static function textToBinary(string $data): bool|string
    {
        return base64_decode(str_pad(strtr($data, "-_", "+/"), strlen($data) % 4, "=", STR_PAD_RIGHT));
    }

    /**
     * Converts binary data to encoded string.
     *
     * @param mixed $data
     * @return string
     */
    public static function binaryToText(mixed $data): string
    {
        return rtrim(strtr(base64_encode($data), "+/", "-_"), "=");
    }

    /**
     * Encodes an (positive) integer to a short string representation.
     *
     * BASE_36_CHARSET - url safe; uses numbers and lowercase letters
     * BASE_62_CHARSET - url safe; uses numbers, lowercase, and uppercase letters;
     *                   will produce shorter strings than BASE_36_CHARSET for numbers larger than 1,679,616
     * BASE_92_CHARSET - not url safe; uses all printable characters excluding two that we reserve for separating data: | and ,
     *                   will produce shorter strings than BASE_62_CHARSET for numbers larger than 14,776,335
     *
     * @param string|int $number - a positive integer
     * @param string $charset (any charset string, or presets: BASE_36_CHARSET, BASE_62_CHARSET, BASE_92_CHARSET)
     * @return string
     */
    public static function baseEncode(string|int $number, string $charset = Code::BASE_36_CHARSET): string
    {
        $number = trim($number);
        if (!is_numeric($number) || $number < 1) {
            return "";
        }

        $base = strlen($charset);
        $result = "";
        while ($number > 0) {
            $result = $charset[$number % $base] . $result;
            $number = intdiv($number, $base);
        }
        return $result;
    }

    /**
     * Decodes string encoded with baseDecode. Remember to specify the same charset that the integer was encoded with.
     *
     * @param string $encoded
     * @param string $charset
     * @return int
     */
    public static function baseDecode(string $encoded, string $charset = Code::BASE_36_CHARSET): int
    {
        $base = strlen($charset);
        $length = strlen($encoded);
        $number = 0;

        for ($i = 0; $i < $length; $i++) {
            $number = $number * $base + strpos($charset, $encoded[$i]);
        }

        return $number;
    }
}

