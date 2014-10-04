<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\KernelAbstract;

class Kernel extends KernelAbstract
{
    public static function create($data)
    {
        return new Kernel(
            $data['id'],
            $data['name'],
            $data['version']
        );
    }
}