<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class RequirementTransliterationRepository extends EntityRepository
{
    public function findTransliterationByLanguageCode($requirement, string $langCode)
    {
        $result = $this->createQueryBuilder('rt')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'rt.language = l.id')
            ->where('rt.requirement = :requirement')
            ->andWhere('l.code = :code')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('requirement', $requirement)
            ->setParameter('code', $langCode)
            ->getQuery()->getOneOrNullResult()
        ;

        return $result->getName();
    }

    /**
     * Get all Artist Requirements
     *
     * @param User $user
     * @param string $locale
     * @return array
     */
    public function getUserRequirements(User $user, string $locale)
    {
        return $this->createQueryBuilder('rt')
            ->leftJoin('AppBundle:Requirement', 'r', 'WITH', 'r.id = rt.requirement')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'rt.language = l.id')
            ->leftJoin('AppBundle:UserRequirements', 'ur', 'WITH', 'ur.requirement = rt.requirement')
            ->where('l.code = :code')
            ->andWhere('r.deleted = :deleted')
            ->andWhere('rt.deleted = :deleted')
            ->andWhere('ur.deleted = :deleted')
            ->andWhere('ur.user = :user')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('code', $locale)
            ->setParameter('user', $user)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();
    }
}
