<?php
namespace Codeaken\CloudServer\Exception;

class AuthenticationException extends \Exception
{
    public function __construct()
    {
        parent::__construct('Authentication failed', 0);
    }
}
