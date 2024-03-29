<?php

namespace core\api\form\elements;

use InvalidArgumentException;
use pocketmine\form\FormValidationException;

class Slider extends Element
{

    private float $min = 0;
    private float $max = 0;
    private float $step = 1.0;
    private float $default;

    public function __construct(string $text, float $min, float $max, float $step = 1.0, ?float $default = null)
    {
        parent::__construct($text);
        if ($this->min > $this->max) {
            throw new InvalidArgumentException("Slider min value should be less than max value");
        }
        $this->min = $min;
        $this->max = $max;
        if ($default !== null) {
            if ($default > $this->max or $default < $this->min) {
                throw new InvalidArgumentException("Default must be in range $this->min ... $this->max");
            }
            $this->default = $default;
        } else {
            $this->default = $this->min;
        }
        if ($step <= 0) {
            throw new InvalidArgumentException("Step must be greater than zero");
        }
        $this->step = $step;
    }

    public function getValue()
    {
        return parent::getValue();
    }

    public function getMin(): float
    {
        return $this->min;
    }

    public function getMax(): float
    {
        return $this->max;
    }

    public function getStep(): float
    {
        return $this->step;
    }

    public function getDefault(): float
    {
        return $this->default;
    }

    public function getType(): string
    {
        return "slider";
    }

    public function serializeElementData(): array
    {
        return [
            "min" => $this->min,
            "max" => $this->max,
            "default" => $this->default,
            "step" => $this->step
        ];
    }

    public function validate($value): void
    {
        if (!is_int($value) && !is_float($value)) {
            throw new FormValidationException("Expected int or float, got " . gettype($value));
        }
    }
}