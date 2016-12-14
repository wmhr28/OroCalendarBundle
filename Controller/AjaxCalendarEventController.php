<?php

namespace Oro\Bundle\CalendarBundle\Controller;

use Sensio\Bundle\FrameworkExtraBundle\Configuration\Route;

use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\JsonResponse;

use Oro\Bundle\CalendarBundle\Entity\Attendee;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Exception\ChangeInvitationStatusException;
use Oro\Bundle\CalendarBundle\Manager\AttendeeManager;

/**
 * @Route("/event/ajax")
 */
class AjaxCalendarEventController extends Controller
{
    /**
     * @Route("/accepted/{id}",
     *      name="oro_calendar_event_accepted",
     *      requirements={"id"="\d+"}, defaults={"status"="accepted"})
     * @Route("/tentative/{id}",
     *      name="oro_calendar_event_tentative",
     *      requirements={"id"="\d+"}, defaults={"status"="tentative"})
     * @Route("/declined/{id}",
     *      name="oro_calendar_event_declined",
     *      requirements={"id"="\d+"}, defaults={"status"="declined"})
     *
     * @param CalendarEvent $entity
     * @param string        $status
     *
     * @return JsonResponse
     */
    public function changeStatus(CalendarEvent $entity, $status)
    {
        try {
            $loggedUser = $this->get('oro_security.security_facade')->getLoggedUser();
            $manager = $this->get('oro_calendar.calendar_event_manager');
            $manager->changeInvitationStatus($entity, $status, $loggedUser);
        } catch (ChangeInvitationStatusException $exception) {
            return new JsonResponse(
                [
                    'successfull' => false,
                    'message'     => $exception->getMessage(),
                ]
            );
        }

        $this->getDoctrine()
            ->getManagerForClass('Oro\Bundle\CalendarBundle\Entity\CalendarEvent')
            ->flush();

        $this->get('oro_calendar.send_processor.email')->sendRespondNotification($entity);

        return new JsonResponse(['successful' => true]);
    }

    /**
     * @Route(
     *      "/attendees-autocomplete-data/{id}",
     *      name="oro_calendar_event_attendees_autocomplete_data",
     *      options={"expose"=true}
     * )
     *
     * @param int $id
     *
     * @return JsonResponse
     */
    public function attendeesAutocompleteDataAction($id)
    {
        $attendeeManager = $this->getAttendeeManager();
        $attendees = $attendeeManager->loadAttendeesByCalendarEventId($id);

        $attendeeRelationManager = $this->get('oro_calendar.attendee_relation_manager');

        $result = [];

        foreach ($attendees as $attendee) {
            $result[] = [
                'text'        => $attendeeRelationManager->getRelatedDisplayName($attendee),
                'displayName' => $attendee->getDisplayName(),
                'email'       => $attendee->getEmail(),
                'type'        => $attendee->getType() ? $attendee->getType()->getId() : null,
                'status'      => $attendee->getStatus() ? $attendee->getStatus()->getId() : null,
                'hidden'      => !$attendeeRelationManager->getRelatedEntity($attendee),
                /**
                 * Selected Value Id should additionally encoded because it should be used as string key
                 * to compare with value
                 */
                'id'          => json_encode(
                    [
                        'entityClass' => Attendee::class,
                        'entityId'    => $attendee->getId(),
                    ]
                )
            ];
        }

        return new JsonResponse([
            'result'   => $result,
            'excluded' => $attendeeManager->createAttendeeExclusions($attendees),
        ]);
    }

    /**
     * @return AttendeeManager
     */
    protected function getAttendeeManager()
    {
        return $this->get('oro_calendar.attendee_manager');
    }
}
