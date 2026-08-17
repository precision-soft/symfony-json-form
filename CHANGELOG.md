# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.1.0/), and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [Unreleased]

## [v1.1.0] - 2026-08-17 - Optional render props no longer crash the form, and the package gets phpstan and a typecheck

### Fixed

- `assets/react/formV1/FormField.tsx`, `assets/react/formV2/FormField.tsx` — the field-level change handler guarded the optional `renderProps.onChange` with `null !== props.renderProps?.onChange` and then dereferenced
  `props.renderProps.onChange`. `renderProps` and `onChange` are both **optional**, so the guard compared `undefined`
  against `null`, passed, and threw `TypeError: Cannot read properties of undefined` on every change of any field the host did not configure — which is the default usage. Guarded with `undefined !==` instead
- `assets/react/formV2/FormField.tsx` — the `bool` branch read `props.renderProps.checkboxIcon` and
  `props.renderProps.checkboxCheckedIcon` off the optional `renderProps` with no guard at all, throwing for every checkbox rendered without render props (formV1 has no checkbox render props and was not affected)
- `assets/react/formV1/FormButtons.tsx`, `assets/react/formV2/FormButton.tsx` — the reset and cancel buttons guarded the click callback with `null !== onClick`, but the third element of `ButtonType` is optional, so a button declared without one threw `TypeError: onClick is not a function` when clicked
- `assets/react/formV1/{TextField,DateField,DateTimeField,SelectField}.tsx` and their formV2 counterparts —
  `props.autoFocus.current` was read (and written) unguarded although `FocusType.autoFocus` is optional, so a component used directly rather than through `FormField` threw on mount
- `assets/react/service/HttpClient.ts` — `jqXhr.responseJSON` is **absent**, not null, when the response body is not json, so `null !== jqXhr.responseJSON?.errors` was always true and the `'invalid backend response received'` fallback never fired for the case it was written for; `error()` then received `undefined` and reported nothing.
  `getFormDataFromResponse()` and `getXhrJsonResponse()` returned `undefined` where their declared return types promised a value or `null`. All three now use `??`
- `assets/react/service/Element.ts` — computing the initial value of a single-mode `array` element read
  `element.value.length` after checking a different variable for null, throwing when the `value` key was absent
- `src/Service/Contract/AbstractFormService.php` — `getDataAndContext()` type-checked the decoded body but not the form's own key inside it, so a payload such as `{"myForm": "text"}` reached `sanitizeData(array $data)` and raised a raw `TypeError` — the exact failure the enclosing check was added in v1.0.6 to prevent. It now throws the bundle's
  `Exception`
- `src/Element/ArrayElement.php` — a non-scalar item in the value reached `array_diff()`, which raised
  `Array to string conversion` and compared the item as the string `Array`; it now throws `InvalidValueException`, matching the guard `PrototypeCollectionElement` received in v1.0.7
- `src/Exception/InvalidValueException.php` — `serialize()` called `implode()` on the value, so an array containing an array raised `Array to string conversion` and reported `Array`: the exception whose job is to name the bad value corrupted it. It now serializes recursively
- `src/Element/Trait/DateValidationTrait.php` — a `$min`/`$max` that does not parse in the element's own `$format` was silently skipped by `isWithinRange()`, so a typo disabled the range check without any signal. Both bounds are now validated in the constructor and throw `InvalidValueException`

### Changed

- `src/Service/Contract/AbstractFormService.php` — `$serializer` and `setSerializer()` now require
  `SerializerInterface&NormalizerInterface&DenormalizerInterface`. `render()` calls `normalize()` and `handleRequest()`
  calls `denormalize()`, neither of which `SerializerInterface` declares; the previous declaration accepted an implementation that would fatal at the first call. Symfony's `Serializer` — what the `SerializerInterface` service resolves to — satisfies the intersection, so the documented wiring is unaffected
- `src/Element/NumberElement.php` — `$min`/`$max` are now enforced server-side as an inclusive range and a value outside it throws `InvalidValueException`, matching what `DateElement`/`DateTimeElement` have done since v1.0.7; a `$min`
  greater than `$max` throws at construction. `$step` remains a frontend hint (behavior change for consumers that relied on `$min`/`$max` being client-side hints only)
- `assets/react/formV1/Types.ts`, `assets/react/formV2/Types.ts` — `ElementType.label` and `FieldType.label` are nullable, because `AbstractElement` takes `?string $label` and renders it as it is; `OnSubmitSuccessType`'s data and
  `OnSubmitFailureType`'s errors are nullable, because the response carries null for both; the prototype collection
  `get()` modifier returns null when no row matches, which it always could
- `phpunit.xml` → `phpunit.xml.dist` — the suite ran on **PHPUnit 9** while every sibling package runs 11.5; the config still used the 9.3 schema and the `<coverage processUncoveredFiles>` and `<listeners>` elements both removed in PHPUnit 10, and set neither `failOnRisky` nor `failOnWarning`. Now 11.5 with both, which is what turned the
  `ArrayElement` warning above into a failing test rather than a line of ignored output
