<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Entity\Notification;
use App\Entity\ParticipantResponse;
use App\Form\MeetingType;
use App\Form\ParticipantResponseType;
use App\Meeting\Scheduler;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/meeting")
 */
class MeetingController extends AbstractController
{
    /**
     * @Route("/new", name="meeting_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $meeting = new Meeting();
        $meeting->setInitiator($this->getUser());

        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($meeting);
            $entityManager->flush();

            $this->addFlash('success', 'Meeting initiated successfully');

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('meeting/new.html.twig', [
            'meeting' => $meeting,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}/edit", name="meeting_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Meeting $meeting, Scheduler $scheduler): Response
    {
        // If user isn't the initiator then they can't edit
        if ($this->getUser() !== $meeting->getInitiator()) {
            $this->addFlash('danger', 'You must the the initiator of the meeting in order to edit it.');

            return $this->redirectToRoute('dashboard', [
                'id' => $meeting->getId(),
            ]);
        }
        // Initiator can't edit if it's already been scheduled, only cancel and start new request

        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Try and schedule meeting
            $scheduler->schedule($meeting);

            $this->getDoctrine()->getManager()->flush();

            $this->addFlash('success', 'Meeting saved successfully.');

            return $this->redirectToRoute('meeting_edit', [
                'id' => $meeting->getId(),
            ]);
        }

        return $this->render('meeting/edit.html.twig', [
            'meeting' => $meeting,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="meeting_delete", methods={"DELETE"})
     */
    public function delete(Request $request, Meeting $meeting): Response
    {
        if ($this->isCsrfTokenValid('delete'.$meeting->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($meeting);
            $entityManager->flush();

            $this->addFlash('success', 'Meeting deleted.');
        }

        return $this->redirectToRoute('dashboard');
    }

    /**
     * @Route("/{id}/response", name="meeting_response", methods={"GET","POST"})
     */
    public function response(Request $request, Meeting $meeting, Scheduler $scheduler)
    {
        $entityManager = $this->getDoctrine()->getManager();

        $participantResponse = $meeting->getResponseForUser($this->getUser());

        if (is_null($participantResponse)) {
            $participantResponse = new ParticipantResponse();
            $participantResponse->setMeeting($meeting);
            $participantResponse->setUser($this->getUser());
            $entityManager->persist($participantResponse);
            $entityManager->flush();
        }

        $form = $this->createForm(ParticipantResponseType::class, $participantResponse, [
            'slots' => $meeting->getProposedSlots(),
            'userIsImportant' => $meeting->userIsImportant($this->getUser()),
        ]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($participantResponse->allExcluded() && $meeting->userIsImportant($participantResponse->getUser())) {
                $notification = new Notification();
                $notification->setTitle('Cannot schedule meeting!');
                $notification->setMessage($this->getUser()->getUsername().' is unable to attend your meeting <b>'.$meeting->getTitle().'</b>. To schedule this meeting you must remove '.$this->getUser()->getUsername().' as a participant or propose some additional slots.');
                $notification->setUser($meeting->getInitiator());
                $notification->setRead(false);
                $entityManager->persist($notification);
            }

            // Try and schedule meeting
            $scheduler->schedule($meeting);

            $entityManager->flush();

            $this->addFlash('success', 'Response saved successfully.');

            return $this->redirectToRoute('dashboard');
        }

        return $this->render('meeting/response.html.twig', [
            'meeting' => $meeting,
            'form' => $form->createView(),
        ]);
    }
}
