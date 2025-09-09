<?php

namespace App\Entity;

use App\Repository\CampusRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CampusRepository::class)]
class Campus
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    private ?string $name = null;

    /**
     * @var Collection<int, User>
     */
    #[ORM\OneToMany(targetEntity: User::class, mappedBy: 'campus', orphanRemoval: true)]
    private Collection $studentLst;

    /**
     * @var Collection<int, Hangout>
     */
    #[ORM\OneToMany(targetEntity: Hangout::class, mappedBy: 'campus', orphanRemoval: true)]
    private Collection $hangoutLst;

    public function __construct()
    {
        $this->studentLst = new ArrayCollection();
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

    /**
     * @return Collection<int, User>
     */
    public function getStudentLst(): Collection
    {
        return $this->studentLst;
    }

    public function addStudentLst(User $studentLst): static
    {
        if (!$this->studentLst->contains($studentLst)) {
            $this->studentLst->add($studentLst);
            $studentLst->setCampus($this);
        }

        return $this;
    }

    public function removeStudentLst(User $studentLst): static
    {
        if ($this->studentLst->removeElement($studentLst)) {
            // set the owning side to null (unless already changed)
            if ($studentLst->getCampus() === $this) {
                $studentLst->setCampus(null);
            }
        }

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
            $hangoutLst->setCampus($this);
        }

        return $this;
    }

    public function removeHangoutLst(Hangout $hangoutLst): static
    {
        if ($this->hangoutLst->removeElement($hangoutLst)) {
            // set the owning side to null (unless already changed)
            if ($hangoutLst->getCampus() === $this) {
                $hangoutLst->setCampus(null);
            }
        }

        return $this;
    }
}
