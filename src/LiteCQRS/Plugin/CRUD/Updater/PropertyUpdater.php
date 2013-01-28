<?php

namespace LiteCQRS\Plugin\CRUD\Updater;

interface PropertyUpdater
{
    /**
     * Update domain model
     *
     * @param object $domain
     * @param array  $data   new data, key-value model properties
     */
    function update($domain, array $data);
}
