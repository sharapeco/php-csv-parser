<?php

use PHPUnit\Framework\TestCase;
use sharapeco\CSVParser\CSVParser;

final class CSVParserTest extends TestCase
{
	private function getCSV(string $name): string
	{
		return file_get_contents(__DIR__ . '/csv/' . $name . '.csv');
	}

	/**
	 * @test
	 */
	public function testBasic()
	{
		$parser = new CSVParser();
		$this->assertEquals(
			$parser->parse($this->getCSV('basic')),
			[
				['basic', 'csv', 'file'],
				['line', 'number', 'two'],
				['line', 'number', 'three'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testEmptyCell()
	{
		$parser = new CSVParser();
		$this->assertEquals(
			$parser->parse($this->getCSV('emptycell')),
			[
				['basic', 'csv', 'file'],
				['line', 'number', 'two'],
				['line', 'three'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testNewlines()
	{
		$parser = new CSVParser();
		$this->assertEquals(
			$parser->parse($this->getCSV('newlines')),
			[
				['basic', 'csv', 'file'],
				['line', 'number', 'two'],
				['line', 'number', 'three'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testQuote()
	{
		$parser = new CSVParser();
		$this->assertEquals(
			$parser->parse($this->getCSV('quote')),
			[
				["111\n222\n333", 'quoted value "test"'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testTSV()
	{
		$parser = new CSVParser([
			'delimiter' => "\t",
		]);
		$this->assertEquals(
			$parser->parse($this->getCSV('tsv')),
			[
				['basic', 'TAB separated value', 'file'],
				['line', 'number', 'two'],
				['line', 'number', 'three'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testUTF8BOM()
	{
		$parser = new CSVParser();
		$this->assertEquals(
			$parser->parse($this->getCSV('utf8bom')),
			[
				['with BOM', 'csv', 'file'],
				['line', 'number', 'two'],
				['line', 'number', 'three'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testShiftJISWin()
	{
		$parser = new CSVParser([
			'csv_encoding' => 'sjis-win',
		]);
		$this->assertEquals(
			$parser->parse($this->getCSV('sjis')),
			[
				['CP932', 'csv', 'file'],
				['行', '番号', '２'],
				['行', '番号', '３'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testAssociate()
	{
		$parser = new CSVParser();
		$this->assertEquals(
			$parser->parse($this->getCSV('basic'), ['first', 'second', 'third']),
			[
				['first' => 'basic', 'second' => 'csv', 'third' => 'file'],
				['first' => 'line', 'second' => 'number', 'third' => 'two'],
				['first' => 'line', 'second' => 'number', 'third' => 'three'],
			]
		);
	}

	/**
	 * @test
	 */
	public function testAssociateWithEmptyCell()
	{
		$parser = new CSVParser();
		$this->assertEquals(
			$parser->parse($this->getCSV('emptycell'), ['first', 'second', 'third']),
			[
				['first' => 'basic', 'second' => 'csv', 'third' => 'file'],
				['first' => 'line', 'second' => 'number', 'third' => 'two'],
				['first' => 'line', 'second' => 'three', 'third' => null],
			]
		);
	}

	/**
	 * @test
	 */
	public function testUnterminated()
	{
		$this->expectException(\sharapeco\CSVParser\StructException::class);

		$parser = new CSVParser();
		$parser->parse($this->getCSV('unterminated'));
	}
}
