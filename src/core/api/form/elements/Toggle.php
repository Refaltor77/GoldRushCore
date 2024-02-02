<?php

namespace core\api\form\elements;

use pocketmine\form\FormValidationException;
use function is_bool;

class Toggle extends Element
{

    protected bool $default = false;

    public function __construct(string $text, bool $default = false)
    {
        parent::__construct($text);
        $this->default = $default;
    }

    public function getValue(): bool
    {
        return parent::getValue();
    }

    public function hasChanged(): bool
    {
        return $this->default !== $this->value;
    }

    public function getDefault(): bool
    {
        return $this->default;
    }

    public function getType(): string
    {
        return "toggle";
    }

    public function serializeElementData(): array
    {
        return [
            "default" => $this->default
        ];
    }

    public function validate($value): void
    {
        if (!is_bool($value)) {
            throw new FormValidationException("Expected bool, got " . gettype($value));
        }
    }
}