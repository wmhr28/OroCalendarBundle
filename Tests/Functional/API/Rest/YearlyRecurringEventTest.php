<?php

namespace Oro\Bundle\CalendarBundle\Tests\Functional\API\Rest;

use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\CalendarBundle\Model\Recurrence;
use Oro\Bundle\CalendarBundle\Tests\Functional\DataFixtures\LoadUserData;
use Oro\Bundle\CalendarBundle\Tests\Functional\AbstractTestCase;

/**
 * The test covers operations with yearly recurring events.
 *
 * Use cases covered:
 * - Expanding of recurring event with recurrence pattern "Yearly every X year(s) on Y month Z day" in case
 *   when Z is greater than count of days in Y month.
 *
 * @dbIsolation
 */
class YearlyRecurringEventTest extends AbstractTestCase
{
    protected function setUp()
    {
        $this->initClient([], $this->generateWsseAuthHeader(), true);
        $this->loadFixtures([LoadUserData::class], true);
    }

    /**
     * Test of expanding of event with pattern "Yearly every X year(s) on Y month Z day" in case
     * when Z is greater than count of days in Y month.
     *
     * Steps:
     * 1. Create new calendar event with pattern "Yearly Day 31 of every 1 year Start Sun 2/28/2015 No end date".
     * 2. Get expanded events and verify all properties in response.
     *    The significant part of response verification is count of events and "start", "end" properties.
     */
    public function testExpandingOfEventWithBorderConditionsOfDayOfMonth()
    {
        // Step 1. Create new calendar event with pattern
        // "Yearly Day 31 of every 1 year Start Sun 2/28/2015 No end date".
        $eventData = [
            'title'       => 'Test Yearly Recurring Event',
            'description' => 'Test Yearly Recurring Event Description',
            'allDay'      => false,
            'calendar'    => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
            'start'       => '2015-02-28T09:00:00+00:00',
            'end'         => '2015-02-28T09:30:00+00:00',
            'recurrence'  => [
                'timeZone'       => 'UTC',
                'recurrenceType' => Recurrence::TYPE_YEARLY,
                'interval'       => 12,
                'monthOfYear'    => 2, // February
                'dayOfMonth'     => 31,
                'startTime'      => '2015-02-28T09:00:00+00:00',
                'occurrences'    => null,
                'endTime'        => null,
            ],
            'attendees'   => [],
        ];
        $this->restRequest(
            [
                'method'    => 'POST',
                'url'       => $this->getUrl('oro_api_post_calendarevent'),
                'server'    => $this->generateWsseAuthHeader('foo_user_1', 'foo_user_1_api_key'),
                'content'   => json_encode($eventData)
            ]
        );

        // Step 2. Get expanded events and verify all properties in response.
        //         The significant part of response verification is count of events and "start", "end" properties.
        $response = $this->getRestResponseContent(['statusCode' => 201, 'contentType' => 'application/json']);
        /** @var CalendarEvent $recurringEvent */
        $recurringEvent = $this->getEntity(CalendarEvent::class, $response['id']);
        $this->restRequest(
            [
                'method' => 'GET',
                'url' => $this->getUrl(
                    'oro_api_get_calendarevents',
                    [
                        'calendar'    => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                        'start'       => '2015-01-01T00:00:00+00:00',
                        'end'         => '2018-01-01T00:00:00+00:00',
                        'subordinate' => true,
                    ]
                ),
                'server' => $this->generateWsseAuthHeader('foo_user_1', 'foo_user_1_api_key')
            ]
        );

        $response = $this->getRestResponseContent(['statusCode' => 200, 'contentType' => 'application/json']);
        $expectedResponse = [
            [
                'id'            => $recurringEvent->getId(),
                'title'         => 'Test Yearly Recurring Event',
                'description'   => 'Test Yearly Recurring Event Description',
                'allDay'        => false,
                'calendar'      => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                'start'         => '2015-02-28T09:00:00+00:00',
                'end'           => '2015-02-28T09:30:00+00:00',
                'attendees'     => [],
            ],
            [
                'id'            => $recurringEvent->getId(),
                'title'         => 'Test Yearly Recurring Event',
                'description'   => 'Test Yearly Recurring Event Description',
                'allDay'        => false,
                'calendar'      => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                'start'         => '2016-02-29T09:00:00+00:00', // leap year
                'end'           => '2016-02-29T09:30:00+00:00',
                'attendees'     => [],
            ],
            [
                'id'            => $recurringEvent->getId(),
                'title'         => 'Test Yearly Recurring Event',
                'description'   => 'Test Yearly Recurring Event Description',
                'allDay'        => false,
                'calendar'      => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                'start'         => '2017-02-28T09:00:00+00:00',
                'end'           => '2017-02-28T09:30:00+00:00',
                'attendees'     => [],
            ],
        ];
        $this->assertResponseEquals($expectedResponse, $response, false);
    }

