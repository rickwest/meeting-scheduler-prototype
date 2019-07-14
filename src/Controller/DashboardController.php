<?php

namespace App\Controller;

use App\Repository\MeetingRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class DashboardController extends AbstractController
{
    /**
     * @Route("/dashboard", name="dashboard")
     */
    public function index(MeetingRepository $meetings)
    {
        $all = $meetings->findAll();

        $proposed = [];
        foreach ($all as $meeting) {
            foreach ($meeting->getParticipants() as $participant) {
                if ($participant->getUser() === $this->getUser()) {
                    $proposed[] = $meeting;
                }
            }
        }

        return $this->render('dashboard/index.html.twig', [
            'initiated' => array_filter($all, function ($meeting) {
                return $meeting->getInitiator() === $this->getUser();
            }),
            'scheduled' => array_filter($all, function ($meeting) {
                return $meeting->getScheduledSlot();
            }),
            'proposed' => $proposed,
        ]);
    }
}
