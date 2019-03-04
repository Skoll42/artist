<?php

namespace AppBundle\Service;

use JMS\DiExtraBundle\Annotation as DI;
use Vich\UploaderBundle\Naming\DirectoryNamerInterface;
use Vich\UploaderBundle\Mapping\PropertyMapping;

/**
 * @see http://jmsyst.com/bundles/JMSDiExtraBundle/master/annotations
 * @DI\Service(ServiceI::UPLOADER_DIRECTORY_NAMER_DATE)
 */
class UploaderDirectoryNamerDate implements DirectoryNamerInterface
{
    public function directoryName($object, PropertyMapping $mapping)
    {
        return date('Ym');
    }
}
