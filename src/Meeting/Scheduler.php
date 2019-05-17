<?php

namespace App\Meeting;

use App\Entity\Meeting;

class Scheduler
{
    public function schedule(Meeting $meeting)
    {
        // Assumption - A meeting can't be scheduled if all the important participants haven't responded.
        $importantUsers = [];
        $nonImportantUsers = [];

        foreach ($meeting->getParticipants() as $participant) {
            if ($participant->getImportant()) {
                $importantUsers[] = $participant->getUser();
            } else {
                $nonImportantUsers[] = $participant->getUser();
            }
        }

        $importantResponses = [];
        $nonImportantResponses = [];

        foreach ($meeting->getParticipantResponses() as $response) {
            if (in_array($importantUsers, $response->getUser())) {
                $importantResponses[] = $response;
            } else {
                $nonImportantResponses[] = $response;
            }
        }

        if (count($importantUsers) !== count($importantResponses)) {
            return;
        }

        return $meeting;
    }
}