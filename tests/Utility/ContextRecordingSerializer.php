<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Test\Utility;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;

class ContextRecordingSerializer implements SerializerInterface, NormalizerInterface, DenormalizerInterface
{
    /** @var array<string, mixed> */
    private array $normalizationContext = [];

    /** @var array<string, mixed> */
    private array $denormalizationContext = [];

    public function __construct(
        private readonly DtoInterface $dto,
    ) {}

    /** @return array<string, mixed> */
    public function getRecordedNormalizationContext(): array
    {
        return $this->normalizationContext;
    }

    /** @return array<string, mixed> */
    public function getRecordedDenormalizationContext(): array
    {
        return $this->denormalizationContext;
    }

    /**
     * @param array<string, mixed> $context
     *
     * @return array<string, mixed>
     */
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        $this->normalizationContext = $context;

        return [];
    }

    /** @param array<string, mixed> $context */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): mixed
    {
        $this->denormalizationContext = $context;

        return $this->dto;
    }

    /** @param array<string, mixed> $context */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return true;
    }

    /** @param array<string, mixed> $context */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return true;
    }

    /** @return array<string, bool|null> */
    public function getSupportedTypes(?string $format): array
    {
        return ['*' => true];
    }

    /** @param array<string, mixed> $context */
    public function serialize(mixed $data, string $format, array $context = []): string
    {
        return '';
    }

    /** @param array<string, mixed> $context */
    public function deserialize(mixed $data, string $type, string $format, array $context = []): mixed
    {
        return $this->dto;
    }
}
