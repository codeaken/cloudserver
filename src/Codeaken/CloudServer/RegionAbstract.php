<?php
namespace Codeaken\CloudServer;

abstract class RegionAbstract implements AttributeObjectInterface
{
    protected $id;
    protected $name;
    protected $country;
    protected $city;
    protected $available;

    protected function __construct($id, $name, $country, $city, $available)
    {
        $this->id        = $id;
        $this->name      = $name;
        $this->country   = $country;
        $this->city      = $city;
        $this->available = $available;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->id;
    }

    public function getCountry()
    {
        return $this->id;
    }

    public function getCity()
    {
        return $this->id;
    }

    public function isAvailable()
    {
        return $this->available;
    }
}