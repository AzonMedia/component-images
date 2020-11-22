<?php

declare(strict_types=1);

namespace GuzabaPlatform\Images\Interfaces;

interface SupportsImagesInterface
{
    /**
     * @return ImageInterface[]
     */
    public function get_images(): array ;

    public function add_image(string $url): void ;

    public function delete_images(): void ;

    public static function get_class_id(): ?int ;

    public function get_id()  /* int|string */ ;
}