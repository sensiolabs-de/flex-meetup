<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\GroupRequest;
use App\Meetup\Exception\GatewayException;
use App\Meetup\Gateway;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;

class EventController extends Controller
{
    /**
     * @Route("/{urlname}/event/{eventId}", name="event")
     */
    public function eventAction(Gateway $gateway, GroupRequest $groupRequest, string $eventId): Response
    {
        if (!$groupRequest->isApproved()) {
            throw $this->createNotFoundException(sprintf('Group "%s" not approved.', $groupRequest->getUrlname()));
        }

        try {
            $event = $gateway->getEvent($groupRequest->getUrlname(), $eventId);
        } catch (GatewayException $exception) {
            throw new ServiceUnavailableHttpException(60, 'Event could not be loaded', $exception);
        }

        return $this->render('event/show.html.twig', [
            'event' => $event,
        ]);
    }

    public function listAction(Gateway $gateway, string $urlname = null): Response
    {
        return $this->render('event/list.html.twig', [
            'events' => $gateway->getEventList($urlname)
        ]);
    }
}
