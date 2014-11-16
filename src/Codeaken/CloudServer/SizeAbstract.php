<?php
namespace Codeaken\CloudServer;

abstract class SizeAbstract implements AttributeObjectInterface
{
    protected $id;
    protected $memory;
    protected $cpu;
    protected $disk;
    protected $transfer;
    protected $priceMonthly;
    protected $priceHourly;

    protected function __construct($id, $memory, $cpu, $disk, $transfer, $priceMonthly, $priceHourly)
    {
        $this->id           = $id;
        $this->memory       = $memory;
        $this->cpu          = $cpu;
        $this->disk         = $disk;
        $this->transfer     = $transfer;
        $this->priceMonthly = $priceMonthly;
        $this->priceHourly  = $priceHourly;
    }

    public function getId()
    {
        return $this->id;
    }

    public function getMemory()
    {
        return $this->memory;
    }

    public function getCpu()
    {
        return $this->cpu;
    }

    public function getDisk()
    {
        return $this->disk;
    }

    public function getTransfer()
    {
        return $this->transfer;
    }

    public function getMonthlyPrice()
    {
        return $this->priceMonthly;
    }

    public function getHourlyPrice()
    {
        return $this->priceHourly;
    }

    public function __toString()
    {
        return $this->getId();
    }   
}
