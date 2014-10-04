<?php
namespace Codeaken\CloudServer;

abstract class KernelAbstract implements AttributeObjectInterface
{
    protected $id;
    protected $name;
    protected $version;

    protected function __construct($id, $name, $version)
    {
        $this->id      = $id;
        $this->name    = $name;
        $this->version = $version;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getName()
    {
        return $this->name;
    }

    public function getVersion()
    {
        return $this->version;
    }
}
