<?php

$loader = require_once __DIR__ . "/../vendor/autoload.php";
$loader->add('LiteCQRS\\', __DIR__);

Doctrine\Common\Annotations\AnnotationRegistry::registerLoader(array($loader, 'loadClass'));
