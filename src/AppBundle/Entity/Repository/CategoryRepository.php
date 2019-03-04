<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class CategoryRepository extends EntityRepository
{
    public function getCategories()
    {
        return $this->createQueryBuilder('c')
            ->where('c.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('deleted', false);
    }
}