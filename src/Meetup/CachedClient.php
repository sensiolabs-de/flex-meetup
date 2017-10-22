<?php

declare(strict_types=1);

namespace App\Meetup;

use Psr\Cache\CacheItemPoolInterface;

class CachedClient implements ClientInterface
{
    private const GROUP_TTL = 3600;
    private const EVENT_TTL = 3600;
    private const EVENT_LIST_TTL = 3600;

    private $decoratedClient;
    private $cachePool;

    public function __construct(Client $decoratedClient, CacheItemPoolInterface $cachePool)
    {
        $this->decoratedClient = $decoratedClient;
        $this->cachePool = $cachePool;
    }

    public function getGroup(string $urlname): Group
    {
        $item = $this->cachePool->getItem(sprintf('group_%s', $urlname));
        if (!$item->isHit()) {
            $group = $this->decoratedClient->getGroup($urlname);
            $item->set($group);
            $item->expiresAfter(self::GROUP_TTL);
            $this->cachePool->save($item);
        }

        return $item->get();
    }

    public function getEvent(string $urlname, string $id): Event
    {
        $item = $this->cachePool->getItem(sprintf('event_%s_%s', $urlname, $id));
        if (!$item->isHit()) {
            $event = $this->decoratedClient->getEvent($urlname, $id);
            $item->set($event);
            $item->expiresAfter(self::EVENT_TTL);
            $this->cachePool->save($item);
        }

        return $item->get();
    }

    public function getEventList(string $urlname): array
    {
        $item = $this->cachePool->getItem(sprintf('eventlist_%s', $urlname));
        if (!$item->isHit()) {
            $eventList = $this->decoratedClient->getEventList($urlname);
            $item->set($eventList);
            $item->expiresAfter(self::EVENT_LIST_TTL);
            $this->cachePool->save($item);
        }
        return $item->get();
    }
}
