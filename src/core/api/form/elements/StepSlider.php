<?php

namespace core\api\form\elements;

class StepSlider extends Dropdown
{

    public function getType(): string
    {
        return "step_slider";
    }

    public function serializeElementData(): array
    {
        return [
            "steps" => $this->getOptions(),
            "default" => $this->getDefault()
        ];
    }
}