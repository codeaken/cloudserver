<?php
namespace Codeaken\CloudServer;

abstract class IpAddressAbstract implements AttributeObjectInterface
{
    protected $version;
    protected $type;
    protected $address;

    protected function __construct($address, $version, $type)
    {
        $this->address = $address;
        $this->version = $version;
        $this->type    = $type;
    }

    public function getVersion()
    {
        return $this->version;
    }

    public function getType()
    {
        return $this->type;
    }

    public function getAddress()
    {
        return $this->address;
    }

    public function isIpV4()
    {
        return ($this->version == 4);
    }

    public function isIpV6()
    {
        return ($this->version == 6);
    }

    public function isPublic()
    {
        return ($this->type == 'public');
    }

    public function isPrivate()
    {
        return ($this->type == 'private');
    }

    public function __toString()
    {
        return $this->getAddress();
    }
}
