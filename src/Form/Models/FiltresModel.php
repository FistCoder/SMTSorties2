<?php

namespace App\Form\Models;

use App\Entity\Campus;
use App\Entity\State;

use Symfony\Component\Validator\Constraints as Assert;


class FiltresModel
{
    private ?Campus $campus = null;
    private ?string $name = null;
    #[Assert\LessThanOrEqual(propertyPath: "end", message: 'La date de début ne peut pas dépasser la date de fin !')]
    private ?\DateTimeInterface $start = null;

    #[Assert\GreaterThanOrEqual(propertyPath: "start", message: 'La date de fin ne peut pas être antérieure à la date de début !')]
    private ?\DateTimeInterface $end = null;

    private ?State $state = null;

    private bool $isOrganizer = false;
    private bool $isRegistered = false;
    private bool $isNotRegistered = false;
    private bool $isPast = false;


    public function getCampus(): ?Campus
    {
        return $this->campus;
    }

    public function setCampus(?Campus $campus): void
    {
        $this->campus = $campus;
    }


    public function isNotRegistered(): bool
    {
        return $this->isNotRegistered;
    }

    public function setIsNotRegistered(bool $isNotRegistered): void
    {
        $this->isNotRegistered = $isNotRegistered;
    }

    public function isOrganizer(): bool
    {
        return $this->isOrganizer;
    }

    public function setIsOrganizer(bool $isOrganizer): void
    {
        $this->isOrganizer = $isOrganizer;
    }

    public function isPast(): bool
    {
        return $this->isPast;
    }

    public function setIsPast(bool $isPast): void
    {
        $this->isPast = $isPast;
    }

    public function isRegistered(): bool
    {
        return $this->isRegistered;
    }

    public function setIsRegistered(bool $isRegistered): void
    {
        $this->isRegistered = $isRegistered;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(?string $name): void
    {
        $this->name = $name;
    }

    public function getStart(): ?\DateTimeInterface
    {
        return $this->start;
    }

    public function setStart(?\DateTimeInterface $start): void
    {
        $this->start = $start;
    }

    public function getEnd(): ?\DateTimeInterface
    {
        return $this->end;
    }

    public function setEnd(?\DateTimeInterface $end): void
    {
        $this->end = $end;
    }

    public function getState(): ?State
    {
        return $this->state;
    }

    public function setState(?State $state): void
    {
        $this->state = $state;
    }

    /**
     * @Assert\Callback
     */
    public function validateExclusiveFilters(ExecutionContextInterface $context)
    {
        $countFilled = 0;

        // Booléens
        $bools = [
            $this->isOrganizer,
            $this->isRegistered,
            $this->isNotRegistered,
            $this->isPast,
        ];

        $countFilled += count(array_filter($bools, function($v) {
            return $v === true;
        }));

        // Champs de sélection / entités
        $countFilled += $this->campus !== null ? 1 : 0;
        $countFilled += $this->state !== null ? 1 : 0;

        // Champs texte
        $countFilled += !empty(trim((string)$this->name)) ? 1 : 0;

        // Dates
        $countFilled += $this->start !== null ? 1 : 0;
        $countFilled += $this->end !== null ? 1 : 0;

        if ($countFilled > 1) {
            $context->buildViolation('Vous ne pouvez sélectionner ou remplir qu\'un seul filtre à la fois.')
                ->addViolation();
        }
    }

}