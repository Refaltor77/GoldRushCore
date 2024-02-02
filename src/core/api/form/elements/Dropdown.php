<?php

namespace core\api\form\elements;

use pocketmine\form\FormValidationException;

class Dropdown extends Element
{

    private array $options = [];
    private int $default = 0;

    public function __construct(string $text, array $options, int $default = 0)
    {
        parent::__construct($text);
        $this->options = $options;
        $this->default = $default;
    }

    public function getOptions(): array
    {
        return $this->options;
    }

    public function getSelectedOption(): string
    {
        return $this->options[$this->value];
    }

    public function getDefault(): int
    {
        return $this->default;
    }

    public function getType(): string
    {
        return "dropdown";
    }

    public function serializeElementData(): array
    {
        return [
            "options" => $this->options,
            "default" => $this->default
        ];
    }

    public function validate($value): void
    {
        parent::validate($value);
        if (!isset($this->options[$value])) {
            throw new FormValidationException("Option with index $value does not exist in dropdown");
        }
    }
}