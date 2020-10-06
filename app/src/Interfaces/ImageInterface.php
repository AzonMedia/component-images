<?php

declare(strict_types=1);

namespace GuzabaPlatform\Images\Interfaces;

interface ImageInterface
{
    public function get_object(): SupportsImagesInterface ;
}