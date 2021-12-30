<?php declare(strict_types=1);

require_once(__DIR__ . "/PngTest.php");

use PerryRylance\Png;
use PerryRylance\Png\Chunk;
use PerryRylance\Png\Chunks\zTXt;

final class TextTest extends PngTest
{
	private function getTextChunk():Chunk
	{
		$chunks = array_filter( $this->png->chunks, function(Chunk $chunk) {
			return $chunk instanceof zTXt;
		} );

		$this->assertNotEmpty($chunks);

		$chunk = array_values($chunks)[0];

		return $chunk;
	}

	public function testReading()
	{
		$this->parseTestFile();

		$chunk = $this->getTextChunk();

		$this->assertEquals($chunk->text, "https://www.google.com");
	}

	public function testModification()
	{
		$this->parseTestFile();

		$chunk = $this->getTextChunk();

		$value = "https://perryrylance.com";

		$chunk->text = $value;

		$this->assertEquals($chunk->text, $value);
		$this->assertEquals($chunk->crc, 0xacd87090);
	}
}