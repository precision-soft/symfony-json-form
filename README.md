# Symfony json form

[![ci](https://github.com/precision-soft/symfony-json-form/actions/workflows/ci.yml/badge.svg)](https://github.com/precision-soft/symfony-json-form/actions/workflows/ci.yml)
[![PHP >= 8.2](https://img.shields.io/badge/php-%3E%3D8.2-8892BF)](https://www.php.net/)
[![PHPStan Level 8](https://img.shields.io/badge/phpstan-level%208-brightgreen)](https://phpstan.org/)
[![Code Style PER-CS2.0](https://img.shields.io/badge/code%20style-PER--CS2.0-blue)](https://www.php-fig.org/per/coding-style/)
[![License MIT](https://img.shields.io/badge/license-MIT-green)](LICENSE)

**You may fork and modify it as you wish**.

Any suggestions are welcomed.

## Purpose

The purpose of this library is to create forms for single page applications, with a symfony backend. The forms are constructed in the backend and serialized to json, that can be rendered in the frontend. In the assets folder you can find a react component to render the form.

A form is described by three pieces that you provide per form:

* a **DTO** (`DtoInterface`) — the typed data structure the form maps to and from;
* a **form service** (`AbstractFormService`) — declares the HTTP method, the submit action, and the elements;
* the **elements** — the individual fields (`NumberElement`, `StringElement`, ...).

`render()` serializes the form (plus the DTO values) to a json structure for the frontend; `handleRequest()` takes the incoming request, sanitizes it, and denormalizes it back into the DTO.

### V1 vs V2

There are 2 versions of the react renderer. They consume the **same** backend json — only the frontend components differ:

* [formV1](./assets/react/formV1) — the original react components, kept for backwards compatibility.
* [formV2](./assets/react/formV2) — the new components and the **recommended** way to render the json.

Both are maintained in parallel and every fix lands in both. What actually differs:

|                                 | formV1                                                              | formV2                                                                      |
|---------------------------------|---------------------------------------------------------------------|-----------------------------------------------------------------------------|
| the formik form                 | passed down as a `form` prop                                        | read from `FormContext`                                                     |
| entry points                    | `Form.tsx`, `FormBuilder.tsx`, `FormButtons.tsx`, `FormControl.tsx` | `Form.tsx` re-exports everything; `FormButton.tsx`                          |
| prototype collections           | rendered inside `FormField`/`FormBuilder`                           | its own `PrototypeCollectionField.tsx`                                      |
| mutating a prototype collection | `FormFieldCallbacksType`, filled in by the field                    | `PrototypeCollectionModifiersType`, handed to the render prop               |
| checkboxes                      | the `bool` branch of `FormField`                                    | `CheckboxField.tsx`, with `checkboxIcon`/`checkboxCheckedIcon` render props |
| the language context            | `React.useContext(LanguageContext)`                                 | `useLanguageContext()`                                                      |
| the url generator               | `useUrlGenerator()` from `service/UrlGenerator`                     | `useUrlGeneratorContext()`, host provided                                   |
| mui autocomplete types          | `@mui/base/AutocompleteUnstyled/useAutocomplete`                    | `@mui/material/useAutocomplete`                                             |

The field components themselves (`TextField`, `DateField`, `DateTimeField`, `SelectField`, `AutocompleteField`) are the same in both apart from those imports. The schema logic they share — turning the rendered json into the form's initial values — lives in [service/Element.ts](./assets/react/service/Element.ts) and is used by both.

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

Each element renders to a json node with a `type` the frontend dispatches on. All take `name` and `label` first; the most relevant extra constructor arguments are noted below.

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

`ArrayElement` and `AutocompleteElement` throw `InvalidModeException` for an unknown `$mode`. Element names must be alphanumeric (`ctype_alnum`) — this is enforced and intentional.

`DateElement` and `DateTimeElement` validate the value strictly against `$format`: a value that does not round-trip through the format (e.g. an overflow date such as `2021-02-30`) throws `InvalidValueException`. When `$min` and/or
`$max` are set, the value is also enforced server-side to fall within that inclusive range — an out-of-range value throws `InvalidValueException`. A `$min` or `$max` that does not itself parse in the element's `$format` throws
`InvalidValueException` **at construction**: it used to be skipped silently, which turned the range off without saying so.

`NumberElement` enforces `$min`/`$max` the same way — inclusive, server-side, `InvalidValueException` when the value falls outside — and rejects a `$min` greater than `$max` at construction. `$step` is a frontend hint only.

`CollectionElement` and `PrototypeCollectionElement` throw `InvalidValueException` when the value — or, for the prototype collection, any item — is not an array. `ArrayElement` throws it for any item that is not scalar, because the options it is compared against always are.

## Request handling and sanitization

`handleRequest(Request $request, ?DtoInterface $dto = null, bool $sanitizeData = true)`:

* For `GET` the data is read from the query string; for `POST`/`PUT`/`PATCH` from the json body (falling back to
  `request->all()` when the body is empty).
* A request body that decodes to a non-array scalar (e.g. `5`) throws an `Exception` rather than a raw `TypeError`, and so does the form's own key inside it (e.g. `{"myForm": "text"}`).
* Pass an existing `$dto` to populate it in place (`OBJECT_TO_POPULATE`) — useful for `PATCH`/`PUT`.

When `$sanitizeData` is `true` (default), `sanitizeData()` applies the following rules before denormalization:

* **empty arrays are dropped** — an absent nested structure does not override DTO defaults;
* **empty strings are kept** — so a `PATCH`/`PUT` can explicitly clear a field by sending `""`.

Pass `sanitizeData: false` to denormalize the raw payload unchanged.

## React

Use the components from **./assets/react** to interpret the backend response. This package ships the **sources** only; the host application is responsible for bundling them (there is no build step here). The **Config** component is project specific — it holds the locale context of the application. It is integrated with:

* `willdurand/js-translation-bundle` for the Translator.
* `friendsofsymfony/jsrouting-bundle` for the UrlGenerator.

## Tests

PHP tests run in the dev container:

```shell
./dc exec dev php vendor/bin/simple-phpunit
```

`composer test` excludes the `integration` group; `composer test-integration` runs only it. The integration suite (`tests/Functional/`) drives the whole round trip with nothing mocked — a form service renders a DTO to json, that json comes back as a real request body, and the service denormalizes it into a DTO again. It needs no external service.

The framework-agnostic react asset services (`service/Element.ts`, `service/Utility.ts`) are covered by a dependency-free harness using Node's built-in test runner (the dev container ships Node):

```shell
./dc exec dev sh -c 'cd assets/react && npm test'
```

The react sources are type-checked with `tsc --noEmit`. There is no build step and no `node_modules` here: the host application bundles these sources and owns the dependencies, so the typescript compiler lives in the dev image and the dependencies are declared in [assets/react/types/vendor.d.ts](./assets/react/types/vendor.d.ts). The modules the host is expected to provide (`../context/*`, `../component/*`, `../config/Config`, `../exception/Exception`, `../form/Form`,
`../service/Logger`) are declared as `.d.ts` stubs beside their import paths, which doubles as the contract the host has to satisfy; a real `.tsx` in the host tree takes precedence over the stub.

```shell
./dc exec dev sh -c 'cd assets/react && npm run typecheck'
```

## Exception context

Every exception in this package carries a structured `context` array next to its message, so the facts describing a failure do not have to be parsed back out of a string:

```php
try {
    // ...
} catch (Exception $exception) {
    $logger->error($exception->getMessage(), $exception->getContext());
}
```

`getContext()` returns `[]` when nothing was attached. `setContext()` replaces it and returns the exception, and the constructor accepts it as an optional fourth argument. Values are expected to be scalars, so the array stays serialisable by a logger.

The context is purely **additive**: no message, code or previous throwable changed when it was introduced, so code that logs only `getMessage()` behaves exactly as before.

What this package attaches: `AbstractFormService::getDataAndContext()` reports `formName` and `requestMethod` when a request body is not valid JSON. The form name was previously only available interpolated into the message, and the request method was not reported at all.

Every exception in the package implements `Contract\ExceptionInterface`, so a consumer can read the context off any of them without knowing the concrete class. A subclass of your own that already declares a `$context` property or a
`getContext()`/`setContext()` method will collide with `Exception\Trait\ExceptionTrait`.

## Dev

```shell
git clone git@github.com:precision-soft/symfony-json-form.git
cd symfony-json-form

./dc build && ./dc up -d
```

Run the full gate the way the pre-commit hook runs it - the CI workflow in
`.github/workflows/ci.yml` calls the same composer scripts and node commands, so the two cannot drift — cs-check, phpstan, phpunit, `tsc --noEmit` and the node tests:

```shell
.dev/validate/all.sh
.dev/validate/all.sh --integration   # also runs the integration suite
.dev/validate/all.sh --audit         # also audits the locked dependencies ( needs the network )
.dev/validate/all.sh --staged        # what the pre-commit hook runs: only the languages the index touches
```

Mutation testing is opt-in for the same reason, plus cost - it runs the suite once per mutant:

```shell
.dev/validate/all.sh --mutation
```

Infection is a pinned phar in the image, not a composer dependency, and `infection.json5` carries a
`minMsi` floor equal to the last measured score, so the section fails when a change makes the suite weaker rather than only reporting a number. Raise the floor when the score improves.

The React asset tests run through Node's own test runner, inside the same container:

```shell
./dc exec dev sh -c 'cd assets/react && node --test'
```

Build against another PHP version with the `PHP_VERSION` build argument - each version is tagged as its own image, so switching back and forth costs nothing:

```shell
PHP_VERSION=8.4 ./dc build && PHP_VERSION=8.4 ./dc up -d
```

Coverage is available through pcov, which is installed but disabled by default:

```shell
./dc exec dev php -d pcov.enabled=1 vendor/bin/simple-phpunit --coverage-text
```

After editing a file, `./dc restart dev` (a few seconds) is enough to be sure the container is not serving a stale copy - the bind mount can keep the old inode after an atomic rewrite.

## Todo

* Render and handle complex types like `\DateTime` in DTO denormalization.
