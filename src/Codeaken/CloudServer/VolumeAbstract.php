<?php
namespace Codeaken\CloudServer;

abstract class VolumeAbstract implements AttributeObjectInterface
{
    protected $id;
    protected $region;
    protected $size;
    protected $name;
    protected $description;

    protected function __construct($id, $region, $size, $name, $description)
    {
        $this->id          = $id;
        $this->region      = $region;
        $this->size        = $size;
        $this->name        = $name;
        $this->description = $description;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getRegion()
    {
        return $this->region;
    }

    public function getSize()
    {
        return $this->size;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDescription()
    {
        return $this->description;
    }

    public function __toString()
    {
        return $this->getId();
    }
}
