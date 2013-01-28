<?php

namespace LiteCQRS\Plugin\CRUD\Updater\AccessFilter;

use LiteCQRS\Plugin\CRUD\Updater\AccessFilter\Model\PublicPropertiesMap;

class InterfaceFilter implements PropertyFilter
{
    /**
     * {@inheritDoc}
     */
    public function filter($domain, array $data)
    {
        return $domain instanceof PublicPropertiesMap
            ? array_intersect_assoc($data, $domain->getPublicPropertiesMap())
            : array();
    }
}
