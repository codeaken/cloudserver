<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\VolumeAbstract;

class Volume extends VolumeAbstract
{
    public static function create($data)
    {
        return new Volume(
            $data['id'],
            $data['region']['slug'],
            $data['size_gigabytes'],
            $data['name'],
            $data['description']
        );
    }
}
