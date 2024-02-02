<?php

namespace core\api\form;

use Closure;
use core\api\form\elements\Image;

class ServerSettingsForm extends CustomForm
{

    private ?Image $image;

    /**
     * @param string $title
     * @param               $elements
     * @param Image|null $image
     * @param Closure $onSubmit
     * @param Closure|null $onClose
     */
    public function __construct(string $title, $elements, ?Image $image, Closure $onSubmit, ?Closure $onClose = null)
    {
        parent::__construct($title, $elements, $onSubmit, $onClose);
        $this->image = $image;
    }

    public function serializeFormData(): array
    {
        $data = parent::serializeFormData();
        if ($this->hasImage()) {
            $data["icon"] = $this->image;
        }
        return $data;
    }

    public function hasImage(): bool
    {
        return $this->image !== null;
    }
}