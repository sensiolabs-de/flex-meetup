<?php

namespace App\Meetup;

interface ClientInterface
{
    public function getGroup(string $urlname): Group;

    public function getEvent(string $urlname, string $id): Event;

    /**
     * @return Event[]
     */
    public function getEventList(string $urlname): array;
}
