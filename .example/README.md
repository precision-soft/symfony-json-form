# Symfony JSON Form — example

The product editor of a product catalogue — a product with every element type the package ships, a search and a registration form — whose test suite shows every public capability of `precision-soft/symfony-json-form` on code that does something real. It is the minimum of code that demonstrates the maximum of the library: the backend describes the forms and serializes them to json, the react half renders that json with both form versions and derives its initial values from it, and each half is asserted against the other through one committed fixture.

Paths in this file are relative to `.example/`.

## What it represents

- `src/Dto/ProductEditDto.php` — the product as the editor sees it: an identifier, a name, a price, a `Currency` value object, sales channels, a category, a state, two dates (one as a string, one as a `DateTimeImmutable`), typed dimensions (`ProductDimensionsDto`), prices per currency and an image upload.
- `src/Form/ProductEditForm.php` — the `POST` form that maps every element type onto that dto: `HiddenElement`, `StringElement`, `NumberElement` with bounds and a step, `LabelElement`, `ArrayElement` in both modes, `AutocompleteElement`, `BoolElement`, `DateElement`, `DateTimeElement`, `CollectionElement`, `PrototypeCollectionElement` and `FileElement`; its two context hooks give the serializer the date-time wire format and the currency codes it may accept.
- `src/Form/ProductSearchForm.php` — a `GET` form: the values arrive in the query string, as strings, and land typed in `ProductSearchDto`.
- `src/Form/UserRegistrationForm.php` — a `PasswordElement` next to a `StringElement`, on `UserRegistrationDto`.
- `src/ValueObject/Currency.php`, `src/Serializer/CurrencyNormalizer.php` — a value object the serializer cannot build on its own; the normalizer reads the allowed codes off the context the form declared, so the options offered and the values accepted cannot drift.
- `src/Serializer/CatalogSerializerFactory.php` — the serializer an application wires as `@serializer`.
- `src/Service/CatalogOptionProvider.php` — the nomenclator lists (currencies, channels) the forms offer; the tests double it.
- `assets/react/ProductEditor.tsx` — a host component rendering the editor's json with `formV1` or `formV2`; `assets/react/ProductEditor.test.ts` derives the initial values from the fixture with the shared `service/Element.ts`.

## What each test shows

| Test file                                       | Library capability demonstrated                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                              |
|-------------------------------------------------|------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------------|
| `tests/Functional/ProductEditFormTest.php`      | `render()` pinned to `tests/Fixture/product-edit-form.json` (every element type's json shape, the value object as its code, the date-time in the hook's format); a json body round-tripping into a typed dto (a whole number into a `float`, the value object, the `DateTimeImmutable`); a `PATCH` populating the dto in place; the empty string / empty list asymmetry of `sanitizeData()`; a subclass of the dto populated in place; a dto of another form refused with its context; a currency outside the nomenclator refused by the normalizer through the context; a price below `min` refused at render; a payload keyed for another form leaving the defaults; a file element that never renders a stored value back |
| `tests/Functional/ProductSearchFormTest.php`    | a `GET` form: the query string denormalized with type enforcement disabled, so `'2'` and `'1'` land as `int` and `bool`; the rendered description of the search form                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| `tests/Functional/UserRegistrationFormTest.php` | the `password` element type; a plain json round trip                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                                         |
| `assets/react/ProductEditor.test.ts`            | `computeInitialValues()` over the committed fixture: the values the frontend starts from are the ones the backend rendered, including the single-mode array reduced to its first entry, the collection and the prototype collection rows                                                                                                                                                                                                                                                                                                                                                                                                                                                                                     |

The tests are written on `precision-soft/symfony-phpunit`: `tests/Utility/CatalogOptionProviderMock.php` is a `MockDtoInterface` like the library's built-in mocks, its `onCreate` installs `byDefault()` stubs, and the fixture test overrides one with an explicit `once()` expectation.

Four behaviours worth knowing before writing a scenario of your own: a `FileElement` renders only a `null` value (an upload is never echoed back, so the dto property that receives it must be `null` when the form is rendered); a single-mode `ArrayElement` and an `AutocompleteElement` render their value as a one-item list, and the react `computeInitialValues()` reduces the array one to its first entry; a json client sends `40` for `40.0`, which the serializer turns into a `float` for a typed property (`ProductDimensionsDto`) but not inside an `array<string, float>`; the backend renders `method` in upper case (`POST`) while the react `HttpRequestTypeEnum` spells it in lower case — `HttpClient` lowercases before comparing, so both work.

## How to run

The example installs the library from the working tree through a path repository, so it always tests the code as it stands. Its `composer.lock` is not committed: a fresh install resolves the dependencies at that moment, and the root's `composer.lock` stays the reproducible set.

```shell
./dc exec dev sh -c 'cd .example && composer install && composer check'
./dc exec dev sh -c 'cd .example/assets/react && npm run typecheck && node --test'
```

Or, from the repository root, as one section of the gate:

```shell
.dev/validate/all.sh --example
```

`composer check` here is `phpstan` (level 8, with the house rules of `../.dev/phpstan/rules.neon`) and the test suite; the formatting check is the root's `composer cs-check`, whose finder includes this directory. The react half is typechecked with the root's `tsconfig.json` extended to include the library sources, and tested with Node's own runner.
