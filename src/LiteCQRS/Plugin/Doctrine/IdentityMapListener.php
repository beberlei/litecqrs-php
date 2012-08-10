<?php

namespace LiteCQRS\Plugin\Doctrine;

use Doctrine\Common\EventSubscriber;
use LiteCQRS\AggregateRootInterface;
use LiteCQRS\EventStore\IdentityMapInterface;

class IdentityMapListener implements EventSubscriber
{
    private $identityMap;

    public function __construct(IdentityMapInterface $identityMap)
    {
        $this->identityMap = $identityMap;
    }

    public function postLoad($event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof AggregateRootInterface) {
            $this->identityMap->add($entity);
        }
    }

    public function prePersist($event)
    {
        $entity = $event->getEntity();
        if ($entity instanceof AggregateRootInterface) {
            $this->identityMap->add($entity);
        }
    }

    public function getSubscribedEvents()
    {
        return array('postLoad', 'prePersist');
    }
}

