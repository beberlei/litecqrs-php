<?php

namespace LiteCQRS\Plugin\CRUD\Updater\AccessFilter\Model;

interface PublicPropertiesMap
{
    /**
     * Get writable properties map
     *
     * @return string[]
     */
    function getPublicPropertiesMap();
}
