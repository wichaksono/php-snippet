<?php

declare(strict_types=1);

namespace Abstracts;

use AttributeTrait;
use Element;

/**
 * Abstract class FormAbstract
 *
 * This class provides a structure for building HTML forms and managing form attributes.
 * It includes methods to set various form attributes and open/close form tags.
 */
abstract class FormAbstract
{
    use AttributeTrait;

    /**
     * @var array<string, mixed> $formAttributes
     *
     * Default form attributes, including charset, action, method, etc.
     */
    protected array $formAttributes = [
        'accept-charset' => 'UTF-8',
        'action'         => '',
        'autocomplete'   => '',
        'enctype'        => '',
        'method'         => 'GET',
        'name'           => '',
        'novalidate'     => false,
        'rel'            => '',
        'target'         => '',
        'echo'           => false
    ];

    /**
     * @var Element $element
     *
     * Instance of the Element class used to generate form elements like inputs, buttons, etc.
     */
    public Element $element;

    /**
     * FormAbstract constructor.
     *
     * Initializes form attributes and creates a new Element instance.
     *
     * @param array<string, mixed> $formAttributes Form attributes to override default values.
     */
    public function __construct(array $formAttributes = [])
    {
        $this->formAttributes = array_merge($this->formAttributes, $formAttributes);
        $this->element        = new Element($this->formAttributes['echo']);
    }

    /**
     * Set a specific form attribute.
     *
     * @param string $attribute The name of the form attribute to set.
     * @param string $value The value of the attribute.
     *
     * @return void
     */
    public function setAttribute(string $attribute, string $value): void
    {
        $this->formAttributes[$attribute] = $value;
    }

    /**
     * Set the form's accept-charset attribute.
     *
     * @param string $acceptCharset The charset to accept (e.g., UTF-8).
     *
     * @return void
     */
    public function setAcceptCharset(string $acceptCharset): void
    {
        $this->formAttributes['accept-charset'] = $acceptCharset;
    }

    /**
     * Set the form's action attribute.
     *
     * @param string $action The URL where the form will be submitted.
     *
     * @return void
     */
    public function setAction(string $action): void
    {
        $this->formAttributes['action'] = $action;
    }

    /**
     * Set the form's autocomplete attribute.
     *
     * @param string $autocomplete The autocomplete setting (e.g., 'on' or 'off').
     *
     * @return void
     */
    public function setAutocomplete(string $autocomplete): void
    {
        $this->formAttributes['autocomplete'] = $autocomplete;
    }

    /**
     * Set the form's enctype attribute.
     *
     * @param string $enctype The encoding type for the form (e.g., 'multipart/form-data').
     *
     * @return void
     */
    public function setEnctype(string $enctype): void
    {
        $this->formAttributes['enctype'] = $enctype;
    }

    /**
     * Set the form's method attribute.
     *
     * @param string $method The HTTP method to use for form submission (e.g., 'POST', 'GET').
     *
     * @return void
     */
    public function setMethod(string $method): void
    {
        $this->formAttributes['method'] = $method;
    }

    /**
     * Set the form's name attribute.
     *
     * @param string $name The name of the form.
     *
     * @return void
     */
    public function setName(string $name): void
    {
        $this->formAttributes['name'] = $name;
    }

    /**
     * Set the form's novalidate attribute.
     *
     * @param bool $novalidate Whether to disable form validation.
     *
     * @return void
     */
    public function setNovalidate(bool $novalidate): void
    {
        $this->formAttributes['novalidate'] = $novalidate;
    }

    /**
     * Set the form's rel attribute.
     *
     * @param string $rel The relationship between the current page and the linked resource.
     *
     * @return void
     */
    public function setRel(string $rel): void
    {
        $this->formAttributes['rel'] = $rel;
    }

    /**
     * Set the form's target attribute.
     *
     * @param string $target The target window or frame where the form response will be displayed.
     *
     * @return void
     */
    public function setTarget(string $target): void
    {
        $this->formAttributes['target'] = $target;
    }

    /**
     * Open the form tag with the given attributes.
     *
     * @param array<string, mixed> $attributes Additional attributes to include in the form tag.
     *
     * @return string The opening form tag with attributes.
     */
    public function open(array $attributes = []): string
    {
        $this->formAttributes = array_merge($this->formAttributes, $attributes);
        return '<form ' . $this->attributes($this->formAttributes) . '>';
    }

    /**
     * Close the form tag.
     *
     * @return string The closing form tag.
     */
    public function close(): string
    {
        return '</form>';
    }
}
