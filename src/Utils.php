<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

use enshrined\svgSanitize\Sanitizer;
use Exception;

class Utils
{
    public static function getHost(): string
    {
        return (isset($_SERVER['HTTPS']) && $_SERVER['HTTPS'] === 'on' ? "https" : "http") . "://" . $_SERVER['SERVER_NAME'];
    }

    /**
     * Converts an integer to a human-readable file size string.
     *
     * @param $size
     * @return string
     */
    static public function sizeToString($size): string
    {
        if (!is_numeric($size)) {
            return "-";
        }

        $units = ["B", "kB", "MB", "GB", "TB", "PB"];
        $index = 0;

        while ($size >= 1000) {
            $size /= 1000;
            $index++;
        }

        return round($size) . " " . $units[$index];
    }

    /**
     * Shortens a string to the specified length and appends (...). If the string is shorter than the specified length,
     * the string will be left intact.
     *
     * @param string $string
     * @param int $length
     * @return string
     */
    public static function shortenString(string $string, int $length = 50): string
    {
        $string = trim($string);

        if (strlen($string) <= $length) {
            return $string;
        }

        $string = substr($string, 0, $length);

        if ($i = strrpos($string, " ")) {
            $string = substr($string, 0, $i);
        }

        return $string . "...";
    }

    /**
     * Returns a string containing a relative path for saving files based on the passed id. This is used for limiting
     * the amount of files stored in a single directory.
     *
     * @param $id
     * @param int $idsPerDir
     * @param int $levels
     * @return string
     */
    public static function structuredDirectory($id, int $idsPerDir = 500, int $levels = 2): string
    {
        if ($idsPerDir <= 0) {
            $idsPerDir = 100;
        }

        if ($levels < 1 || $levels > 3) {
            $levels = 2;
        }

        $level1 = floor($id / $idsPerDir);
        $level2 = floor($level1 / 1000);
        $level3 = floor($level2 / 1000);

        return ($levels > 2 ? sprintf("%03d", $level3 % 1000) . "/" : "") .
            ($levels > 1 ? sprintf("%03d", $level2 % 1000) . "/" : "") .
            sprintf("%03d", $level1 % 1000) . "/";
    }

    /**
     * Returns a string that is sure to be a valid file name.
     *
     * @param string $string
     * @return string
     */
    public static function ensureFileName(string $string): string
    {
        $result = preg_replace(["/[\/\\\:?*+%|\"<>]/i", "/_{2,}/"], "_", strtolower($string));
        return trim($result, "_ \t\n\r\0\x0B") ?: "unknown";
    }

    /**
     * Returns a unique file name. This function generates a random name, then checks if the file with this name already
     * exists in the specified directory. If it does, it generates a new random file name.
     *
     * @param string $path
     * @param bool|string $ext
     * @param bool|string $prefix
     * @return string
     */
    public static function getUniqueFileName(string $path, bool|string $ext = false, bool|string $prefix = false): string
    {
        if (strlen($ext) && $ext[0] != ".") {
            $ext = "." . $ext;
        }

        $path = self::addSlash($path);

        do {
            $fileName = uniqid($prefix, true) . $ext;
        } while (file_exists($path . $fileName));

        return $fileName;
    }

    /**
     * Extracts the extension from file name.
     *
     * @param string $fileName
     * @return string
     */
    public static function getExtension(string $fileName): string
    {
        return strtolower(pathinfo($fileName, PATHINFO_EXTENSION));
    }

    /**
     * Creates an empty directory with write permissions. It returns true if the directory already exists and is
     * writable. Also, if umask is set, mkdir won't create the directory with 0777 permissions, for example, if umask
     * is 0022, the outcome will be 0777-0022 = 0755, so we reset umask before creating the directory.
     *
     * @param string $dir
     * @return boolean
     */
    public static function makeDir(string $dir): bool
    {
        if (file_exists($dir)) {
            return is_writable($dir);
        }

        $umask = umask(0);
        $result = @mkdir($dir, 0777, true);
        umask($umask);

        return $result;
    }

    /**
     * Recursively removes a directory (including all the hidden files.)
     *
     * @param string $dir
     * @param bool $followLinks Should we follow directory links?
     * @param bool $contentsOnly Removes contents only leaving the directory itself intact.
     * @return boolean
     */
    public static function removeDir(string $dir, bool $followLinks = false, bool $contentsOnly = false): bool
    {
        if (empty($dir) || !is_dir($dir)) {
            return true;
        }

        $dir = self::addSlash($dir);
        $files = array_diff(scandir($dir), [".", ".."]);

        foreach ($files as $file) {
            if (is_dir($dir . $file)) {
                self::removeDir($dir . $file, $followLinks);
                continue;
            }

            if (is_link($dir . $file) && $followLinks) {
                unlink(readlink($dir . $file));
            }

            unlink($dir . $file);
        }

        return $contentsOnly || rmdir($dir);
    }

