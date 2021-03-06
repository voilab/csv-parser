# CSV parser

This class uses `fgetcsv` to parse a file or a string, extract columns and
provide per-column methods to manipulate data.

It can parse large files, HTTP streams, any types of resources, or strings.

It comes with a basic error handling, so it is possible to collect all errors
in the CSV resource and, then, do something with this array of errors.

It is extendable, so you can parse your own type of resource/stream, if you
have very special needs.

## Install

Via Composer

Create a composer.json file in your project root:
``` json
{
    "require": {
        "voilab/csv": "^4.0.0"
    }
}
```

``` bash
$ composer require voilab/csv
```

### Install PHP5 compatible version

This PHP5 version can't parse streams nor iterables.

``` json
{
    "require": {
        "voilab/csv": "dev-feature/php5"
    }
}
```

## Usage

### Available methods

```php
$parser = new \voilab\csv\Parser($defaultOptions = []);

$result = $parser->fromString($str = "A;B\n1;test", $options = []);

// or
$result = $parser->fromFile($file = '/path/file.csv', $options = []);

// or with a raw resource (fopen, fsockopen, php://memory, etc)
$result = $parser->fromResource($resource, $options = []);

// or with an array or an Iterator interface
$result = $parser->fromIterable($array = [['A', 'B'], ['1', 'test']], $options = []);

// or with a SPL file object
$result = $parser->fromSplFile($object = new \SplFileObject('file.csv'), $options = []);

// or with a PSR stream interface (ex. HTTP response message body)
$response = $someHttpClient->request('GET', '/');
$result = $parser->fromStream($response->getBody(), $options = []);

// or with a custom \voilab\csv\CsvInterface implementation
$result => $parser->parse($myCsvInterface, $options = []);
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
            return ucfirst($data);
        }
    ]
]);

$csv = <<<CSV
A; B
4; hello
2; world
CSV;

$result = $parser->fromString($csv);

foreach ($result as $row) {
    var_dump($row['A']); // int
    var_dump($row['B']); // string with first capital letter
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
    'autoDetectLn' => null,

    // resources
    'metadata' => [],
    'close' => false,

    // PSR stream
    'lineEnding' => "\n",

    // headers management
    'headers' => true,
    'strict' => false,
    'required' => ['id', 'name'],

    // big files
    'start' => 0,
    'size' => 0,
    'seek' => 0,

    // data pre-manipulation
    'autotrim' => true,
    'onBeforeColumnParse' => function (string $data) {
        return utf8_encode($data);
    },

    // data post-manipulation
    'onRowParsed' => function (array $row) {
        $row['other_stuff'] = do_some_stuff($row);
        return $row;
    },
    'onError' => function (\Exception $e, int $index) {
        throw new \Exception($e->getMessage() . ": at line $index");
    }

    // CSV columns definition
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
        },
        // use of Optimizers (see at the end of this doc for more info)
        'D as optimized' => new \voilab\csv\Optimizer(
            function (string $data) {
                return (int) $data;
            },
            function (array $data) {
                return some_reduce_function($data);
            }
        )
    ]
]);
```

## Documentation

### Options

These are the options you can provide at constructor level or when calling
`from*` methods. Details for `fgetcsv` options can be found here:
https://php.net/fgetcsv and https://php.net/str_getcsv


