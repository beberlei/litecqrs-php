<?php

namespace LiteCQRS\Plugin\CRUD\Updater\AccessFilter;

interface PropertyFilter
{
    /**
     * Property access filter
     *
     * @param object  $domain
     * @param array   $data domain property key => data
     *
     * @return array filtered data
     */
    function filter($domain, array $data);
}
