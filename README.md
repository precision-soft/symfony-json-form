# Symfony json form

**You may fork and modify it as you wish**.

Any suggestions are welcomed.

## Purpose

The purpose of this library is to create forms for single page applications, with a symfony backend.
The forms are constructed in the backend and serialized to json, that can be rendered in the frontend. In the assets folder you can find a react component to render the form.

A form is described by three pieces that you provide per form:

* a **DTO** (`DtoInterface`) — the typed data structure the form maps to and from;
* a **form service** (`AbstractFormService`) — declares the HTTP method, the submit action, and the elements;
* the **elements** — the individual fields (`NumberElement`, `StringElement`, ...).

`render()` serializes the form (plus the DTO values) to a json structure for the frontend; `handleRequest()` takes the
incoming request, sanitizes it, and denormalizes it back into the DTO.

### V1 vs V2

There are 2 versions of the react renderer. They consume the **same** backend json — only the frontend components differ:

* [formV1](./assets/react/formV1) — the original react components, kept for backwards compatibility.
* [formV2](./assets/react/formV2) — the new components and the **recommended** way to render the json.

## Usage

Add this to your **services.yaml** so every form service receives the serializer:

```yaml
services:
    _instanceof:
        PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService:
            calls:
                - [ setSerializer, [ '@serializer' ] ]
```

A form service must implement four abstract methods: `getDtoClass()`, `getMethod()`, `getAction(DtoInterface $dto)`
and `build(Form $form, DtoInterface $dto)`.

```php
<?php

declare(strict_types=1);

namespace Acme\Form;

use Acme\Dto\ProductEditDto;
use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;
use PrecisionSoft\Symfony\JsonForm\Element\ArrayElement;
use PrecisionSoft\Symfony\JsonForm\Element\NumberElement;
use PrecisionSoft\Symfony\JsonForm\Form\Action;
use PrecisionSoft\Symfony\JsonForm\Form\Form;
use PrecisionSoft\Symfony\JsonForm\Service\Contract\AbstractFormService;
use Symfony\Component\HttpFoundation\Request;

class ProductEditForm extends AbstractFormService
{
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
        return new Action('product-edit', ['id' => $dto instanceof ProductEditDto ? $dto->getId() : null]);
    }

    protected function build(Form $form, DtoInterface $dto): void
    {
        $form->addElement(new NumberElement('id', 'Id'))
            ->addElement(new ArrayElement('status', 'Status', ['active' => 'Active', 'inactive' => 'Inactive']));
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Acme\Dto;

use PrecisionSoft\Symfony\JsonForm\Contract\DtoInterface;

class ProductEditDto implements DtoInterface
{
    private int $id;
    private string $status;

    public function getId(): int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;

        return $this;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
    }
}
```

```php
<?php

declare(strict_types=1);

namespace Acme\Controller;

use Acme\Dto\ProductEditDto;
use Acme\Form\ProductEditForm;
use Acme\Service\ProductEditService;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class ProductController extends AbstractController
{
    public function edit(Request $request, ProductEditForm $productEditForm, ProductEditService $productEditService): Response
    {
        $id = (int)$request->get('id');

        if (Request::METHOD_POST === $request->getMethod()) {
            /** @var ProductEditDto $dto */
            $dto = $productEditForm->handleRequest($request);

            $productEditService->save($dto);
        } else {
            $dto = $productEditService->createDto($id);
        }

        return $this->json(['form' => $productEditForm->render($dto)]);
    }
}
```

## Form elements

Each element renders to a json node with a `type` the frontend dispatches on. All take `name` and `label` first;
the most relevant extra constructor arguments are noted below.

| Element                      | json `type`           | Extra arguments                                                  |
|------------------------------|-----------------------|------------------------------------------------------------------|
| `StringElement`              | `string`              | —                                                                |
| `NumberElement`              | `number`              | `?float $min`, `?float $max`, `?float $step`                     |
| `BoolElement`                | `bool`                | —                                                                |
| `DateElement`                | `date`                | `string $format = 'Y-m-d'`, `?string $min`, `?string $max`       |
| `DateTimeElement`            | `dateTime`            | `string $format = 'Y-m-d H:i'`, `?string $min`, `?string $max`   |
| `PasswordElement`            | `password`            | —                                                                |
| `HiddenElement`              | `hidden`              | (label is not required)                                          |
| `LabelElement`               | `label`               | display-only                                                     |
| `FileElement`                | `file`                | —                                                                |
| `ArrayElement`               | `array`               | `array $options`, `string $mode` (`MODE_SINGLE`/`MODE_MULTIPLE`) |
| `AutocompleteElement`        | `autocomplete`        | `string $route`, `string $mode`, `string $parameter = 'query'`   |
| `CollectionElement`          | `collection`          | nested elements via `addElement()`                               |
| `PrototypeCollectionElement` | `prototypeCollection` | nested elements via `addElement()` (repeatable)                  |

`ArrayElement` and `AutocompleteElement` throw `InvalidModeException` for an unknown `$mode`. Element names must be
alphanumeric (`ctype_alnum`) — this is enforced and intentional.

`DateElement` and `DateTimeElement` validate the value strictly against `$format`: a value that does not round-trip
through the format (e.g. an overflow date such as `2021-02-30`) throws `InvalidValueException`. When `$min` and/or
`$max` are set, the value is also enforced server-side to fall within that inclusive range — an out-of-range value
throws `InvalidValueException`. `CollectionElement`
and `PrototypeCollectionElement` throw `InvalidValueException` when the value — or, for the prototype collection, any
item — is not an array.

## Request handling and sanitization

`handleRequest(Request $request, ?DtoInterface $dto = null, bool $sanitizeData = true)`:

* For `GET` the data is read from the query string; for `POST`/`PUT`/`PATCH` from the json body (falling back to
  `request->all()` when the body is empty).
* A request body that decodes to a non-array scalar (e.g. `5`) throws an `Exception` rather than a raw `TypeError`.
* Pass an existing `$dto` to populate it in place (`OBJECT_TO_POPULATE`) — useful for `PATCH`/`PUT`.

When `$sanitizeData` is `true` (default), `sanitizeData()` applies the following rules before denormalization:

* **empty arrays are dropped** — an absent nested structure does not override DTO defaults;
* **empty strings are kept** — so a `PATCH`/`PUT` can explicitly clear a field by sending `""`.

Pass `sanitizeData: false` to denormalize the raw payload unchanged.

## React

Use the components from **./assets/react** to interpret the backend response. This package ships the **sources** only;
the host application is responsible for bundling them (there is no build step here).
The **Config** component is project specific — it holds the locale context of the application. It is integrated with:

* `willdurand/js-translation-bundle` for the Translator.
* `friendsofsymfony/jsrouting-bundle` for the UrlGenerator.

## Tests

PHP tests run in the dev container:

```shell
./dc exec dev php vendor/bin/simple-phpunit
```

The framework-agnostic react asset services (e.g. `service/Utility.ts`) are covered by a dependency-free harness using
Node's built-in test runner (the dev container ships Node):

```shell
./dc exec dev sh -c 'cd assets/react && npm test'
```

## Dev

```shell
git clone git@gitlab.com:precision-soft-open-source/symfony/json-form.git
cd json-form

./dc build && ./dc up -d
```

## Todo

* Render and handle complex types like `\DateTime` in DTO denormalization.
