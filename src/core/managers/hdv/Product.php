<?php

namespace core\managers\hdv;

use pocketmine\nbt\tag\CompoundTag;
use pocketmine\network\mcpe\protocol\types\CacheableNbt;

class Product
{
    private string $id;
    private int $amount;
    private int $price;
    private string $xuidSeller;
    private string $uniqueId;
    public array $lore;
    private ?string $customName = null;
    private ?string $nbtSerialized = null;

    public function __construct(int $id, int $meta, int $amount, int $price, string $xuidSeller, ?CompoundTag $tag = null, ?string $customName = null, array $lore = [])
    {
        $this->id = $id;
        $this->meta = $meta;
        $this->amount = $amount;
        $this->price = $price;
        $this->xuidSeller = $xuidSeller;
        $this->customName = $customName;
        $this->lore = $lore;
        $this->uniqueId = uniqid();
        if (!is_null($tag)) {
            $nbtSerilized = new CacheableNbt($tag);
            $string = $nbtSerilized->getEncodedNbt();
            $this->nbtSerialized = $string;
        }
    }

    public function getNbt(): ?string
    {
        return $this->nbtSerialized;
    }

    public function setXuid(string $xuid): self
    {
        $this->xuidSeller = $xuid;
        return $this;
    }

    public function getLore(): array
    {
        return $this->lore;
    }

    public function getPrice(): int
    {
        return $this->price;
    }

    public function setPrice(int $price): self
    {
        $this->price = $price;
        return $this;
    }

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    public function getAmount(): int
    {
        return $this->amount;
    }

    public function setAmount(int $amount): self
    {
        $this->amount = $amount;
        return $this;
    }

    public function getCustomName(): ?string
    {
        return $this->customName;
    }

    public function getMeta(): int
    {
        return $this->meta;
    }

    public function setMeta(int $meta): self
    {
        $this->meta = $meta;
        return $this;
    }

    public function getXuid(): string
    {
        return $this->xuidSeller;
    }

    public function getUniqueId(): string
    {
        return $this->uniqueId;
    }
}