<?php
namespace Codeaken\CloudServer;

class CloudServer
{
    public static function provider($name, $options)
    {
        $name = strtolower($name);

        switch ($name) {
            case 'digitalocean':
                return new Provider\DigitalOcean\Provider($options);

            default:
                throw new \DomainException("Unknown provider: {$name}");
        }
    }
}
