<?php
namespace Codeaken\CloudServer;

interface ProviderInterface
{
    public function __construct(array $options);
    public function getHttpClient();
    public function getRegions();
    public function getRegion($id);
    public function getSizes();
    public function getSize($id);
    public function getImages();
    public function getImage($id);
    public function getMachines();
    public function getMachine($id);
}