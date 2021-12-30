<?php declare(strict_types=1);

require_once(__DIR__ . "/PngTest.php");

use PerryRylance\Png;

final class ParseTest extends PngTest
{
	public function testSuccessfulParse()
	{
		$this->parseTestFile();

		$this->assertNotEmpty($this->png->chunks);
	}

	public function testInvalidParse()
	{
		$this->expectException(Exception::class);

		$png = new Png();
		$png->parse("Totally invalid input");
	}
}