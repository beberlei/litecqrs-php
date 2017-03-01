<?php

namespace LiteCQRS\Serializer;

class NoopSerializer implements Serializer
{

	public function toArray($data)
	{
		return [ 'php_class' => get_class($data) ];
	}

	public function fromArray(array $data)
	{
		return new $data['php_class'];
	}
}
