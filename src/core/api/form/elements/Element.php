<?php

namespace core\api\form\elements;

use JsonSerializable;
use pocketmine\form\FormValidationException;

abstract class Element implements JsonSerializable
{

    protected string $text;
    protected $value;

    public function __construct(string $text)
    {
        $this->text = $text;
    }

    public function getValue()
    {
        return $this->value;
    }

    public function setValue($value)
    {
        $this->value = $value;
    }

    final public function jsonSerialize(): array
    {
        $array = ["text" => $this->getText()];
        if ($this->getType() !== null) {
            $array["type"] = $this->getType();
        }
        return $array + $this->serializeElementData();
    }

    public function getText(): string
    {
        return $this->text;
    }

    abstract public function getType(): ?string;

    abstract public function serializeElementData(): array;

    public function validate($value): void
    {
        if (!is_int($value)) {
            throw new FormValidationException("Expected int, got " . gettype($value));
        }
    }
}