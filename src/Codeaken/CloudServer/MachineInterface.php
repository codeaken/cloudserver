<?php
namespace Codeaken\CloudServer;

interface MachineInterface
{
    public function __construct($provider, $data);
    public function getId();
    public function getName();
    public function getImage();
    public function getSize();
    public function getRegion();
    public function getKernel();
    public function getAvailableKernels();
    public function getIpAddresses();
    public function getPublicIpv4();
    public function boot();
    public function reboot();
    public function shutdown();
    public function powerOff();
    public function powerCycle();
    public function delete();
}
