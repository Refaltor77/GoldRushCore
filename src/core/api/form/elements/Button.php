<?php

namespace core\api\form\elements;

class Button extends Element
{

    private ?Image $image;
    private string $type;

    public function __construct(string $text, ?Image $image = null)
    {
        parent::__construct($text);
        $this->image = $image;
    }

    public function getType(): ?string
    {
        return null;
    }

    public function serializeElementData(): array
    {
        $data = ["text" => $this->text];
        if ($this->hasImage()) {
            $data["image"] = $this->image;
        }
        return $data;
    }

    public function hasImage(): bool
    {
        return $this->image !== null;
    }
}