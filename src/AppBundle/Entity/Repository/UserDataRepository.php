<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class UserDataRepository extends EntityRepository
{
    public function findAllCustomers()
    {
        $result = $this->createQueryBuilder('ud')
            ->select("u.email, DATE_FORMAT(u.createdDate, '%Y-%m-%d') as registered_on, ud.firstName, ud.lastName")
            ->addSelect('u.enabled as verified, COUNT(b.id) as booked, u.deleted')
            ->leftJoin('AppBundle:User', 'u', 'WITH', 'u.id = ud.user')
            ->leftJoin('AppBundle:Booking', 'b', 'WITH', 'b.customer = ud.user')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%ROLE_CUSTOMER%')
            ->groupBy('u.email')
            ->orderBy('u.createdDate', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}