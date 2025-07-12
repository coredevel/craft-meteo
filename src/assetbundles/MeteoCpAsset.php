<?php

namespace CoreDev\Meteo\assetbundles;

use craft\web\AssetBundle;
use craft\web\assets\cp\CpAsset;

class MeteoCpAsset extends AssetBundle
{
    public function init()
    {
        $this->sourcePath = __DIR__ . '/../resources';

        $this->depends = [
            CpAsset::class,
        ];

        $this->css = [
            'latlong-finder.css',
            'meteo-tabs.css',
        ];
        $this->js = [
            'latlong-finder.js',
            'meteo-tabs.js',
        ];

        parent::init();
    }
}
