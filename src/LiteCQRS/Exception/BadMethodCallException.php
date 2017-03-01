<?php

namespace LiteCQRS\Exception;

use LiteCQRS\LiteCQRSException;

class BadMethodCallException extends \BadMethodCallException implements LiteCQRSException
{

}