| Name | Type | Default | Description |
|------|------|---------|-------------|
| delimiter | `string` | `,` | `fgetcsv` the delimiter |
| enclosure | `string` | `"` | `fgetcsv` the enclosure string. To tell PHP there isn't enclosure, set to and empty string |
| escape | `string` | `\\` | `fgetcsv` the escape string |
| length | `int` | `0` | `fgetcsv` the line length |
| autoDetectLn | `bool` | `null` | If supplied, set the PHP ini param "auto_detect_line_endings". Doesn't work with PSR streams. |
| metadata | `array` | `[]` | Resource metadata. Only used with iterables or arrays |
| close | `bool` | `false` | Tells if resource must be closed after parsing is done |
| lineEnding | `string` | `\n` | Used with PSR streams to define what is a line ending. You must set a length, so it's possible to read a line |
| headers | `bool` | `true` | Tells that CSV resource has the first line as headers |
| strict | `bool` | `false` | Tells if columns defined in [columns] option must match exactly the number of columns in CSV resource |
| required | `array` | `[]` | Columns defined in [columns] options that must be present in CSV resource (if aliased, must be the column alias) |
| start | `int` | `0` | Line index to start with. Used in big files, in conjunction with [size] option. The first index of data is `0`, regardless of headers |
| size | `int` | `0` | Number of lines to process. `0` ignores [start] and [size] |
| seek | `int` | `0` | Pointer position in file, used in conjunction with [size]. Take over [start] to define the starting position |
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
| $meta | `array` | The current column information |
| $options | `array` | The options array |
|------|------|-------------|
| return | `?mixed` | Returns final cell value |

```php
$parser->fromFile('file.csv', [
    'columns' => [
        // minimal usage
        'col1' => function (string $data) {
            return $data;
        }
    ]
]);
```

#### Headers auto-sanitization

Note that headers are automatically trimmed and their carriage returns are
removed. Also, all spaces following a space are removed. This is only for the
headers. Cells content are not manipulated, except if `autotrim` is true.

```
" a header "     => "a header"
"a       header" => "a header"
"a  
header  "        => "a header"
```

> If the column you defined in your code doesn't exist in CSV resource **and**
> doesn't appear in `required` array, the `$meta` argument will have a flag
> `phantom` set to `true`. This is the way to know if the column exists or not
> in the CSV resource during parsing.

### On before column parse function parameters

Just before any CSV column data is parsed, a standard method is called so you
can operate the same way on every rows and columns data. You can use that to
manage encoding, for example.

| Name | Type | Description |
|------|------|-------------|
| $data | `string` | The first argument will always be a string. It is the cell content (trimmed if `autotrim` is set to true) |
| $index | `int` | The line index actually parsed. Correspond to the line number in the CSV resource (taken headers into account) |
| $meta | `array` | The current column information |
| $options | `array` | The options array |
|------|------|-------------|
| return | `string` | Returns cell value |

> Be aware of type declaration in your columns functions if you want to return
> other types from here.

```php
$parser->fromFile('file.csv', [
    // minimal usage
    'onBeforeColumnParse' => function (string $data) : string {
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
|------|------|-------------|
| return | `array` | Returns a multidimensional `array` of `?mixed` values |

```php
$parser->fromFile('file.csv', [
    // minmal usage
    'onRowParsed' => function (array $rowData) {
        return $rowData;
    }
]);
```

### Aliasing columns

You can define aliases for columns to ease data manipulation. Just write ` as `
to activate this functionality, like `CSV column name as alias`.

Alias **must not** itself contain ` as ` string. But in the CSV resource, the
header can have such a string.

> Note that if you have ` as ` in a CSV resource header, you **must** alias it
> in the columns definitions. Otherwise, the parser will not find this column.


```php
$str = <<<CSV
A; B    ; Just as I said
4; hello; hey
2; world; hi
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

#### Required columns

If you have aliased a column, and it is a required column, you must use the
alias inside the `required` option.

```php
$result = $parser->fromString($str, [
    'required' => ['id', 'content'],
    'columns' => [
        'A as id' => function (string $data) {
            return (int) $data;
        },
        'B as content' => function (string $data) {
            return ucfirst($data);
        }
    ]
]);
```

### No header

If you have no header in you CSV resource, you need to define the parser like
this.

```php
$str = <<<CSV
4; hello
2; world
CSV;

$result = $parser->fromString($str, [
    'columns' => [
        '0 as id' => function (string $data) {
            return (int) $data;
        },
        '1 as content' => function (string $data) {
            return ucfirst($data);
        }
    ]
]);
print_r($result);

/* prints:
Array (
    [0] => Array (
        [id] => 4
        [content] => Hello
    )
    [1] => Array (
        [id] => 9
        [content] => World
    )
)
*/
```

