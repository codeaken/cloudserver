<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\ProviderInterface;
use Codeaken\CloudServer\Exception\AuthenticationException;
use Codeaken\CloudServer\Exception\AuthorizationException;
use Codeaken\CloudServer\Exception\RequestException;
use Codeaken\SshKey\SshKey;
use Codeaken\SshKey\SshPublicKey;
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

    public function getTokenInfo()
    {
        $info = [
            'valid'        => false,
            'read_access'  => false,
            'write_access' => false,
        ];

        try {
            // First check if this access token is valid by making a call to the
            // API
            $this->getRegions();

            // No exception occured, this token can be used to access the account
            $info['valid']       = true;
            $info['read_access'] = true;

            // Next try to create a bogus DNS entry to check for write access
            // to the account
            $charset = 'abcdefhgijklmnopqrstuvwxyz';

            $domain = '';
            for ($i = 0; $i < 60; $i++) {
                $domain .= $charset[ rand(0, strlen($charset) - 1) ];
            }
            $domain .= '.com';

            $this->sendRequest(
                'post',
                'domains',
                [
                    'name'       => $domain,
                    'ip_address' => '127.0.0.1'
                ]
            );

            // We are still here so the creation of the DNS entry was
            // successful. Delete the domain and mark this token as having
            // write access to the account
            $this->sendRequest('delete', "domains/$domain");
            $info['write_access'] = true;
        }
        catch (AuthenticationException $e)
        { }
        catch (AuthorizationException $e)
        { }

        return $info;
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

    public function getSizesByRegion()
    {
        $allSizes = $this->getSizes();

        $regions = [];
        foreach ($allSizes as $size) {
            foreach ($size->getRegions() as $region) {
                if (!isset($regions[$region])) {
                    $regions[$region] = [];
                }
                $regions[$region][] = $size;
            }
        }

        return $regions;
    }

    public function getSizesInRegion($region)
    {
        $allSizes = $this->getSizes();

        $sizesInRegion = [];
        foreach ($allSizes as $size) {
            if ($size->availableInRegion($region)) {
                $sizesInRegion[] = $size;
            }
        }

        return $sizesInRegion;
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
        // @todo make a request for the specific machine instead of getting
        // all of them and picking the one we want

        $machines = $this->getMachines();

        if ( ! isset($machines[$id])) {
            return false;
        }

        return $machines[$id];
    }

    public function create($name, $region, $size, $image, SshPublicKey $key = null)
    {
        $hasSshKey = !is_null($key);

        $attributes = [
            'name'               => $name,
            'region'             => $region,
            'size'               => $size,
            'image'              => $image,
            'ipv6'               => true,
            'private_networking' => true
        ];

        if ($hasSshKey) {
            // Upload the ssh key so we can attach it to the machine
            $sshKeyId = $this->addSshKey($key);
            $attributes['ssh_keys'] = [ $sshKeyId ];
        }

        $apiMachine = $this->sendRequest('post', 'droplets', $attributes);

        Action::waitUntilActionCompletes(
            $this,
            $apiMachine['links']['actions'][0]['id']
        );

        if ($hasSshKey) {
            // Remove the ssh key since we dont need it anymore now that the
            // machine is created and the key has been added to it
            $this->removeSshKey($key);
        }

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
        } catch (\GuzzleHttp\Exception\ClientException $e) {
            switch ($e->getResponse()->getStatusCode()) {
                case '401':
                    throw new AuthenticationException();

                case '403':
                    throw new AuthorizationException();

                case '422':
                    $error = $e->getResponse()->json();
                    throw new RequestException($error['message']);

                default:
                    throw $e;
            }
        }

        return $response;
    }

    protected function addSshKey(SshPublicKey $key)
    {
        $apiKey = $this->sendRequest(
            'post',
            'account/keys',
            [
                'name'       => $key->getFingerprint(),
                'public_key' => $key->getKeyData(SshKey::FORMAT_OPENSSH)
            ]
        );

        return $apiKey['ssh_key']['id'];
    }

    protected function removeSshKey(SshPublicKey $key)
    {
        $this->sendRequest('delete', 'account/keys/' . $key->getFingerprint());
    }
}
