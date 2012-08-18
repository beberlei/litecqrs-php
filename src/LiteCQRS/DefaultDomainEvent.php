<?php
namespace LiteCQRS;

// This is necessary, because JMS is very aggresive about parsing stuff and
// then complaining.
use JMS\SerializerBundle\Annotation\ExclusionPolicy;
use LiteCQRS\Bus\EventMessageHeader;

/**
 * @ExclusionPolicy("all")
 */
abstract class DefaultDomainEvent implements DomainEvent
{
    /**
     * @var MessageHeader
     */
    private $messageHeader;

    public function __construct(array $data = array())
    {
        foreach ($data as $key => $value) {
            if (!property_exists($this, $key )) {
                throw new \RuntimeException("Property " . $key . " is not a valid property on event " . $this->getEventName());
            }

            $this->$key = $value;
        }
        $this->messageHeader = new EventMessageHeader();
    }

    public function getEventName()
    {
        $class = get_class($this);

        if (substr($class, -5) === "Event") {
            $class = substr($class, 0, -5);
        }

        if (strpos($class, "\\") === false) {
            return $class;
        }

        $parts = explode("\\", $class);
        return end($parts);
    }

    public function getMessageHeader()
    {
        return $this->messageHeader;
    }

    public function getAggregateId()
    {
        return $this->messageHeader->aggregateId;
    }
}

