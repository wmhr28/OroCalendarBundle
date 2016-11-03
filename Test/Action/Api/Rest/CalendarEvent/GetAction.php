<?php

namespace Oro\Bundle\CalendarBundle\Test\Action\Api\Rest\CalendarEvent;

use Oro\Bundle\CalendarBundle\Test\Action\Api\Rest\GetAction as BaseGetAction;

/**
 * @internal
 *
 * {@inheritdoc}
 */
class GetAction extends BaseGetAction
{
    const ALIAS = 'test_api_rest_get_calendar_event';

    /**
     * {@inheritdoc}
     */
    public function initialize(array $options)
    {
        parent::initialize($options);
        $this->requestRoute = 'oro_api_get_calendarevent';

        return $this;
    }
}