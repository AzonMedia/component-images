<?php

declare(strict_types=1);

namespace GuzabaPlatform\Images\Interfaces;

interface ImageInterface
{
    public static function create(SupportsImagesInterface $Object, string $image_url): self ;

    public function get_object(): SupportsImagesInterface ;
}