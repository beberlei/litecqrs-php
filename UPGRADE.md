## Changes

A list of changes that may cause backwards incompatible breaks in your code.

### From 1.0 to 1.1

* Extending ``LiteCQRS\Command`` and ``LiteCQRS\DomainEvent`` is NOT required anymore.
  In fact you can use any class as command or event. The naming conventions alone
  make sure command handlers and event listeners are detected.

* JMS Serializer Plugin cannot "detach" aggregate root properties that are part
  of an event that is serialized anymore. Putting related aggregate roots into
  an Event is therefore not supported anymore (and not a good idea even with
  JMS Serializer 0.9 anyways).
