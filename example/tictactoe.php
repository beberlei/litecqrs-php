<?php

/**
 * A TicTacToe game using CQRS.
 *
 * You start, the computer plays a random strategy.
 *
 * Commands:
 * - MarkField
 *
 * Events:
 * - FieldMarked
 * - GameWon
 * - GameDraw
 *
 * Command Handlers:
 * - BoardService
 *
 * Event Listeners:
 * - Display Board Listener
 * - Human Player Listener
 * - Computer Player Listener
 */

namespace TicTacToe;

require_once __DIR__ . "/../vendor/autoload.php";

use LiteCQRS\AggregateRoot;
use LiteCQRS\Bus\DirectCommandBus;
use LiteCQRS\Bus\InMemoryEventMessageBus;
use LiteCQRS\Bus\SimpleIdentityMap;
use LiteCQRS\Bus\EventMessageHandlerFactory;
use LiteCQRS\DefaultCommand;
use LiteCQRS\DefaultDomainEvent;
use InvalidArgumentException;
use LiteCQRS\Bus\CommandFailedStackException;

class Board extends AggregateRoot
{
    private $rows = array();
    private $fields = array();

    public function __construct()
    {
        // horizontal: A1, A2, A3; B1, B2; B3; C1; C2; C3
        $this->rows[] = array("A1", "A2", "A3");
        $this->rows[] = array("B1", "B2", "B3");
        $this->rows[] = array("C1", "C2", "C3");
        // vertical
        $this->rows[] = array("A1", "B1", "C1");
        $this->rows[] = array("A2", "B2", "C2");
        $this->rows[] = array("A3", "B3", "C3");
        // diagonal
        $this->rows[] = array("A1", "B2", "C3");
        $this->rows[] = array("A3", "B2", "C1");

        $this->fields = array(
            "A1" => false, "A2" => false, "A3" => false,
            "B1" => false, "B2" => false, "B3" => false,
            "C1" => false, "C2" => false, "C3" => false,
        );
    }

    public function mark($field, $player)
    {
        $field = strtoupper($field);

        if (!isset($this->fields[$field])) {
            throw new \InvalidArgumentException("Field $field does not exist.");
        }

        if ($this->fields[$field] !== false) {
            throw new \InvalidArgumentException("Field was already marked.");
        }

        $this->fields[$field] = $player;

        $cannotBeWon = 0;
        foreach ($this->rows as $row) {

            if ($this->isWinner($player, $row)) {
                $this->raise(new GameWon(array("row" => $row, "player" => $player)));
                return;
            } else if ($this->cannotBeWon($row)) {
                $cannotBeWon++;
            }
        }

        if ($cannotBeWon === count($this->rows)) {
            $this->raise(new GameDraw());
            return;
        }

        $this->raise(new FieldMarked(array("field" => $field, "player" => $player)));
    }

    private function isWinner($player, $row)
    {
        foreach ($row as $field) {
            if ($this->fields[$field] !== $player) {
                return false;
            }
        }

        return true;
    }

    private function cannotBeWon($row)
    {
        $owners = array();
        foreach ($row as $field) {
            $owners[] = $this->fields[$field];
        }

        return count(array_filter($owners)) == 3 && count(array_unique($owners)) > 1;
    }
}

class BoardService
{
    const HUMAN = 'X';
    const COMPUTER = 'O';

    private $board;
    private $currentPlayer;

    public function __construct($identityMap)
    {
        $this->board         = new Board();
        $this->currentPlayer = self::HUMAN;
        $identityMap->add($this->board);
    }

    public function markField(MarkField $command)
    {
        if ($command->player !== $this->currentPlayer) {
            throw new \InvalidArgumentException("You are not currenetly on turn.");
        }

        $this->board->mark($command->field, $command->player);
        $this->currentPlayer = $this->currentPlayer == self::HUMAN ? self::COMPUTER : self::HUMAN;
    }
}

