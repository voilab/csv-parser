# CSV parser

This class uses `fgetcsv` to parse a file or a string, extract columns and
provide per-column method to manipulate data.

It comes with a basic error handling, so it is possible to collect all errors
in the CSV resource and, then, do something with this array of errors.

The process is very quick and uses all the power of `fgetcsv`. Very small code
is added around it to provide all the functionalities. It is mainly up to you
to be aware of what your methods do and how many memory they use.

## Install

Via Composer

Create a composer.json file in your project root:
``` json
{
    "require": {
        "voilab/csv": "^0.3.0"
    }
}
```

``` bash
$ composer require voilab/csv
```

## Usage

### Available methods

```php
$parser = new \voilab\csv\Parser($defaultOptions = []);

$result = $parser->fromString($str = "A;B\n1;test", $options = []);

// or
$result = $parser->fromFile($file = 'file.csv', $options = []);

// or with a raw resource (fopen, fsockopen, php://memory, etc)
$result = $parser->fromResource($resource, $options = []);
```

### Simple example

```php
$parser = new \voilab\csv\Parser([
    'delimiter' => ';',
    'columns' => [
        'A' => function (string $data) {
            return (int) $data;
        },
        'B' => function (string $data) {
            return get_object_from_db($data);
        }
    ]
]);

$csv = <<<CSV
A;B
4;hello
2;world
CSV;

$result = $parser->fromString($csv);

foreach ($result as $row) {
    $row['B']->someMethod();
    var_dump($row['A']); // int
}
```

### Full example

```php
$parser->fromFile('file.csv', [
    // fgetcsv
    'delimiter' => ',',
    'enclosure' => '"',
    'escape' => '\\',
    'length' => 0,
    // headers management
    'headers' => true,
    'strictHeaders' => true,
    'ignoreMissingHeaders' => false,
    // big files
    'start' => 0,
    'size' => 0,
    // data pre-manipulation
    'autotrim' => true,
    'onBeforeColumnParse' => function (string $data) {
        return utf8_encode($data);
    },
    // data post-manipulation
    'onRowParsed' => function (array $row) {
        do_some_stuff($row);
    },
    'onError' => function (\Exception $e, int $index) {
        throw new \Exception($e->getMessage() . ": at line $index");
    }
    // CSV column definition
    'columns' => [
        'A as id' => function (string $data) {
            return (int) $data;
        },
        'B as firstname' => function (string $data) {
            return ucfirst($data);
        },
        'C as name' => function (string $data) {
            if (!$data) {
                throw new \Exception("Name is mandatory and is missing");
            }
            return ucfirst($data);
        }
    ]
]);
```

## Documentation

### Options

These are the options you can provide at constructor level or when calling
`from*` methods. Details for `fgetcsv` options can be found here:
http://php.net/manual/fr/function.fgetcsv.php


| Name | Type | Default | Description |
|------|------|---------|-------------|
| delimiter | `string` | `,` | `fgetcsv` the delimiter |
| enclosure | `string` | `"` | `fgetcsv` the enclosure string. To tell PHP there isn't enclosure, set to and empty string |
| escape | `string` | `\\` | `fgetcsv` the escape string |
| length | `int` | `0` | `fgetcsv` the line length |
| headers | `bool` | `true` | Tells that CSV resource has the first line as headers |
| strictHeaders | `bool` | `true` | The defined columns must match exactly the columns in the CSV resource |
| ignoreMissingHeaders | `bool` | `false` | Skip CSV columns that aren't defined in [columns] option. Take over [strictHeaders] option. |
| start | `int` | `0` | Line index to start with. Used in big files, in conjunction with [size] option. The first index of data is `0`, regardless of headers |
| size | `int` | `0` | Number of lines to process. `0` ignores [start] and [size] |
| autotrim | `bool` | `true` | Trim all cell content, so you have always trimmed data in you columns functions |
| onBeforeColumnParse | `callable` | `null` | Method called just before any defined column method |
| onRowParsed | `callable` | `null` | Method called when a row has finished parsing |
| onError | `callable` | `null` | Method called when an error occurs, at column and at row level |
| columns | `array` |  | CSV columns definition (see examples). This option is required |

### Column function parameters

When defining a function for a column, you have access to these parameters:

| Name | Type | Description |
|------|------|-------------|
| $data | `string` | The first argument will always be a string. It is the cell content (trimmed if `autotrim` is set to true) |
| $index | `int` | The line index actually parsed. Correspond to the line number in the CSV resource (taken headers into account) |
| $row | `array` | The entire row data, **raw from `fgetcsv`**. These datas **are not** the result of the columns functions |
| $parsed | `array` | The parsed data from previous columns (columns are handled one after the other) |
| $options | `array` | The options array |

The function returns `?mixed` value.

```php
$parser->fromFile('file.csv', [
    'columns' => [
        // minimal usage
        'col1' => function (string $data) {
            return $data;
        },
        // complete usage
        'col2' => function (string $data, int $index, array $row, array $parsed, array $options) {
            return $data;
        }
    ]
]);
```

### On before column parse function parameters

