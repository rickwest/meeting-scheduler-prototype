<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

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
     * @Assert\NotBlank()
     * @Assert\Length(max="190")
     *
     * @ORM\Column(type="string", length=190)
     */
    private $title;

    /**
     * @ORM\Column(type="text", nullable=true)
     */
    private $description;

    /**
     * @Assert\NotBlank()
     *
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="meetings")
     * @ORM\JoinColumn(nullable=false)
     */
    private $initiator;

    /**
     * @Assert\Count(min="1", minMessage = "You must specify at least one meeting slot")
     * @Assert\Valid()
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Slot", mappedBy="meeting", cascade={"persist", "remove"}, orphanRemoval=true)
     */
    private $proposedSlots;

    /**
     * @Assert\Valid()
     *
     * @ORM\OneToOne(targetEntity="App\Entity\Slot", cascade={"persist", "remove"})
     */
    private $scheduledSlot;

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ParticipantResponse", mappedBy="meeting", orphanRemoval=true)
     */
    private $participantResponses;

    /**
     * @Assert\Count(min="1", minMessage = "You must specify at least one meeting participant",)
     * @Assert\Valid()
     *
     * @ORM\ManyToMany(targetEntity="App\Entity\Participant", cascade={"persist", "remove"})
     */
    private $participants;

    public function __construct()
    {
        $this->proposedSlots = new ArrayCollection();
        $this->participantResponses = new ArrayCollection();
        $this->participants = new ArrayCollection();
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
     * @return Collection|Participant[]
     */
    public function getParticipants(): Collection
    {
        return $this->participants;
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

    /**
     * @return Collection|ParticipantResponse[]
     */
    public function getParticipantResponses(): Collection
    {
        return $this->participantResponses;
    }

    public function addParticipantResponse(ParticipantResponse $participantResponse): self
    {
        if (!$this->participantResponses->contains($participantResponse)) {
            $this->participantResponses[] = $participantResponse;
            $participantResponse->setMeeting($this);
        }

        return $this;
    }

    public function removeParticipantResponse(ParticipantResponse $participantResponse): self
    {
        if ($this->participantResponses->contains($participantResponse)) {
            $this->participantResponses->removeElement($participantResponse);
            // set the owning side to null (unless already changed)
            if ($participantResponse->getMeeting() === $this) {
                $participantResponse->setMeeting(null);
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

        foreach ($this->getParticipantResponses() as $participantResponse) {
            if ($participantResponse->getUser() === $user) {
                $response = $participantResponse;
            }
        }
        return $response;
    }

    public function userIsImportant($user)
    {
        return $this->getParticipants()->filter(function ($participant) use ($user) {
            return $user === $participant && $participant->isImportant();
        });
    }

    public function status()
    {
        if ($this->getScheduledSlot()) {
            return 'Scheduled';
        }

        return 'Awaiting Responses';
    }

    public function countParticipants()
    {
        return $this->getParticipants()->count();
    }

    public function countResponses()
    {
        return $this->getParticipantResponses()->count();
    }

    public function addParticipant(Participant $participant): self
    {
        if (!$this->participants->contains($participant)) {
            $this->participants[] = $participant;
        }

        return $this;
    }

    public function removeParticipant(Participant $participant): self
    {
        if ($this->participants->contains($participant)) {
            $this->participants->removeElement($participant);
        }

        return $this;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        $vals = array_map(function ($val) {
            return $val->getUser()->getUsername();
        }, $this->getParticipants()->toArray());

        $countVals = array_count_values($vals);

        foreach ($this->getParticipants() as $participant) {
            if ($participant->getUser() === $this->getInitiator()) {
                $context
                    ->buildViolation('Do you like talking to talk to yourself 🙃?')
                    ->addViolation()
                ;
            }

            if ($countVals[$participant->getUser()->getUsername()] > 1)  {
                $context
                    ->buildViolation('You cannot invite the same participant twice')
                    ->addViolation()
                ;
                return;
            }
        }
    }
}
