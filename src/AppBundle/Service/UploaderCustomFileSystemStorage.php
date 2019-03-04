<?php

namespace AppBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Storage\FileSystemStorage;
use Vich\UploaderBundle\Mapping\PropertyMappingFactory;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(ServiceI::UPLOADER_CUSTOM_FILE_SYSTEM_STORAGE)
 */
class UploaderCustomFileSystemStorage extends FileSystemStorage
{
    /**
     * @DI\InjectParams({
     *     "factory" = @DI\Inject("vich_uploader.property_mapping_factory"),
     * })
     */
    public function __construct(PropertyMappingFactory $factory)
    {
        parent::__construct($factory);
    }

    /**
     * {@inheritdoc}
     */
    protected function doResolvePath(PropertyMapping $mapping, $dir, $name, $relative = false)
    {
        $path = preg_replace('/^[^\d]+/', '', $name);

        if ($relative) {
            return $path;
        }

        return $mapping->getUploadDestination().DIRECTORY_SEPARATOR.$path;
    }

    private function convertWindowsDirectorySeparator($string)
    {
        return str_replace('\\', '/', $string);
    }
}