class HumanPlayerService
{
    private $commandBus;
    private $gameOver = false;

    public function __construct($commandBus)
    {
        $this->commandBus = $commandBus;
    }
    public function onFieldMarked(FieldMarked $event)
    {
        if ($event->player == BoardService::HUMAN) {
            return;
        }

        $this->ask();
    }

    public function ask()
    {
        $nextField = $this->askNextField();
        $this->commandBus->handle(new MarkField(array(
            "player" => BoardService::HUMAN,
            "field" => $nextField,
        )));
    }

    protected function askNextField()
    {
        fwrite(STDOUT, "What field do you want to mark? ");
        return trim(fgets(STDIN));
    }

    public function onGameDraw(GameDraw $event)
    {
        $this->gameOver = true;
    }

    public function onGameWon(GameWon $event)
    {
        $this->gameOver = true;
    }

    public function gameOver()
    {
        return $this->gameOver;
    }
}

class ComputerPlayerService
{
    private $fields = array(
        "A1" => true, "A2" => true, "A3" => true,
        "B1" => true, "B2" => true, "B3" => true,
        "C1" => true, "C2" => true, "C3" => true,
    );

    private $commandBus;

    public function __construct($commandBus)
    {
        $this->commandBus = $commandBus;
    }

    public function onFieldMarked(FieldMarked $event)
    {
        $this->fields[$event->field] = false;

        if ($event->player == BoardService::COMPUTER) {
            return;
        }

        $availableFields = array_filter($this->fields);
        $nextField = array_rand($availableFields);

        $this->commandBus->handle(new MarkField(array(
            'player' => BoardService::COMPUTER,
            'field' => $nextField,
        )));
    }
}

class DisplayService
{
    private $fields = array(
        "A1" => "", "A2" => "", "A3" => "",
        "B1" => "", "B2" => "", "B3" => "",
        "C1" => "", "C2" => "", "C3" => "",
    );

    public function onFieldMarked(FieldMarked $event)
    {
        $this->fields[$event->field] = $event->player;

        printf("Player %s marked field %s\n", $event->player, $event->field);

        $i = 0;
        foreach ($this->fields as $field => $player) {
            if ($i % 3 == 0) {
                echo str_repeat("-", 9) . "\n";
                echo "|";
            }
            echo $player ?: " ";
            echo "|";
            if ($i % 3 == 2) {
                echo "\n";
            }
            $i++;
        }
        echo str_repeat("-", 9) . "\n\n";
    }

    public function onGameWon(GameWon $event)
    {
        printf("PLAYER %s HAS WON THE GAME WITH ROW: %s\n\n", $event->player, implode(", ", $event->row));
    }

    public function onGameDraw(GameDraw $event)
    {
        printf("THE GAME IS A DRAW\n\n");
    }
}

class GameDraw extends DefaultDomainEvent
{
}
class GameWon extends DefaultDomainEvent
{
    public $row;
    public $player;
}
class FieldMarked extends DefaultDomainEvent
{
    public $field;
    public $player;
}
class MarkField extends DefaultCommand
{
    public $field;
    public $player;
}

// 1. Setup the Library with InMemory Handlers
$messageBus  = new InMemoryEventMessageBus();
$identityMap = new SimpleIdentityMap();
$commandBus  = new DirectCommandBus(array(
    new EventMessageHandlerFactory($messageBus, $identityMap)
));

// 2. register
$boardService = new BoardService($identityMap);
$humanService = new HumanPlayerService($commandBus);
$aiService = new ComputerPlayerService($commandBus);
$uiService = new DisplayService();

$commandBus->register("TicTacToe\MarkField", $boardService);
$messageBus->register($aiService);
$messageBus->register($uiService);
$messageBus->register($humanService);

while (!$humanService->gameOver()) {
    try {
        $humanService->ask();
    } catch(CommandFailedStackException $e) {
    }
}
