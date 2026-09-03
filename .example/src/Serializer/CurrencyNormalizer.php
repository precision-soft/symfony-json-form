<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Serializer;

use PrecisionSoft\Symfony\JsonForm\Example\ValueObject\Currency;
use PrecisionSoft\Symfony\JsonForm\Exception\InvalidValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

/** a `Currency` travels as its code; coming back, only the codes the form allowed through its context are accepted */
class CurrencyNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public const ALLOWED_CODES_KEY = 'currencyAllowedCodes';

    /** @param array<string, mixed> $context */
    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        if (false === $data instanceof Currency) {
            throw new InvalidValueException('currency', $data);
        }

        return $data->getCode();
    }

    /** @param array<string, mixed> $context */
    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Currency
    {
        /** @var list<string> $allowedCodes */
        $allowedCodes = $context[static::ALLOWED_CODES_KEY] ?? [];

        if (false === \is_string($data) || false === \in_array($data, $allowedCodes, true)) {
            throw new InvalidValueException('currency', $data);
        }

        return new Currency($data);
    }

    /** @param array<string, mixed> $context */
    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return true === $data instanceof Currency;
    }

    /** @param array<string, mixed> $context */
    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return Currency::class === $type;
    }

    /** @return array<string, bool|null> */
    public function getSupportedTypes(?string $format): array
    {
        return [Currency::class => true];
    }
}
