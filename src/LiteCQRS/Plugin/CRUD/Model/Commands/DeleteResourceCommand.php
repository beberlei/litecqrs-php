<?php

namespace LiteCQRS\Plugin\CRUD\Model\Commands;

use LiteCQRS\DefaultCommand;

class DeleteResourceCommand extends DefaultCommand
{
    public $class;
    public $id;
}
