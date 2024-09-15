<?php

declare(strict_types=1);

/**
 * Class Element
 *
 * This class generates various HTML form elements like input, label, textarea, select, and button.
 * It utilizes the `AttributeTrait` for handling HTML attributes.
 */
class Element
{
    use AttributeTrait;

    /**
     * @var bool $echo
     *
     * Determines whether the generated HTML elements are echoed directly or returned as a string.
     */
    private bool $echo;

    /**
     * Element constructor.
     *
     * @param bool $echo Determines if HTML should be echoed directly. Defaults to true.
     */
    public function __construct(bool $echo = true)
    {
        $this->echo = $echo;
    }

    /**
     * Generate a label element.
     *
     * @param string $id The ID of the related input element.
     * @param string $title The text to display within the label.
     * @param array<string, string> $attributes Optional HTML attributes for the label element.
     *
     * @return string The HTML string for the label element.
     */
    public function label(string $id, string $title, array $attributes = []): string
    {
        $attr  = $this->attributes($attributes);
        $label = "<label for='$id' $attr>$title</label>";

        return $this->output($label);
    }

    /**
     * Generate an input element.
     *
     * @param string $type The input type (e.g. 'text', 'password', 'checkbox', 'radio').
     * @param string $id The ID of the input element.
     * @param array<string, string> $controls Additional properties like 'name', 'value', and custom attributes.
     *
     * @return string The HTML string for the input element.
     */
    public function input(string $type, string $id, array $controls = []): string
    {
        if ($this->validateId($id)) {
            return '';
        }

        $controls['type'] = $type;
        $controls['id']   = $id;

        if ( isset($controls['checked']) && $controls['checked'] ) {
            $controls['checked'] = 'checked';
        } else {
            unset($controls['checked']);
        }

        $attr  = $this->controls($controls);
        $input = "<input $attr />";

        return $this->output($input);
    }

    /**
     * Generate a textarea element.
     *
     * @param string $id The ID of the textarea element.
     * @param array<string, string> $controls Additional properties like 'name', 'value', and custom attributes.
     *
     * @return string The HTML string for the textarea element.
     */
    public function textarea(string $id, array $controls = []): string
    {
        if ($this->validateId($id)) {
            return '';
        }

        $controls['id'] = $id;
        $value          = $controls['value'] ?? '';
        unset($controls['value']);
        $attr     = $this->controls($controls);
        $textarea = "<textarea $attr>$value</textarea>";

        return $this->output($textarea);
    }

    /**
     * Generate a select element with options.
     *
     * @param string $id The ID of the select element.
     * @param array<string, string> $options The available options in 'value' => 'label' format.
     * @param array<string, string> $controls Additional properties like 'name', and custom attributes.
     *                                          Set 'selected_value' to preselect an option.
     *
     * @return string The HTML string for the select element with options.
     */
    public function select(string $id, array $options = [], array $controls = []): string
    {
        if ($this->validateId($id)) {
            return '';
        }

        $controls['id'] = $id;
        $selectedValue  = $controls['value'] ?? null;
        unset($controls['value']);

        $attr = $this->controls($controls);
        $optionsHtml = '';
        foreach ($options as $value => $label) {
            $selected = ($value == $selectedValue) ? ' selected' : '';
            $optionsHtml .= "<option value='$value'$selected>$label</option>";
        }

        $select = "<select $attr>$optionsHtml</select>";

        return $this->output($select);
    }

    /**
     * Generate a button element.
     *
     * @param string $type The type of button (e.g. 'submit', 'button', 'reset').
     * @param string $id The ID of the button element.
     * @param string $title The text to display on the button.
     * @param array<string, string> $controls Additional properties like 'name' and custom attributes.
     *
     * @return string The HTML string for the button element.
     */
    public function button(string $type, string $id, string $title, array $controls = []): string
    {
        if ($this->validateId($id)) {
            return '';
        }

        $controls['type'] = $type;
        $controls['id']   = $id;
        $attr             = $this->controls($controls);
        $button           = "<button $attr>$title</button>";

        return $this->output($button);
    }

    /**
     * Convert an array of controls to an HTML attribute string.
     *
     * @param array{
     *     id: string,
     *     name?: string,
     *     value?: string,
     *     attributes?: array<string, string>
     * } $controls An array of control properties, including 'id', 'name', 'value', and other custom attributes.
     *
     * @return string A string of HTML attributes based on the controls provided.
     */
    private function controls(array $controls): string
    {
        $attributes = $controls['attributes'] ?? [];
        unset($controls['attributes']);
        $attributes = array_merge($controls, $attributes);
        return $this->attributes($attributes);
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
