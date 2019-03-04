<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;
use Doctrine\ORM\QueryBuilder;

class ThemeTransliterationRepository extends EntityRepository
{
    public function findTransliterationByLanguageCode($theme, string $langCode)
    {
        $result = $this->createQueryBuilder('tt')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'tt.language = l.id')
            ->where('tt.theme = :theme')
            ->andWhere('l.code = :code')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('theme', $theme)
            ->setParameter('code', $langCode)
            ->getQuery()->getOneOrNullResult(null)
        ;

        return $result->getName();
    }

    public function findThemesByLanguageCode(string $locale)
    {
        return $this->createQueryBuilder('tt')
            ->leftJoin('AppBundle:Theme', 't', 'WITH', 't.id = tt.theme')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'tt.language = l.id')
            ->where('l.code = :code')
            ->andWhere('t.deleted = :deleted')
            ->andWhere('tt.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('code', $locale)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();
    }

    public function getUserThemes(User $user, string $locale)
    {
        $result = $this->createQueryBuilder('tt')
            ->leftJoin('AppBundle:Theme', 't', 'WITH', 't.id = tt.theme')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'tt.language = l.id')
            ->leftJoin('AppBundle:UserThemes', 'ut', 'WITH', 'ut.theme = tt.theme')
            ->where('l.code = :code')
            ->andWhere('t.deleted = :deleted')
            ->andWhere('tt.deleted = :deleted')
            ->andWhere('ut.deleted = :deleted')
            ->andWhere('ut.user = :user')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('code', $locale)
            ->setParameter('user', $user)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();

        return $this->prepareResultToIDArray($result);
    }

    private function prepareResultToIDArray($result)
    {
        $arr = [];
        foreach ($result as $item) {
            $arr[] = $item->getName();
        }

        return $arr;
    }

    public function getThemeNameById(int $themeId, string $locale)
    {
        return $this->createQueryBuilder('tt')
            ->select('tt.name')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'tt.language = l.id')
            ->where('l.code = :code')
            ->andWhere('tt.deleted = :deleted')
            ->andWhere('tt.theme = :theme')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('code', $locale)
            ->setParameter('theme', $themeId)
            ->setParameter('deleted', false)
            ->getQuery()->getOneOrNullResult();
    }
}