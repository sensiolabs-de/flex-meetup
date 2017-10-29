<?php

declare(strict_types = 1);

namespace App\Command;

use App\Entity\GroupRequest;
use Doctrine\Common\Persistence\ObjectManager;
use Doctrine\Common\Persistence\ObjectRepository;
use Symfony\Bundle\FrameworkBundle\Command\ContainerAwareCommand;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\Workflow\Exception\LogicException;

class GroupRequestCommand extends ContainerAwareCommand
{
    /**
     * @var SymfonyStyle
     */
    private $io;

    protected function configure()
    {
        $this
            ->setName('user-group:group-request')
            ->setDescription('Command to administer group requests');
    }

    protected function execute(InputInterface $input, OutputInterface $output)
    {
        $this->io = new SymfonyStyle($input, $output);
        $this->io->title('Group Request Administration');

        $groups = $this->fetchGroups();

        if (0 === count($groups)) {
            $this->io->success('No groups to process.');

            return 0;
        }

        $this->io->comment(sprintf('There are %d groups to process.', count($groups)));

        $processed = 0;
        while (0 < count($groups)) {
            $group = $this->nextGroup($groups);
            $this->displayGroup($group);

            if ($this->processGroup($group)) {
                ++$processed;
                $this->io->success(
                    sprintf('Group "%s" successful processed: %s.', $group->getUrlname(), $group->getStatus())
                );
            }

            unset($groups[$group->getUrlname()]);

            if (0 < count($groups) && !$this->continue()) {
                break;
            }
        }

        $this->io->success(sprintf('Processed %d groups.', $processed));

        return 0;
    }

    /**
     * @return GroupRequest[]
     */
    private function fetchGroups(): array
    {
        /** @var ObjectManager $om */
        $om = $this->getContainer()->get('doctrine')->getManager();
        /** @var ObjectRepository $repo */
        $repo = $om->getRepository(GroupRequest::class);
        /** @var GroupRequest[] $groups */
        $groups = $repo->findBy(['status' => GroupRequest::STATUS_CONFIRMED], ['createdAt' => 'ASC']);

        foreach ($groups as $key => $group) {
            $groups[$group->getUrlname()] = $group;
            unset($groups[$key]);
        }

        return $groups;
    }

    private function nextGroup(array $groups): GroupRequest
    {
        if (1 === count($groups)) {
            return reset($groups);
        }

        $urlname = $this->io->choice('Which group do you want to edit?', array_keys($groups));

        return $groups[$urlname];
    }

    private function displayGroup(GroupRequest $group): void
    {
        $this->io->section(sprintf('Details of group request for "<comment>%s</comment>":', $group->getUrlname()));

        $this->io->table(['Attribute', 'Value'], [
            ['URL-Name', $group->getUrlname()],
            ['E-Mail-Address', $group->getEmail()],
            ['Description', $group->getComment()],
            ['Created at', $group->getCreatedAt()->format('m/d/Y H:i:s')],
            ['Meetup-Link', sprintf('https://www.meetup.com/%s', $group->getUrlname())]
        ]);
    }

    private function processGroup(GroupRequest $group): bool
    {
        $transition = $this->io->choice(
            sprintf('What to do with "<comment>%s</comment>"?', $group->getUrlname()),
            ['skip', 'approved', 'reject']
        );

        if ('skip' === $transition) {
            return false;
        }


        $this->getContainer()->get('workflow.group_request')->apply($group, $transition);

        $this->getContainer()->get('doctrine')->getManager()->flush();

        return true;
    }

    private function continue(): bool
    {
        $answer = $this->io->ask('Do you want to continue? (y/n)', 'y', function ($answer) {
            if (!in_array($answer, ['y', 'n'])) {
                throw new \RuntimeException('Please answer with "y" or "n".');
            }

            return $answer;
        });

        return 'y' === $answer;
    }
}
