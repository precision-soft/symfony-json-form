# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v1.0.4] - 2026-04-24 - Remove Final Modifiers And Improve Extensibility

### Changed

- `AbstractElement`, `Action`, `Form` — removed `final` keyword from classes and public methods to allow library consumers to extend and override
- `AbstractElement`, `Action`, `Form`, `AbstractFormService`, `ElementCollectionTrait`, `PrototypeCollectionElement` — changed `private` visibility to `protected` on constructor-promoted properties and regular properties
- `ArrayElement::getOptionsValues()`, `ElementCollectionTrait::renderElements()`, `InvalidValueException::serialize()` — changed visibility from `private` to `protected`
- `CollectionElement::getType()`, `CollectionElement::renderElement()`, `PrototypeCollectionElement::getType()`, `PrototypeCollectionElement::renderElement()` — corrected visibility from `public` to `protected` to match the abstract parent declaration
- `Form::render()` — added explicit `array` type hint on `$data` parameter
- `AbstractFormService::render()` — added explicit `(array)` cast on `$this->serializer->normalize()` result to satisfy the `array` parameter type of `Form::render()`
- `InvalidValueException::__construct()`, `InvalidValueException::serialize()` — added `mixed` type hint on `$value` parameter
- `PrototypeCollectionElement::renderElement()` — renamed loop variable `$v` to `$itemData`
- `TestDto::isBool()` — renamed to `getBool()` per project getter naming convention
- `composer.json` — removed the `version` field; version is managed exclusively via GitHub release tags
- `ArrayElement`, `BoolElement`, `CollectionElement`, `PasswordElement`, `PrototypeCollectionElement`, `StringElement` — removed WHAT-only class docblock comments
- `ArrayElement`, `AbstractFormService`, `InvalidValueException` — removed stale `@todo` comments
- `NumberElement::renderElement()` — replaced `!\is_numeric()` negation with `false === \is_numeric()` explicit comparison
- `Exception` — added `use Exception as BaseException` import and replaced inline `\Exception` FQN in extends clause
- `TestForm::build()` — replaced `ArrayElement::MODE_SINGLE` with `AutocompleteElement::MODE_SINGLE` when constructing `AutocompleteElement`
- `ArrayElement::renderElement()` — extracted `$diff` assignment out of `empty()` call argument for readability
- `AbstractFormService::setSerializer()`, `ElementCollectionTrait::addElement()`, `ReadonlyTrait::setReadonly()`, `RequiredTrait::setRequired()` — changed fluent return type from `self` to `static` for correct late static binding in subclasses
- `FormTest::getSerializer()` — renamed `$extractor` to `$propertyInfoExtractor` per variable-equals-class-name convention
- `assets/react/**` — removed `'use strict'` directive from all 31 TypeScript/TSX files (not required in modern ESM)
- `BlockUi`, `PrototypeCollectionField` — changed from `export default` to named exports
- `FormFields` (formV1, formV2), `PrototypeCollectionDefaultField` — replaced shorthand fragments `<></>` with `<React.Fragment></React.Fragment>`
- `FormField` (formV1, formV2), `PrototypeCollectionField` — renamed `_` in destructuring to `unusedIndex`; renamed abbreviations `v` → `itemData` / `selectedOption`, `params` → `renderInputParameters`
- `HttpClient`, `FormField` (formV1, formV2), `FormBuilder`, `AutocompleteField` (formV1, formV2), `SelectField` (formV1, formV2), `TextField` (formV1, formV2), `FormButtons`, `FormButton`, `CheckboxField`, `DateField` (formV1, formV2), `DateTimeField` (formV1, formV2), `Form` (formV1, formV2), `BlockUi` — replaced implicit boolean coercions with explicit comparisons and applied Yoda ordering throughout
- `assets/react/**` — removed WHAT-only section comments (`/** external libraries */`, `/** internal components */`) and stale `@todo` comments from all React files

### Added

