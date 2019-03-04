<?php

namespace AppBundle\Controller;

use AppBundle\Entity\ArtistData;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Component\HttpFoundation\Request;

/**
 * Class SearchController
 * @package AppBundle\Controller
 *
 * @Route("/search")
 */
class SearchController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/artist/list", name="artist_search_list")
     * @Method({"GET", "POST"})
     */
    public function viewListAction()
    {
        return $this->render('@App/search/artist/found_list.html.twig');
    }

    /**
     * @param Request $r
     * @return \Symfony\Component\HttpFoundation\Response
     * @throws \Exception
     *
     * @Route("/result", name="search_result")
     * @Method({"GET", "POST"})
     */
    public function searchAction(Request $r)
    {
        $platformFee = $this->getParameter('platform_fee');
        $keyWord = trim($r->get('keyWord'));
        $themes = trim($r->get('theme')) ? explode(',', trim($r->get('theme'))) : [];
        $dateRange = trim($r->get('dateRange'));

        if (!empty($keyWord)
            || !empty($r->get('theme'))
            || !empty($r->get('category'))
            || !empty($r->get('tags'))
            || !empty($r->get('location')))
        {
            if (!empty($keyWord)) {
                if (!empty($arr = $this->getRepo('Theme')->getAllThemeByKeyword($keyWord, $r->getLocale()))) {
                    foreach ($arr as $item) {
                        $themes[] = $item;
                    }
                }
            }

            if (!empty(trim($r->get('theme')))) {
                $keyWord = $this->getRepo('ThemeTransliteration')->getThemeNameById(trim($r->get('theme')), $r->getLocale());

                if (!empty($keyWord)) {
                    $r->request->set('keyWord', $keyWord['name']);
                }
            }

            //get all artists regarding search parameters
            $artists = $this->getSearchResultFilter([
                'category' => (int)$r->get('category'),
                'themes' => $themes,
                'tags' => $r->get('tags') ? explode(',', $r->get('tags')) : [],
                'keyWord' => trim($r->get('keyWord')),
                'location' => trim($r->get('location')),
                'result' => true
            ]);
        }

        if(!empty($dateRange)
            && empty($r->get('theme'))
            && empty($keyWord)
            && empty($r->get('category'))
            && empty($r->get('tags'))
            && empty($r->get('location')))
        {
            $artists = $this->getSearchResultFilter([
                'result' => true
            ]);
        }

        //get artists max price regarding search parameters
        $maxPrice = $this->getRepo('ArtistData')->findArtistMaxPrices($artists);

        //include and format artist max price with platform fee
        $maxPriceWithFee = floatval($maxPrice[0]["maxPrice"]) * $platformFee + floatval($maxPrice[0]["maxPrice"]) + 1;

        $maxPriceWithFeeFormatted[] = ["maxPrice" => number_format($maxPriceWithFee, 2, '.', '')];

        //exclude artists that are not in price range
        $amountArtists = count($artists);

        for ($i = 0; $i < $amountArtists; $i++) {
            $artistPriceWithFee = floatval($artists[$i]->getPrice()) * $platformFee + floatval($artists[$i]->getPrice());
            $priceFrom = floatval(trim($r->get('priceFrom')));
            $priceTo = (floatval(trim($r->get('priceTo')))) ? floatval(trim($r->get('priceTo'))) : $maxPriceWithFee;

            if ($artistPriceWithFee < $priceFrom || $artistPriceWithFee > $priceTo) {
                unset($artists[$i]);
            }
        }

        if(!empty($dateRange)) {
            $dateMinMaxArr = explode(' - ', $dateRange);

            if (is_array($dateMinMaxArr)) {
                $startDate = new \DateTimeImmutable($dateMinMaxArr[0]);
                $endDate = new \DateTimeImmutable($dateMinMaxArr[1]);

                $datesRangeArr = $this->generateDatesRangeArr($startDate, $endDate);

                //reset indexes on $artists array
                $artists = array_values($artists);
                $amountArtists = count($artists);

                for ($i = 0; $i < $amountArtists; $i++) {
                    $artistBusyDates = $this->getArtistBusyDatesArr($artists[$i], $startDate, $endDate);
                    if($artistBusyDates === $datesRangeArr) {
                        unset($artists[$i]);
                    }
                }
            }
        }

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $artists,
            $r->query->get('page', 1)/*page number*/,
            8/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination.html.twig');

        return $this->render('@App/search/artist/found_list.html.twig', array(
            'artists' => $pagination,
            'categories' => $this->getRepo('CategoryTransliteration')->findCategoriesByLanguageCode($r->getLocale()),
            'tags' => $this->getRepo('Tag')->getPopularTag(),
            'prices' => $maxPriceWithFeeFormatted[0],
            'platform_fee' => $platformFee
        ));
    }

    /**
     * Search artist by filter
     *
     * @param array $filter
     * @return mixed
     */
    private function getSearchResultFilter(array $filter)
    {
        return $this->getRepo('ArtistData')->search($filter);
    }

    private function generateDatesRangeArr($startDate, $endDate)
    {
        $date = array();
        $endDate = $endDate->modify('+1 day');
        $period = new \DatePeriod(
            $startDate,
            new \DateInterval('P1D'),
            $endDate
        );

        foreach ($period as $key => $value) {
            $date[] = $value->format('Y-m-d');
        }

        return $date;
    }

    private function getArtistBusyDatesArr(ArtistData $artist, $startDate, $endDate)
    {
        $result = array();
        $artistBusyDates = $this->getRepo('UserBusyDates')->getArtistBusyDates(
            $artist->getUser(),
            $startDate->format('Y-m-d'),
            $endDate->format('Y-m-d')
        );

        foreach ($artistBusyDates as $artistBusyDate) {
            $result[] = $artistBusyDate->getBusyDate()->format('Y-m-d');
        }

        return $result;
    }
}