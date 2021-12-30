<?php

namespace PerryRylance;

require_once(__DIR__ . "/png/Stream.php");

use PerryRylance\Png\Stream;

class Png
{
	const SIGNATURE = [0x89, 0x50, 0x4e, 0x47, 0xd, 0xa, 0x1a, 0xa];

	protected $_chunks;

	public function parse($binary)
	{
		$this->_chunks = [];
		
		$stream = new Stream($binary);

		// NB: Read signature
		for($i = 0; $i < 8; $i++)
			if($stream->readByte() != Png::SIGNATURE[$i])
				throw new \Exception("Invalid signature");

		// NB: Read chunks
		while(!$stream->isEof())
			$this->_chunks []= $stream->readChunk();
	}

	public function write()
	{
		$stream = new Stream();

		$stream->append( Png::SIGNATURE );

		foreach($this->_chunks as $chunk)
			$stream->writeChunk($chunk);
		
		return $stream->toBinaryString();
	}

	public function __get($name)
	{
		if($name == "chunks")
			return $this->_chunks;
	}
}