- `tests/Element/StringElementTest` — unit tests for null/string values, invalid type exception, readonly/required flags
- `tests/Element/BoolElementTest` — unit tests for null/true/false values, string and integer coercion exceptions
- `tests/Element/NumberElementTest` — unit tests for null/integer/float/numeric-string values, non-numeric exception, min/max/step structure
- `tests/Element/ArrayElementTest` — unit tests for null value, valid options, grouped options, value-not-in-options exception, invalid mode exception, default mode
- `tests/Element/DateElementTest` — unit tests for null value, Y-m-d and d-m-Y formats, wrong format exception, non-string exception, invalid string exception
- `tests/Element/AbstractElementTest` — unit tests for `getName()`, non-alphanumeric name exception, render structure keys, null label via `LabelElement`
- `tests/Exception/InvalidValueExceptionTest` — unit tests for message with scalar/integer/array/object/null values and base class inheritance
- `tests/Exception/InvalidModeExceptionTest` — unit tests for message format and base class inheritance
- `tests/Form/ActionTest` — unit tests for `render()` with and without parameters, structure keys
- `tests/Trait/ElementCollectionTraitTest` — unit tests for fluent chaining, duplicate element name exception, missing value defaults to null
- `.dev/git-hooks/pre-commit` — replaced `phpcs` with `php-cs-fixer` (auto-fix + auto-stage), added `php_unit` step, fixed `STAGED_FILES` to use `--diff-filter=ACMR`, fixed exit code from `stop $?` to `stop 0`
- `.dev/utility.sh` — fixed `check_container()` missing `return` after `echo 1` (container check always returned 0), quoted `${PWD}/dc` path, aligned `print_error` to delegate to `error()`, fixed `docker_compose` to use `$@` instead of `$*`

## [v1.0.3] - 2026-03-19 - Validation Bug Fixes And Dev Setup Cleanup

### Fixed

- `ArrayElement::renderElement()` — removed leftover `\print_r()` debug call that printed to stdout on every invalid-value validation error
- `DateElement::renderElement()`, `DateTimeElement::renderElement()` — corrected validation condition from `&& false === is_string()` to `|| false === is_string()`; the original `&&` caused valid string values to pass the type check and then fail the format check silently instead of throwing
- `InvalidValueException::serialize()` — added explicit `(string)` cast before returning scalar values to satisfy `strict_types=1` return type

### Changed

- Dev directory renamed from `dev/` to `.dev/` and `dc` script updated accordingly

## [v1.0.2] - 2025-10-25 - Fix Nullable Parameter In HandleRequest

### Fixed

- `AbstractFormService::handleRequest()` — corrected nullable parameter declaration from `DtoInterface $dto = null` to `?DtoInterface $dto = null` to resolve PHP 8.4 deprecation notice

## [v1.0.1] - 2025-10-25 - Fix Nullable Parameter Declarations

### Fixed

- `LabelElement::__construct()` — corrected nullable parameter `string $label = null` to `?string $label = null`
- `AbstractFormService::render()` — corrected nullable parameter `DtoInterface $dto = null` to `?DtoInterface $dto = null`

### Changed

- `.php-cs-fixer.dist.php` — added `cast_spaces` rule with `single` option
- Docker setup updated to match project PHP version

## [v1.0.0] - 2024-09-18 - Initial Release

### Added

- `AbstractElement`, `ElementCollectionTrait` — base element contract and collection management
- `ArrayElement`, `AutocompleteElement`, `BoolElement`, `CollectionElement`, `DateElement`, `DateTimeElement`, `FileElement`, `HiddenElement`, `LabelElement`, `NumberElement`, `PasswordElement`, `PrototypeCollectionElement`, `StringElement` — full set of typed form elements
- `ReadonlyTrait`, `RequiredTrait` — reusable element modifier traits
- `AbstractFormService` — base service handling form render and request deserialization via Symfony Serializer
- `Form`, `Action` — form and action value objects
- `Exception`, `InvalidModeException`, `InvalidValueException` — project-specific exception hierarchy
- `DtoInterface` — contract for form data transfer objects
- React form components (formV1, formV2) with TypeScript types, autocomplete, date, datetime, select, and collection field support
- Docker-based development environment with git hooks

[Unreleased]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.4...HEAD
[v1.0.4]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.3...v1.0.4
[v1.0.3]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.2...v1.0.3
[v1.0.2]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.1...v1.0.2
[v1.0.1]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.0...v1.0.1
[v1.0.0]: https://github.com/precision-soft/symfony-json-form/releases/tag/v1.0.0
