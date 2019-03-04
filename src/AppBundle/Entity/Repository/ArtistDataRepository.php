<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ArtistDataRepository extends EntityRepository
{
    public function findCategoriesByLanguageCode(string $locale)
    {
        return $this->createQueryBuilder('ct')
            ->leftJoin('AppBundle:Category', 'c', 'WITH', 'c.id = ct.category')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'ct.language = l.id')
            ->where('l.code = :code')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('code', $locale)
            ->getQuery()->getResult();
    }

    public function getBestArtist(int $limit)
    {
        return $this->createQueryBuilder('ad')
            ->leftJoin('AppBundle:User', 'u', 'WITH', 'u.id = ad.user')
            ->where('ad.deleted = :deleted')
            ->andWhere('u.deleted = :deleted')
            ->andWhere('u.enabled = :enabled')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->andWhere('ad.isVisible = :isVisible')
            ->setParameter('isVisible', true)
            ->setParameter('deleted', false)
            ->setParameter('deleted', false)
            ->setParameter('enabled', true)
            ->orderBy('u.id', 'DESC')
            ->setMaxResults($limit)
            ->getQuery()->getResult();
    }

    public function search(array $filter)
    {
        $result = $this->createQueryBuilder('ad')
            ->leftJoin('AppBundle:User', 'u', 'WITH', 'u.id = ad.user')
            ->where('ad.deleted = :deleted')
            ->andWhere('u.deleted = :deleted')
            ->setParameter('deleted', false);

        if (!empty($filter['keyWord']) && !empty($filter['themes'])) {
            $result = $result
                ->leftJoin('AppBundle:UserThemes', 'ut', 'WITH', 'ut.user = ad.user')
                ->andWhere('ad.name LIKE :name')
                ->orWhere('ut.theme IN (:themes)')
                ->andWhere('ad.deleted = :deleted')
                ->andWhere('ut.deleted = :deleted')
                ->setParameter('deleted', false)
                ->setParameter('name', '%' . $filter['keyWord'] . '%')
                ->setParameter('themes', $filter['themes']);
        } elseif (!empty($filter['themes'])) {
            $result = $result
                ->leftJoin('AppBundle:UserThemes', 'ut', 'WITH', 'ut.user = ad.user')
                ->andWhere('ut.deleted = :deleted')
                ->andWhere('ut.theme IN (:themes)')
                ->setParameter('themes', $filter['themes'])
                ->setParameter('deleted', false);
        } elseif (!empty($filter['keyWord'])) {
            $result = $result
                ->andWhere('ad.name LIKE :name')
                ->andWhere('ad.deleted = :deleted')
                ->setParameter('deleted', false)
                ->setParameter('name', '%' . $filter['keyWord'] . '%');
        }

        if (isset($filter['category']) && !empty($filter['category'])) {
            $result = $result
                ->leftJoin('AppBundle:UserCategories', 'uc', 'WITH', 'uc.user = ad.user')
                ->andWhere('uc.deleted = :deleted')
                ->andWhere('uc.category = :category')
                ->setParameter('category', (int)$filter['category'])
                ->setParameter('deleted', false);
        }

        if (isset($filter['location']) && !empty($filter['location'])) {
            $result = $result
                ->leftJoin('AppBundle:Location', 'l', 'WITH', 'l.id = ad.location')
                ->andWhere('l.deleted = :deleted')
                ->andWhere('l.name = :location')
                ->setParameter('deleted', false)
                ->setParameter('location', $filter['location']);
        }

        if (isset($filter['tags']) && !empty($filter['tags'])) {
            $result = $result
                ->leftJoin('AppBundle:UserTags', 'ut1', 'WITH', 'ut1.user = ad.user')
                ->andWhere('ut1.deleted = :deleted')
                ->andWhere('ut1.tag IN (:tags)')
                ->setParameter('tags', $filter['tags'])
                ->setParameter('deleted', false);
        }

        if (isset($filter['priceFrom']) && !empty($filter['priceFrom'])) {
            $result = $result
                ->andWhere('ad.price >= :priceFrom')
                ->setParameter('priceFrom', $filter['priceFrom']);
        }

        if (isset($filter['priceTo']) && !empty($filter['priceTo'])) {
            $result = $result
                ->andWhere('ad.price <= :priceTo')
                ->setParameter('priceTo', $filter['priceTo']);
        }

        $result = $result
            ->andWhere('ad.isVisible = :isVisible')
            ->setParameter('isVisible', true);

        $result = $result->getQuery();

        if ($filter['result']) {
            $result = $result->getResult();
        }

        return $result;
    }

    public function findArtistMaxPrices($artists)
    {
        return $this->createQueryBuilder('ad')
            ->select('MAX(ad.price) as maxPrice')
            ->where('ad.id IN (:artists)')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('artists', $artists)
            ->getQuery()->getResult();
    }

    public function findAllArtists()
    {
        $result = $this->createQueryBuilder('ad')
            ->select("u.email, DATE_FORMAT(u.createdDate, '%Y-%m-%d') as registered_on, ad.firstName, ad.lastName")
            ->addSelect('u.enabled as verified, ad.firstName as filled, ad.stripeId, ad.isVisible, u.deleted')
            ->leftJoin('AppBundle:User', 'u', 'WITH', 'u.id = ad.user')
            ->where('u.roles LIKE :roles')
            ->setParameter('roles', '%ROLE_ARTIST%')
            ->groupBy('u.email')
            ->orderBy('u.createdDate', 'DESC')
            ->getQuery()
            ->getResult();

        return $result;
    }
}