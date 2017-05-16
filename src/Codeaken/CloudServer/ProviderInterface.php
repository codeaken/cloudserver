<?php
namespace Codeaken\CloudServer;

interface ProviderInterface
{
    public function __construct(array $options);
    public function getTokenInfo();
    public function getRegions();
    public function getRegion($id);
    public function getSizes();
    public function getSizesByRegion();
    public function getSizesInRegion($region);
    public function getSize($id);
    public function getImages();
    public function getImage($id);
    public function getMachines();
    public function getMachine($id);
    public function getVolume($id);
    public function createVolume($name, $region, $size);
    public function resizeVolume($id, $size);
    public function deleteVolume($id);
}
