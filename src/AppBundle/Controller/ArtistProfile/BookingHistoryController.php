<?php
declare(strict_types=1);

namespace AppBundle\Controller\ArtistProfile;

use AppBundle\DBAL\BookingStatusEnum;
use AppBundle\Traits\ControllerSupport;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\Security;

/**
 * @Route("/profile/artist")
 * @Security("has_role('ROLE_ARTIST')")
 */
class BookingHistoryController extends Controller
{
    use ControllerSupport;

    /**
     * @Route("/{id}/edit/booking/all", name="artist_edit_booking_all")
     * @Security("user.getID() == id")
     */
    public function editBookingAction(Request $r, $id)
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

        $bookings = $this->getRepo('Booking')->getAllByPerformer($artist->getUser());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $bookings,
            $r->query->get('page', 1)/*page number*/,
            $this->getParameter('pagination_booking_count')/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/artist/booking_history/all_booking.html.twig', [
            'bookings' => $pagination,
            'artist' => $artist
        ]);
    }

    /**
     * @Route("/{id}/edit/booking/accepted", name="artist_edit_booking_accepted")
     */
    public function editBookingAcceptedAction(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        $bookings = $this->getRepo('Booking')->getAllAcceptedByPerformer($artist->getUser());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $bookings,
            $r->query->get('page', 1)/*page number*/,
            $this->getParameter('pagination_booking_count')/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/artist/booking_history/accepted_booking.html.twig', [
            'bookings' => $pagination,
            'artist' => $artist
        ]);
    }

    /**
     * @Route("/{id}/edit/booking/archived", name="artist_edit_booking_archived")
     */
    public function editBookingArchivedAction(Request $r, $id)
    {
        $artist = $this->getRepo("ArtistData")->findOneBy(['user' => $id]);

        if (!$artist) {
            throw $this->createNotFoundException('Unable to find Artist entity.');
        }

        $bookings = $this->getRepo('Booking')->getArchivedByPerformer($artist->getUser());

        $paginator = $this->get('knp_paginator');
        $pagination = $paginator->paginate(
            $bookings,
            $r->query->get('page', 1)/*page number*/,
            $this->getParameter('pagination_booking_count')/*limit per page*/
        );
        $pagination->setTemplate('AppBundle:Pagination:pagination_booking.html.twig');

        return $this->render('@App/user/artist/booking_history/archived_booking.html.twig', [
            'bookings' => $pagination,
            'artist' => $artist
        ]);
    }

    /**
     * @Route("/{id}/accept/booking/modal", name="artist_accept_booking_modal")
     */
    public function acceptBookingModalAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $html = $this->renderView('@App/modals/accept_booking.html.twig', ['id' => $id]);

        return new JsonResponse($this->prepareJsonArr(true, 'Confirm complete !', ['html' => $html]));
    }

    /**
     * @Route("/{id}/reject/booking/modal", name="artist_reject_booking_modal")
     */
    public function rejectBookingModalAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $html = $this->renderView('@App/modals/reject_booking.html.twig', ['id' => $id]);

        return new JsonResponse($this->prepareJsonArr(true, 'Confirm reject !', ['html' => $html]));
    }

    /**
     * @Route("/{id}/cancel/booking/modal", name="artist_cancel_booking_modal")
     */
    public function cancelBookingModalAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $date = $booking->getEventDate()->getTimeStamp();
        $diffDate = (date_diff((new \DateTime()), $booking->getEventDate()))->days;
        $html = '';

        if ($diffDate <= 7) {
            $html = $this->renderView('@App/modals/cancel_booking.html.twig', [
                'id' => $id,
                'text' => 'By accepting the booking you\'ve committed to perform. In case of force majeure you have to inform User and cancel the booking.',
            ]);
        } else {
            $html = $this->renderView('@App/modals/cancel_booking.html.twig', [
                'id' => $id,
                'text' => 'Are you sure you want to cancel this event?'
            ]);
        }

        return new JsonResponse($this->prepareJsonArr(true, 'Confirm cancel !', ['html' => $html]));
    }


    /**
     * @Route("/{id}/accept/booking", name="artist_accept_booking")
     */
    public function acceptBookingAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $em = $this->getDoctrine()->getManager();
        $booking->setBookingStatus(BookingStatusEnum::VALUE_CONFIRMED);
        $em->persist($booking);
        $em->flush();

        return new JsonResponse($this->prepareJsonArr(true, 'Confirm complete !'));
    }

    /**
     * @Route("/{id}/reject/booking", name="artist_reject_booking")
     */
    public function rejectBookingAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $stripeCharge = $this->get('stripe.charge');
        $charge = $stripeCharge->retrieve($booking->getChargeId());

        if ($charge->outcome->type != "authorized") {
            return new JsonResponse($this->prepareJsonArr(false, "Can't rejected this booking"));
        }

        $stripeRefund = $this->get('stripe.refund');
        $stripeRefund->create($charge->id);

        $em = $this->getDoctrine()->getManager();
        $booking->setBookingStatus(BookingStatusEnum::VALUE_REJECTED);
        $em->persist($booking);
        $em->flush();

        return new JsonResponse($this->prepareJsonArr(true, 'Booking reject !'));
    }

    /**
     * @Route("/{id}/cancel/booking", name="artist_cancel_booking")
     */
    public function cancelBookingAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $stripeCharge = $this->get('stripe.charge');
        $charge = $stripeCharge->retrieve($booking->getChargeId());

        if ($charge->outcome->type != "authorized") {
            return new JsonResponse($this->prepareJsonArr(false, "Can't cancelled this booking"));
        }

        $stripeRefund = $this->get('stripe.refund');
        $stripeRefund->create($charge->id);

        $em = $this->getDoctrine()->getManager();
        $booking->setBookingStatus(BookingStatusEnum::VALUE_CANCELED);
        $em->persist($booking);
        $em->flush();

        return new JsonResponse($this->prepareJsonArr(true, 'Booking canceled !'));
    }

    /**
     * @Route("/{id}/comment/booking/modal", name="artist_comment_booking_modal")
     */
    public function commentBookingModalAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $html = $this->renderView('@App/modals/comment_booking.html.twig', ['booking' => $booking]);

        return new JsonResponse($this->prepareJsonArr(true, 'Comment Booking', ['html' => $html]));
    }

    /**
     * @Route("/{id}/location/booking/modal", name="artist_location_booking_modal")
     */
    public function locationBookingModalAction(Request $r, $id)
    {
        $booking = $this->getRepo("Booking")->find($id);

        if (!$booking) {
            return new JsonResponse($this->prepareJsonArr(false, 'Unable to find Booking entity.'));
        }

        $html = $this->renderView('@App/modals/location_booking.html.twig', ['booking' => $booking]);

        return new JsonResponse($this->prepareJsonArr(true, 'Booking Location', ['html' => $html]));
    }
}