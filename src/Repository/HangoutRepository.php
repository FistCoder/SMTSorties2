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

    /**
     * Recherche les sorties filtrées selon les critères donnés,
     * en limitant les résultats à ceux avec un statut dans la liste autorisée
     * et dont la date de début est aujourd'hui ou dans le futur.
     *
     * Optimisé pour charger en une seule requête les relations importantes (campus, organisateur, état, abonnés)
     * et éviter le problème N+1 lié au lazy loading.
     *
     * @param User|null $user     L'utilisateur courant, utilisé pour certains filtres liés à l'inscription ou à l'organisation
     * @param FiltresModel $filters  Modèle contenant les critères de filtre (campus, nom, date, état, inscription, etc.)
     * @param int $page          Numéro de la page pour la pagination, par défaut 1
     *
     * @return Paginator         Résultat paginé des sorties filtrées
     */
    public function findFilteredEvent(?User $user, FiltresModel $filters, int $page = 1): Paginator
    {
        $qb = $this->createQueryBuilder('h')
            // Jointure avec la table Campus pour récupération groupe Campus
            ->leftJoin('h.campus', 'campus')
            ->addSelect('campus')

            // Jointure avec l'organisateur (utilisateur) de la sortie
            ->leftJoin('h.organizer', 'user')
            ->addSelect('user')

            // Jointure avec l’État (statut) de la sortie
            ->leftJoin('h.state', 'state')
            ->addSelect('state')

            // Jointure avec la liste des abonnés (users inscrits)
            ->leftJoin('h.subscriberLst', 'subscribers')
            ->addSelect('subscribers')

            // Tri par id d'état (optionnel, peut aider pour affichage)
            ->addOrderBy('h.startingDateTime', 'ASC');

        // Filtrage fixe : ne récupérer que les sorties dont l'état fait partie des 4 autorisés
        $allowedStatuses = ['open', 'create', 'closed', 'in process', 'cancelled', 'finished'];
        $qb->andWhere('state.label IN (:allowedStatuses)')
            ->setParameter('allowedStatuses', $allowedStatuses);

        // Filtrage selon le campus si précisé dans les filtres
        if ($filters->getCampus()) {
            $qb->andWhere('h.campus = :campus')
                ->setParameter('campus', $filters->getCampus());
        }

        // Filtre sur le nom, recherche avec LIKE partiel
        if ($filters->getName()) {
            $qb->andWhere('h.name LIKE :name')
                ->setParameter('name', '%' . $filters->getName() . '%');
        }

        // Filtrer les sorties démarrant après ou à partir d'une date (optionnel)
        if ($filters->getStart()) {
            $qb->andWhere('h.startingDateTime >= :start')
                ->setParameter('start', $filters->getStart());
        }

        // Filtrer les sorties démarrant avant une date (optionnel)
        if ($filters->getEnd()) {
            $qb->andWhere('h.startingDateTime <= :end')
                ->setParameter('end', $filters->getEnd());
        }

        // Filtre supplémentaire sur l'état (rare si présent car on filtre déjà sur la liste)
        if ($filters->getState()) {
            $qb->andWhere('h.state = :state')
                ->setParameter('state', $filters->getState());
        }

        // Filtre pour ne garder que les sorties dont l'utilisateur est organisateur si demandé
        if ($filters->isOrganizer() && $user) {
            $qb->andWhere('h.organizer = :user')
                ->setParameter('user', $user);
        }

        // Filtre sur inscription ou non-inscription de l'utilisateur aux sorties
        if (($filters->isRegistered() || $filters->isNotRegistered()) && $user) {
            // La jointure subscribers existe déjà, on filtre dessus
            if ($filters->isRegistered()) {
                // Sorties où l'utilisateur est inscrit
                $qb->andWhere('subscribers = :user')
                    ->setParameter('user', $user);
            }

            if ($filters->isNotRegistered()) {
                // Sorties où l'utilisateur n'est pas inscrit et n'est pas l'organisateur
                $qb->andWhere('(subscribers IS NULL OR subscribers != :user) AND h.organizer != :user')
                    ->setParameter('user', $user);
            }
        }

//        // Filtre obligatoire pour n'avoir que les sorties à venir (date de début >= aujourd'hui)
//        $now = new \DateTime();
//        $qb->andWhere('h.startingDateTime >= :now')
//            ->setParameter('now', $now);

        // Pagination : nombre par page et offset
        $limit = Hangout::HANGOUT_PER_PAGE ?? 10;
        $offset = ($page - 1) * $limit;

        $query = $qb->getQuery()
            ->setFirstResult($offset)
            ->setMaxResults($limit);

        // Retourne un Paginator avec gestion correcte des doublons (deuxième param à true)
        return new Paginator($query, true);
    }
}
