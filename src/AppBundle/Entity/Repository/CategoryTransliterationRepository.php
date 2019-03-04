<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class CategoryTransliterationRepository extends EntityRepository
{
    public function findTransliterationByLanguageCode($category, string $langCode)
    {
        $result = $this->createQueryBuilder('ct')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'ct.language = l.id')
            ->where('ct.category = :category')
            ->andWhere('l.code = :code')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('category', $category)
            ->setParameter('code', $langCode)
            ->getQuery()->getOneOrNullResult(null)
        ;

        return $result->getName();
    }


    public function findCategoriesByLanguageCode(string $locale)
    {
        return $this->createQueryBuilder('ct')
            ->leftJoin('AppBundle:Category', 'c', 'WITH', 'c.id = ct.category')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'ct.language = l.id')
            ->where('l.code = :code')
            ->andWhere('c.deleted = :deleted')
            ->andWhere('ct.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('code', $locale)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();
    }

    public function getUserCategory(User $user, string $locale)
    {
        return $this->createQueryBuilder('ct')
            ->leftJoin('AppBundle:Category', 'c', 'WITH', 'c.id = ct.category')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'ct.language = l.id')
            ->leftJoin('AppBundle:UserCategories', 'uc', 'WITH', 'uc.category = ct.category')
            ->where('l.code = :code')
            ->andWhere('c.deleted = :deleted')
            ->andWhere('ct.deleted = :deleted')
            ->andWhere('uc.deleted = :deleted')
            ->andWhere('uc.user = :user')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('code', $locale)
            ->setParameter('user', $user)
            ->setParameter('deleted', false)
            ->getQuery()->getOneOrNullResult();
    }
}