<?php

namespace AppBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Vich\UploaderBundle\Mapping\PropertyMapping;
use Vich\UploaderBundle\Naming\UniqidNamer;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(ServiceI::UPLOADER_FILE_NAMER_FULL_PATH)
 */
class UploaderFileNamerFullPath extends UniqidNamer
{
    /**
     * @var UploaderDirectoryNamerDate
     */
    private $directoryNamer;

    /**
     * @DI\InjectParams({
     *     "directoryNamer" = @DI\Inject("uploader.directory_namer_date"),
     * })
     */
    public function __construct(UploaderDirectoryNamerDate $directoryNamer)
    {
        $this->directoryNamer = $directoryNamer;
    }

    public function name($object, PropertyMapping $mapping)
    {
        $basePath = $mapping->getUriPrefix();
        $subDir = $this->directoryNamer->directoryName($object, $mapping);
        $fileName = parent::name($object, $mapping);

        return $basePath.'/'.$subDir.'/'.$fileName;
    }
}
