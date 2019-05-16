<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass="App\Repository\MeetingRepository")
 */
class Meeting
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=190)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="meetings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $initiator;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\Slot", mappedBy="meeting", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $proposedSlots;

    /**
     * @ORM\OneToOne(targetEntity="App\Entity\Slot", cascade={"persist", "remove"})
     */
    private $scheduledSlot;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\AttendeeResponse", mappedBy="meeting", orphanRemoval=true)
     */
    private $attendeeResponses;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Attendee", inversedBy="meetings", cascade={"persist", "remove"})
     */
    private $attendees;

    public function __construct()
    {
        $this->proposedSlots = new ArrayCollection();
        $this->attendeeResponses = new ArrayCollection();
        $this->attendees = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): self
    {
        $this->title = $title;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getInitiator(): ?User
    {
        return $this->initiator;
    }

    public function setInitiator(?User $initiator): self
    {
        $this->initiator = $initiator;

        return $this;
    }

    /**
     * @return Collection|User[]
     */
    public function getAttendees(): Collection
    {
        return $this->attendees;
    }
    
    /**
     * @return Collection|Slot[]
     */
    public function getProposedSlots(): Collection
    {
        return $this->proposedSlots;
    }

    public function addProposedSlot(Slot $proposedSlot): self
    {
        if (!$this->proposedSlots->contains($proposedSlot)) {
            $this->proposedSlots[] = $proposedSlot;
            $proposedSlot->setMeeting($this);
        }

        return $this;
    }

    public function removeProposedSlot(Slot $proposedSlot): self
    {
        if ($this->proposedSlots->contains($proposedSlot)) {
            $this->proposedSlots->removeElement($proposedSlot);
            // set the owning side to null (unless already changed)
            if ($proposedSlot->getMeeting() === $this) {
                $proposedSlot->setMeeting(null);
            }
        }

        return $this;
    }

    public function getScheduledSlot(): ?Slot
    {
        return $this->scheduledSlot;
    }

    public function setScheduledSlot(?Slot $scheduledSlot): self
    {
        $this->scheduledSlot = $scheduledSlot;

        return $this;
    }

    public function addAttendee(Attendee $attendee): self
    {
        if (!$this->attendees->contains($attendee)) {
            $this->attendees[] = $attendee;
            $attendee->setMeeting($this);
        }

        return $this;
    }

    public function removeAttendee(Attendee $attendee): self
    {
        if ($this->attendees->contains($attendee)) {
            $this->attendees->removeElement($attendee);
            // set the owning side to null (unless already changed)
            if ($attendee->getMeeting() === $this) {
                $attendee->setMeeting(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection|AttendeeResponse[]
     */
    public function getAttendeeResponses(): Collection
    {
        return $this->attendeeResponses;
    }

    public function addAttendeeResponse(AttendeeResponse $attendeeResponse): self
    {
        if (!$this->attendeeResponses->contains($attendeeResponse)) {
            $this->attendeeResponses[] = $attendeeResponse;
            $attendeeResponse->setMeeting($this);
        }

        return $this;
    }

    public function removeAttendeeResponse(AttendeeResponse $attendeeResponse): self
    {
        if ($this->attendeeResponses->contains($attendeeResponse)) {
            $this->attendeeResponses->removeElement($attendeeResponse);
            // set the owning side to null (unless already changed)
            if ($attendeeResponse->getMeeting() === $this) {
                $attendeeResponse->setMeeting(null);
            }
        }

        return $this;
    }

    public function userHasResponded($user)
    {
        if ($this->getResponseForUser($user)) {
            return true;
        };

        return false;
    }

    public function getResponseForUser($user)
    {
        $response = null;

        foreach ($this->getAttendeeResponses() as $attendeeResponse) {
            if ($attendeeResponse->getUser() === $user) {
                $response = $attendeeResponse;
            }
        }
        return $response;
    }

    public function userIsImportant($user)
    {
        return $this->getAttendees()->filter(function ($attendee) use ($user) {
            return $user === $attendee && $attendee->isImportant();
        });
    }

    public function status()
    {
        if ($this->getScheduledSlot()) {
            return 'Scheduled';
        }

        return 'Awaiting Responses';
    }

    public function countAttendees()
    {
        return $this->getAttendees()->count();
    }

    public function countResponses()
    {
        return $this->getAttendeeResponses()->count();
    }
}
