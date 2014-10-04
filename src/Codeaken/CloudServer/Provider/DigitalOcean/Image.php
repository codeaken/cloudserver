<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\ImageAbstract;

class Image extends ImageAbstract
{
    public static function create($data)
    {
        $id = empty($data['slug']) ? $data['id'] : $data['slug'];

        return new Image(
            $id,
            $data['name'],
            $data['distribution']
        );
    }
}
