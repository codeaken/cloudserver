<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

class Action
{
    public static function waitUntilActionCompletes($provider, $id)
    {
        while (true) {
            $apiAction = $provider->sendRequest(
                'get', "actions/{$id}"
            )['action'];

            if ('in-progress' != $apiAction['status']) {
                return ('completed' == $apiAction['status']) ? true : false;
            }

            sleep(2);
        }
    }
}
