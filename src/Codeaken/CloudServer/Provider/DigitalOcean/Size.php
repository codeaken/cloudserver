<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\SizeAbstract;

class Size extends SizeAbstract
{
    public static function create($data)
    {
        return new Size(
            $data['slug'],
            $data['memory'],
            $data['vcpus'],
            $data['disk'],
            $data['transfer'],
            $data['price_monthly'],
            $data['price_hourly']
        );
    }
}
