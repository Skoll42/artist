<?php
declare(strict_types=1);

namespace AppBundle\Controller;

use AppBundle\Entity\User;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Request;

/**
 * @Route("/profile/artist")
 */
class ArtistProfileController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/view", name="artist_profile_public")
     */
    public function viewProfilePublicAction(Request $r, $id)
    {
        $locale = $r->getLocale();
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        $user = $artist->getUser();

        if ($user->getDeleted()) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        $images = $this->getRepo('UserMedia')->findBy([
            'user' => $user,
            'deleted' => false
        ]);

        $category = $this->getRepo('CategoryTransliteration')->getUserCategory($user, $locale);
        $themes = $this->getRepo('ThemeTransliteration')->getUserThemes($user, $locale);
        $tags = $this->getRepo('UserTags')->getArtistTags($user);
        $requirements = $this->getRepo('RequirementTransliteration')->getUserRequirements($user, $locale);

        $events = $this->getRepo('Booking')->getAllAcceptedByPerformerFilteredByEventDate($user);
        $events = $events->getResult();
        $otherReq = $this->getRepo("UserRequirements")->getArtistOtherRequirements($user, $locale);

        $busyDates = json_encode($this->getBusyDates($user));

        return $this->render('@App/user/artist/view_profile_public.html.twig', array(
            'artist' => $artist,
            'events' => $this->convertEventData($events, $artist),
            'images' => $images,
            'category' => $category,
            'themes' => implode(', ', $themes),
            'tags' => $tags,
            'requirements' => $requirements,
            'otherReq' => $otherReq ?? null,
            'busy_dates' => ($busyDates) ? $busyDates : '[]'
        ));
    }

    public function getBusyDates($user)
    {
        $busyDates  = array();
        $busyDatesObjArr = $this->getRepo("UserBusyDates")->getArtistBusyDates($user);

        foreach ($busyDatesObjArr as $busyDate) {
            $busyDates[] = date_format($busyDate->getBusyDate(),'Y-m-d');
        }

        return $busyDates;
    }

    public function convertEventData($events, $artist)
    {
        $convertedEvents = array();
        $index = 0;
        if (is_array($events)) {
            foreach ($events as $event) {
                sscanf($artist->getTime(), "%d:%d", $hours, $minutes);
                $performTimeConvert = $hours * 3600 + $minutes * 60;
                $convertedEvents[$index]['start'] = date_timestamp_get($event['booking']->getEventDate());
                $convertedEvents[$index]['end'] = date_timestamp_get($event['booking']->getEventDate()) + $performTimeConvert;
                $convertedEvents[$index]['allDay'] = false;
                $convertedEvents[$index]['className'] = 'important';
                $index++;
            }
        }

        return json_encode($convertedEvents);
    }
}