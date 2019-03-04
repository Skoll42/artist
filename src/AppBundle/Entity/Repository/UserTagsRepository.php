<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserTagsRepository extends EntityRepository
{
    public function getArtistTags(User $user)
    {
        return $this->createQueryBuilder('ut')
            ->select('t')
            ->leftJoin('AppBundle:Tag', 't', 'WITH', 't.id = ut.tag')
            ->where('ut.user = :user')
            ->andWhere('ut.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('user', $user)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();
    }
}