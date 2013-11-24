<?php

namespace LiteCQRS\Serializer;

class NoopSerializer
{
    public function serialize($data)
    {
        return $data;
    }

    public function unserialize($data)
    {
        return $data;
    }
}
