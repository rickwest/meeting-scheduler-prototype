<?php

namespace App\Controller;

use App\Entity\Meeting;
use App\Form\MeetingType;
use App\Repository\MeetingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

/**
 * @Route("/meeting")
 */
class MeetingController extends AbstractController
{
//    /**
//     * @Route("/", name="meeting_index", methods={"GET"})
//     */
//    public function index(MeetingRepository $meetingRepository): Response
//    {
//        return $this->render('meeting/index.html.twig', [
//            'meetings' => $meetingRepository->findAll(),
//        ]);
//    }

    /**
     * @Route("/new", name="meeting_new", methods={"GET","POST"})
     */
    public function new(Request $request): Response
    {
        $meeting = new Meeting();
        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $meeting->setInitiator($this->getUser());

            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->persist($meeting);
            $entityManager->flush();

            return $this->redirectToRoute('index');
        }

        return $this->render('meeting/new.html.twig', [
            'meeting' => $meeting,
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("/{id}", name="meeting_show", methods={"GET"})
     */
    public function show(Meeting $meeting): Response
    {
        return $this->render('meeting/show.html.twig', [
            'meeting' => $meeting,
        ]);
    }

    /**
     * @Route("/{id}/edit", name="meeting_edit", methods={"GET","POST"})
     */
    public function edit(Request $request, Meeting $meeting): Response
    {
        // If user isn't the initiator then they can't edit
        if ($this->getUser() !== $meeting->getInitiator()) {
            $this->addFlash('danger', 'You must the the initiator of the meeting in order to edit it.');
            return $this->redirectToRoute('meeting_show', [
                'id' => $meeting->getId(),
            ]);
        }
        // Initiator can't edit if it's already been scheduled, only cancel and start new request

        $form = $this->createForm(MeetingType::class, $meeting);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->getDoctrine()->getManager()->flush();

            return $this->redirectToRoute('meeting_index', [
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
        }

        return $this->redirectToRoute('meeting_index');
    }

    /**
     * @Route("/{id}", name="meeting_cancel", methods={"DELETE"})
     */
    public function cancel(Request $request, Meeting $meeting): Response
    {
        if ($this->isCsrfTokenValid('cancel'.$meeting->getId(), $request->request->get('_token'))) {
            $entityManager = $this->getDoctrine()->getManager();
            $entityManager->remove($meeting);
            $entityManager->flush();
        }

        return $this->redirectToRoute('meeting_index');
    }
}
