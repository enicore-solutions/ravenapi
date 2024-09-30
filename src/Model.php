<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

class Model
{
    use InjectionManual;

    protected string $table = ""; // can be overloaded in the derived classes
    protected array $publicFields = [];
    protected array $data = [];
    private bool $loaded = false;

    public function __construct($id = 0)
    {
        $this->setDependencies();

        // set the table name to plural of the model name
        if (empty($this->table)) {
            $array = explode("\\", get_class($this));
            $this->table = strtolower(array_pop($array)) . "s";
        }

        // add id to the public fields, we should always send it to the frontend and always have it encoded
        $this->publicFields["id"] = ["type" => "integer", "encoded" => true, "default" => ""];

        // run onInit in the derived classes
        $this->onInit();

        // if the id is specified, load the data
        if (!empty($id)) {
            $this->load($id);
        }
    }

    /**
     * Shortcut to get the data values.
     * @param string $name
     * @return mixed
     */
    public function __get(string $name): mixed
    {
        return $this->get($name);
    }

    public function get(string $name): mixed
    {
        return $this->data[$name] ?? null;
    }

    /**
     * Returns the array of the object's data that corresponds to the db record.
     * @return array
     */
    public function getData(): array
    {
        return $this->data;
    }

    public function set(string $key, $value): void
    {
        if (!empty($key)) {
            $this->data[$key] = $value;
        }
    }

    /**
     * Sets the object's data that will be saved to the database.
     * @param array $data
     * @return $this
     */
    public function setData(array $data): static
    {
        $this->data = $data;
        return $this;
    }

    /**
     * Returns the data as specified in publicFields for use in the frontend. It encodes the required ids and converts the data to the
     * correct types.
     * @return array
     */
    public function getPublicData(): array
    {
        $data = [];
        $fields = $this->publicFields;


        foreach ($fields as $key => $options) {
            $data[$key] = $this->id ? $this->data[$key] ?? "" : $options['default'] ?? "";
            switch ($options["type"]) {
                case "int":
                case "integer":
                    if (empty($options['encoded'])) {
                        $data[$key] = (string)(int)$data[$key];
                    } else {
                        // if the value is empty or zero, don't encode it
                        $data[$key] = (int)$data[$key] ? $this->code->encodeId((int)$data[$key]) : "";
                    }
                    break;
                case "bool":
                case "boolean":
                    $data[$key] = $data[$key] ? "1" : "0";
                    break;
                default:
                    $data[$key] = (string)$data[$key];
            }
        }

        return $data;
    }

    /**
     * Validates the data coming from the frontend, decodes it and sets it into internal record data that can be saved to the db.
     * Note that id is always included in the public fields so we can send it encoded to the frontend, but we're not overwriting it here.
     * @param array $data
     * @param array $errors
     * @return bool
     */
    public function setPublicData(array $data, array &$errors = []): bool
    {
        if (!$this->decodeAndValidate($data, $errors)) {
            return false;
        }

        $this->beforeSetPublicData($data);

        foreach ($this->publicFields as $key => $options) {
            if ($key != "id") {
                $this->data[$key] = $data[$key];
            }
        }

        return true;
    }

    /**
     * Decodes and validates the data coming from the frontend. The decoding is done directly on the passed data. Returns the validation
     * result and the list of errors.
     * @param array $data
     * @param array $errors
     * @return bool
     */
    public function decodeAndValidate(array &$data, array &$errors = []): bool
    {
        $errors = [];

        foreach ($this->publicFields as $key => $options) {
            $data[$key] = $data[$key] ?? "";

            if (!empty($options['required']) && empty($data[$key])) {
                $errors[$key] = "This value is required";
                return false;
            }

            $data[$key] = match ($options["type"]) {
                "int", "integer" => empty($options['encoded']) ? (int)$data[$key] : (int)$this->code->decodeId($data[$key]),
                "bool", "boolean" => $data[$key] ? 1 : 0,
                default => (string)$data[$key],
            };
        }

        return $this->onValidate($data, $errors);
    }

    /**
     * Loads the record from the database.
     * @param $id
     * @return int|bool - the ID of the loaded record
     */
    public function load($id): int|bool
    {
        $this->loaded = false;

        if (empty($id)) {
            return false;
        }

        // if the id is not a number, try decoding it
        if (is_string($id) && !is_numeric($id)) {
            $id = $this->code->decodeId($id);
        }

        if (!empty($id) && $data = $this->db->getFirst($this->table, "*", ["id" => $id])) {
            $this->data = $this->afterLoad($data);
            $this->loaded = true;
            return $id;
        }

        return false;
    }

    /**
     * Returns true if the last load() operation was successful; false otherwise. This is useful if we pass the id to the constructor to
     * load the data -- in this case we don't know if the load was successful, so we can check it using this function.
     * @return bool
     */
    public function isLoaded(): bool
    {
        return $this->loaded;
    }

    /**
     * Saves the record to the database. Parameters will be passed to beforeSave and afterSave that can be overwritten in the derived
     * classes.
     * @param array $parameters
     * @return int|bool
     */
    public function save(array $parameters = []): int|bool
    {
        // before save callback - throw an exception to abort
        try {
            $data = $this->beforeSave($this->data, $parameters);
        } catch (\Exception $e) {
            return false;
        }

        if (empty($data['id']) || !$this->db->getFirst($this->table, "id", ["id" => $data['id']])) {
            if (!$this->db->insert($this->table, $data)) {
                return false;
            }
            $this->data['id'] = $this->db->getLastInsertId();
            $newRecord = true;
        } else {
            isset($data['modified_at']) && ($data['modified_at'] = date("Y-m-d H:i:s"));
            if (!$this->db->update($this->table, $data, ["id" => $data['id']])) {
                return false;
            }
            $newRecord = false;
        }

        $this->afterSave($newRecord, $parameters);

        return $this->data['id'];
    }

    public function delete(): bool
    {
        if (empty($this->data['id'])) {
            return false;
        }

        // before delete callback - throw an exception to abort
        try {
            $this->beforeDelete();
        } catch (\Exception $e) {
            return false;
        }

        if (!$this->db->delete($this->table, ["id" => $this->data['id']])) {
            return false;
        }

        $this->afterDelete();

        $this->data['id'] = false;
        return true;
    }

    /**
     * Gets called after the object is created. Can be overloaded in the derived classes.
     */
    protected function onInit()
    {
    }

    protected function onValidate(&$data, array &$errors): bool
    {
        return true;
    }

    /**
     * Called after the data is loaded from the db. Takes the loaded data as a parameter and should return the data that will be loaded
     * into the model's data array.
     * @param array $data
     * @return array
     */
    protected function afterLoad(array $data): array
    {
        return $data;
    }

    /**
     * Called before the data from post is applied to the model. The new data can be changed here before it's applied.
     * @param $publicData
     * @return void
     */
    protected function beforeSetPublicData(&$publicData): void
    {
    }

    /**
     * Called before the data is saved to the database. Takes the data that is about to be saved as a parameter and returns the data that
     * will be saved. The returned data does not overwrite the current model's data.
     * @param array $data
     * @param array $parameters - this array is passed on to this function from save()
     * @return array
     */
    protected function beforeSave(array $data, array $parameters): array
    {
        return $data;
    }

    /**
     * @param bool $newRecord - is true if the record was inserted; false if it was updated
     * @param array $parameters - this array is passed on to this function from save()
     */
    protected function afterSave(bool $newRecord, array $parameters)
    {
    }

    protected function beforeDelete(): void
    {
    }

    protected function afterDelete(): void
    {
    }
}
