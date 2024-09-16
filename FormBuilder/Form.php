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
     * @var array<string> A stack to store the opened tag names.
     */
    private array $openTags = [];

    /**
     * Start an HTML container element with the given tag name and attributes.
     *
     * @param string $tagName The name of the HTML element.
     * @param array<string, string> $attributes Optional HTML attributes for the element.
     * @return string The opening tag of the element with attributes.
     */
    public function startElement(string $tagName, array $attributes = []): string
    {
        // Push the tag name to the stack
        $this->openTags[] = $tagName;

        $html = "<{$tagName} {$this->attributes($attributes)}>";

        // Echo or return based on $echo property
        return $this->output($html);
    }

    /**
     * End the last opened HTML container element.
     *
     * @return string The closing tag for the last opened container element.
     */
    public function endElement(): string
    {
        // Get the last opened tag from the stack
        $tagName = array_pop($this->openTags);

        if (!$tagName) {
            return ''; // No tag to close
        }

        $html = "</{$tagName}>";

        // Echo or return based on $echo property
        return $this->output($html);
    }

    /**
     * Output the generated HTML element.
     *
     * @param string $element The HTML element string.
     * @return string The HTML element, either echoed or returned as a string.
     */
    private function output(string $element): string
    {
        if ($this->echo) {
            echo $element;
        }

        return $element;
    }
}
