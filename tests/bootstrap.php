<?php

$loader = require_once __DIR__ . "/../vendor/autoload.php";

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
