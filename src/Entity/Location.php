<?php

namespace App\Entity;

use App\Repository\LocationRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: LocationRepository::class)]
class Location
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Place name can't be empty")]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(message: "Place address can't be empty")]
    private ?string $street = null;

    #[ORM\Column]
    private ?float $latitude = null;

    #[ORM\Column]
    private ?float $longitude = null;

    #[ORM\ManyToOne(inversedBy: 'locationLst')]
    #[ORM\JoinColumn(nullable: false)]
    #[Assert\NotNull(message: "Must be a valid city!")]
    private ?City $city = null;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\OneToMany(targetEntity: Hangout::class, mappedBy: 'location',orphanRemoval: true)]
    private Collection $hangoutLst;

    public function __construct()
    {
        $this->hangoutLst = new ArrayCollection();
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

    public function getStreet(): ?string
    {
        return $this->street;
    }

    public function setStreet(string $street): static
    {
        $this->street = $street;

        return $this;
    }

    public function getLatitude(): ?float
    {
        return $this->latitude;
    }

    public function setLatitude(float $latitude): static
    {
        $this->latitude = $latitude;

        return $this;
    }

    public function getLongitude(): ?float
    {
        return $this->longitude;
    }

    public function setLongitude(float $longitude): static
    {
        $this->longitude = $longitude;

        return $this;
    }

    public function getCity(): ?City
    {
        return $this->city;
    }

    public function setCity(?City $city): static
    {
        $this->city = $city;

        return $this;
    }

    /**
     * @return Collection<int, Hangout>
     */
    public function getHangoutLst(): Collection
    {
        return $this->hangoutLst;
    }

    public function addHangoutLst(Hangout $hangoutLst): static
    {
        if (!$this->hangoutLst->contains($hangoutLst)) {
            $this->hangoutLst->add($hangoutLst);
            $hangoutLst->setLocation($this);
        }

        return $this;
    }

    public function removeHangoutLst(Hangout $hangoutLst): static
    {
        if ($this->hangoutLst->removeElement($hangoutLst)) {
            // set the owning side to null (unless already changed)
            if ($hangoutLst->getLocation() === $this) {
                $hangoutLst->setLocation(null);
            }
        }

        return $this;
    }

}
