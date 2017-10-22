<?php

declare(strict_types = 1);

namespace App\Meetup;

use App\Entity\GroupRequest;
use App\Meetup\Exception\MailerException;
use Twig\Environment;
use Twig\Error\Error;

class Mailer
{
    private $mailer;
    private $twig;
    private $adminMail;

    public function __construct(\Swift_Mailer $mailer, Environment $twig, string $adminMail)
    {
        $this->mailer = $mailer;
        $this->twig = $twig;
        $this->adminMail = $adminMail;
    }

    public function sendProposal(TalkProposal $talkProposal, GroupRequest $groupRequest): void
    {
        $this->send(
            $groupRequest,
            'email/proposal.txt.twig',
            sprintf('Talk Proposal for %s', $groupRequest->getUrlname()),
            null,
            ['talk_proposal' => $talkProposal]
        );
    }

    public function sendConfirmation(GroupRequest $groupRequest): void
    {
        $this->send($groupRequest, 'email/confirmation.txt.twig', 'Please confirm your mail address');
    }

    public function sendReviewNotification(GroupRequest $groupRequest): void
    {
        $this->send($groupRequest, 'email/review_notification.txt.twig', 'New group request to review', $this->adminMail);
    }

    public function sendApproval(GroupRequest $groupRequest): void
    {
        $this->send($groupRequest, 'email/approval.txt.twig', 'Your group request is approved');
    }

    public function sendRejection(GroupRequest $groupRequest): void
    {
        $this->send($groupRequest, 'email/rejection.txt.twig', 'Your group request was rejected');
    }

    private function send(GroupRequest $groupRequest, string $template, string $subject, string $sendTo = null, array $params = []): void
    {
        try {
            $emailBody = $this->twig->render($template, array_merge(['group_request' => $groupRequest], $params));

            /** @var \Swift_Message $message */
            $message = $this->mailer->createMessage();
            $message
                ->setSubject(sprintf('User Group Radar: %s', $subject))
                ->setFrom($this->adminMail)
                ->setTo($sendTo ? $sendTo : $groupRequest->getEmail())
                ->setBody($emailBody);

            $this->mailer->send($message);
        } catch (\Swift_SwiftException|Error $exception) {
            throw new MailerException('Mail could not be send', 0, $exception);
        }
    }
}
