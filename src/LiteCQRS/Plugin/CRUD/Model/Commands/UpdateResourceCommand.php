<?php

namespace LiteCQRS\Plugin\CRUD\Model\Commands;

use LiteCQRS\DefaultCommand;

class UpdateResourceCommand
{
    public $class;
    public $id;
    public $data = array();
}

