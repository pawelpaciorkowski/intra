<?php

if (file_exists(dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php')) {
    /** @noinspection PreloadingUsageCorrectnessInspection */
    require dirname(__DIR__).'/var/cache/prod/App_KernelProdContainer.preload.php';
}