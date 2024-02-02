<?php

namespace core\api\form\elements;

use JsonSerializable;

class Image implements JsonSerializable
{

    public const TYPE_URL = "url";
    public const TYPE_PATH = "path";

    private string $type;
    private string $data;

    public function __construct(string $data, string $type = self::TYPE_PATH)
    {
        $this->type = $type;
        $this->data = $data;
    }

    public function getType(): string
    {
        return $this->type;
    }

    public function getData(): string
    {
        return $this->data;
    }

    public function jsonSerialize(): array
    {
        return [
            "type" => $this->type,
            "data" => $this->data
        ];
    }
}