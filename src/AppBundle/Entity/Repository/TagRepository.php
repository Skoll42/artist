<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class TagRepository extends EntityRepository
{
    public function getAll()
    {
        return $this->createQueryBuilder('t')
            ->where('t.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('deleted', false);
    }

    public function getPopularTag()
    {
        return $this->createQueryBuilder('t')
            ->where('t.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('deleted', false)
            ->setMaxResults(10)
            ->getQuery()->getResult();
    }
}