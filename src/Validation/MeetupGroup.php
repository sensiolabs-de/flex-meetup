<?php

declare(strict_types = 1);

namespace App\Validation;

use Symfony\Component\Validator\Constraint;

/**
 * @Annotation
 * @Target({"PROPERTY"})
 */
class MeetupGroup extends Constraint
{
    public const ERROR = 'bb034146-37f4-4b63-b6fa-a30ee05cf019';

    public $message = 'There is no group with urlname "{{ urlname }}" on meetup.com';
}
