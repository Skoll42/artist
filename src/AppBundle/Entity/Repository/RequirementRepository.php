<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class RequirementRepository extends EntityRepository
{
    public function getRequirement()
    {
        return $this->createQueryBuilder('r')
            ->where('r.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('deleted', false)
        ;
    }
}