<?php

namespace core\api\form;

use Closure;
use pocketmine\player\Player;
use pocketmine\utils\Utils;

abstract class Form implements \pocketmine\form\Form
{
    protected const TYPE_MODAL = "modal";
    protected const TYPE_MENU = "form";
    protected const TYPE_CUSTOM_FORM = "custom_form";
    protected ?Closure $onSubmit = null;
    protected ?Closure $onClose = null;
    private string $title;

    public function __construct(string $title)
    {
        $this->title = $title;
    }


    public function setTitle(string $title): self
    {
        $this->title = $title;
        return $this;
    }

    public function onSubmit(Closure $onSubmit): self
    {
        Utils::validateCallableSignature($this->getOnSubmitCallableSignature(), $onSubmit);
        $this->onSubmit = $onSubmit;
        return $this;
    }

    abstract protected function getOnSubmitCallableSignature(): callable;

    public function onClose(Closure $onClose): self
    {
        Utils::validateCallableSignature(function (Player $player): void {
        }, $onClose);
        $this->onClose = $onClose;
        return $this;
    }

    public function jsonSerialize(): array
    {
        return array_merge(
            ["title" => $this->title, "type" => $this->getType()],
            $this->serializeFormData()
        );
    }

    abstract protected function getType(): string;

    abstract protected function serializeFormData(): array;
}