<?php

declare(strict_types = 1);

namespace App\Validation;

use App\Meetup\ClientInterface;
use App\Meetup\Exception\MeetupException;
use Symfony\Component\Validator\Constraint;
use Symfony\Component\Validator\ConstraintValidator;
use Symfony\Component\Validator\Exception\UnexpectedTypeException;

class MeetupGroupValidator extends ConstraintValidator
{
    private $client;

    public function __construct(ClientInterface $client)
    {
        $this->client = $client;
    }

    public function validate($value, Constraint $constraint)
    {
        if (!$constraint instanceof MeetupGroup) {
            throw new UnexpectedTypeException($constraint, MeetupGroup::class);
        }

        if (null === $value || '' === $value) {
            return;
        }

        try {
            $this->client->getGroup($value);
        } catch (MeetupException $exception) {
            $this->context->buildViolation($constraint->message)
                ->setParameter('{{ urlname }}', $value)
                ->setCode(MeetupGroup::ERROR)
                ->addViolation();
        }
    }
}
