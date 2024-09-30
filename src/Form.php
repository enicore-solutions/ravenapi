<?php
/**
 * Raven API Framework.
 * Copyright 2024 Enicore Solutions.
 */
namespace RavenApi;

/**
 * There are two concepts we're dealing with here:
 * 1. Elements - which is the list of all the html element information including the value. Those can be stored in a separate file
 *    to make things cleaner.
 * 2. Data - which is a key/value list of the element key => element value.
 *
 * There are functions to set elements and data. Data can be set from any key/value source, for example from the database. So
 * initially we set up the elements and set the data with validation turned off. Then we send the elements to the frontend, render
 * the form, and get the elements back - but with the value fields updated. Then we need to extract the data from that elements array
 * (extractDataFromElements()) and set that data with validation turned on. This updates the elements' values and error fields
 * which we can then send back to the frontend and save in the db.
 */

class Form
{
    use Injection;

    private array $elements = [];
    private array $inputs = ["text", "textarea", "select", "check", "image", "color", "custom"];

    public function __construct(array $elements = [])
    {
        $this->setElements($elements);
    }

    /**
     * Returns the elements array.
     * @return array
     */
    public function getElements(): array
    {
        return $this->elements;
    }

    public function sendErrorResponse(): void
    {
        $this->response->error("Correct the highlighted errors.", ["elements" => $this->elements]);
    }

    /**
     * Sets the elements array adding the missing properties.
     * @param array $elements
     */
    public function setElements(array $elements): void
    {
        $this->elements = [];

        foreach ($elements as $key => $element) {
            // make sure the input is supported
            if (!isset($element['input']) || !in_array($element['input'], $this->inputs)) {
                continue;
            }

            // add the missing properties
            isset($element['title']) || ($element['title'] = "");
            isset($element['info']) || ($element['info'] = "");
            isset($element['tab']) || ($element['tab'] = "");
            isset($element['value']) || ($element['value'] = "");
            isset($element['htmlBefore']) || ($element['htmlBefore'] = "");
            isset($element['htmlAfter']) || ($element['htmlAfter'] = "");
            $element['input'] == "select" && !isset($element['options']) && ($element['options'] = []);
            $element['input'] == "text" && !isset($element['placeholder']) && ($element['placeholder'] = "");

            $this->elements[$key] = $element;
        }
    }

    /**
     * Sets a particular property of an element.
     * @param string $key
     * @param string $property
     * @param $value
     */
    public function setElementProperty(string $key, string $property, $value): void
    {
        if (isset($this->elements[$key])) {
            $this->elements[$key][$property] = $value;
        }
    }

    /**
     * Iterates the elements and sets their values according to the key/value $data array. If $validate is true, validation is performed.
     * Note that the element values are set regardless of whether there are errors or not, because we'll be sending the elements back
     * to the frontend to show the errors, and if the values are not set, the html form will lose the user-set values.
     * @param array $data
     * @param bool $validate
     * @return bool
     */
    public function setData(array $data, bool $validate = true): bool
    {
        $result = true;

        // reset all the error properties
        foreach ($this->elements as $key => $element) {
            $this->elements[$key]['error'] = false;
        }

        foreach ($this->elements as $key => $element) {
            // make sure the value exists in data
            isset($data[$key]) || ($data[$key] = "");

            // validate the data according to the rules set in the element
            if ($validate) {
                if (!empty($element['required']) && empty($data[$key])) {
                    $this->elements[$key]['error'] = "This value is required.";
                    $result = false;
                }

                if (!empty($element['required_unique'])) {
                    if ($this->db->row(
                        "SELECT id FROM `{$element['required_unique']}` WHERE $key = ? AND (id != ? OR ? IS NULL) LIMIT 1",
                        [$data[$key], $data['id'], $data['id']])
                    ) {
                        $this->elements[$key]['error'] = "A record with this value already exists.";
                        $result = false;
                    }
                }

                if (!empty($element['required_length']) && strlen($data[$key]) != $element['required_length']) {
                    $this->elements[$key]['error'] = "This value must be {$element['required_length']} characters long.";
                    $result = false;
                }

                if ($element['input'] == "select" && !array_key_exists($data[$key], $element['options'])) {
                    // validate selects: check if the incoming value is in the keys of the select options
                    $this->elements[$key]['error'] = "This selection is invalid";
                    $result = false;
                }
            }

            // convert the data to the correct format (all the values from the frontend come in as strings)
            $this->elements[$key]['value'] = match ($element["input"]) {
                "check" => (int)($data[$key] == "1"),
                default => (string)$data[$key],
            };

            if ($element["input"] == "image" && !empty($element['value'])) {
                $this->elements[$key]['uploaded'] = true;
            }
        }

        return $result;
    }

    public function setDataFromRequest($recordId): bool
    {
        return $this->setDataFromElements($recordId, $this->request->get("elements"));
    }

    public function setDataFromElements($recordId, $elements): bool
    {
        // if the id is not a number, try decoding it
        if (is_string($recordId) && !is_numeric($recordId)) {
            $recordId = $this->code->decodeId($recordId);
        }

        return $this->setData(['id' => $recordId] + self::extractDataFromElements($elements));
    }

    /**
     * Returns the data extracted from the elements array - in the key/value array format.
     * @return array
     */
    public function getData(): array
    {
        return $this->extractDataFromElements($this->elements);
    }

    public function setErrors(array $errors): void
    {
        foreach ($errors as $key => $value) {
            if (array_key_exists($key, $this->elements) && !empty($value)) {
                $this->elements[$key]['error'] = $value;
            }
        }
    }

    /**
     * Extracts the data from the elements and returns it in the key/value format.
     * @param $elements
     * @return array
     */
    public static function extractDataFromElements($elements): array
    {
        $data = [];

        if (is_array($elements)) {
            foreach ($elements as $key => $element) {
                $data[$key] = $element['value'] ?? "";
            }
        }

        return $data;
    }
}