- `assets/react/service/Element.ts` — new shared module holding the schema logic both versions had a copy of (`computeInitialValues`, `createPrototypeCollectionElementValues`) plus `requireElementProperty`, which turns a rendered element missing a property its own type always carries into a named error instead of an `undefined` handed to a component several frames away. `FormBuilder` in both versions delegates to it and its public API is unchanged
- comments across the package normalized to the house rule — the default is no comment, and a warranted one is a single short line. Every multi-line rationale block, narrative test docblock and shell section header was removed; the `.dev/` scripts, the `Dockerfile` and the compose file now carry nothing but their shebang and one line about `tini` as PID 1. Nothing behavioral changed. `CONTRIBUTING.md` gained the two sections that now carry the rationale — *Development toolchain* (the pinned pcov and infection builds, the `php.dev.ini` overlay, the pinned node/typescript and why there is no vitest, the mutation thresholds) and *Continuous integration* (the four jobs including `js`, and why `--fail-on-skipped` is passed in CI only) — and its *Verification* section now documents `.dev/validate/all.sh`, its flags and the two-language gate, replacing the stale description of the old hook

### Added

- `Contract\ExceptionInterface` and `Exception\Trait\ExceptionTrait` — exceptions now carry a structured `context` array alongside the message, read with `getContext()` and set with `setContext()` or the new fourth constructor argument. The context is purely additive: no existing message, code or previous throwable changed, so a consumer logging only `getMessage()` sees exactly what it saw before. Ported from `precision-soft/symfony-console`, which has carried it since v4.5.0, so every package in the portfolio now exposes the same contract. Note for consumers subclassing the package exception: a subclass that already declares its own `$context` property or a `getContext()`/`setContext()` method will collide with the trait
- coverage for the two request-handling contracts that only POST had ever exercised. **`PUT` and `PATCH` are now tested**: they share one `switch` case with `POST` in `getDataAndContext()`, and every test in the suite drove `POST`, so dropping either label — which sends that method to the `default:` arm and its *"can not handle"* exception — left the whole suite green. Both are documented request methods, and outside `POST` nothing proved a form could handle a body at all. **And `handleRequest()`'s `$sanitizeData` default is asserted**: `sanitizeData()` was covered directly but nothing exercised the default that applies it, so flipping it would have silently stopped sanitising every payload in every consumer. The two states are separated by populating an existing dto, where a dropped empty array leaves the previous value in place and an unsanitised one overwrites it
- `AbstractFormService::getDataAndContext()` — a malformed JSON request body now reports `formName` and `requestMethod` in the exception context. The form name was only ever available interpolated into the message, and the request method was not reported at all
- `assets/react/tsconfig.json`, `assets/react/types/vendor.d.ts` and the host-module `.d.ts` stubs — `tsc --noEmit` at
  `strict`, wired into `composer check`'s neighbour `.dev/validate/all.sh` and the pre-commit hook. The package ships sources only, so the compiler lives in the dev image and the dependencies are declared rather than installed; the stubs beside the host-provided import paths double as the contract the host application has to satisfy. The first run reported 196 errors, every one of the crash-grade fixes above among them
- `phpstan.neon` — phpstan level 8 over `src/` and `tests/`, in `composer check`. **No baseline**: the 39 errors of the first run are all fixed, and two of them were the `SerializerInterface` defect above
- `tests/Functional/FormRoundTripFunctionalTest.php` — the package's first end-to-end test, `@group integration`, run by the new `composer test-integration`: a real form service renders a DTO to json, that json returns as a real request body, and the service denormalizes it back. It proved the documented sanitize asymmetry end to end — an empty string clears the field, an empty array leaves the DTO default standing — and found that `TestDto` had no setters, so the round trip had been writing nothing while the assertions compared defaults to defaults
- `assets/react/service/Element.test.ts` — node test coverage for the extracted schema logic (4 → 19 node tests)
- `tests/Element/`, `tests/Form/`, `tests/Exception/` — coverage pinning every fix above, each verified to fail against the code before it (108 → 120 phpunit tests)

### Removed

- `phpcs.xml` and the `squizlabs/php_codesniffer` dev dependency — the pre-commit hook replaced `phpcs` with
  `php-cs-fixer` in v1.0.6 and nothing has invoked phpcs since; the ruleset still pointed at `bin/`, `config/` and
  `public/`, none of which exist in this repository

## [v1.0.7] - 2026-06-17 - Strict Date Validation And Min/Max Range Enforcement

### Fixed

- `DateElement::renderElement()`, `DateTimeElement::renderElement()` — value validation now round-trips through the format (`DateTime::createFromFormat('!'.$format, $value)->format($format) === $value`), so overflow dates (e.g. `2021-02-30`), trailing data and non-canonical input are rejected with `InvalidValueException`; previously `createFromFormat()` silently normalized them (e.g. `2021-02-30` → `2021-03-02`) and accepted the value
- `PrototypeCollectionElement::renderElement()` — a non-array item inside the collection value now throws the bundle's `InvalidValueException` instead of a raw `TypeError` from the typed `renderElements(array $value)` call
- `assets/react/formV1/Form.tsx`, `assets/react/formV2/Form.tsx` — the `BlockUi` loading className concatenated `loadingClassName` directly onto `'h-100 w-100'` without a separator and guarded it with a `null` check that never matched the optional (`undefined`) prop, producing values like `h-100 w-100undefined`; it is now assembled with an array join guarded by `undefined !==`, matching the existing `containerClassName` handling

