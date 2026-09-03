<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Service\Contract;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Exception\Exception;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use ReflectionClass;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\AbstractNormalizer;
use Symfony\Component\Serializer\Normalizer\AbstractObjectNormalizer;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Throwable;

abstract class AbstractFormService
{
    /** the intersection is required: `SerializerInterface` declares neither `normalize()` nor `denormalize()` */
    protected SerializerInterface&NormalizerInterface&DenormalizerInterface $serializer;

    abstract protected function getDtoClass(): string;

    abstract protected function getMethod(): string;

    abstract protected function getAction(DtoInterface $dto): Action;

    abstract protected function build(Form $form, DtoInterface $dto): void;

    public function setSerializer(SerializerInterface&NormalizerInterface&DenormalizerInterface $serializer): static
    {
        $this->serializer = $serializer;

        return $this;
    }

    /** @return array<string, mixed> */
    public function render(?DtoInterface $dto = null): array
    {
        $dto ??= $this->constructDto();

        $this->validateDtoClass($dto);

        $formName = $this->getName();

        $action = $this->getAction($dto);

        $form = new Form($formName, $this->getMethod(), $action);

        $this->build($form, $dto);

        $data = (array)$this->serializer->normalize($dto, null, $this->getNormalizationContext());

        return $form->render($data);
    }

    public function handleRequest(
        Request $request,
        ?DtoInterface $dto = null,
        bool $sanitizeData = true,
    ): DtoInterface {
        [$data, $context] = $this->getDataAndContext($request);

        if (true === $sanitizeData) {
            $data = $this->sanitizeData($data);
        }

        $context = \array_replace($this->getDenormalizationContext(), $context);

        if (null !== $dto) {
            $this->validateDtoClass($dto);

            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $dto;
        }

        $format = true === $this->hasJsonBody($request) ? JsonEncoder::FORMAT : null;

        return $this->serializer->denormalize($data, $this->getDtoClass(), $format, $context);
    }

    protected function validateDtoClass(DtoInterface $dto): void
    {
        $dtoClass = $this->getDtoClass();

        if (false === $dto instanceof $dtoClass) {
            throw (new Exception(\sprintf('invalid dto class for form `%s`', $this->getName())))
                ->setContext([
                    'formName' => $this->getName(),
                    'dtoClass' => $dto::class,
                    'expectedDtoClass' => $dtoClass,
                ]);
        }
    }

    protected function constructDto(): DtoInterface
    {
        $dtoClass = $this->getDtoClass();

        try {
            /** @var DtoInterface $dto */
            $dto = new $dtoClass();
        } catch (Throwable $throwable) {
            throw (new Exception(
                \sprintf('the dto of form `%s` can not be built without arguments', $this->getName()),
                0,
                $throwable,
            ))->setContext(['formName' => $this->getName(), 'dtoClass' => $dtoClass]);
        }

        return $dto;
    }

    /** @return array{array<string, mixed>, array<string, mixed>} the form's own payload, and the denormalization context */
    protected function getDataAndContext(Request $request): array
    {
        $context = [];

        switch ($this->getMethod()) {
            case Request::METHOD_GET:
                $data = $request->query->all();
                $context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT] = true;
                break;
            case Request::METHOD_POST:
            case Request::METHOD_PUT:
            case Request::METHOD_PATCH:
                if (true === $this->hasJsonBody($request)) {
                    try {
                        $data = (new JsonEncoder())->decode($request->getContent(), JsonEncoder::FORMAT);
                    } catch (Throwable $throwable) {
                        throw new Exception(
                            \sprintf('request body for form `%s` is not valid JSON', $this->getName()),
                            0,
                            $throwable,
                            [
                                'formName' => $this->getName(),
                                'requestMethod' => $request->getMethod(),
                            ],
                        );
                    }
                } else {
                    $data = $request->request->all();
                    $context[AbstractObjectNormalizer::DISABLE_TYPE_ENFORCEMENT] = true;
                }
                break;
            default:
                throw new Exception(
                    \sprintf('can not handle `%s` request method', $request->getMethod()),
                );
        }

        if (false === \is_array($data)) {
            throw new Exception(
                \sprintf('request body for form `%s` must decode to an array', $this->getName()),
            );
        }

        $formData = $data[$this->getName()] ?? [];

        if (false === \is_array($formData)) {
            throw new Exception(
                \sprintf('`%1$s` in the request body for form `%1$s` must decode to an array', $this->getName()),
            );
        }

        return [$formData, $context];
    }

    /** a body-carrying method with a non-empty body: the payload is json, and the serializer must know it is */
    protected function hasJsonBody(Request $request): bool
    {
        $bodyCarryingMethods = [Request::METHOD_POST, Request::METHOD_PUT, Request::METHOD_PATCH];

        return true === \in_array($this->getMethod(), $bodyCarryingMethods, true) && false === empty($request->getContent());
    }

    /** @return array<string, mixed> */
    protected function getNormalizationContext(): array
    {
        return [];
    }

    /** @return array<string, mixed> */
    protected function getDenormalizationContext(): array
    {
        return [];
    }

    protected function getName(): string
    {
        $className = \lcfirst((new ReflectionClass(static::class))->getShortName());

        $position = \strrpos($className, 'Service');

        return false === $position ? $className : \substr($className, 0, $position);
    }

    /**
     * @param array<string, mixed> $data
     *
     * @return array<string, mixed>
     */
    protected function sanitizeData(array $data): array
    {
        $sanitizedData = [];

        foreach ($data as $key => $value) {
            /* the asymmetry is deliberate: an empty array must not override a DTO default, an empty string must be able to clear a field */
            if (true === \is_array($value)) {
                $value = $this->sanitizeData($value);

                if (true === empty($value)) {
                    continue;
                }
            }

            $sanitizedData[$key] = $value;
        }

        return $sanitizedData;
    }
}
