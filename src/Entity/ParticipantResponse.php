<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

/**
 * @ORM\Entity(repositoryClass="App\Repository\ParticipantResponseRepository")
 */
class ParticipantResponse
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Slot")
     * @ORM\JoinTable(name="preference_set_slot")
     */
    private $preferenceSet;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Slot")
     * @ORM\JoinTable(name="exclusion_set_slot")
     */
    private $exclusionSet;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\User", inversedBy="participantResponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Meeting", inversedBy="participantResponses")
     * @ORM\JoinColumn(nullable=false)
     */
    private $meeting;

    /**
     * @ORM\ManyToMany(targetEntity="App\Entity\Equipment")
     */
    private $equipment;

    /**
     * @ORM\ManyToOne(targetEntity="App\Entity\Location")
     */
    private $preferredLocation;

    public function __construct()
    {
        $this->preferenceSet = new ArrayCollection();
        $this->exclusionSet = new ArrayCollection();
        $this->equipment = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    /**
     * @return Collection|Slot[]
     */
    public function getPreferenceSet(): Collection
    {
        return $this->preferenceSet;
    }

    public function addPreferenceSet(Slot $preferenceSet): self
    {
        if (!$this->preferenceSet->contains($preferenceSet)) {
            $this->preferenceSet[] = $preferenceSet;
        }

        return $this;
    }

    public function removePreferenceSet(Slot $preferenceSet): self
    {
        if ($this->preferenceSet->contains($preferenceSet)) {
            $this->preferenceSet->removeElement($preferenceSet);
        }

        return $this;
    }

    /**
     * @return Collection|Slot[]
     */
    public function getExclusionSet(): Collection
    {
        return $this->exclusionSet;
    }

    public function addExclusionSet(Slot $exclusionSet): self
    {
        if (!$this->exclusionSet->contains($exclusionSet)) {
            $this->exclusionSet[] = $exclusionSet;
        }

        return $this;
    }

    public function removeExclusionSet(Slot $exclusionSet): self
    {
        if ($this->exclusionSet->contains($exclusionSet)) {
            $this->exclusionSet->removeElement($exclusionSet);
        }

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function getMeeting(): ?Meeting
    {
        return $this->meeting;
    }

    public function setMeeting(?Meeting $meeting): self
    {
        $this->meeting = $meeting;

        return $this;
    }

    /**
     * @return Collection|Equipment[]
     */
    public function getEquipment(): Collection
    {
        return $this->equipment;
    }

    public function addEquipment(Equipment $equipment): self
    {
        if (!$this->equipment->contains($equipment)) {
            $this->equipment[] = $equipment;
        }

        return $this;
    }

    public function removeEquipment(Equipment $equipment): self
    {
        if ($this->equipment->contains($equipment)) {
            $this->equipment->removeElement($equipment);
        }

        return $this;
    }

    public function getPreferredLocation(): ?Location
    {
        return $this->preferredLocation;
    }

    public function setPreferredLocation(?Location $preferredLocation): self
    {
        $this->preferredLocation = $preferredLocation;

        return $this;
    }

    public function allExcluded()
    {
        return !is_null($this->getMeeting())
            ? ($this->getMeeting()->getProposedSlots()->toArray() == $this->getExclusionSet()->toArray())
            : false;
    }

    /**
     * @Assert\Callback
     */
    public function validate(ExecutionContextInterface $context, $payload)
    {
        foreach ($this->getPreferenceSet() as $preferred) {
            if ($this->getExclusionSet()->contains($preferred)) {
                $context
                    ->buildViolation('You cannot have the same slot in your preference set and your exclusion set')
                    ->addViolation()
                ;
            }
        }
    }
}
