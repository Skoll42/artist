<?php
declare(strict_types=1);

namespace AppBundle\Controller\ArtistProfile;

use AppBundle\Entity\ArtistData;
use AppBundle\Entity\UserBusyDates;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Method;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;
use Doctrine\ORM\EntityManager;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/profile/artist")
 * @Security("has_role('ROLE_ARTIST')")
 */
class CalendarController extends Controller
{
    use ControllerSupport;

    /**
     * @var array
     */
    private $busyDates;

    /**
     * @Route("/{id}/edit/calendar", name="artist_view_calendar")
     * @Method({"GET"})
     * @Security("user.getID() == id")
     */
    public function viewCalendarsAction(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        if ($artist
            && empty($artist->getFirstName())
            && empty($artist->getLastName())
            && empty($artist->getLocation())
            && empty($artist->getPhone())) {
            throw $this->createNotFoundException('You should enter you profile details first.');
        }

        $user = $artist->getUser();

        $events = $this->getRepo('Booking')->getAllAcceptedByPerformerFilteredByEventDate($user);
        $events = $events->getResult();

        $busyDates = json_encode($this->getBusyDates($artist));

        return $this->render('@App/user/artist/calendar.html.twig', array(
            'artist' => $artist,
            'events' => $this->convertEventData($events, $artist),
            'busy_dates' => ($busyDates) ? $busyDates : '[]'
        ));
    }

    /**
     * @Route("/{id}/edit/calendar", name="artist_edit_calendar")
     * @Method({"POST"})
     * @Security("user.getID() == id")
     */
    public function editCalendarsAction(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        $busyDatesData = $r->get('busydates');

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        if ($artist
            && empty($artist->getFirstName())
            && empty($artist->getLastName())
            && empty($artist->getLocation())
            && empty($artist->getPhone())) {
            throw $this->createNotFoundException('You should enter you profile details first.');
        }

        if ($r->isXmlHttpRequest() && $busyDatesData) {
            $this->busyDates = json_decode($busyDatesData, true);
            $this->setBusyDates($artist, $this->busyDates);
        }

        return new JsonResponse($this->prepareJsonArr(true, ''));
    }

    /**
     * @param ArtistData $artist
     * @param array|null $busyDates
     *
     */
    public function setBusyDates(ArtistData $artist, ?array $busyDates) {
        $user = $artist->getUser();
        $items = $this->getRepo('UserBusyDates')->findBy([
            'user' => $user
        ]);

        $em = $this->getDoctrine()->getManager();

        foreach ($items as $item) {
            $em->remove($item);
        }

        foreach ($busyDates as $busyDate) {
            $date = \DateTime::createFromFormat('Y-m-d', ($busyDate));
            $item = (new UserBusyDates())->setBusyDate($date)->setUser($user);
            $em->persist($item);
        }

        $em->flush();
    }

    /**
     * @param ArtistData $artist
     * @return array
     *
     */

    public function getBusyDates(ArtistData $artist)
    {
        $busyDates = array();
        $this->busyDates = $this->getRepo("UserBusyDates")->getArtistBusyDates($artist->getUser());

        foreach ($this->busyDates as $busyDate) {
            $busyDates[] = date_format($busyDate->getBusyDate(),'Y-m-d');
        }

        return $busyDates;
    }

    /**
     * @param $events
     * @param $artist
     * @return false|string
     *
     */

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