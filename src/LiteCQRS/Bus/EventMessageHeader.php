<?php

namespace LiteCQRS\Bus;

use LiteCQRS\Util;
use LiteCQRS\EventProviderInterface;

class EventMessageHeader
{
    /**
     * Microseconds precision date of the event.
     *
     * @var DateTime
     */
    public $date;
    /**
     * UUID of the event.
     *
     * @var string
     */
    public $id;

    /**
     * @var EventProviderInterface
     */
    private $aggregate;

    /**
     * Class Name of the Aggregate that this event was triggered from.
     *
     * @var string
     */
    public $aggregateType;

    /**
     * Identifier of the Aggregate that this event was triggered from.
     *
     * @var mixed
     */
    public $aggregateId;

    /**
     * UUID of the command that lead towards emitting this event.
     *
     * @var string
     */
    public $commandId;

    /**
     * SessionId or UserId that was used during the emitting this event.
     *
     * @var string
     */
    public $sessionId;

    public function __construct()
    {
        $this->id   = Util::generateUuid();
        $this->date = Util::createMicrosecondsNow();
    }

    public function setAggregate(EventProviderInterface $object = null)
    {
        $this->aggregate = $object;
    }

    public function getAggregate()
    {
        return $this->aggregate;
    }

    public function __sleep()
    {
        return array('date', 'id', 'aggregateType', 'aggregateId', 'commandId', 'sessionId');
    }
}

