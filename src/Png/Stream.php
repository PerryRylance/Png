<?php

namespace PerryRylance\Png;

require_once(__DIR__ . "/Chunk.php");
require_once(__DIR__ . "/Chunks/zTXt.php");

use PerryRylance\Png\Chunks\zTXt;

class Stream
{
	private $cursor = 0;
	private $bytes;

	public function __construct($input=null)
	{
		if(is_array($input))
			$this->bytes = $input;
		else if(!is_null($input))
			$this->bytes = array_values( unpack("C*", $input) );
		else
			$this->bytes = [];
	}

	public function isEof()
	{
		return $this->cursor == count($this->bytes);
	}

	private function assertNotEof()
	{
		if($this->cursor >= count($this->bytes))
			throw new \Exception("End of file reached in stream");
	}

	public function readByte():int
	{
		$this->assertNotEof();

		return $this->bytes[ $this->cursor++ ];
	}

	public function readUnsignedInt():int
	{
		$result = 0x0;

		for($i = 0; $i < 4; $i++)
		{
			$result <<= 8;
			$result |= $this->readByte();
		}
		
		return $result;
	}

	public function readChar()
	{
		return chr( $this->readByte() );
	}

	public function readChunk():Chunk
	{
		$chunk			= new Chunk();

		$chunk->length	= $this->readUnsignedInt();
		$chunk->data	= [];

		if($chunk->length > Chunk::MAX_LENGTH)
			throw new \Exception("Chunk length exceeds maximum");

		for($i = 0; $i < 4; $i++)
			$chunk->type .= $this->readChar(); // NB: According to the PNG spec, these should not be treated as characters, so this is technically incorrect

		for($i = 0; $i < $chunk->length; $i++)
			$chunk->data []= $this->readByte();
		
		$chunk->crc		= $this->readUnsignedInt();

		if($chunk->type == "zTXt")
			$chunk = zTXt::fromChunk($chunk);
		
		return $chunk;
	}

	public function skip(int $count)
	{
		for($i = 0; $i < $count; $i++)
			$this->readByte();
	}

	public function writeByte(int $byte)
	{
		if($byte < 0 || $byte > 255)
			throw new \Exception("Range error");
		
		$this->bytes	[]= $byte;
		$this->cursor	= count($this->bytes);
	}

	public function writeUnsignedInt(int $value)
	{
		if($value < 0 || $value > pow(2, 32) - 1)
			throw new \Exception("Range error");
		
		$this->writeByte( ($value >> 24) & 0xff );
		$this->writeByte( ($value >> 16) & 0xff );
		$this->writeByte( ($value >> 8) & 0xff );
		$this->writeByte( $value & 0xff );
	}

	public function writeString(string $str)
	{
		for($i = 0; $i < strlen($str); $i++)
			$this->writeByte(ord($str[$i]));
	}

	public function writeBytes(string $str)
	{
		$this->bytes = array_merge(
			$this->bytes,
			array_values( unpack("C*", $str) )
		);
		$this->cursor	= count($this->bytes);
	}

	public function writeChunk(Chunk $chunk)
	{
		$this->writeUnsignedInt($chunk->length);
		$this->writeString($chunk->type);
		$this->append($chunk->data);
		$this->writeUnsignedInt($chunk->crc);
	}

	public function append(array $bytes)
	{
		$this->bytes	= array_merge($this->bytes, $bytes);
		$this->cursor	= count($this->bytes);
	}

	public function toByteArray()
	{
		return array_merge([], $this->bytes);
	}

	public function toBinaryString()
	{
		$args		= array_merge(["C*"], $this->bytes);
		$binary		= call_user_func_array("pack", $args);

		return $binary;
	}
}