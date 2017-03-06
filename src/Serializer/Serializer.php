<?php

namespace Lidskasila\Glow\Serializer;

interface Serializer
{

	public function fromArray(array $data);

	public function toArray($object);
}
