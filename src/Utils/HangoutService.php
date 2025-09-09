<?php

namespace App\Utils;

use App\Entity\Hangout;
use App\Repository\HangoutRepository;
use App\Repository\StateRepository;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

class HangoutService
{

    public function __construct(private readonly EntityManagerInterface $entityManager,
                                private readonly HangoutRepository $hangoutRepository,
                                private readonly StateRepository $stateRepository)
    {

    }

    public function updateState(): void
    {
        $dateNow = new DateTimeImmutable();
        $hangoutList = $this->hangoutRepository->findAll();
        $stateList = $this->stateRepository->findAll();

        foreach ($stateList as $state) {
            $states[$state->getLabel()] = $state;
        }

        foreach ($hangoutList as $hangout) {
            $dateEnd = clone $hangout->getStartingDateTime();

            $hours = (int) $hangout->getLength()->format('H');
            $minutes = (int) $hangout->getLength()->format('i');
            $totalMinutes = $hours * 60 + $minutes;

            if ($hangout->getState()->getLabel()=== 'CANCELLED'){

                if ($dateEnd->modify('+ 1 month') < $dateNow) {
                    $hangout->setState($states['ARCHIVED']);
                }

            } else {

                if ($hangout->getLastSubmitDate() < $dateNow or $hangout->getSubscriberLst()->count() >= $hangout->getMaxParticipant()) {
                    $hangout->setState($states['CLOSED']);
                }
                if ($hangout->getStartingDateTime() < $dateNow) {
                    $hangout->setState($states['IN_PROCESS']);
                }
                if ($dateEnd->modify('+' .$totalMinutes. 'minutes')< $dateNow) {
                    $hangout->setState($states['FINISHED']);
                }
                if ($dateEnd->modify('+' .$totalMinutes. 'minutes + 1 month') < $dateNow) {
                    $hangout->setState($states['ARCHIVED']);
                }
            }
            $this->entityManager->persist($hangout);

        }
        $this->entityManager->flush();

    }


}