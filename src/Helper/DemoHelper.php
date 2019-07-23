<?php

namespace App\Helper;

use App\Entity\Location;
use App\Entity\Meeting;
use App\Entity\Participant;
use App\Entity\ParticipantResponse;
use App\Entity\Slot;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;

class DemoHelper
{
    /** @var EntityManagerInterface */
    private $em;

    /** @var User[] */
    private $users = [];

    private $locations = [];

    public function __construct(EntityManagerInterface $em)
    {
        $this->em = $em;
        $this->usersByUsername();
        $this->locationsByName();
    }

    public function usersByUsername()
    {
        $users = $this->em->getRepository(User::class)->findAll();

        /** @var User $user */
        foreach ($users as $user) {
            $this->users[$user->getUsername()] = $user;
        }
    }

    public function locationsByName()
    {
        $locations = $this->em->getRepository(Location::class)->findAll();

        /** @var Location $location */
        foreach ($locations as $location) {
            $this->locations[$location->getName()] = $location;
        }
    }

    public function createDemoSystem()
    {
        // User Story 3 - Cannot exclude and prefer same slot. Steve has been asked to attend a meeting proposed by Matt.
        $meeting = new Meeting();
        $meeting->setTitle('User Story 3');
        $meeting->setDescription('A meeting for the user story 3 acceptance test.');
        $meeting->setInitiator($this->users['Matt']);

        $meeting->addProposedSlot($this->slot('2019-09-03 10:00:00'));
        $meeting->addParticipant($this->participant($this->users['Steve'], true));

        $this->em->persist($meeting);
        $this->em->flush();

        // User Story 4 - Important meeting member excludes all dates, Sends initiator a notification saying can't proceed
        $meeting = new Meeting();
        $meeting->setTitle('User Story 4');
        $meeting->setDescription('A meeting for the user story 4 acceptance test.');
        $meeting->setInitiator($this->users['James']);

        $slots = ['2019-09-03 10:00:00', '2019-09-03 14:30:00', '2019-09-04 08:15:00'];
        foreach ($slots as $slot) {
            $meeting->addProposedSlot($this->slot($slot));
        }

        $meeting->addParticipant($this->participant($this->users['Nick'], true));

        $notImportantUsers = [$this->users['Josh'], $this->users['Brian']];
        foreach ($notImportantUsers as $user) {
            $meeting->addParticipant($this->participant($user, false));
            $participantResponse = new ParticipantResponse();
            $participantResponse->setMeeting($meeting);
            $participantResponse->setUser($user);
            $this->em->persist($participantResponse);
        }

        $this->em->persist($meeting);
        $this->em->flush();

        // User Story 5 - When more than one common slot is found, soonest slot is taken and meeting scheduled.
        $meeting = new Meeting();
        $meeting->setTitle('User Story 5');
        $meeting->setDescription('A meeting for the user story 5 acceptance test.');
        $meeting->setInitiator($this->users['John']);

        $slots = ['2019-10-10 10:00:00', '2019-10-09 11:45:00', '2019-09-04 16:15:00'];
        foreach ($slots as $slot) {
            $meeting->addProposedSlot($this->slot($slot));
        }

        $meeting->addParticipant($this->participant($this->users['Alex'], false));
        $meeting->addParticipant($this->participant($this->users['Matt'], false));
        $participantResponse = new ParticipantResponse();
        $participantResponse->setMeeting($meeting);
        $participantResponse->setUser($this->users['Matt']);
        $participantResponse->addPreferenceSet($meeting->getProposedSlots()[1]);
        $participantResponse->addPreferenceSet($meeting->getProposedSlots()[2]);

        $this->em->persist($participantResponse);
        $this->em->persist($meeting);
        $this->em->flush();

        // User Story 6 - If all important members of a meeting can attend and at least half of the none important meeting then schedule.
        $meeting = new Meeting();
        $meeting->setTitle('User Story 6');
        $meeting->setDescription('A meeting for the user story 6 acceptance test.');
        $meeting->setInitiator($this->users['Brian']);

        $meeting->addProposedSlot($this->slot('2019-09-09 09:00:00'));

        $meeting->addParticipant($this->participant($this->users['Alex'], false));
        $meeting->addParticipant($this->participant($this->users['James'], true));
        $meeting->addParticipant($this->participant($this->users['Josh'], false));

        $meeting->addParticipant($this->participant($this->users['Matt'], true));
        $participantResponse = new ParticipantResponse();
        $participantResponse->setMeeting($meeting);
        $participantResponse->setUser($this->users['Matt']);
        $this->em->persist($participantResponse);

        $meeting->addParticipant($this->participant($this->users['Steve'], false));
        $participantResponse = new ParticipantResponse();
        $participantResponse->setMeeting($meeting);
        $participantResponse->setUser($this->users['Steve']);
        $this->em->persist($participantResponse);

        $this->em->persist($participantResponse);
        $this->em->persist($meeting);
        $this->em->flush();

        // User Story 7 - When a meeting can me scheduled, most popular location is preferred and meeting scheduled.
        $meeting = new Meeting();
        $meeting->setTitle('User Story 7');
        $meeting->setDescription('A meeting for the user story 7 acceptance test.');
        $meeting->setInitiator($this->users['Josh']);

        $meeting->addProposedSlot($this->slot('2019-11-11 10:30:00'));

        $meeting->addParticipant($this->participant($this->users['Alex'], true));
        $meeting->addParticipant($this->participant($this->users['Nick'], false));

        $participantResponse = new ParticipantResponse();
        $participantResponse->setMeeting($meeting);
        $participantResponse->setUser($this->users['Nick']);
        $this->em->persist($participantResponse);

        $this->em->persist($participantResponse);
        $this->em->persist($meeting);
        $this->em->flush();
    }

    public function participant(User $user, $important)
    {
        return (new Participant())
            ->setUser($user)
            ->setImportant($important);
    }

    public function slot($date)
    {
        return (new Slot())->setDate(new \DateTime($date));
    }
}