    /**
     * Returns the current url. Optionally it appends a path specified by the $path parameter.
     *
     * @param string $path
     * @param bool $hostOnly
     * @param bool $cut
     * @return string|boolean
     */
    public static function getUrl(string $path = "", bool $hostOnly = false, bool $cut = false): string|bool
    {
        // if absolute path specified, simply return it
        if (strpos($path, "://")) {
            return $path;
        }

        $requestUri = empty($_SERVER['REQUEST_URI']) ? "_" : $_SERVER['REQUEST_URI'];
        $parts = parse_url($requestUri);
        $urlPath = $parts['path'] ?? "";

        if (!empty($parts['scheme'])) {
            $scheme = strtolower($parts['scheme']) == "https" ? "https" : "http";
        } else {
            $scheme = empty($_SERVER["HTTPS"]) || $_SERVER["HTTPS"] != "on" ? "http" : "https";
        }

        if (!empty($parts['host'])) {
            $host = $parts['host'];
        } else {
            $host = empty($_SERVER['HTTP_HOST']) ? false : $_SERVER['HTTP_HOST'];

            if (empty($host)) {
                $host = empty($_SERVER['SERVER_NAME']) ? false : $_SERVER['SERVER_NAME'];
            }
        }

        if (!empty($parts['port'])) {
            $port = $parts['port'];
        } else {
            $port = empty($_SERVER['SERVER_PORT']) ? "80" : $_SERVER['SERVER_PORT'];
        }

        // if url not specified in the config, check for proxy values
        empty($_SERVER['HTTP_X_FORWARDED_PROTO']) || ($scheme = $_SERVER['HTTP_X_FORWARDED_PROTO']);
        empty($_SERVER['HTTP_X_FORWARDED_HOST']) || ($host = $_SERVER['HTTP_X_FORWARDED_HOST']);
        empty($_SERVER['HTTP_X_FORWARDED_PORT']) || ($port = $_SERVER['HTTP_X_FORWARDED_PORT']);

        // if full url specified but without the protocol, prepend http or https and return.
        // we can't just leave it as is because roundcube will prepend the current domain
        if (str_starts_with($path, "//")) {
            return $scheme . ":" . $path;
        }

        // we have to have the host
        if (empty($host)) {
            return false;
        }

        // if need host only, return it
        if ($hostOnly) {
            return $host;
        }

        // format port
        if ($port && is_numeric($port) && $port != "443" && $port != "80") {
            $port = ":" . $port;
        } else {
            $port = "";
        }

        // in cpanel $urlPath will have index.php at the end
        if (str_ends_with($urlPath, ".php")) {
            $urlPath = dirname($urlPath);
        }

        // if path begins with a slash, cut it
        if (str_starts_with($path, "/")) {
            $path = substr($path, 1);
        }

        $result = self::addSlash($scheme . "://" . $host . $port . $urlPath);

        // if paths to cut were specified, find and cut the resulting url
        if ($cut) {
            if (!is_array($cut)) {
                $cut = [$cut];
            }

            foreach ($cut as $val) {
                if (($pos = strpos($result, $val)) !== false) {
                    $result = substr($result, 0, $pos);
                }
            }
        }

        return $result . $path;
    }

    /**
     * Removes the slash from the end of a string.
     *
     * @param string $string
     * @return string
     */
    public static function removeSlash(string $string): string
    {
        return str_ends_with($string, '/') || str_ends_with($string, '\\') ? substr($string, 0, -1) : $string;
    }

    /**
     * Adds a slash to the end of the string.
     *
     * @param string $string
     * @return string
     */
    public static function addSlash(string $string): string
    {
        return str_ends_with($string, '/') || str_ends_with($string, '\\') ? $string : $string . '/';
    }

    /**
     * Converts a string representation of the boolean "true" or "false" into the actual boolean value.
     *
     * @param string $value
     * @return bool|string
     */
    public static function strToBool(string $value): bool|string
    {
        return match ($value) {
            "true" => true,
            "false" => false,
            default => $value,
        };
    }

    /**
     * Encodes binary data using base64.
     *
     * @param $data
     * @return string
     */
    public static function encodeBinary($data): string
    {
        return urlencode(rtrim(strtr(base64_encode($data), "+/", "-_"), "="));
    }

