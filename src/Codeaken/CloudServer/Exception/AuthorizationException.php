<?php
namespace Codeaken\CloudServer\Exception;

class AuthorizationException extends \Exception
{
    public function __construct()
    {
        parent::__construct('You are not allowed to perform this action', 0);
    }
}
