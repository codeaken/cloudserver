<?php
namespace Codeaken\CloudServer;

abstract class ImageAbstract implements AttributeObjectInterface
{
    protected $id;
    protected $name;
    protected $distribution;

    protected function __construct($id, $name, $distribution)
    {
        $this->id           = $id;
        $this->name         = $name;
        $this->distribution = $distribution;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getDistribution()
    {
        return $this->distribution;
    }

    public function __toString()
    {
        return $this->getId();
    }    
}
