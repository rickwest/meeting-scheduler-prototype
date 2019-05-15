<?php

namespace App\Controller;

use App\Repository\MeetingRepository;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;

class AppController extends AbstractController
{
    /**
     * @Route("/", name="index")
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

        return $this->render('app/index.html.twig', [
            'initiated' => $initiated,
            'proposed' => $proposed,
            'scheduled' => $scheduled,
        ]);
    }

    public function impersonateUsers(UserRepository $users)
    {
        $usernames = array_map(function($user) {
            return $user->getUsername();
        }, $users->findAll());

        return $this->render('app/partials/impersonateUsers.html.twig', [
            'usernames' => $usernames,
        ]);
    }
}
