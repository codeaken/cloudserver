<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\ProviderInterface;
use GuzzleHttp\Client;

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

    public function getHttpClient()
    {
        return $this->httpClient;
    }

    public function getRegions()
    {
        $apiRegions = $this->httpClient->get('regions')->json()['regions'];

        $regions = [];
        foreach ($apiRegions as $region) {
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
        $apiSizes = $this->httpClient->get('sizes')->json()['sizes'];

        $sizes = [];
        foreach ($apiSizes as $size) {
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
        $apiImages = $this->httpClient->get('images')->json()['images'];

        $images = [];
        foreach ($apiImages as $image) {
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
        $apiMachines = $this->httpClient->get('droplets')->json()['droplets'];

        $machines = [];
        foreach ($apiMachines as $machine) {
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
            'name'   => $name,
            'region' => $region,
            'size'   => $size,
            'image'  => $image
        ];

        $apiMachine = $this->httpClient->post(
            'droplets',
            ['body' => json_encode($attributes)]
        )->json();

        Action::waitUntilActionCompletes(
            $this,
            $apiMachine['links']['actions'][0]['id']
        );

        return $this->getMachine($apiMachine['droplet']['id']);
    }
}
