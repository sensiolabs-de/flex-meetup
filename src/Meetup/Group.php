<?php

declare(strict_types = 1);

namespace App\Meetup;

/**
 * A meetup.com group organizes events.
 */
class Group
{
    private $id;
    private $name;
    private $status;
    private $link;
    private $urlname;
    private $description;
    private $created;
    private $city;
    private $country;
    private $joinMode;
    private $visibility;
    private $lat;
    private $lon;
    private $members;
    private $who;
    private $groupPhoto;

    public function getId(): int
    {
        return $this->id;
    }

    public function getName(): string
    {
        return $this->name;
    }

    public function getStatus(): string
    {
        return $this->status;
    }

    public function getLink(): string
    {
        return $this->link;
    }

    public function getUrlname(): string
    {
        return $this->urlname;
    }

    public function getDescription(): string
    {
        return $this->description;
    }

    public function getCreated(): \DateTimeInterface
    {
        return $this->created;
    }

    public function getCity(): string
    {
        return $this->city;
    }

    public function getCountry(): string
    {
        return $this->country;
    }

    public function getJoinMode(): string
    {
        return $this->joinMode;
    }

    public function getVisibility(): string
    {
        return $this->visibility;
    }

    public function getLatitude(): float
    {
        return $this->lat;
    }

    public function getLongitude(): float
    {
        return $this->lon;
    }

    public function getMembers(): int
    {
        return $this->members;
    }

    public function getWho(): string
    {
        return $this->who;
    }

    public function getGroupPhoto(): string
    {
        return $this->groupPhoto;
    }
}
