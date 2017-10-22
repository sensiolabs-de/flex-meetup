<?php

declare(strict_types = 1);

namespace App\Meetup;

use App\Meetup\Exception\ClientRequestException;
use App\Meetup\Exception\ClientResponseException;
use App\Meetup\Exception\ClientResponseFormatException;
use GuzzleHttp\Psr7\Request;
use Http\Client\Exception;
use Http\Client\HttpClient;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Serializer\Exception\ExceptionInterface as SerializerException;

class Client implements ClientInterface
{
    private $httpClient;
    private $serializer;

    public function __construct(HttpClient $httpClient, SerializerInterface $serializer)
    {
        $this->httpClient = $httpClient;
        $this->serializer = $serializer;
    }

    public function getGroup(string $urlname): Group
    {
        $response = $this->request(sprintf('/%s', $urlname));

        try {
            /** @var Group $group */
            $group = $this->serializer->deserialize($response, Group::class, 'json');
        } catch (SerializerException $exception) {
            throw new ClientResponseFormatException('Client could not hydrate group from response.', 0 , $exception);
        }

        return $group;
    }

    public function getEvent(string $urlname, string $id): Event
    {
        $response = $this->request(sprintf('/%s/events/%s', $urlname, $id));

        try {
            /** @var Event $event */
            $event = $this->serializer->deserialize($response, Event::class, 'json');
        } catch (SerializerException $exception) {
            throw new ClientResponseFormatException('Client could not hydrate event from response.', 0 , $exception);
        }

        return $event;
    }

    /**
     * @return Event[]
     */
    public function getEventList(string $urlname): array
    {
        $response = $this->request(sprintf('/%s/events', $urlname));

        try {
            /** @var Event[] $events */
            $events = $this->serializer->deserialize($response, sprintf('%s[]', Event::class), 'json');
        } catch (SerializerException $exception) {
            throw new ClientResponseFormatException('Client could not hydrate event list from response.', 0 , $exception);
        }

        return $events;
    }

    private function request(string $path): string
    {
        $request = new Request('GET', $path);

        try {
            $response = $this->httpClient->sendRequest($request);
        } catch (Exception $exception) {
            throw new ClientRequestException(sprintf('Reqeuest GET %s could not be performed.', $path), 0, $exception);
        }

        if ($response->getStatusCode() !== 200) {
            throw new ClientResponseException(
                sprintf('Response of GET %s was not successful [HTTP: %d]', $path, $response->getStatusCode())
            );
        }

        return $response->getBody()->getContents();
    }
}
