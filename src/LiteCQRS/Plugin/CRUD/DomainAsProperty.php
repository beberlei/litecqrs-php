<?php

namespace LiteCQRS\Plugin\CRUD;

use LiteCQRS\Plugin\CRUD\Model\Events\DefaultDataEvent;

use LiteCQRS\Plugin\CRUD\Updater\AccessFilter\PropertyFilter;
use LiteCQRS\Plugin\CRUD\Updater\PropertyUpdater;

trait DomainAsProperty
{
    /** @var PropertyUpdater */
    protected $updater;

    /** @var PropertyFilter */
    protected $filter;

    /** @var object */
    protected $domain;

    /**
     * Sets domain
     *
     * @param object $domain
     *
     * @return DomainAsProperty
     */
    public function setDomain($domain)
    {
        $this->domain = $domain;

        return $this;
    }

    /**
     * Sets domain
     *
     * @return object
     */
    public function getDomain()
    {
        return $this->domain;
    }

    /**
     * Update domain data
     *
     * @param Model\Events\DefaultDataEvent $event
     */
    protected function updateDomain(DefaultDataEvent $event)
    {
        $data = $this->filter->filter($this->domain, $event->data);
        $this->updater->update($this->domain, $data);
    }
}
