<?php

declare(strict_types = 1);

namespace App\Meetup\Exception;

class GatewayException extends \DomainException implements MeetupException
{
}
