<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 12/4/2018
 * Time: 4:35 PM
 */

declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserBusyDatesRepository extends EntityRepository
{
    public function getArtistBusyDates(User $user, $startDate = null, $endDate = null)
    {
        $result = $this->createQueryBuilder('ubd')
                       ->select('ubd')
                       ->where('ubd.user = :user')
                       ->andWhere('ubd.deleted = :deleted')
                       ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
                       ->setParameter('user', $user)
                       ->setParameter('deleted', false);

        if(!is_null($startDate) && !is_null($endDate)) {
            $result = $result->andWhere('ubd.busyDate >= :startDate')
                             ->andWhere('ubd.busyDate <= :endDate')
                             ->setParameter('startDate', $startDate)
                             ->setParameter('endDate', $endDate);
        }

        $result = $result->orderBy('ubd.busyDate', 'ASC');

        return $result->getQuery()->getResult();
    }
}