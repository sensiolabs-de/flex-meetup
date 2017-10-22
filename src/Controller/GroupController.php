<?php

declare(strict_types = 1);

namespace App\Controller;

use App\Entity\GroupRequest;
use App\Form\GroupRequestType;
use App\Form\TalkProposalType;
use App\Meetup\Exception\MailerException;
use App\Meetup\Exception\MeetupException;
use App\Meetup\Gateway;
use App\Meetup\Mailer;
use Doctrine\DBAL\DBALException;
use Doctrine\DBAL\Driver\PDOException;
use Doctrine\ORM\ORMException;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\Controller;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\Exception\ServiceUnavailableHttpException;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Workflow\Exception\LogicException;

class GroupController extends Controller
{
    /**
     * @Route("/group-request/", name="group_request")
     */
    public function requestAction(Mailer $mailer, LoggerInterface $logger, Request $request): Response
    {
        $form = $this->createForm(GroupRequestType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em = $this->getDoctrine()->getManager();
            $groupRequest = $form->getData();
            try {
                $mailer->sendConfirmation($groupRequest);
                $em->persist($groupRequest);
                $em->flush();

                $this->addFlash('success', 'Group request submitted - check your emails.');

                return $this->redirectToRoute('home');
            } catch (MailerException|PDOException|ORMException|DBALException $exception) {
                $logger->error('Group request could not be persisted', ['exception' => $exception]);
                $this->addFlash('danger', 'Error occurred');
            }
        }

        return $this->render('group/request.html.twig', [
            'request_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/group-request/{token}", name="group_request_confirmation")
     */
    public function confirmRequestAction(GroupRequest $groupRequest): RedirectResponse
    {
        try {
            $this->get('workflow.group_request')->apply($groupRequest, 'confirm');

            $this->getDoctrine()->getManager()->flush();
            $this->addFlash('success', 'Group request was confirmed and will be reviewed. This may take a few days.');
        } catch (LogicException $exception) {
            $this->addFlash('danger', 'Could not confirm group request: '.$exception->getMessage());
        }

        return $this->redirectToRoute('home');
    }

    /**
     * @Route("/{urlname}/talk-proposal", name="group_proposal")
     */
    public function proposalAction(
        Mailer $mailer,
        LoggerInterface $logger,
        Request $request,
        GroupRequest $groupRequest
    ): Response {
        $form = $this->createForm(TalkProposalType::class);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            try {
                $mailer->sendProposal($form->getData(), $groupRequest);
                $this->addFlash('success', 'Talk proposal submitted');

                return $this->redirectToRoute('group', ['urlname' => $groupRequest->getUrlname()]);
            } catch (MailerException $exception) {
                $logger->error('Talk proposal could not be sent', ['exception' => $exception]);
                $this->addFlash('danger', 'Error occurred');
            }
        }

        return $this->render('group/proposal.html.twig', [
            'proposal_form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{urlname}", name="group")
     */
    public function showAction(Gateway $gateway, GroupRequest $groupRequest): Response
    {
        if (!$groupRequest->isApproved()) {
            throw $this->createNotFoundException(sprintf('Group "%s" not approved.', $groupRequest->getUrlname()));
        }

        try {
            $group = $gateway->getGroup($groupRequest->getUrlname());
        } catch (MeetupException $exception) {
            throw new ServiceUnavailableHttpException(60, 'Group could not be loaded', $exception);
        }

        return $this->render('group/show.html.twig', [
            'group' => $group,
        ]);
    }

    public function listAction(Gateway $gateway): Response
    {
        return $this->render('group/list.html.twig', [
            'groups' => $gateway->getGroupList()
        ]);
    }
}
