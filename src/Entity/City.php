<?php

namespace App\Entity;

use App\Repository\CityRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CityRepository::class)]
class City
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    #[ORM\Column]
    private ?int $postalCode = null;

    /**
     * @var Collection<int, Location>
     */
    #[ORM\OneToMany(targetEntity: Location::class, mappedBy: 'city', orphanRemoval: true)]
    private Collection $locationLst;


    public function __construct()
    {
        $this->locationLst = new ArrayCollection();
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

    public function getPostalCode(): ?int
    {
        return $this->postalCode;
    }

    public function setPostalCode(int $postalCode): static
    {
        $this->postalCode = $postalCode;

        return $this;
    }

    /**
     * @return Collection<int, Location>
     */
    public function getLocationLst(): Collection
    {
        return $this->locationLst;
    }

    public function addLocationLst(Location $locationLst): static
    {
        if (!$this->locationLst->contains($locationLst)) {
            $this->locationLst->add($locationLst);
            $locationLst->setCity($this);
        }

        return $this;
    }

    public function removeLocationLst(Location $locationLst): static
    {
        if ($this->locationLst->removeElement($locationLst)) {
            // set the owning side to null (unless already changed)
            if ($locationLst->getCity() === $this) {
                $locationLst->setCity(null);
            }
        }

        return $this;
    }

}
