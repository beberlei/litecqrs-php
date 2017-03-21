<?php

namespace LidskaSila\Glow\Serializer;

interface Serializer
{

	public function fromArray(array $data);

	public function toArray($value);
}
