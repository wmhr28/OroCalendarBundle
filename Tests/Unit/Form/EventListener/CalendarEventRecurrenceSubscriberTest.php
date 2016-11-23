<?php

namespace Oro\Bundle\CalendarBundle\Tests\Unit\Form\EventListener;

use Symfony\Component\Form\FormEvent;

use Oro\Bundle\CalendarBundle\Entity\Recurrence;
use Oro\Bundle\CalendarBundle\Form\EventListener\CalendarEventRecurrenceSubscriber;

class CalendarEventRecurrenceSubscriberTest extends \PHPUnit_Framework_TestCase
{
    /**
     * @var  CalendarEventRecurrenceSubscriber
     */
    protected $calendarEventRecurrenceSubscriber;

    public function setUp()
    {
        $this->calendarEventRecurrenceSubscriber = new CalendarEventRecurrenceSubscriber();
    }

    public function testPreSubmitShouldRemoveRecurrence()
    {
        $form = $this->getMock('Symfony\Component\Form\FormInterface');
        $recurrenceForm = $this->getMock('Symfony\Component\Form\FormInterface');
        $recurrence = new Recurrence();

        $event = new FormEvent($form, ['id' => 1, 'recurrence' => []]);
        $form->expects($this->at(0))
            ->method('has')
            ->with('recurrence')
            ->willReturn(true);
        $form->expects($this->at(1))
            ->method('has')
            ->with('repeat')
            ->willReturn(true);
        $form->expects($this->any())
            ->method('get')
            ->with('recurrence')
            ->will($this->returnValue($recurrenceForm));
        $recurrenceForm->expects($this->once())
            ->method('getData')
            ->will($this->returnValue($recurrence));
        $recurrenceForm->expects($this->once())
            ->method('setData')
            ->with(null);

        $this->calendarEventRecurrenceSubscriber->preSubmit($event);
        $this->assertEquals(['id' => 1], $event->getData());
    }
}