    /**
     * Decodes base64-encoded binary string.
     *
     * @param $data
     * @return bool|string
     */
    public static function decodeBinary($data): bool|string
    {
        return base64_decode(str_pad(strtr($data, "-_", "+/"), strlen($data) % 4, "="));
    }

    /**
     * Generates UUID v4.
     * @return bool|string
     * @throws Exception
     */
    public static function generateUuid(): bool|string
    {
        $data = function_exists("random_bytes") ? random_bytes(16) : openssl_random_pseudo_bytes(16);

        if (strlen($data) != 16) {
            return false;
        }

        $data[6] = chr(ord($data[6]) & 0x0f | 0x40); // set version to 0100
        $data[8] = chr(ord($data[8]) & 0x3f | 0x80); // set bits 6-7 to 10

        return vsprintf('%s%s-%s-%s-%s-%s%s%s', str_split(bin2hex($data), 4));
    }

    public static function generatePassword($length = 24): string
    {
        $characters = "1234567890qwertyuiopasdfghjklzxcvbnmQWERTYUIOPASDFGHJKLZXCVBNM!@#%^&*()_-+=";

        $result = "";
        while (strlen($result) < $length) {
            $result .= $characters[rand(0, strlen($characters) - 1)];
        }

        return $result;
    }

    /**
     * Creates a random token composed of lower case letters and numbers.
     *
     * @param int $length
     * @return string
     */
    public static function generateRandomToken(int $length = 32): string
    {
        $characters = "abcdefghijklmnopqrstuvwxyz1234567890";
        $charactersLength = strlen($characters);
        $result = "";

        for ($i = 0; $i < $length; $i++) {
            $result .= $characters[mt_rand(0, $charactersLength - 1)];
        }

        return $result;
    }

    public static function backwardStrpos($haystack, $needle, $offset = 0): bool|int
    {
        $length = strlen($haystack);
        $offset = $offset > 0 ? $length - $offset : abs($offset);
        $pos = strpos(strrev($haystack), strrev($needle), $offset);
        return ($pos === false) ? false : $length - $pos - strlen($needle);
    }

    /**
     * Create a permalink that can be used for a page url.
     * @param $text
     * @return bool|string
     */
    public static function permalink($text): bool|string
    {
        if (!($text = trim($text))) {
            return false;
        }

        $text = iconv("UTF-8", "ASCII//TRANSLIT", $text);
        $text = preg_replace("%[^-/+|\w ]%", "-", $text);
        $text = strtolower(trim($text, "-"));
        return (string)preg_replace("/[\/_|+ -]+/", "-", $text);
    }

    public static function deleteOutdatedFiles($directory, $interval, &$error): bool
    {
        $directory = self::removeSlash($directory);

        if (!$handle = opendir($directory)) {
            $error = "Cannot open directory"; // not translated, logged for admins only
            return false;
        }

        while (($file = readdir($handle)) !== false) {
            if ($file != "." && $file != ".." && filemtime($directory . '/' . $file) < time() - $interval) {
                unlink($directory . '/' . $file);
            }
        }

        closedir($handle);
        return true;
    }

    /**
     * Returns the current browser language.
     *
     * @return string
     */
    public static function getBrowserLanguage(): string
    {
        $result = "en";

        if (empty($_SERVER["HTTP_ACCEPT_LANGUAGE"])) {
            return $result;
        }

        $lan = [];

        foreach (explode(",", $_SERVER["HTTP_ACCEPT_LANGUAGE"]) as $val) {
            // check for q-value and create associative array. No q-value means 1
            if (preg_match("/(.*);q=([0-1]{0,1}.\d{0,4})/i", $val, $matches)) {
                $lan[$matches[1]] = (float)$matches[2];
            } else {
                $lan[$val] = 1.0;
            }
        }

        // return default language (highest q-value)
        $value = 0.0;

        foreach ($lan as $key => $val) {
            if ($val > $value) {
                $value = (float)$val;
                $result = $key;
            }
        }

        return strtolower($result);
    }

    /**
     * Returns the max file upload size as specified in php.ini. Takes into consideration two variables, each of them
     * can restrict the size, so we take the smaller one.
     *
     * The function returns 0 if no upload limit is specified.
     *
     * To represent this value in a user-readable format, use sizeToString().
     *
     * @return float|int
     */
    public static function maxFileUploadSize(): float|int
    {
        $bytes = 0;

        if (($b = self::sizeToBytes(ini_get('post_max_size'))) > 0) {
            $bytes = $b;
        }

        if (($b = self::sizeToBytes(ini_get('upload_max_filesize'))) > 0 && $b < $bytes) {
            $bytes = $b;
        }

        return $bytes;
    }

