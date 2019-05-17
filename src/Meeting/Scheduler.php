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

        // Need at least half of the non important participants to have responded before we can proceed
        if (count($nonImportantResponses) < (count($nonImportantUsers) / 2)) {
            return;
        }

        // Can't be scheduled because not all important users have responded.
        // Can we do something else to resolve this conflict, for example, send reminder, offer a delegate?
        if (count($importantUsers) !== count($importantResponses)) {
            return;
        }

        // Try and find a date

        $preferredSlots = [];
        $excludedSlots = [];

        foreach ($meeting->getParticipantResponses() as $response) {
            $preferredSlots = array_merge($preferredSlots, $response->getPreferenceSet());
            $excludedSlots = array_merge($excludedSlots, $response->getExclusionSet());
        }

        // Neautral slots slpots
        // Remove all the excluded slots

        // Look across all preference sets and look for the most popular common date
        return $meeting;
    }
}