<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use Doctrine\ORM\EntityRepository;

class ThemeRepository extends EntityRepository
{
    public function getAllByLocale()
    {
        return $this->createQueryBuilder('t')
            ->where('t.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('deleted', false);
    }

    public function getAllThemeByKeyword($keyWord, $locale)
    {
        $result = $this->createQueryBuilder('t')
            ->leftJoin('AppBundle:ThemeTransliteration', 'tt', 'WITH', 't.id = tt.theme')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'tt.language = l.id')
            ->where('l.code = :code')
            ->andWhere('tt.name LIKE :keyWord')
            ->andWhere('t.deleted = :deleted')
            ->andWhere('l.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('keyWord', '%' . $keyWord . '%')
            ->setParameter('code', $locale)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();

        return $this->prepareResultToIDArray($result);
    }

    private function prepareResultToIDArray($result)
    {
        $arr = [];
        foreach ($result as $item) {
            $arr[] = $item->getId();
        }

        return $arr;
    }
}