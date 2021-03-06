<?php
namespace Codeaken\CloudServer\Provider\DigitalOcean;

use Codeaken\CloudServer\MachineInterface;

class Machine implements MachineInterface
{
    private $provider;

    private $id;
    private $name;
    private $region;
    private $image;
    private $kernel;
    private $sizeId;
    private $ipAddresses;
    private $volumeIds;
    private $isRunning = false;
    private $isDeleted = false;

    public function __construct($provider, $data)
    {
        $this->provider = $provider;

        $this->id        = $data['id'];
        $this->name      = $data['name'];
        $this->region    = Region::create($data['region']);
        $this->sizeId    = $data['size_slug'];
        $this->image     = Image::create($data['image']);
        $this->kernel    = Kernel::create($data['kernel']);
        $this->volumeIds = $data['volume_ids'];

        foreach ($data['networks']['v4'] as $ip) {
            $ip['version'] = '4';

            $this->ipAddresses[] = IpAddress::create($ip);
        }

        foreach ($data['networks']['v6'] as $ip) {
            $ip['version'] = '6';

            $this->ipAddresses[] = IpAddress::create($ip);
        }

        if ($data['status'] == 'active') {
            $this->isRunning = true;
        }

        if ($data['status'] == 'archive') {
            $this->isDeleted = true;
        }
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getImage()
    {
        return $this->image;
    }

    public function getSize()
    {
        return $this->provider->getSize($this->sizeId);
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function getKernel()
    {
        return $this->kernel;
    }

    public function getAvailableKernels()
    {
        $apiKernels = $this->provider->sendRequest(
            'get', "droplets/{$this->id}/kernels"
        );

        $kernels = [];
        foreach ($apiKernels['kernels'] as $kernel) {
            $kernels[$kernel['id']] = Kernel::create($kernel);
        }

        return $kernels;
    }

    public function getIpAddresses()
    {
        return $this->ipAddresses;
    }

    public function getPublicIpv4()
    {
        foreach ($this->getIpAddresses() as $ip) {
            if ($ip->isPublic() && $ip->isIpV4()) {
                return (string)$ip;
            }
        }

        return false;
    }

    public function attachVolume($volumeId)
    {
        $attributes = [
            'type' => 'attach',
            'droplet_id' => $this->id
        ];

        $response = $this->provider->sendRequest('post', "volumes/$volumeId/actions", $attributes);
    }

    public function detachVolume($volumeId)
    {
        $attributes = [
            'type' => 'detach',
            'droplet_id' => $this->id
        ];

        $response = $this->provider->sendRequest('post', "volumes/$volumeId/actions", $attributes);
    }

    public function getVolumeIds()
    {
        return $this->volumeIds;
    }

    public function boot()
    {
        $this->runAction('power_on');
    }

    public function reboot()
    {
        $this->runAction('reboot');
    }

    public function shutdown()
    {
        $this->runAction('shutdown');
    }

    public function powerOff()
    {
        $this->runAction('power_off');
    }

    public function powerCycle()
    {
        $this->runAction('power_cycle');
    }

    public function delete()
    {
        $this->provider->sendRequest('delete', "droplets/{$this->id}");
    }

    public function isRunning()
    {
        return $this->isRunning;
    }

    public function isDeleted()
    {
        return $this->isDeleted;
    }

    private function runAction($action)
    {
        $apiAction = $this->provider->sendRequest(
            'post', "droplets/{$this->id}/actions", ['type' => $action]
        );

        Action::waitUntilActionCompletes(
            $this->provider,
            $apiAction['action']['id']
        );
    }
}