    /**
     * Test of expanding of event with pattern "Yearly every X year(s) on Y month Z day" in case
     * when Z=29 and Y=February and it checks 'recurrencePattern' is the same for several years.
     *
     * Steps:
     * 1. Create new calendar event with pattern "Yearly Day 29 of every 1 year Start Sun 12/28/2016 No end date".
     * Step 2. Get expanded events and verify all properties in response.
     *         The significant part of response verification is 'recurrencePattern'.
     */
    public function testTextPatternOfEventWithDifferentYearsInStarttimeAndStart()
    {
        // Step 1. Create new calendar event with pattern
        // "Yearly Day 29 of every 1 year Start Sun 12/28/2016 No end date".
        $eventData = [
            'title'       => 'Test Yearly Recurring Event',
            'description' => 'Test Yearly Recurring Event Description',
            'allDay'      => false,
            'calendar'    => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
            'start'       => '2016-12-28T09:00:00+00:00', //the year value must be leap year here
            'end'         => '2016-12-28T09:30:00+00:00',
            'recurrence'  => [
                'timeZone'       => 'UTC',
                'recurrenceType' => Recurrence::TYPE_YEARLY,
                'interval'       => 12,
                'monthOfYear'    => 2, // February
                'dayOfMonth'     => 29,
                'startTime'      => '2016-12-28T09:00:00+00:00',
                'occurrences'    => null,
                'endTime'        => null,
            ],
            'attendees'   => [],
        ];
        $this->restRequest(
            [
                'method'    => 'POST',
                'url'       => $this->getUrl('oro_api_post_calendarevent'),
                'server'    => $this->generateWsseAuthHeader('foo_user_1', 'foo_user_1_api_key'),
                'content'   => json_encode($eventData)
            ]
        );

        // Step 2. Get expanded events and verify all properties in response.
        //         The significant part of response verification is 'recurrencePattern'.
        $response = $this->getRestResponseContent(['statusCode' => 201, 'contentType' => 'application/json']);
        /** @var CalendarEvent $recurringEvent */
        $recurringEvent = $this->getEntity(CalendarEvent::class, $response['id']);
        $this->restRequest(
            [
                'method' => 'GET',
                'url' => $this->getUrl(
                    'oro_api_get_calendarevents',
                    [
                        'calendar'    => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                        'start'       => '2017-01-01T00:00:00+00:00',
                        'end'         => '2021-01-01T00:00:00+00:00',
                        'subordinate' => true,
                    ]
                ),
                'server' => $this->generateWsseAuthHeader('foo_user_1', 'foo_user_1_api_key')
            ]
        );

        $response = $this->getRestResponseContent(['statusCode' => 200, 'contentType' => 'application/json']);
        $expectedResponse = [
            [
                'id'                => $recurringEvent->getId(),
                'title'             => 'Test Yearly Recurring Event',
                'description'       => 'Test Yearly Recurring Event Description',
                'allDay'            => false,
                'calendar'          => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                'start'             => '2017-02-28T09:00:00+00:00',
                'end'               => '2017-02-28T09:30:00+00:00',
                'attendees'         => [],
                'recurrencePattern' => "Yearly every 1 year on Feb 29",
            ],
            [
                'id'                => $recurringEvent->getId(),
                'title'             => 'Test Yearly Recurring Event',
                'description'       => 'Test Yearly Recurring Event Description',
                'allDay'            => false,
                'calendar'          => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                'start'             => '2018-02-28T09:00:00+00:00',
                'end'               => '2018-02-28T09:30:00+00:00',
                'attendees'         => [],
                'recurrencePattern' => "Yearly every 1 year on Feb 29",
            ],
            [
                'id'                => $recurringEvent->getId(),
                'title'             => 'Test Yearly Recurring Event',
                'description'       => 'Test Yearly Recurring Event Description',
                'allDay'            => false,
                'calendar'          => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                'start'             => '2019-02-28T09:00:00+00:00',
                'end'               => '2019-02-28T09:30:00+00:00',
                'attendees'         => [],
                'recurrencePattern' => "Yearly every 1 year on Feb 29",
            ],
            [
                'id'                => $recurringEvent->getId(),
                'title'             => 'Test Yearly Recurring Event',
                'description'       => 'Test Yearly Recurring Event Description',
                'allDay'            => false,
                'calendar'          => $this->getReference('oro_calendar:calendar:foo_user_1')->getId(),
                'start'             => '2020-02-29T09:00:00+00:00',
                'end'               => '2020-02-29T09:30:00+00:00',
                'attendees'         => [],
                'recurrencePattern' => "Yearly every 1 year on Feb 29",
            ],
        ];
        $this->assertResponseEquals($expectedResponse, $response, false);
    }
}
