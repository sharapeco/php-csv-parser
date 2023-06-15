<?php

namespace sharapeco\CSVParser;

use \Exception;
use \InvalidArgumentException;

function str_contains($str, $search)
{
	return strpos($str, $search) !== false;
}

class StructException extends Exception
{
}

class CSVParser
{
	protected $internalEncoding = 'UTF-8';
	protected $csvEncoding = 'UTF-8';

	protected $delimiter = ',';
	protected $quotation = '"';
	protected $escapedQuotation = '""';
	protected $newline = "\n";

	public function __construct(array $options = [])
	{
		if (isset($options['internal_encoding'])) {
			$this->internalEncoding = $options['internal_encoding'];
		}
		if (isset($options['csv_encoding'])) {
			$this->csvEncoding = $options['csv_encoding'];
		}
		if (isset($options['delimiter'])) {
			$this->delimiter = (string)$options['delimiter'];
		}
		if (isset($options['quotation'])) {
			$this->quotation = (string)$options['quotation'];
		}
		if (isset($options['newline'])) {
			$this->newline = (string)$options['newline'];
		}
	}

	public function parse($csv, array $header = null)
	{

		// UTF-8 の BOM がついている場合は除去する
		if (strtoupper($this->csvEncoding) === 'UTF-8') {
			$csv = preg_replace('/\\A\\xEF\\xBB\\xBF/', '', $csv);
		}

		$csv = mb_convert_encoding($csv, $this->internalEncoding, $this->csvEncoding);
		$csv = rtrim($csv, "\r\n");
		if ($csv === '') {
			return [];
		}

		$rows = $this->parse_($csv);
		if ($header) {
			return $this->associate($rows, $header);
		} else {
			return $rows;
		}
	}

	public function associate(array $rows, array $header = null)
	{
		if (count($rows) === 0) {
			return [];
		}
		if (!is_array($rows[0])) {
			throw new InvalidArgumentException('First argument must be an array of array');
		}

		if ($header === false) {
			$header = array_shift($rows);
		}

		$associatedRows = [];
		foreach ($rows as $row) {
			$aRow = [];
			foreach ($header as $i => $key) {
				$aRow[$key] = isset($row[$i]) ? $row[$i] : null;
			}
			$associatedRows[] = $aRow;
		}
		return $associatedRows;
	}

	public function render(array $associatedRows, array $header = null, $printHeader = false)
	{
		if (count($associatedRows) === 0) {
			return '';
		}
		if (!is_array($associatedRows[0])) {
			throw new InvalidArgumentException('First argument must be an array of array');
		}
		if (!$header) {
			$header = array_keys($associatedRows[0]);
		}

		$lines = [];
		if ($printHeader) {
			$lines[] = implode($this->delimiter, $header);
		}
		foreach ($associatedRows as $aRow) {
			if (!is_array($aRow)) {
				throw new InvalidArgumentException('First argument must be an array of array');
			}
			$line = [];
			foreach ($header as $colName) {
				$val = isset($aRow[$colName]) ? $aRow[$colName] : '';
				$line[] = $this->escape($val);
			}
			$lines[] = implode($this->delimiter, $line);
		}
		$csv = implode($this->newline, $lines);
		return mb_convert_encoding($csv, $this->csvEncoding, $this->internalEncoding);
	}

	protected function parse_($csv)
	{
		$csvlen = strlen($csv);
		$ld = strlen($this->delimiter);
		$lq = strlen($this->quotation);
		$leq = strlen($this->escapedQuotation);

		$lines = [];
		$line = [];
		$cell = '';
		$i = 0;
		$state = 0; // 0=通常, 1=quoted
		while ($i < $csvlen) {
			if ($state === 0) {
				if (substr($csv, $i, $lq) === $this->quotation) {
					// quote 開始
					$state = 1;
					$i += $lq;
				} else if (substr($csv, $i, $ld) === $this->delimiter) {
					// 列区切り
					$line[] = $cell;
					$cell = '';
					$i += $ld;
				} else if (preg_match('{^(?:\r\n|\r|\n)}', substr($csv, $i, 2), $m)) {
					// 行区切り
					$line[] = $cell;
					$cell = '';
					$lines[] = $line;
					$line = [];
					$i += strlen($m[0]);
				} else {
					// 文字
					$cell .= $this->unescape($csv[$i]);
					$i++;
				}
			} else {
				if (substr($csv, $i, $leq) === $this->escapedQuotation) {
					// escaped quote
					$cell .= $this->quotation;
					$i += $leq;
				} else if (substr($csv, $i, $lq) === $this->quotation) {
					// quote 終了
					$state = 0;
					$i += $lq;
				} else {
					// 文字
					$cell .= $this->unescape($csv[$i]);
					$i++;
				}
			}
		}

		// クォーテーションの外で終わらなければならない
		if ($state !== 0) {
			throw new StructException('Unterminated value.');
		}

		$line[] = $cell;
		if (count($line) > 0) {
			$lines[] = $line;
		}

		return $lines;
	}

	protected function escape($val)
	{
		if (
			!str_contains($val, "\n") &&
			!str_contains($val, "\r") &&
			!str_contains($val, $this->quotation) &&
			!str_contains($val, $this->delimiter)
		) {
			return $val;
		} else {
			return $this->quote($val);
		}
	}

	protected function quote($val)
	{
		return $this->quotation . str_replace($this->quotation, $this->escapedQuotation, $val) . $this->quotation;
	}

	protected function unescape($val)
	{
		return $val;
	}
}
