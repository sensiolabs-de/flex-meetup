<?php

declare(strict_types=1);

namespace App\Meetup;

use Symfony\Component\Workflow\Event\Event as WorkflowEvent;
use Symfony\Component\EventDispatcher\EventSubscriberInterface;

class GroupRequestWorkflowListener implements EventSubscriberInterface
{
    private $mailer;

    public function __construct(Mailer $mailer)
    {
        $this->mailer = $mailer;
    }

    public static function getSubscribedEvents()
    {
        return [
            'workflow.group_request.entered.confirmed' => 'sendReviewNotification',
            'workflow.group_request.entered.approved' => 'sendApproval',
            'workflow.group_request.entered.rejected' => 'sendRejection',
        ];
    }

    public function sendReviewNotification(WorkflowEvent $event): void
    {
        $this->mailer->sendReviewNotification($event->getSubject());
    }

    public function sendApproval(WorkflowEvent $event): void
    {
        $this->mailer->sendApproval($event->getSubject());
    }

    public function sendRejection(WorkflowEvent $event): void
    {
        $this->mailer->sendRejection($event->getSubject());
    }
}
