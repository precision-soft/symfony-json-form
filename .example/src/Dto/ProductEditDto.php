<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Dto;

use DateTimeImmutable;
use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Example\ValueObject\Currency;

class ProductEditDto implements DtoInterface
{
    private ?int $id = null;
    private string $name = '';
    private float $price = 0.0;
    private Currency $currency;
    /** @var list<string> */
    private array $channels = [];
    private ?int $categoryId = null;
    private bool $active = true;
    private ?string $availableFrom = null;
    private ?DateTimeImmutable $publishedAt = null;
    private ProductDimensionsDto $dimensions;
    /** @var list<array<string, mixed>> */
    private array $prices = [];
    private ?string $image = null;

    public function __construct()
    {
        $this->currency = new Currency('EUR');
        $this->dimensions = new ProductDimensionsDto();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(?int $id): static
    {
        $this->id = $id;

        return $this;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getPrice(): float
    {
        return $this->price;
    }

    public function setPrice(float $price): static
    {
        $this->price = $price;

        return $this;
    }

    public function getCurrency(): Currency
    {
        return $this->currency;
    }

    public function setCurrency(Currency $currency): static
    {
        $this->currency = $currency;

        return $this;
    }

    /** @return list<string> */
    public function getChannels(): array
    {
        return $this->channels;
    }

    /** @param list<string> $channels */
    public function setChannels(array $channels): static
    {
        $this->channels = $channels;

        return $this;
    }

    public function getCategoryId(): ?int
    {
        return $this->categoryId;
    }

    public function setCategoryId(?int $categoryId): static
    {
        $this->categoryId = $categoryId;

        return $this;
    }

    public function getActive(): bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getAvailableFrom(): ?string
    {
        return $this->availableFrom;
    }

    public function setAvailableFrom(?string $availableFrom): static
    {
        $this->availableFrom = $availableFrom;

        return $this;
    }

    public function getPublishedAt(): ?DateTimeImmutable
    {
        return $this->publishedAt;
    }

    public function setPublishedAt(?DateTimeImmutable $publishedAt): static
    {
        $this->publishedAt = $publishedAt;

        return $this;
    }

    public function getDimensions(): ProductDimensionsDto
    {
        return $this->dimensions;
    }

    public function setDimensions(ProductDimensionsDto $dimensions): static
    {
        $this->dimensions = $dimensions;

        return $this;
    }

    /** @return list<array<string, mixed>> */
    public function getPrices(): array
    {
        return $this->prices;
    }

    /** @param list<array<string, mixed>> $prices */
    public function setPrices(array $prices): static
    {
        $this->prices = $prices;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(?string $image): static
    {
        $this->image = $image;

        return $this;
    }
}
