<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\ProviderInterface;
use Codeaken\CloudServer\Exception\AuthenticationException;
use Codeaken\CloudServer\Exception\AuthorizationException;
use GuzzleHttp\Client;
use GuzzleHttp\Exception\ClientException;

class Provider implements ProviderInterface
{
    private $httpClient;

    public function __construct(array $options)
    {
        if ( ! isset($options['api_key'])) {
            throw new \DomainException('Missing api_key value in options');
        }

        $clientOptions = [
            'base_url' => 'https://api.digitalocean.com/v2/',
            'defaults' => [
                'headers' => ['Content-type' => 'application/json'],
                'auth'    => [$options['api_key'], '']
            ]
        ];

        $this->httpClient = new Client($clientOptions);
    }

    public function getRegions()
    {
        $apiRegions = $this->sendRequest('get', 'regions');

        $regions = [];
        foreach ($apiRegions['regions'] as $region) {
            $regions[$region['slug']] = Region::create($region);
        }

        return $regions;
    }

    public function getRegion($id)
    {
        $regions = $this->getRegions();

        if ( ! isset($regions[$id])) {
            return false;
        }

        return $regions[$id];
    }

    public function getSizes()
    {
        $apiSizes = $this->sendRequest('get', 'sizes');

        $sizes = [];
        foreach ($apiSizes['sizes'] as $size) {
            $sizes[$size['slug']] = Size::create($size);
        }

        return $sizes;
    }

    public function getSize($id)
    {
        $sizes = $this->getSizes();

        if ( ! isset($sizes[$id])) {
            return false;
        }

        return $sizes[$id];
    }

    public function getImages()
    {
        $apiImages = $this->sendRequest('get', 'images');

        $images = [];
        foreach ($apiImages['images'] as $image) {
            $id = empty($image['slug']) ? $image['id'] : $image['slug'];

            $images[$id] = Image::create($image);
        }

        return $images;
    }

    public function getImage($id)
    {
        $images = $this->getImages();

        if ( ! isset($images[$id])) {
            return false;
        }

        return $images[$id];
    }

    public function getMachines()
    {
        $apiMachines = $this->sendRequest('get', 'droplets');

        $machines = [];
        foreach ($apiMachines['droplets'] as $machine) {
            $machines[$machine['id']] = new Machine(
                $this,
                $machine
            );
        }

        return $machines;
    }

    public function getMachine($id)
    {
        $machines = $this->getMachines();

        if ( ! isset($machines[$id])) {
            return false;
        }

        return $machines[$id];
    }

    public function create($name, $region, $size, $image)
    {
        $attributes = [
            'name'               => $name,
            'region'             => $region,
            'size'               => $size,
            'image'              => $image,
            'ipv6'               => true,
            'private_networking' => true
        ];

        $apiMachine = $this->sendRequest('post', 'droplets', $attributes);

        Action::waitUntilActionCompletes(
            $this,
            $apiMachine['links']['actions'][0]['id']
        );

        return $this->getMachine($apiMachine['droplet']['id']);
    }

    public function sendRequest($method, $action, $data = null)
    {
        if ( ! empty($data)) {
            $request = $this->httpClient->createRequest(
                $method, $action, ['json' => $data]
            );
        } else {
            $request = $this->httpClient->createRequest($method, $action);
        }

        try {
            $response = $this->httpClient->send($request)->json();
        } catch (ClientException $e) {
            switch ($e->getResponse()->getStatusCode()) {
                case '401':
                    throw new AuthenticationException();

                case '403':
                    throw new AuthorizationException();

                default:
                    throw $e;
            }
        }

        return $response;
    }
}
