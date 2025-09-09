<?php

namespace App\Repository;

use App\Entity\Hangout;
use App\Entity\User;
use App\Form\Models\FiltresModel;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\Tools\Pagination\Paginator;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Hangout>
 */
class HangoutRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Hangout::class);
    }

    //methode pour gerer les filters et injections des données utilisateurs et du formulaire
    public function findFilteredEvent(?User $user, FiltresModel $filters, int $page = 1)
    {
        $qb = $this->createQueryBuilder('h')
            ->leftJoin('h.campus', 'campus')
            ->addSelect('campus')
            ->leftJoin('h.organizer', 'user')
            ->addSelect('user')
            ->leftJoin('h.state', 'state')
            ->addSelect('state')
            ->addorderBy('state.id', 'ASC');


        if ($filters->getCampus()) {
            $qb->andWhere('h.campus = :campus')
                ->setParameter('campus', $filters->getCampus());
        }

        if (!empty($filters->getName() !== null)) {
            $qb->andWhere('h.name LIKE :name')
                ->setParameter('name', '%' . $filters->getName() . '%');
        }

        if (!empty($filters->getStart() !== null)) {
            $qb->andWhere('h.startingDateTime >= :start')
                ->setParameter('start', $filters->getStart());
        }

        if (!empty($filters->getEnd() !== null)) {
            $qb->andWhere('h.startingDateTime <= :end')
                ->setParameter('end', $filters->getEnd());
        }

        if (!empty($filters->getState())) {
            $qb->andWhere('h.state = :state')
                ->setParameter('state', $filters->getState());
        }

        if ($filters->isOrganizer()) {
            $qb->andWhere('h.organizer = :user')
                ->setParameter('user', $user);
        }

        if ($filters->isRegistered()) {
            $qb->leftJoin('h.subscriberLst', 'subscribers');
            $qb->andWhere('subscribers = :user')
                ->setParameter('user', $user);
        }

        if ($filters->isNotRegistered()) {
            $qb->leftJoin('h.subscriberLst', 'notSubscribers');
            $qb->andWhere('(notSubscribers IS NULL OR notSubscribers != :user) AND h.organizer != :user')
                ->setParameter('user', $user);
        }

        $now = new \DateTime();

        if ($filters->isPast()) {
            $qb->andWhere('h.startingDateTime < :now');
        } else {
            $qb->andWhere('h.startingDateTime >= :now');
        }

        $qb->setParameter('now', $now);
        $query = $qb->getQuery();

        $limit = Hangout::HANGOUT_PER_PAGE;
        $offset = ($page - 1) * $limit;

        $query->setMaxResults($limit);
        $query->setFirstResult($offset);

        return new Paginator($query);
    }


}