    /**
     * Converts PHP-type size string to bytes. For example 64M will become 67108864. This is useful for converting
     * the php.ini values to bytes. The function is courtesy of Drupal.
     *
     * @param string $size
     * @return int
     */
    public static function sizeToBytes(string $size): int
    {
        $unit = preg_replace('/[^bkmgtpezy]/i', '', $size); // Remove the non-unit characters from the size.
        $size = preg_replace('/[^0-9.]/', '', $size); // Remove the non-numeric characters from the size.

        if ($unit) {
            // Find the position of the unit in the ordered string which is the power of magnitude to multiply a kilobyte by.
            return round($size * pow(1024, stripos('bkmgtpezy', $unit[0])));
        }

        return round($size);
    }

    public static function validateEmail(string $email, bool $checkDns = true): bool
    {
        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return false;
        }

        if ($checkDns) {
            $array = explode("@", $email);
            $domain = end($array);

            // check the mx record (dot at the end, so it doesn't get treated as a subdomain of the local domain)
            if (!checkdnsrr($domain . ".")) {
                return false;
            }
        }

        return true;
    }

    public static function getFileMimeType(string $filePath): string
    {
        $type = finfo_file(finfo_open(FILEINFO_MIME_TYPE), $filePath);

        // it could be "image/svg+xml" so we're simplifying
        if (str_starts_with($type, "image/svg")) {
            return "image/svg";
        }

        // icon could also be this
        if ($type == "image/vnd.microsoft.icon") {
            return "image/x-icon";
        }

        return $type;
    }

    /**
     * Moves the uploaded image file, checking and re-saving it to avoid any potential security risks.
     *
     * @param array $uploadInfo - upload array taken from $_FILES['file']
     * @param string $targetFile - path and file name of the target file
     * @param bool|int $maxSize - max file size allowed, or false if shouldn't check
     * @param null|string $error - any error message will be returned here
     * @param bool|int $maxWidth
     * @param bool|int $maxHeight
     * @param bool $forceTargetType - specify target file type: image/jpeg, image/png, image/gif
     * @return string|bool
     */
    public static function saveUploadedImage(array $uploadInfo, string $targetFile, bool|int $maxSize = false, null|string &$error = null,
                                             bool|int $maxWidth = false, bool|int $maxHeight = false,
                                             bool|string $forceTargetType = false): string|bool
    {
        $allowedExtensions = ["png", "jpg", "jpeg", "gif", "svg", "ico"];
        $allowedTypes = ["image/jpeg", "image/png", "image/gif", "image/svg", "image/svg+xml", "image/x-icon"];
        $filePath = $uploadInfo['tmp_name'];
        $fileName = self::ensureFileName($uploadInfo['name']);
        $fileSize = $uploadInfo['size'];
        $image = null;
        $type = "";

        try {
            // check if the file name is set
            if (empty($fileName) || $fileName == "unknown") {
                throw new Exception("Invalid file name. (44350)");
            }

            // check if file too large
            if ($maxSize && $fileSize > $maxSize) {
                throw new Exception(sprintf("The file size exceeds the allowed %s.", self::sizeToString($maxSize)));
            }

            // check if there is an upload error
            if (!empty($uploadInfo['error'])) {
                throw new Exception("The file has not been uploaded properly. (44351)");
            }

            // check if the uploaded file exists
            if (empty($filePath) || empty($fileSize) || !file_exists($filePath)) {
                throw new Exception("The file has not been uploaded properly. (44352)");
            }

            // check if the file is an uploaded file
            if (!is_uploaded_file($filePath)) {
                throw new Exception("The file has not been uploaded properly. (44353)");
            }

            // check the uploaded file extension
            $pathInfo = pathinfo($fileName);

            if (!in_array(strtolower($pathInfo['extension']), $allowedExtensions)) {
                throw new Exception("Invalid file extension. The allowed extensions are: jpg, png, gif, svg.");
            }

            // check if dstFile has an allowed extension (allow only no extension, svg, png, jpg and gif)
            $pathInfo = pathinfo($targetFile);

            if (!empty($pathInfo['extension']) && !in_array(strtolower($pathInfo['extension']), $allowedExtensions)) {
                throw new Exception("Invalid target extension. (44354)");
            }

            // check if target dir exists and try creating it if it doesn't
            if (!self::makeDir(dirname($targetFile))) {
                throw new Exception("Cannot create target directory or the directory is not writable. (35112)");
            }

            // delete the target file is if exists
            if (file_exists($targetFile) && !@unlink($targetFile)) {
                throw new Exception("Cannot overwrite the target file. (44356).");
            }

            // get the image mime type
            $type = self::getFileMimeType($filePath);

            // open the image
            switch ($type) {
                case "image/jpeg":
                    $image = imagecreatefromjpeg($filePath);
                    break;

                case "image/png":
                    $image = imagecreatefrompng($filePath);
                    break;

                case "image/gif":
                    $image = imagecreatefromgif($filePath);
                    break;

                case "image/svg":
                    $sanitizer = new Sanitizer();
                    $image = $sanitizer->sanitize(file_get_contents($filePath));
                    break;

                case "image/x-icon":
                    $image = "image";
                    break;

                default:
                    $image = false;
            }

            if (!$image) {
                throw new Exception("This file is not a valid image.");
            }

            // if new width and height are specified, resize the image
            if ($maxWidth && $maxHeight && $type != "image/svg" && $type != "image/x-icon") {
                // get the original image size
                $width = imagesx($image);
                $height = imagesy($image);

                // resize only if current dimensions are larger than the new dimensions
                if ($width > $maxWidth || $height > $maxHeight) {
                    // adjust new height or width to retain the aspect ratio
                    if ($width / $maxWidth > $height / $maxHeight) {
                        $maxHeight = (int)round($maxWidth / ($width / $height));
                    } else {
                        $maxWidth = (int)round($maxHeight / ($height / $width));
                    }

                    $newImage = imagecreatetruecolor($maxWidth, $maxHeight);

                    // preserve transparency (gif is more complicated, don't worry about it for now)
                    if ($type == "image/png") {
                        imagealphablending($newImage, false);
                        $transparent = imagecolorallocatealpha($newImage, 255, 255, 255, 127);
                        imagefilledrectangle($newImage, 0, 0, $maxWidth, $maxHeight, $transparent);
                    }

                    // copy the image content
                    if (!imagecopyresampled($newImage, $image, 0, 0, 0, 0, $maxWidth, $maxHeight, $width, $height)) {
                        throw new Exception("Cannot resample image.");
                    }

                    // destroy the original image, and assign new image to old var so the saving can continue below
                    imagedestroy($image);
                    $image = $newImage;
                }
            }

            // save the image to the target file
            if ($forceTargetType && $type != "image/svg") {
                $type = $forceTargetType;
            }

            $targetFile = preg_replace('/\.(jpeg|jpg|png|gif|svg|ico)$/i', '', $targetFile);

            switch ($type) {
                case "image/jpeg":
                    $targetFile .= ".jpg";
                    $result = imagejpeg($image, $targetFile, 75);
                    break;

                case "image/png":
                    $targetFile .= ".png";
                    imagesavealpha($image , true); // preserve png transparency
                    $result = imagepng($image, $targetFile, 9);
                    break;

                case "image/gif":
                    $targetFile .= ".gif";
                    $result = imagegif($image, $targetFile);
                    break;

                case "image/svg":
                    $targetFile .= ".svg";
                    $result = file_put_contents($targetFile, $image);
                    break;

                case "image/x-icon":
                    $targetFile .= ".ico";
                    $result = copy($filePath, $targetFile);
                    break;

                default:
                    $result = false;
            }

            // verify if the image was successfully saved
            if (!$result || !file_exists($targetFile)) {
                throw new Exception("Cannot save the uploaded image (44356).");
            }

            // verify the target file mime type
            if (!in_array($newType = self::getFileMimeType($targetFile), $allowedTypes)) {
                unlink($targetFile);
                throw new Exception("Cannot save the uploaded image (44357). [$newType]");
            }

            // remove the source file and image resource
            @unlink($filePath);

            if ($type != "image/svg" && $type != "image/x-icon") {
                imagedestroy($image);
            }

            return $targetFile;

        } catch (Exception $e) {
            if ($image && $type != "image/svg" && $type != "image/x-icon") {
                imagedestroy($image);
            }

            if (file_exists($filePath)) {
                @unlink($filePath);
            }

            $error = $e->getMessage();
            return false;
        }
    }

    /**
     * Returns the base url.
     *
     * @return string
     */
    public static function baseUrl(): string
    {
        return (@$_SERVER['HTTPS'] == 'on' ? 'https' : 'http') . "://" . rtrim($_SERVER['HTTP_HOST'], "/");
    }
}
