<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

class TestSanitizingForm extends TestForm
{
    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    public function exposeSanitizeData(array $data): array
    {
        return $this->sanitizeData($data);
    }
}