### Added

- `DateElement`, `DateTimeElement` — the existing `$min`/`$max` constructor arguments are now enforced server-side as an inclusive range; an out-of-range value throws `InvalidValueException`. Bounds are parsed with the `!` format prefix so the comparison is deterministic (a value equal to `$min`/`$max` passes) and works for non-lexicographic formats such as `d-m-Y` (behavior change for consumers that previously relied on `$min`/`$max` being client-side hints only)
- `src/Element/Trait/DateValidationTrait` — extracted `isValidDate()` / `isWithinRange()` shared by `DateElement` and `DateTimeElement`
- `tests/Element/` — added `AutocompleteElementTest`, `CollectionElementTest`, `DateTimeElementTest`, `FileElementTest`, `HiddenElementTest`, `LabelElementTest`, `PasswordElementTest` and `PrototypeCollectionElementTest`; extended `DateElementTest` with overflow and min/max boundary/range coverage (including a `d-m-Y` range case proving non-lexicographic comparison)

### Changed

- `README.md` — documented the `DateElement`/`DateTimeElement` `$format`/`$min`/`$max` constructor arguments, the strict format validation, the inclusive server-side min/max enforcement, and the `CollectionElement`/`PrototypeCollectionElement` non-array guards

## [v1.0.6] - 2026-06-17 - Fix asset build and harden request-body handling

### Fixed

- `assets/react/service/Utility.ts` — added the missing `clone()` helper (deep clone via `structuredClone` with a `JSON` round-trip fallback); `formV1/FormBuilder` and `formV2/PrototypeCollectionField` imported `clone` from a non-existent `../service/Uility` module, which broke the bundle build
- `formV1/FormBuilder`, `formV2/PrototypeCollectionField` — corrected the `clone` import path from the misspelled `../service/Uility` to `../service/Utility`
- `AbstractFormService::getDataAndContext()` — a request body that decodes to a non-array scalar (e.g. `5`) now throws a clear `Exception` instead of a raw `TypeError` from the array-offset access
- `AbstractFormService::getDataAndContext()` — a malformed JSON request body now throws the bundle's own `Exception` ("request body for form `…` is not valid JSON", original kept as `previous`) instead of letting Symfony's `NotEncodableValueException` escape, matching how the rest of the bundle reports input problems

### Changed

- `AbstractFormService::sanitizeData()` — empty strings are no longer dropped, so a `PATCH`/`PUT` can explicitly clear a field by sending `""`; empty arrays are still dropped so an absent nested structure does not override DTO defaults (behavior change)
- `README.md` — expanded with form-element reference, request/sanitization behavior, V1-vs-V2 guidance and a Tests section; corrected the form-service example (added the required `getMethod()`, fixed the `getAction()`/`build()` signatures to take `DtoInterface $dto`)

### Added

- `FormTest::testHandleThrowsExceptionForScalarRequestBody` covering the scalar-body guard
- `FormTest::testSanitizeDataKeepsEmptyStringsButDropsEmptyArrays` covering the sanitize behavior
- `FormTest::testHandleThrowsExceptionForMalformedJsonBody` covering the malformed-JSON guard
- `assets/react/` — dependency-free test harness for the framework-agnostic asset services using Node's built-in test runner (`npm test` → `node --test`), with `service/Utility.test.ts` covering `clone()`; the dev container now ships Node, and the pre-commit hook runs `node --test` when `.ts/.tsx` files are staged
- `composer.json` — added `test`, `cs-check`, `cs-fix` and an aggregate `check` convenience script wrapping `simple-phpunit` and `php-cs-fixer`

## [v1.0.5] - 2026-04-28 - Align Symfony Phpunit Dev Dependency With Fleet

### Changed

- `composer.json` — `precision-soft/symfony-phpunit` constraint changed from `1.*` to `^3.0` for fleet alignment; the dependency was effectively unused (tests extend `PHPUnit\Framework\TestCase` directly), so no test code changes were required
- `composer.lock` — regenerated; `precision-soft/symfony-phpunit` upgraded `v1.1.0` → `v3.4.3`

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

[Unreleased]: https://github.com/precision-soft/symfony-json-form/compare/v1.1.0...HEAD

[v1.1.0]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.7...v1.1.0

[v1.0.7]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.6...v1.0.7

[v1.0.6]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.5...v1.0.6

[v1.0.5]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.4...v1.0.5

[v1.0.4]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.3...v1.0.4

[v1.0.3]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.2...v1.0.3

[v1.0.2]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.1...v1.0.2

[v1.0.1]: https://github.com/precision-soft/symfony-json-form/compare/v1.0.0...v1.0.1

[v1.0.0]: https://github.com/precision-soft/symfony-json-form/releases/tag/v1.0.0
