<?php

declare(strict_types=1);

/*
 * Copyright (c) Precision Soft
 */

namespace PrecisionSoft\Symfony\JsonForm\Example\Form;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\ArrayElement;
use PrecisionSoft\Symfony\JsonForm\Element\AutocompleteElement;
use PrecisionSoft\Symfony\JsonForm\Element\BoolElement;
use PrecisionSoft\Symfony\JsonForm\Element\CollectionElement;
use PrecisionSoft\Symfony\JsonForm\Element\DateElement;
use PrecisionSoft\Symfony\JsonForm\Element\DateTimeElement;
use PrecisionSoft\Symfony\JsonForm\Element\FileElement;
use PrecisionSoft\Symfony\JsonForm\Element\HiddenElement;
use PrecisionSoft\Symfony\JsonForm\Element\LabelElement;
use PrecisionSoft\Symfony\JsonForm\Element\NumberElement;
use PrecisionSoft\Symfony\JsonForm\Element\PrototypeCollectionElement;
use PrecisionSoft\Symfony\JsonForm\Element\StringElement;
use PrecisionSoft\Symfony\JsonForm\Example\Dto\ProductEditDto;
use PrecisionSoft\Symfony\JsonForm\Example\Serializer\CurrencyNormalizer;
use PrecisionSoft\Symfony\JsonForm\Example\Service\CatalogOptionProvider;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Serializer\Normalizer\DateTimeNormalizer;

/** every element type the package ships, on the one product a shop actually edits */
class ProductEditForm extends AbstractFormService
{
    public const CATEGORY_ROUTE = 'catalogue-category-search';
    protected const DATE_TIME_FORMAT = DateTimeElement::FORMAT_Y_M_D_H_I;

    public function __construct(protected CatalogOptionProvider $catalogOptionProvider) {}

    protected function getDtoClass(): string
    {
        return ProductEditDto::class;
    }

    protected function getMethod(): string
    {
        return Request::METHOD_POST;
    }

    protected function getAction(DtoInterface $dto): Action
    {
        return new Action(
            'catalogue-product-edit',
            ['id' => true === $dto instanceof ProductEditDto ? $dto->getId() : null],
        );
    }

    protected function build(Form $form, DtoInterface $dto): void
    {
        $currencyOptions = $this->catalogOptionProvider->getCurrencyOptions();

        $form->addElement(new HiddenElement('id'))
            ->addElement(new StringElement('name', 'name'))
            ->addElement(new NumberElement('price', 'price', 0.0, 1000000.0, 0.01))
            ->addElement(new LabelElement('priceHint', 'the price is in the product currency'))
            ->addElement(new ArrayElement('currency', 'currency', $currencyOptions))
            ->addElement(
                new ArrayElement(
                    'channels',
                    'sales channels',
                    $this->catalogOptionProvider->getChannelOptions(),
                    ArrayElement::MODE_MULTIPLE,
                ),
            )
            ->addElement(new AutocompleteElement('categoryId', 'category', static::CATEGORY_ROUTE))
            ->addElement(new BoolElement('active', 'active'))
            ->addElement(new DateElement('availableFrom', 'available from'))
            ->addElement(new DateTimeElement('publishedAt', 'published at', static::DATE_TIME_FORMAT))
            ->addElement(
                (new CollectionElement('dimensions', 'dimensions in centimetres'))
                    ->addElement(new NumberElement('width', 'width', 0.0))
                    ->addElement(new NumberElement('height', 'height', 0.0))
                    ->addElement(new NumberElement('depth', 'depth', 0.0)),
            )
            ->addElement(
                (new PrototypeCollectionElement('prices', 'prices per currency'))
                    ->addElement(new ArrayElement('currency', 'currency', $currencyOptions))
                    ->addElement(new NumberElement('amount', 'amount', 0.0)),
            )
            ->addElement(new FileElement('image', 'image'));
    }

    protected function getNormalizationContext(): array
    {
        return [DateTimeNormalizer::FORMAT_KEY => static::DATE_TIME_FORMAT];
    }

    protected function getDenormalizationContext(): array
    {
        return [
            DateTimeNormalizer::FORMAT_KEY => '!' . static::DATE_TIME_FORMAT,
            CurrencyNormalizer::ALLOWED_CODES_KEY => \array_keys($this->catalogOptionProvider->getCurrencyOptions()),
        ];
    }
}
