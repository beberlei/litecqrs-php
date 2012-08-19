<?php

namespace LiteCQRS\Plugin\CRUD\Model\Commands;

use LiteCQRS\DefaultCommand;

class UpdateResourceCommand extends DefaultCommand
{
    public $class;
    public $id;
    public $data = array();
}

