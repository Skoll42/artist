<?php
declare(strict_types = 1);

namespace AppBundle\Entity\Repository;

use AppBundle\Entity\User;
use Doctrine\ORM\EntityRepository;

class UserThemesRepository extends EntityRepository
{
    public function getArtistThemes(User $user, $locale)
    {
        return $this->createQueryBuilder('ut')
            ->select('t')
            ->leftJoin('AppBundle:ThemeTransliteration', 'tt', 'WITH', 'tt.theme = ut.theme')
            ->leftJoin('AppBundle:Language', 'l', 'WITH', 'tt.language = l.id')
            ->leftJoin('AppBundle:Theme', 't', 'WITH', 't.id = ut.theme')
            ->where('ut.user = :user')
            ->andWhere('l.code = :code')
            ->andWhere('ut.deleted = :deleted')
            ->andWhere("'".substr(strrchr(__METHOD__, '\\'), 1)."' != 1")
            ->setParameter('user', $user)
            ->setParameter('code', $locale)
            ->setParameter('deleted', false)
            ->getQuery()->getResult();
    }
}