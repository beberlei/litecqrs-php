<?php

namespace LiteCQRS\Plugin\CRUD\Updater;

class SetterBasedUpdater implements PropertyUpdater
{
    /**
     * {@inheritDoc}
     */
    public function update($domain, array $data)
    {
        foreach ($data as $key => $value) {
            $method = 'set' . ucfirst($key);
            $domain->$method($value);
        }
    }
}
