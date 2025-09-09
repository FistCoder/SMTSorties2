<?php

namespace App\Entity;

use App\Repository\HangoutRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Context\ExecutionContextInterface;

#[ORM\Entity(repositoryClass: HangoutRepository::class)]
class Hangout
{
    public const HANGOUT_PER_PAGE = 10;
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Hangout name is required")]
    #[Assert\Length(
        min: 3,
        max: 255,
        minMessage: "Hangout name must be at least {{ limit }} characters long",
        maxMessage: "Hangout name cannot be longer than {{ limit }} characters"
    )]
    #[Assert\Regex(
        pattern: '/^[a-zA-Z0-9\s\-_.,!?()]+$/',
        message: "Hangout name contains invalid characters"
    )]
    private ?string $name = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Starting date and time is required")]
    #[Assert\GreaterThan(
        value: "now",
        message: "Starting date and time must be in the future"
    )]
    private ?\DateTime $startingDateTime = null;

    #[ORM\Column(type: Types::TIME_MUTABLE)]
    #[Assert\NotNull(message: "Duration is required")]
    private ?\DateTime $length = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Last submit date is required")]
    #[Assert\LessThan(
        propertyPath: "startingDateTime",
        message: "Last submit date must be before the hangout starts"
    )]
    #[Assert\GreaterThan(
        value: "now",
        message: "Last submit date must be in the future"
    )]
    private ?\DateTime $lastSubmitDate = null;

    #[ORM\Column]
    #[Assert\NotNull(message: "Maximum number of participants is required")]
    #[Assert\Range(
        notInRangeMessage: "Maximum participants must be between {{ min }} and {{ max }}",
        min: 2,
        max: 100
    )]
    private ?int $maxParticipant = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(message: "Description is required")]
    #[Assert\Length(
        min: 10,
        max: 2000,
        minMessage: "Description must be at least {{ limit }} characters long",
        maxMessage: "Description cannot be longer than {{ limit }} characters"
    )]
    private ?string $detail = null;

    #[ORM\ManyToOne(inversedBy: 'hangoutLst')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Location is required")]
    private ?Location $location = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?State $state = null;

    #[ORM\ManyToOne(inversedBy: 'hangoutLst')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Campus $campus = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'subscribedHangoutLst')]
    private Collection $subscriberLst;

    #[ORM\ManyToOne(inversedBy: 'organizedHangoutLst')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $organizer = null;

    // You might also want to add a custom validation method for complex business rules
    #[Assert\Callback]
    public function validate(ExecutionContextInterface $context): void
    {
        // Ensure lastSubmitDate is at least 1 hour before startingDateTime
        if ($this->lastSubmitDate && $this->startingDateTime) {
            $interval = $this->startingDateTime->getTimestamp() - $this->lastSubmitDate->getTimestamp();
            if ($interval < 3600) { // Less than 1 hour
                $context->buildViolation('Last submit date must be at least 1 hour before the hangout starts')
                    ->atPath('lastSubmitDate')
                    ->addViolation();
            }
        }

        // Validate subscriber count against maxParticipant
        if ($this->maxParticipant && $this->subscriberLst->count() > $this->maxParticipant) {
            $context->buildViolation('Cannot have more subscribers than the maximum allowed participants ({{ limit }})')
                ->setParameter('{{ limit }}', (string) $this->maxParticipant)
                ->atPath('subscriberLst')
                ->addViolation();
        }

        // Validate length duration (TimeType creates DateTime objects with today's date)
        if ($this->length) {
            $hours = (int) $this->length->format('H');
            $minutes = (int) $this->length->format('i');
            $totalMinutes = $hours * 60 + $minutes;

            if ($totalMinutes < 30) {
                $context->buildViolation('Hangout must be at least 30 minutes long')
                    ->atPath('length')
                    ->addViolation();
            }

            if ($totalMinutes > 720) { // 12 hours = 720 minutes
                $context->buildViolation('Hangout cannot be longer than 12 hours')
                    ->atPath('length')
                    ->addViolation();
            }
        }
    }

    public function __construct()
    {
        $this->subscriberLst = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getStartingDateTime(): ?\DateTime
    {
        return $this->startingDateTime;
    }

    public function setStartingDateTime(\DateTime $startingDateTime): static
    {
        $this->startingDateTime = $startingDateTime;

        return $this;
    }

    public function getLength(): ?\DateTime
    {
        return $this->length;
    }

    public function setLength(\DateTime $length): static
    {
        $this->length = $length;

        return $this;
    }

    public function getLastSubmitDate(): ?\DateTime
    {
        return $this->lastSubmitDate;
    }

    public function setLastSubmitDate(\DateTime $lastSubmitDate): static
    {
        $this->lastSubmitDate = $lastSubmitDate;

        return $this;
    }

    public function getMaxParticipant(): ?int
    {
        return $this->maxParticipant;
    }

    public function setMaxParticipant(int $maxParticipant): static
    {
        $this->maxParticipant = $maxParticipant;

        return $this;
    }

    public function getDetail(): ?string
    {
        return $this->detail;
    }

    public function setDetail(string $detail): static
    {
        $this->detail = $detail;

        return $this;
    }

    public function getLocation(): ?Location
    {
        return $this->location;
    }

    public function setLocation(?Location $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): static
    {
        $this->state = $state;

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
     * @return Collection<int, User>
     */
    public function getSubscriberLst(): Collection
    {
        return $this->subscriberLst;
    }

    public function addSubscriberLst(User $subscriberLst): static
    {
        if (!$this->subscriberLst->contains($subscriberLst)) {
            $this->subscriberLst->add($subscriberLst);
        }

        return $this;
    }

    public function removeSubscriberLst(User $subscriberLst): static
    {
        $this->subscriberLst->removeElement($subscriberLst);

        return $this;
    }

    public function getOrganizer(): ?User
    {
        return $this->organizer;
    }

    public function setOrganizer(?User $organizer): static
    {
        $this->organizer = $organizer;

        return $this;
    }

}
