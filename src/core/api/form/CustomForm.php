<?php

namespace core\api\form;

use Closure;
use core\api\form\elements\Element;
use pocketmine\form\FormValidationException;
use pocketmine\player\Player;

class CustomForm extends Form
{
    /** @var Element[] */
    private array $elements = [];

    public function __construct(string $title, array $elements, Closure $onSubmit, ?Closure $onClose = null)
    {
        parent::__construct($title);
        $this->elements = $elements;
        $this->onSubmit($onSubmit);
        if ($onClose !== null) {
            $this->onClose($onClose);
        }
    }

    /**
     * @param Element ...$elements
     * @return self
     */
    public function append(Element ...$elements): self
    {
        $this->elements = array_merge($this->elements, $elements);
        return $this;
    }

    final public function handleResponse(Player $player, $data): void
    {
        if ($data === null) {
            if ($this->onClose !== null) {
                ($this->onClose)($player);
            }
        } elseif (is_array($data)) {
            foreach ($data as $index => $value) {
                if (!isset($this->elements[$index])) {
                    throw new FormValidationException("Element at index $index does not exist");
                }
                $element = $this->elements[$index];
                $element->validate($value);
                $element->setValue($value);
            }
            ($this->onSubmit)($player, new CustomFormResponse($this->elements));
        } else {
            throw new FormValidationException("Expected array or null, got " . gettype($data));
        }
    }

    /**
     * @return string
     */
    protected function getType(): string
    {
        return self::TYPE_CUSTOM_FORM;
    }

    /**
     * @return callable
     */
    protected function getOnSubmitCallableSignature(): callable
    {
        return function (Player $player, CustomFormResponse $response): void {
        };
    }

    /**
     * @return array
     */
    protected function serializeFormData(): array
    {
        return ["content" => $this->elements];
    }
}