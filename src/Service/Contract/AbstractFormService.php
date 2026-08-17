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
        if (null === $dto) {
            $dtoClass = $this->getDtoClass();
            /** @var DtoInterface $dto */
            $dto = new $dtoClass();
        }

        $formName = $this->getName();

        if ($dto::class !== $this->getDtoClass()) {
            throw new Exception(\sprintf('invalid dto class for form `%s`', $formName));
        }

        $action = $this->getAction($dto);

        $form = new Form($formName, $this->getMethod(), $action);

        $this->build($form, $dto);

        $data = (array)$this->serializer->normalize($dto);

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

        if (null !== $dto) {
            $context[AbstractNormalizer::OBJECT_TO_POPULATE] = $dto;
        }

        return $this->serializer->denormalize($data, $this->getDtoClass(), null, $context);
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
                $requestContent = $request->getContent();
                if (false === empty($requestContent)) {
                    try {
                        $data = (new JsonEncoder())->decode($requestContent, JsonEncoder::FORMAT);
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
