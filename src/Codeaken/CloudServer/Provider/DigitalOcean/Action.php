<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

class Action
{
    public static function waitUntilActionCompletes($provider, $id)
    {
        $httpClient = $provider->getHttpClient();

        while (true) {
            $apiAction = $httpClient->get("actions/{$id}")->json()['action'];

            if ('in-progress' != $apiAction['status']) {
                return ('completed' == $apiAction['status']) ? true : false;
            }

            sleep(2);
        }
    }
}