### Shuffling columns when defining them

You can define your columns in any order you want. You don't need to provide
them in the order they appear in the CSV. You just have to match your keys with
a header in the CSV resource.

> Note that the execution order of the columns are aligned with your code.
> In the example below, the function `A()` is called after `B()`, even if
> column A appears first in CSV resource.

```php
$str = <<<CSV
A; B
4; hello
2; world
CSV;

$parser = new \voilab\csv\Parser();

$result = $parser->fromString($str, [
    'delimiter' => ';',
    'columns' => [
        'B' => function (string $data) {
            // first call
            return ucfirst($data);
        },
        'A' => function (string $data) {
            // second call
            return (int) $data;
        }
    ]
]);
print_r($result);

/* prints:
Array (
    [0] => Array (
        [B] => Hello
        [A] => 4
    )
    [1] => Array (
        [B] => World
        [A] => 9
    )
)
*/
```

### Seek in big files

You can use the seek mechanism to accelerate parsing big files.

Yon _can_ specify the start index. But it is not mandatory. It is used in the
error managment, to know which line bugs, or in the other methods calls, where
[$index] is given.

You are responsible for keeping [seek] and [start] snychronized. If you don't,
and you have errors, the indexes would be irrelevant.

```php
$str = <<<CSV
A; B
4; hello
2; world
...
CSV;

$parser = new \voilab\csv\Parser();

$resource = new \voilab\csv\CsvString($str);
$result = $parser->parse($resource, [
    'delimiter' => ';',
    'size' => 2,
    'columns' => [
        'B' => function (string $data) {
            return ucfirst($data);
        },
        'A' => function (string $data) {
            return (int) $data;
        }
    ]
]);

$lastPos = $resource->tell();
$resource->close();

$resource2 = new \voilab\csv\CsvString($str);
$nextResult = $parser->parse($resource2, [
    'delimiter' => ';',
    'size' => 2,
    'start' => 2, // yon **can** specify the start index. Not mandatory.
    'seek' => $lastPos,
    'columns' => [
        'B' => function (string $data) {
            return ucfirst($data);
        },
        'A' => function (string $data) {
            return (int) $data;
        }
    ]
]);
```

### Close the resource

Using `fromString()` and `fromFile()` methods, the resource will be closed
automatically. With other `from*()` methods, you can close the resource by
giving the `'close' => true` option.

### Line endings problems

Just as stated in official documentation, if you have problems with recognition
in line endings, you can use the option below to activate auto detect.

`$parser->parse($resource, [ 'autoDetectLn' => true ]);`

> Note that auto detect PHP ini param is not reseted to initial value after the
> parsing has finished.

When parsing streams (like HTTP response message body), line ending must be
specified in the array options.

### Error management

You can use the `onError` option to collect all errors, so you can give a
message to the user with all errors in the file you found, in one shot.

You can stop the process of a row by checking the `$meta` argument. It has a
key `type` which can be `row` or `column`. If it's `column`, you can throw the
error and it will call `onError` again, but with type `row`. Other columns will
be skipped for this row.

If you use an optimizer, you can call an Exception from there too. The key
`type` will then have the value `optimizer`.

