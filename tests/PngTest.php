<?php declare(strict_types=1);

require_once(__DIR__ . "/../src/Png.php");

use PerryRylance\Png;
use PHPUnit\Framework\TestCase;

class PngTest extends TestCase
{
	protected function parseTestFile()
	{
		$this->png = new Png();
		$this->png->parse( file_get_contents(__DIR__ . "/subject.png") );
	}
}

	