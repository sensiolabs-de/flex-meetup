<?php

declare(strict_types = 1);

namespace App\Meetup;

use App\Entity\GroupRequest;
use App\Meetup\Exception\GatewayException;
use App\Meetup\Exception\MeetupException;
use Doctrine\Common\Persistence\ObjectManager;
use Psr\Log\LoggerInterface;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class Gateway
{
    private $client;
    private $manager;
    private $logger;

    public function __construct(ClientInterface $client, ObjectManager $manager, LoggerInterface $logger)
    {
        $this->client = $client;
        $this->manager = $manager;
        $this->logger = $logger;
    }

    public function getGroup(string $urlname): Group
    {
        try {
            return $this->client->getGroup($urlname);
        } catch (MeetupException $exception) {
            throw new GatewayException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @return Group[]
     */
    public function getGroupList(): array
    {
        $groupRequests = $this->manager->getRepository(GroupRequest::class)->findBy(['status' => 'approved']);

        $groups = [];
        /** @var GroupRequest $groupRequest */
        foreach ($groupRequests as $groupRequest) {
            try {
                $groups[] = $this->client->getGroup($groupRequest->getUrlname());
            } catch (MeetupException $exception) {
                $this->logger->error(
                    sprintf('Failed to load group "%s" in list', $groupRequest->getUrlname()),
                    ['exception' => $exception]
                );
            }
        }

        return $groups;
    }

    public function getEvent(string $urlname, string $id): Event
    {
        try {
            return $this->client->getEvent($urlname, $id);
        } catch (MeetupException $exception) {
            throw new GatewayException($exception->getMessage(), $exception->getCode(), $exception);
        }
    }

    /**
     * @return Event[]
     */
    public function getEventList(string $urlname = null): array
    {
        if (null !== $urlname) {
            $groupRequest = $this->manager->getRepository(GroupRequest::class)->findBy([
                'status' => 'approved',
                'urlname' => $urlname,
            ]);

            if (null === $groupRequest) {
                throw new NotFoundHttpException();
            }

            return $this->client->getEventList($urlname);
        }

        $groupRequests = $this->manager->getRepository(GroupRequest::class)->findBy(['status' => 'approved']);

        $events = [];
        /** @var GroupRequest $groupRequest */
        foreach ($groupRequests as $groupRequest) {
            try {
                $events = array_merge($events, $this->client->getEventList($groupRequest->getUrlname()));
            } catch (MeetupException $exception) {
                $this->logger->error(
                    sprintf('Failed to load event list for group "%s"', $groupRequest->getUrlname()),
                    ['exception' => $exception]
                );
            }
        }

        usort($events, [$this, 'sortEvents']);

        return $events;
    }

    private function sortEvents(Event $a, Event $b): int
    {
        return $a->getTime() <=> $b->getTime();
    }
}
