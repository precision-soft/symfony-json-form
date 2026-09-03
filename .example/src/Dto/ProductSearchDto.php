<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Dto;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;

class ProductSearchDto implements DtoInterface
{
    private string $query = '';
    private ?int $categoryId = null;
    private bool $activeOnly = false;
    private int $page = 1;

    public function getQuery(): string
    {
        return $this->query;
    }

    public function setQuery(string $query): static
    {
        $this->query = $query;

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

    public function getActiveOnly(): bool
    {
        return $this->activeOnly;
    }

    public function setActiveOnly(bool $activeOnly): static
    {
        $this->activeOnly = $activeOnly;

        return $this;
    }

    public function getPage(): int
    {
        return $this->page;
    }

    public function setPage(int $page): static
    {
        $this->page = $page;

        return $this;
    }
}
