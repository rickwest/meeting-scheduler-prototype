<?php

namespace App\Entity;

use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\UserInterface;

use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
/**
 * @ORM\Entity(repositoryClass="App\Repository\UserRepository")
 * @ORM\Table(name="user")
 * @UniqueEntity("email")
 * @UniqueEntity("username")
 */
class User implements UserInterface
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=25, unique=true)
     */
    private $username;

    /**
     * @ORM\Column(type="string", length=64)
     */
    private $password;

    /**
     * @ORM\Column(type="string", length=128, unique=true)
     */
    private $email;

    /**
     * @ORM\Column(type="boolean")
     */
    private $isActive;

    /**
     * The meetings that the user has initiated.
     *
     * @ORM\OneToMany(targetEntity="App\Entity\Meeting", mappedBy="initiator", orphanRemoval=true)
     */
    private $meetings;

    /**
     * @ORM\Column(type="array", nullable=true)
     */
    private $roles = [];

    /**
     * @ORM\OneToMany(targetEntity="App\Entity\ParticipantResponse", mappedBy="user")
     */
    private $participantResponses;

    public function __construct()
    {
        $this->meetings = new ArrayCollection();
        $this->participantResponses = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): self
    {
        $this->username = $username;

        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }

    public function getRoles()
    {
        $roles = $this->roles;

        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    public function getSalt()
    {
        // TODO: Implement getSalt() method.
    }

    public function eraseCredentials()
    {
        // TODO: Implement eraseCredentials() method.
    }

    /**
     * @return Collection|Meeting[]
     */
    public function getMeetings(): Collection
    {
        return $this->meetings;
    }

    public function addMeeting(Meeting $meeting): self
    {
        if (!$this->meetings->contains($meeting)) {
            $this->meetings[] = $meeting;
            $meeting->setInitiator($this);
        }

        return $this;
    }

    public function removeMeeting(Meeting $meeting): self
    {
        if ($this->meetings->contains($meeting)) {
            $this->meetings->removeElement($meeting);
            // set the owning side to null (unless already changed)
            if ($meeting->getInitiator() === $this) {
                $meeting->setInitiator(null);
            }
        }

        return $this;
    }

    public function setRoles(array $roles): self
    {
        $this->roles = $roles;

        return $this;
    }

    public function hasRole($role)
    {
        return in_array($role, $this->getRoles());
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
            $participantResponse->setUser($this);
        }

        return $this;
    }

    public function removeParticipantResponse(ParticipantResponse $participantResponse): self
    {
        if ($this->participantResponses->contains($participantResponse)) {
            $this->participantResponses->removeElement($participantResponse);
            // set the owning side to null (unless already changed)
            if ($participantResponse->getUser() === $this) {
                $participantResponse->setUser(null);
            }
        }

        return $this;
    }
}