Just before any CSV column data is parsed, a standard method is called so you
can operate the same way on every rows and columns data. You can use that to
manage encoding, for example.

| Name | Type | Description |
|------|------|-------------|
| $data | `string` | The first argument will always be a string. It is the cell content (trimmed if `autotrim` is set to true) |
| $index | `int` | The line index actually parsed. Correspond to the line number in the CSV resource (taken headers into account) |
| $colInfo | `array` | The current column information |
| $options | `array` | The options array |

The function should return a `string`. Be aware of type declaration in your
other functions if you want to return other types from here.

```php
$parser->fromFile('file.csv', [
    // minimal usage
    'onBeforeColumnParse' => function (string $data) {
        return utf8_encode($data);
    },
    // complete ussage
    'onBeforeColumnParse' => function (string $data, int $index, array $colInfo, array $options) : string
    {
        return utf8_encode($data);
    }
]);
```

### On row parsed function parameters

When a row is completed, you can do something with all that data.

| Name | Type | Description |
|------|------|-------------|
| $rowData | `array` | All the data parsed, for all the columns |
| $index | `int` | The line index actually parsed. Correspond to the line number in the CSV resource (taken headers into account) |
| $parsed | `array` | The parsed data from previous rows (rows are handled one after the other) |
| $options | `array` | The options array |

The function returns an `array` of `?mixed` values.

```php
$parser->fromFile('file.csv', [
    // minmal usage
    'onRowParsed' => function (array $rowData) {
        return $rowData;
    }
    // complete usage
    'onRowParsed' => function (array $rowData, int $index, array $parsed, array $options) : array
    {
        return $rowData;
    }
]);
```

### Aliasing columns

You can define aliases for columns to ease data manipulation. Just write ` as `
to activate this functionality, like `CSV column name as alias`.

Alias **must not** itself contain ` as ` string. But in the CSV resource, the
header can have such a string.

Note that if you have ` as ` in a CSV resource header, you **must** alias it
in the columns definitions. Otherwise, the parser will not find this column.


```php
$str = <<<CSV
A;B;Just as I said
4;hello;hey
2;world;hi
CSV;

$parser = new \voilab\csv\Parser();

$result = $parser->fromString($str, [
    'delimiter' => ';',
    'columns' => [
        'A as id' => function (string $data) {
            return (int) $data;
        },
        'B as content' => function (string $data) {
            return ucfirst($data);
        },
        'Just as I said as notes' => function (string $data) {
            return $data;
        }
    ]
]);
print_r($result);

/* prints:
Array (
    [0] => Array (
        [id] => 4
        [content] => Hello
        [notes] => hey
    )
    [1] => Array (
        [id] => 9
        [content] => World
        [notes] => hi
    )
)
*/
```

### Shuffling columns when defining them

You can define your columns in any order you want. You don't need to provide
them in the order they appear in the CSV. You just have to match your keys with
a header in the CSV resource.

Note that the execution order of the columns are aligned with the CSV resource.
In the example below, the function `A()` is called before `B()`.

```php
$str = <<<CSV
A;B
4;hello
2;world
CSV;

$parser = new \voilab\csv\Parser();

$result = $parser->fromString($str, [
    'delimiter' => ';',
    'columns' => [
        'B' => function (string $data) {
            // second call
            return ucfirst($data);
        },
        'A' => function (string $data) {
            // first call
            return (int) $data;
        }
    ]
]);
print_r($result);

/* prints:
Array (
    [0] => Array (
        [A] => 4
        [B] => Hello
    )
    [1] => Array (
        [A] => 9
        [B] => World
    )
)
*/
```

### Line endings problems

Just as stated in official documentation, if you have problems with recognition
in line endings, you can use the method below to activate auto detect.

`$parser->autoDetectLineEndings($value = true);`

Note that auto detect is not reseted to initial value after the parsing has
finished.

### Error management

You can use the `onError` option to collect all errors, so you can give a
message to the user with all errors in the file you found, in one shot.

You can stop the process of a row by checking the `$info` argument. It has a
key `type` which can be `row` or `column`. If it's `column`, you can throw the
error and it will call `onError` again, but with type `row`. Other columns will
be skipped for this row.

```php
$errors = [];
$data = $parser->fromFile('file.csv', [
    'onError' => function (\Exception $e, int $index, array $info, array $options) use (&$errors) {
        $errors[] = "Line [$index]: " . $e->getMessage();
        // do nothing more, so next columns and next lines can be parsed too.
    },
    'columns' => [
        'email' => function (string $data) {
            // accept null email but validate it if there's one
            if ($data && !filter_var($data, FILTER_VALIDATE_EMAIL)) {
                throw new \Exception("The email [$data] is invalid");
            }
            return $data ?: null;
        }
    ]
]);
if (count($errors)) {
    // now print in some ways all the errors found
    print_r($errors);
} else {
    // everything went well, put data in db on whatever
}
```
## Testing
```
$ phpunit
```
## Security

If you discover any security related issues, please use the issue tracker.

## License

The MIT License (MIT). Please see License File for more information.
