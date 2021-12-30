<?php

namespace PerryRylance\Png\Chunks;

use PerryRylance\Png\Chunk;
use PerryRylance\Png\Stream;

class zTXt extends Chunk
{
	public static function fromChunk(Chunk $chunk)
	{
		$zTXt = new zTXt();

		foreach($chunk as $key => $value)
			$zTXt->{$key} = $value;
		
		return $zTXt;
	}

	public function __get($name)
	{
		switch($name)
		{
			case "keyword":

				$stream		= new Stream($this->data);
				$keyword	= "";

				while($byte = $stream->readByte())
					$keyword .= chr( $byte );

				if(strlen($keyword) < 1 || strlen($keyword) > 79)
					throw new \Exception("Expected keyword between 1 - 79 bytes");
				
				return $keyword;

				break;
			
			case "text":

				$keyword	= $this->keyword;
				$stream		= new Stream($this->data);

				// NB: Skip keyword and null terminator
				$stream->skip( strlen($keyword) + 1 );
				
				$method		= $stream->readByte();

				if($method != 0)
					throw new \Exception("Unsupported compression method");
				
				$bytes		= [];

				while(!$stream->isEof())
					$bytes []= $stream->readByte();
				
				$args		= array_merge(["C*"], $bytes);
				$binary		= call_user_func_array("pack", $args);

				$result		= gzuncompress($binary);

				return $result;

				break;
		}
	}

	public function __set($name, $value)
	{
		if($name == "text")
		{
			$stream		= new Stream();
			
			$stream->writeString( $this->keyword );
			$stream->writeByte(0); // NB: Null terminator
			$stream->writeByte(0); // NB: Compression method
			
			$compressed	= gzcompress($value);

			$stream->writeBytes($compressed);

			$this->data	= $stream->toByteArray();

			$this->length = count($this->data);

			$this->updateCrc();
		}
	}

	private function updateCrc()
	{
		$data = new Stream();
		$data->writeString( $this->type );
		$data->append( $this->data );
		$this->crc = crc32( $data->toBinaryString() );
	}
}