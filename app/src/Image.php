<?php

declare(strict_types=1);

namespace GuzabaPlatform\Images;

use Guzaba2\Base\Exceptions\RunTimeException;
use Guzaba2\Orm\ActiveRecord;
use Guzaba2\Orm\Exceptions\ValidationFailedException;
use Guzaba2\Orm\Interfaces\ValidationFailedExceptionInterface;
use Guzaba2\Orm\Transaction;
use Guzaba2\Transaction\Interfaces\TransactionManagerInterface;
use Guzaba2\Translator\Translator as t;
use GuzabaPlatform\Images\Interfaces\ImageInterface;
use GuzabaPlatform\Images\Interfaces\SupportsImagesInterface;
use GuzabaPlatform\Platform\Application\BaseActiveRecord;

/**
 * Class Image
 * @package GuzabaPlatform\Images
 *
 * @property int    image_id
 * @property string image_path
 * @property int    image_class_id
 * @property int    image_object_id
 */
class Image extends BaseActiveRecord implements ImageInterface
{

    protected const CONFIG_DEFAULTS = [
        'main_table'            => 'images',
        'route'                 => '/admin/image',//to be used for editing and deleting

        'object_name_property'  => 'image_path',//required by BaseActiveRecord::get_object_name_property()
    ];

    protected const CONFIG_RUNTIME = [];

    /**
     * @param SupportsImagesInterface $Object
     * @param string $image_url A local path
     * @return Image
     * @throws RunTimeException
     * @throws \Azonmedia\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\InvalidArgumentException
     * @throws \Guzaba2\Base\Exceptions\LogicException
     * @throws \Guzaba2\Coroutine\Exceptions\ContextDestroyedException
     * @throws \Guzaba2\Kernel\Exceptions\ConfigurationException
     * @throws \ReflectionException
     */
    public static function create(SupportsImagesInterface $Object, string $image_path): self
    {
        $Image = new static();
        $Image->image_class_id = $Object::get_class_id();
        $Image->image_object_id = $Object->get_id();
        $Image->image_path = $image_path;
        $Image->write();
        return $Image;
    }

    public function get_object(): SupportsImagesInterface
    {
        if ($this->is_new()) {
            throw new RunTimeException(sprintf(t::_('The method %1$s can not be invoked on new instances.'), __METHOD__ ));
        }
        $class = self::get_class_name();
        $Object = new $class($this->image_object_id);
        return $Object;
    }

    protected function _validate_image_path(): ?ValidationFailedExceptionInterface
    {
        if (!$this->image_path) {
            return new ValidationFailedException($this, 'image_path', sprintf(t::_('The image_path is not set.') ));
        }
        if ($this->image_path[0] !== '/') {
            return new ValidationFailedException($this, 'image_path', sprintf(t::_('The image_path %1$s is not an absolute one.'), $this->image_path));
        }
        return null;
    }

    protected function _before_delete(): void
    {
        //if there delete transaction is successful delete the images
        //object the transaction and add a on commit event
        //it is better to do it this way insted of deleting the images in _after_delete but without taking into account the transaction.
        //there is no way to obtain the current transaction (this is intentional)
        //instead a nested one is started
        $Transaction = self::new_transaction($TR);
        $Transaction->begin();
        $Transaction->add_callback('_after_commit', function(): void
        {
            //if there is an exception here will hit a bug in CompositeTransaction nesting
            if (file_exists($this->image_path) && is_writable($this->image_path)) {
                unlink($this->image_path);
            }
        });
        $Transaction->commit();
    }


}