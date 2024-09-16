<?php
declare(strict_types=1);

/**
 * Trait AttributeTrait
 *
 * Provides methods to handle HTML attributes and element IDs.
 */
trait AttributeTrait
{
    /**
     * Collection of element IDs to track uniqueness.
     *
     * @var array<string, bool>
     */
    private array $idCollection = [];

    /**
     * Convert an associative array to an HTML attribute string.
     *
     * @param array<string, string> $attributes Associative array where key is the attribute name and value is the attribute value.
     * @return string The formatted HTML attribute string.
     */
    private function attributes(array $attributes): string
    {
        $html = '';
        foreach ($attributes as $key => $value) {
            if (is_array($value)) {
                $value = json_encode($value);
            }

            $html .= "$key='$value' ";
        }
        return trim($html);
    }

    /**
     * Check if an ID already exists in the ID collection.
     *
     * @param string $id The ID to check.
     * @return bool True if the ID exists, false otherwise.
     */
    private function isIdExist(string $id): bool
    {
        return isset($this->idCollection[$id]);
    }

    /**
     * Validate if an ID already exists in the collection.
     *
     * @param string $id The ID to validate.
     * @return bool True if the ID is already used, otherwise false.
     */
    private function validateId(string $id): bool
    {
        if ($this->isIdExist($id)) {
            echo 'ID already exists';
            return true;
        }

        $this->idCollection[$id] = true;
        return false;
    }
}
