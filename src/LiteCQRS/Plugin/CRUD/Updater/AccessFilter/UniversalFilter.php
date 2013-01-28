<?php

namespace LiteCQRS\Plugin\CRUD\Updater\AccessFilter;

class UniversalFilter implements PropertyFilter
{
    protected $reflection;
    protected $setter;
    protected $interface;

    public function __construct()
    {
        $this->reflection = new ReflectionFilter;
        $this->setter     = new SetterFilter;
        $this->interface  = new InterfaceFilter;
    }

    /**
     * {@inheritDoc}
     */
    public function filter($domain, array $data)
    {
        $filteredData = $this->interface->filter($domain, $data);

        if (!$filteredData) {
            $filteredData = $this->setter->filter($domain, $data);
        }

        if (!$filteredData) {
            $filteredData = $this->reflection->filter($domain, $data);
        }

        return $filteredData;
    }
}
