## Release 3.1.1

- changed refactoring of error management

## Release 3.1.0

- debug streams with no header
- added arrays and iterables management
- added `metadata` option
- changed errors with headers. They are propagated in `onError` option if available.
- changed tests to better reflect all CSV interface implementations

## Release 3.0.0

- removed i18n implementation. You have to manage translation with exception code
- removed `strictHeaders` and `strictDefinedHeaders`
- added `strict`, which checks if row has the same number of columns as defined
- added `required` to define which column is required
- changed tests to reflect these adaptations

## Release 2.0.1

- changed interfaces names
- documentation

## Release 2.0.0

- internally, uses CsvInterface to manipulate and parse data
- thanks to the above, it is now possible to parse streams (like PSR Stream)
- added tests for PSR streams

## Release 1.1.0

- added support for seek for large files

## Release 1.0.4

- debug counting headers

## Release 1.0.0

- deployment
