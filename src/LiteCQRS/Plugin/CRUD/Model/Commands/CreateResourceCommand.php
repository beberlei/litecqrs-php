<?php

namespace LiteCQRS\Plugin\CRUD\Model\Commands;

use LiteCQRS\DefaultCommand;

class CreateResourceCommand extends DefaultCommand
{
    public $class;
    public $data = array();
}

