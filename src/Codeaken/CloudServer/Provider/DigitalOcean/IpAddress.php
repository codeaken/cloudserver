<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\IpAddressAbstract;

class IpAddress extends IpAddressAbstract
{
    public static function create($data)
    {
        return new IpAddress(
            $data['ip_address'],
            $data['version'],
            $data['type']
        );
    }
}