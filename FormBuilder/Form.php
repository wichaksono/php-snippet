<?php
declare(strict_types=1);

use Abstracts\FormAbstract;

/**
 * Class Form
 *
 * A class for managing form elements, extending the abstract Form functionalities.
 */
class Form extends FormAbstract
{
    use AttributeTrait;

    /**
     * Start an HTML container element with the given ID and attributes.
     *
     * @param string $elementId  The ID of the element.
     * @param array<string, string> $attributes Optional HTML attributes for the element.
     * @return string The opening tag of the element with attributes.
     */
    public function startElement(string $elementId, array $attributes = []): string
    {
        if ($this->validateId($elementId)) {
            exit;
        }

        return '<div id="' . $elementId . '" ' . $this->attributes($attributes) . '>';
    }

    /**
     * End an HTML container element.
     *
     * @return string The closing tag for a container element.
     */
    public function endElement(): string
    {
        return '</div>';
    }

}
