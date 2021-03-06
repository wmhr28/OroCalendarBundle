<?php

namespace Oro\Bundle\CalendarBundle\Form\EventListener;

use Doctrine\Common\Persistence\ManagerRegistry;

use Symfony\Component\EventDispatcher\EventSubscriberInterface;
use Symfony\Component\Form\FormEvent;
use Symfony\Component\Form\FormEvents;

use Oro\Bundle\CalendarBundle\Entity\Calendar;
use Oro\Bundle\CalendarBundle\Entity\CalendarEvent;
use Oro\Bundle\SecurityBundle\Authentication\TokenAccessorInterface;

class CalendarSubscriber implements EventSubscriberInterface
{
    /** @var TokenAccessorInterface */
    protected $tokenAccessor;

    /** @var ManagerRegistry */
    protected $registry;

    /**
     * @param TokenAccessorInterface $tokenAccessor
     * @param ManagerRegistry        $registry
     */
    public function __construct(TokenAccessorInterface $tokenAccessor, ManagerRegistry $registry)
    {
        $this->tokenAccessor = $tokenAccessor;
        $this->registry = $registry;
    }

    /**
     * {@inheritdoc}
     */
    public static function getSubscribedEvents()
    {
        return [
            FormEvents::PRE_SET_DATA => 'fillCalendar',
        ];
    }

    /**
     * PRE_SET_DATA event handler
     *
     * @param FormEvent $event
     */
    public function fillCalendar(FormEvent $event)
    {
        /** @var CalendarEvent $data */
        $data = $event->getData();
        if ($data && !$data->getId() && !$data->getCalendar() && !$data->getSystemCalendar()) {
            /** @var Calendar $defaultCalendar */
            $defaultCalendar = $this->registry
                ->getRepository('OroCalendarBundle:Calendar')
                ->findDefaultCalendar(
                    $this->tokenAccessor->getUserId(),
                    $this->tokenAccessor->getOrganizationId()
                );
            $data->setCalendar($defaultCalendar);
            $event->setData($data);
        }
    }
}
