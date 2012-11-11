<?php

namespace LiteCQRS\Plugin\CRUD\Updater\AccessFilter;

class ReflectionFilter implements PropertyFilter
{
    /**
     * {@inheritDoc}
     */
    public function filter($domain, array $data)
    {
        $objectRef = new \ReflectionObject($domain);

        $filteredData = array();

        foreach ($data as $propertyName => $value) {
            if ($objectRef->hasProperty($propertyName)) {
                $filteredData[$propertyName] = $value;
            }
        }

        return $filteredData;
    }
}
