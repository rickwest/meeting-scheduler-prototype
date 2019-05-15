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

        $initiated = array_filter($all, function($meeting) {
            return $meeting->getInitiator() === $this->getUser();
        });

        $proposed = array_filter($all, function($meeting) {
            foreach ($meeting->getAttendees() as $assignee) {
                if ($assignee->getUser() === $this->getUser()) {
                    return true;
                }
                return false;
            }
            return $meeting->getAdd == $this->getUser();
        });

        $scheduled = array_filter($all, function($meeting) {
            return $meeting->getScheduledSlot();
        });

        return $this->render('dashboard/index.html.twig', [
            'initiated' => $initiated,
            'proposed' => $proposed,
            'scheduled' => $scheduled,
        ]);
    }
}
