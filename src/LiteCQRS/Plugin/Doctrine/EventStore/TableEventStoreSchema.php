<?php
namespace LiteCQRS\Plugin\Doctrine\EventStore;

use Doctrine\DBAL\Connection;
use Doctrine\DBAL\Schema\Schema;

class TableEventStoreSchema
{
    private $table;

    public function __construct($table = 'litecqrs_events')
    {
        $this->table = $table;
    }

    public function getTableSchema()
    {
        $schema = new Schema();
        $table = $schema->createTable($this->table);
        $table->addColumn('id', 'integer', array('autoincrement' => true));
        $table->addColumn('event_id', 'string', array('notnull' => true));
        $table->addColumn('aggregate_type', 'string', array('notnull' => false));
        $table->addColumn('aggregate_id', 'string', array('notnull' => false));
        $table->addColumn('event', 'string', array('notnull' => true));
        $table->addColumn('event_date', 'datetime', array('notnull' => true));
        $table->addColumn('command_id', 'string', array('notnull' => false));
        $table->addColumn('session_id', 'string', array('notnull' => false));
        $table->addColumn('data', 'text');
        $table->setPrimaryKey(array('id'));
        $table->addIndex(array('aggregate_type', 'aggregate_id'));
        return $table;
    }
}
