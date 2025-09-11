<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]

#[UniqueEntity(fields: ['username'], message: 'This username is already in use.')]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'lastname is required')]
    #[ORM\Column(length: 50)]
    private ?string $lastname = null;

    #[Assert\NotBlank(message: 'firstname is required')]
    #[ORM\Column(length: 50)]
    private ?string $firstname = null;

    #[Assert\NotBlank(message: 'phone number is required')]
    #[ORM\Column(length: 15, nullable: true)]
    private ?string $phone = null;

    #[Assert\NotBlank(message: 'email is required')]
    #[ORM\Column(length: 180)]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    private array $roles = [];

    /**
     * @var string|null The hashed password
     */
    #[ORM\Column]
    private ?string $password = null;


    #[ORM\Column]
    private ?bool $active = null;

    #[ORM\ManyToOne(inversedBy: 'studentLst')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\ManyToMany(targetEntity: Hangout::class, mappedBy: 'subscriberLst')]
    private Collection $subscribedHangoutLst;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\OneToMany(targetEntity: Hangout::class, mappedBy: 'organizer')]
    private Collection $organizedHangoutLst;

    #[Assert\NotBlank(message: 'username is required')]
    #[ORM\Column(length: 255)]
    private ?string $username = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $userPicture = null;


    public function __construct()
    {
        $this->subscribedHangoutLst = new ArrayCollection();
        $this->organizedHangoutLst = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLastname(): ?string
    {
        return $this->lastname;
    }

    public function setLastname(string $lastname): static
    {
        $this->lastname = $lastname;

        return $this;
    }

    public function getFirstname(): ?string
    {
        return $this->firstname;
    }

    public function setFirstname(string $firstname): static
    {
        $this->firstname = $firstname;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): static
    {
        $this->phone = $phone;

        return $this;
    }


    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

        return $this;
    }


    public function isActive(): ?bool
    {
        return $this->active;
    }

    public function setActive(bool $active): static
    {
        $this->active = $active;

        return $this;
    }

    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): static
    {
        $this->campus = $campus;

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getSubscribedHangoutLst(): Collection
    {
        return $this->subscribedHangoutLst;
    }

    public function addSubscribedHangoutLst(Hangout $subscribedHangoutLst): static
    {
        if (!$this->subscribedHangoutLst->contains($subscribedHangoutLst)) {
            $this->subscribedHangoutLst->add($subscribedHangoutLst);
            $subscribedHangoutLst->addSubscriberLst($this);
        }

        return $this;
    }

    public function removeSubscribedHangoutLst(Hangout $subscribedHangoutLst): static
    {
        if ($this->subscribedHangoutLst->removeElement($subscribedHangoutLst)) {
            $subscribedHangoutLst->removeSubscriberLst($this);
        }

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getOrganizedHangoutLst(): Collection
    {
        return $this->organizedHangoutLst;
    }

    public function addOrganizedHangoutLst(Hangout $organizedHangoutLst): static
    {
        if (!$this->organizedHangoutLst->contains($organizedHangoutLst)) {
            $this->organizedHangoutLst->add($organizedHangoutLst);
            $organizedHangoutLst->setOrganizer($this);
        }

        return $this;
    }

    public function removeOrganizedHangoutLst(Hangout $organizedHangoutLst): static
    {
        if ($this->organizedHangoutLst->removeElement($organizedHangoutLst)) {
            // set the owning side to null (unless already changed)
            if ($organizedHangoutLst->getOrganizer() === $this) {
                $organizedHangoutLst->setOrganizer(null);
            }
        }

        return $this;
    }

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    #[\Deprecated]
    public function eraseCredentials(): void
    {
        // @deprecated, to be removed when upgrading to Symfony 8
    }

    public function getUsername(): ?string
    {
        return $this->username;
    }

    public function setUsername(string $username): static
    {
        $this->username = $username;

        return $this;
    }

    public function getUserPicture(): ?string
    {
        return $this->userPicture;
    }

    public function setUserPicture(?string $userPicture): static
    {
        $this->userPicture = $userPicture;

        return $this;
    }
}
