<?php

namespace LiteCQRS\Plugin\CRUD\Updater\AccessFilter;

class SetterFilter implements PropertyFilter
{
    /**
     * {@inheritDoc}
     */
    public function filter($domain, array $data)
    {
        $filteredData = array();

        foreach ($data as $propertyName => $value) {
            $method = 'set' . ucfirst($propertyName);
            if (method_exists($domain, $method)) {
                $filteredData[$propertyName] = $value;
            }
        }

        return $filteredData;
    }
}
