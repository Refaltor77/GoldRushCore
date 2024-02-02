<?php

namespace core\api\form\elements;

use pocketmine\form\FormValidationException;

class Input extends Element
{

    private string $placeholder;
    private string $default;

    public function __construct(string $text, string $placeholder, string $default = "")
    {
        parent::__construct($text);
        $this->placeholder = $placeholder;
        $this->default = $default;
    }

    public function getValue(): string
    {
        return parent::getValue();
    }


    public function getPlaceholder(): string
    {
        return $this->placeholder;
    }

    public function getDefault(): string
    {
        return $this->default;
    }

    public function getType(): string
    {
        return "input";
    }

    public function serializeElementData(): array
    {
        return [
            "placeholder" => $this->placeholder,
            "default" => $this->default
        ];
    }

    public function validate($value): void
    {
        if (!is_string($value)) {
            throw new FormValidationException("Expected string, got " . gettype($value));
        }
    }
}