<?php declare(strict_types=1);

require_once(__DIR__ . "/PngTest.php");

final class WriteTest extends PngTest
{
	public function testIdenticalOutput()
	{
		$this->parseTestFile();

		$binary = $this->png->write();

		$this->assertEquals(file_get_contents(__DIR__ . "/subject.png"), $binary);
	}

	public function testGdCanParse()
	{
		$this->parseTestFile();

		$filename	= __DIR__ . "/output.png";
		$binary		= $this->png->write();

		file_put_contents($filename, $binary);

		$image		= imagecreatefrompng($filename);

		$this->assertEquals("gd", get_resource_type($image));
	}
}