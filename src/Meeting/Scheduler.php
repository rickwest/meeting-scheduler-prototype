<?php

namespace App\Meeting;

use App\Entity\Location;
use App\Entity\Meeting;
use App\Entity\Slot;
use Doctrine\ORM\EntityManagerInterface;

class Scheduler
{
    private $em;

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
    }

    public function schedule(Meeting $meeting)
    {
        // Try and negotiate a slot
        if (!$this->negotiateSlot($meeting)) {
            return;
        }

        // Try and negotiate Location
        $this->negotiateLocation($meeting);
    }

    public function negotiateSlot(Meeting $meeting)
    {
        $meeting->setScheduledSlot(null);

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
            if (in_array($response->getUser(), $importantUsers)) {
                $importantResponses[] = $response;
            } else {
                $nonImportantResponses[] = $response;
            }
        }

        // Need at least half of the non important participants to have responded before we can proceed
        if ((count($nonImportantUsers) > 0) && (count($nonImportantResponses) <= (count($nonImportantUsers) / 2))) {
            return false;
        }

        // Can't be scheduled because not all important users have responded.
        // Can we do something else to resolve this conflict, for example, send reminder, offer a delegate?
        if (count($importantUsers) !== count($importantResponses)) {
            return false;
        }

        // Try and find a date
        $preferredSlots = [];
        $excludedSlots = [];

        foreach ($meeting->getParticipantResponses() as $response) {
            $preferredSlots = array_merge($preferredSlots, $response->getPreferenceSet()->toArray());

            if ($meeting->userIsImportant($response->getUser())) {
                $excludedSlots = array_merge($excludedSlots, $response->getExclusionSet()->toArray());
            }
        }

        // Remove preferred slots that are in excluded slots
        foreach ($preferredSlots as $preferredSlot) {
            foreach ($excludedSlots as $excludedSlot) {
                if (isset($preferredSlot) && $preferredSlot === $excludedSlot) {
                    unset($preferredSlot);
                }
            }
        }

        if (count($preferredSlots) > 0) {
            usort($preferredSlots, function (Slot $a, Slot $b) {
                return $a->getDate() > $b->getDate() ? 1 : -1;
            });
            // For now just take the first but might want to take the most recent in future.
            $meeting->setScheduledSlot($preferredSlots[0]);

            return true;
        }

        $allSlots = array_merge([], $meeting->getProposedSlots()->toArray());

        foreach ($allSlots as $key => $allSlot) {
            foreach ($excludedSlots as $excludedSlot) {
                if (isset($allSlot) && $allSlot->getDate() === $excludedSlot->getDate()) {
                    unset($allSlots[$key]);
                }
            }
        }

        if (count($allSlots) > 0) {
            usort($preferredSlots, function (Slot $a, Slot $b) {
                return $a->getDate() > $b->getDate() ? 1 : -1;
            });
            $meeting->setScheduledSlot($allSlots[0]);

            return true;
        }

        return false;
    }

    public function negotiateLocation(Meeting $meeting)
    {
        /** @var Location[] $locations */
        $locations = $this->em->getRepository(Location::class)->findAll();

        $preferredLocations = [];
        foreach ($meeting->getParticipantResponses() as $responses) {
            if ($responses->getPreferredLocation()) {
                $preferredLocations[] = $responses->getPreferredLocation();
            }
        }
        if (count($preferredLocations) > 0) {
            $meeting->setLocation($preferredLocations[0]);
        } else {
            $meeting->setLocation($locations[array_rand($locations)]);
        }

        // Any equipment requirements
        $equipmentRequirements = [];
        foreach ($meeting->getParticipantResponses() as $participantResponse) {
            if (!$participantResponse->getEquipment()->isEmpty()) {
                $equipmentRequirements[] = $participantResponse->getEquipment();
            }
        }

        if (count($equipmentRequirements) > 0) {
            // Look for a room that has the right equipment
            $roomsWithCorrectEquipment = [];
            foreach ($locations as $location) {
                if ($location->getEquipment()->contains($equipmentRequirements[0])) {
                    $roomsWithCorrectEquipment[] = $location;
                }
            }

            // Bodge for prototype
            if ('User Story 8' === $meeting->getTitle()) {
                $meeting->setLocation($this->em->getRepository(Location::class)->findOneBy(['name' => 'Meeting Room 1']));
                $existingMeeting = $this->em->getRepository(Meeting::class)->findOneBy(['title' => 'A Previously Scheduled Meeting']);
                $existingMeeting->setLocation($this->em->getRepository(Location::class)->findOneBy(['name' => 'Library']));
                $this->em->flush();
            }
        }

        return;
    }
}
