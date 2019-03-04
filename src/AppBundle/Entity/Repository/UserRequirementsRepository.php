<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserRequirementsRepository extends EntityRepository
{
    public function getArtistRequirements(User $user, $locale)
    {
        return $this->createQueryBuilder('ur')
            ->select('r')
            ->leftJoin('AppBundle:RequirementTransliteration', 'rt', 'WITH', 'rt.requirement = ur.requirement')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'rt.language = l.id')
            ->leftJoin('AppBundle:Requirement', 'r', 'WITH', 'r.id = ur.requirement')
            ->where('ur.user = :user')
            ->andWhere('l.code = :code')
            ->andWhere('ur.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('user', $user)
            ->setParameter('code', $locale)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();
    }

    public function getArtistOtherRequirements(User $user, $locale)
    {
        $result = $this->createQueryBuilder('ur')
            ->leftJoin('AppBundle:RequirementTransliteration', 'rt', 'WITH', 'rt.requirement = ur.requirement')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'rt.language = l.id')
            ->leftJoin('AppBundle:Requirement', 'r', 'WITH', 'r.id = ur.requirement')
            ->where('ur.user = :user')
            ->andWhere('ur.deleted = :deleted')
            ->andWhere('ur.requirement = 7')
            ->andWhere('l.code = :code')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('user', $user)
            ->setParameter('code', $locale)
            ->setParameter('deleted', false)
            ->getQuery()->getOneOrNullResult();

        if(is_null($result)) {
            return '';
        }

        return $result->getDescription();
    }
}