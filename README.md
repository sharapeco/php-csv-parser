# Simple CSV Parser for PHP

CSVParser can:

- Parse CSV from string
- Output indexed arrays or associative arrays
- Output CSV from associative arrays

## Usage

Basic:

```PHP
$csv = 'aaa,bbb,ccc
ddd,eee,fff
ggg,hhh,iii';

$parser = new \sharapeco\CSVParser\CSVParser();
$parser->parse($csv);

// => [
//   ['aaa', 'bbb', 'ccc'],
//   ['ddd', 'eee', 'fff'],
//   ['ggg', 'hhh', 'iii'],
// ]
```

Associate with key:

```PHP
$csv = 'aaa,bbb,ccc
ddd,eee,fff
ggg,hhh,iii';
$keys = ['foo', 'bar', 'baz'];

$parser = new \sharapeco\CSVParser\CSVParser();
$parser->parse($csv, $keys);

// => [
//   ['foo' => 'aaa', 'bar' => 'bbb', 'baz' => 'ccc'],
//   ['foo' => 'ddd', 'bar' => 'eee', 'baz' => 'fff'],
//   ['foo' => 'ggg', 'bar' => 'hhh', 'baz' => 'iii'],
// ]
```

Render CSV:

```PHP
$rows = [
    ['foo' => 'aaa', 'bar' => 'bbb', 'baz' => 'ccc'],
    ['foo' => 'ddd', 'bar' => 'eee', 'baz' => 'fff'],
    ['foo' => 'ggg', 'bar' => 'hhh', 'baz' => 'iii'],
];
$header = ['foo', 'bar', 'baz'];

$parser = new \sharapeco\CSVParser\CSVParser();
$parser->render($rows, $header);

```

## Settings

```PHP
$parser = new CSVParser([
    'csv_encoding' => 'sjis-win',
	...
])
```

| Key | Default value | Description |
| --- | ------------- | ----------- |
| internal_encoding | 'UTF-8' | Encoding of internal data |
| csv_encoding | 'UTF-8' | CSV input/output encoding |
| delimiter | ',' | Delimiter of columns |
| quotation | '"' | Character to quote values includes delimiter, new lines or quotation charater |
| newline | "\n" | Character of newline |
