# LiteCQRS for PHP

Small convention based CQRS library for PHP (loosly based on [LiteCQRS for C#](https://github.com/danielwertheim/LiteCQRS)).

Conventions are:

* All public methods of a command handler class are mapped to Commands "Command Class Shortname" => "MethodName", for example "MyLib\DoSomethingCommand" => "doSomething($command)"
* Domain Events are applied on Entities/Aggregate Roots "Event Class Shortname" => "applyEventClassShortname", for example "MyLib\SomethingDoneEvent => "applySomethingDone($event)"
* Domain Events are applied to Event Handlers "Event Class Shortname" => "onEventClassShortname", for example "MyLib\SomethingDoneEvent" => "onSomethingDone($event)"

You can stick yourself together a simple CQRS application by implementing a ``CommandBus` and an ``EventMessageBus``.