```php
$errors = [];
$data = $parser->fromFile('file.csv', [
    'onError' => function (\Exception $e, int $index, array $meta, array $options) use (&$errors) {
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

### Initialization errors

Some errors are thrown before any line is parsed. You have to take this into
account.

```php
$data = $parser->fromFile('file.csv', [
    'onError' => function (\Exception $e, int $index, array $meta) {
        if ($meta['type'] === 'init') {
            // called during initialization.
            var_dump($meta['key']); // for errors with specific key
            if ($e->getCode() === \voilab\csv\Exception::HEADERMISSING) {
                throw new \Exception(sprintf("La colonne [%s] est obligatoire", $meta['key']));
            }
        }
        throw $e;
    }
]);
```

### Error and internationalization (i18n)

If you want to translate error messages, you can use the `onError` function
with `meta['type'] === 'init'` to throw the translated message.

## Working with database, column optimization

When parsing large set of data, if one column is, for example, a user ID, it's
a bad idea to call a `find($id)` method for each CSV row iteration. It's better
to take all column values, and call for a `findByIds($ids)`.

The build-in class `Optimizer` allows you to define a column this way. It takes
three arguments. The first is the function needed to parse value from CSV.
The second is a reduce function. It recieves all data of the column, and must
return an indexed array.

For example, if you have 2 rows with values `a` and `b`, the indexed result of
the reduce function would be `Array ( a => something, b => something else )`.

The third argument is a function called when a value is not found in the reduced
function.

### Documentation

#### Parse function

Same as Column function (see above)

#### Reduce function

| Name | Type | Description |
|------|------|-------------|
| $data | `array` | All the data parsed, for the column |
| $parsed | `array` | The parsed data (complete set of data) |
| $optimized | `array` | Columns already optimized. Key => value pair, where key is column name and value is the reduced function result of the column |
| $meta | `array` | The current column information |
| $options | `array` | The options array |
|------|------|-------------|
| return | `array` | Returns an indexed array |

> Returns an indexed array. If there's no correspondance between CSV column
> values and the result of the reduce function, you should not return the
> missing value.
> For example, if values are [10, 22], they are used in database query to find
> users by id, and user ID 22 doesn't exist, the result should be
> `Array ( 10 => User(id=10) )`

#### Absent function

When a value is not found in the reduced result, the default behaviour is to
set the value (like there wasn't any reduce function for this row). You can
override this by defining the absent function, and do what you want with the
value.

| Name | Type | Description |
|------|------|-------------|
| $value | `mixed` | The data parsed for the column, for this row |
| $index | `int` | The line index actually parsed. Correspond to the line number in the CSV resource (taken headers into account) |
| $parsed | `array` | The parsed data of this row |
| $optimized | `array` | Columns already optimized. Key => value pair, where key is column name and value is the reduced function result of the column |
| $meta | `array` | The current column information |
| $options | `array` | The options array |
|------|------|-------------|
| return | `?mixed` | Returns the default value for this "not found" key |

> If you have defined an error function, it will be called with a type of
> `optimizer` (check error management above) if you throw an error from here.

### Example

```php
$str = <<<CSV
A; B
4; updated John
2; updated Sybille
CSV;

$database = some_database_abstraction();

$data = $parser->fromString($str, [
    'delimiter' => ';',
    'columns' => [
        'A as user' => new \voilab\csv\Optimizer(
            // column function, same as when there's no optimizer
            function (string $data) {
                return (int) $data;
            },
            // reduce function that uses the set of datas from the 1st function
            function (array $data) use ($database) {
                $query = 'SELECT id, firstname FROM user WHERE id IN(?)';
                $users = $database->query($query, array_unique($data));
                return array_reduce($users, function ($acc, $user) {
                    $acc[$user->id] = $user;
                    return $acc;
                }, []);
            },
            // absent function. data is [int] because the first function returns
            // an [int]
            function (int $data, int $index) {
                throw new \Exception("User with id $data at index $index doesn't exist!");
            }
        ),
        'B as firstname' => function (string $data) {
            return $data;
        }
    ]
]);
print_r($result);

/* prints:
Array (
    [0] => Array (
        [user] => User ( id => 4, firstname => John )
        [firstname] => updated John
    )
    [1] => Array (
        [user] => User ( id => 2, firstname => Sybille )
        [firstname] => updated Sybille
    )
)
*/
```

## Known issues

+ with PSR streams, carriage returns are not supported in headers and in cells
content

## Testing
```
$ /vendor/bin/phpunit
```
## Security

If you discover any security related issues, please use the issue tracker.

## License

The MIT License (MIT). Please see License File for more information.
