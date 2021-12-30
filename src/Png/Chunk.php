<?php

namespace PerryRylance\Png;

class Chunk
{
	const MAX_LENGTH = 2 ** 31 - 1;

	public $length;
	public $type;
	public $data;
	public $crc;
}