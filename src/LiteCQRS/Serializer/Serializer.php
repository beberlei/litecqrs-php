<?php

namespace LiteCQRS\Serializer;

interface Serializer
{
    public function fromArray(array $data);
    public function toArray($object);
}
