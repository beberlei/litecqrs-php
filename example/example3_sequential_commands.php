<?php
/**
 * LiteCQRS processes commands sequentially and in isolation.  If you have a
 * chain of commands, called from each other, then they are executed in
 * isolation and one after another.
 *
 * This requires some rethinking, but also simplifies considerably.
 *
 * This example shows how nesting many commands still executes them
 * sequentially. Commands are handled and then a value is echoed in the parent
 * command that clearly shows that the child command hasn't been executed yet.
 */

namespace MyApp;

require_once __DIR__ . "/../vendor/autoload.php";

use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Bus\CommandBus;
use LiteCQRS\DefaultCommand;

class StringManipulation
{
    private $commandBus;

    public function __construct(CommandBus $commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function slugify(Slugify $command)
    {
        $this->commandBus->handle(new Lower(array("string" => $command->string)));
        echo "Scheduled some commands, current value: " . $command->string->value . "\n";
    }

    public function lower(Lower $command)
    {
        $this->commandBus->handle(new Ucfirst(array("string" => $command->string)));
        $command->string->value = strtolower($command->string->value);
        echo "Lower executed, current value: " . $command->string->value . "\n";
    }

    public function ucfirst(Ucfirst $command)
    {
        $this->commandBus->handle(new RemoveNonAscii(array("string" => $command->string)));
        $command->string->value = ucfirst($command->string->value);
        echo "Ucfirst executed, current value: " . $command->string->value . "\n";
    }

    public function removeNonAscii(RemoveNonAscii $command)
    {
        $command->string->value = preg_replace('([^a-zA-Z0-9]+)', '', $command->string->value);
        echo "Remove Non Ascii executed, current value: " . $command->string->value . "\n";
    }
}

class String
{
    public $value;
}

class Slugify extends DefaultCommand
{
    public $string;
}

class Lower extends DefaultCommand
{
    public $string;
}

class Ucfirst extends DefaultCommand
{
    public $string;
}

class RemoveNonAscii extends DefaultCommand
{
    public $string;
}

$commandBus = new DirectCommandBus();

$stringService = new StringManipulation($commandBus);

$commandBus->register('MyApp\Slugify', $stringService);
$commandBus->register('MyApp\Lower', $stringService);
$commandBus->register('MyApp\Ucfirst', $stringService);
$commandBus->register('MyApp\RemoveNonAscii', $stringService);

$string = new String();
$string->value = isset($argv[1]) ? $argv[1] : "Hello World!";
$commandBus->handle(new Slugify(array("string" => $string)));

