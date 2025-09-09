<?php

namespace App\Controller;

use App\Entity\Hangout;
use App\Entity\Location;
use App\Entity\State;
use App\Entity\User;
use App\Form\FilterHangoutType;
use App\Form\HangoutType;
use App\Form\Models\FiltresModel;
use App\Form\PlaceType;
use App\Repository\HangoutRepository;
use App\Repository\StateRepository;
use App\Repository\UserRepository;
use App\Utils\HangoutService;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use function PHPUnit\Framework\throwException;



#[Route('/hangouts', name: 'hangout_')]
final class HangoutController extends AbstractController
{

    public function __construct(
        private readonly StateRepository        $stateRepository,
        private readonly HangoutRepository      $hangoutRepository,
        private readonly EntityManagerInterface $entityManager,
        private readonly ValidatorInterface     $validator,
    )
    {
    }


    #[Route('/', name: 'list')]
    public function listHangouts(Request $request): Response
    {

        /**
         * @var User $user
         */
        $user = $this->getUser();

        $filtersModel = new FiltresModel();//permet de mapper les données directement atravers le model

        if (!$user) {
            // Gère le cas utilisateur non connecté (redirige, exception, etc.)
            throw $this->createAccessDeniedException('Vous devez être connecté');
        }


//creation du form - et je lui passe le model
        $filterForm = $this->createForm(FilterHangoutType::class, $filtersModel);
        $filterForm->handleRequest($request);


//recuperation des donées du formulaire de filtres remplis et ajout de ces données dans le tableau de filtre qui seras envoyer au repository
        $hangouts = [];

        //if ($filterForm->isSubmitted() && $filterForm->isValid()) {
            //$filters = $filterForm->getData();

            $hangouts = $this->hangoutRepository->findFilteredEvent($user, $filtersModel);
//            return $this->render('hangout/list.html.twig', [
//                'hangouts' => $hangouts,
//                'filterForm' => $filterForm
//            ]);
//        } else {
//            // Par défaut (pas de filtre), recupère tout ou selon ta logique
//            $hangouts = $this->hangoutRepository->findFilteredEvent($user, new FiltresModel());
//        }


        return $this->render('hangout/list.html.twig', [
            'hangouts' => $hangouts,
            'filterForm' => $filterForm
        ]);
    }


    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function detailHangout(int $id): Response
    {
        $hangout = $this->hangoutRepository->find($id);

        if (!$hangout) {
            throw $this->createNotFoundException("La sortie n'existe pas.");
        }

        return $this->render('hangout/detail.html.twig', [
            'hangout' => $hangout
        ]);
    }

    #[Route('/add', name: 'add')]
    public function addHangout(Request $request): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();


        $hangout = new Hangout();
        $place = new Location();
        $form = $this->createForm(HangoutType::class, $hangout);

        $formPlace = $this->createForm(PlaceType::class, $place, [
            'action' => $this->generateUrl('places_add')
        ]);

        $form->handleRequest($request);
        $formPlace->handleRequest($request);

        $request->query->get('cancelMotif', 'not_existing');

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('save')->isClicked()) {
                $hangout->setState($this->stateRepository->findOneBy(['label' => 'CREATE']));
            } elseif ($form->get('publish')->isClicked()) {
                $hangout->setState($this->stateRepository->findOneBy(['label' => 'OPEN']));
            }
            $hangout->setCampus($user->getCampus());
            $hangout->setOrganizer($user);
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
            $this->addFlash("success", "Sortie " . $hangout->getName() . " ajoutée");

            return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
        }

        return $this->render('hangout/add.html.twig', [
            'formHangout' => $form,
            'formPlace' => $formPlace,
        ]);
    }

    #[IsGranted('POST_MODIFY', 'hangout')]
    #[Route('/modify/{id}', name: 'modify', requirements: ['id' => '\d+'])]
    public function modifyHangout(Request $request, Hangout $hangout): Response
    {

        $form = $this->createForm(HangoutType::class, $hangout);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            if ($form->get('delete')->isClicked()) {
                $this->entityManager->remove($hangout);
                $this->entityManager->flush();
                $this->addFlash('success', "Sortie ".$hangout->getName()." supprimée avec success");
                return $this->redirectToRoute('hangout_list', ['id' => $hangout->getId()]);
            } elseif ($form->get('save')->isClicked()) {
                $this->entityManager->persist($hangout);
                $this->entityManager->flush();

                $this->addFlash("success", "Sortie mise a jours !");
                return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
            }

        }
        return $this->render('hangout/modify.html.twig', [
            'formUpdate' => $form,
            'hangout' => $hangout,
        ]);
    }

    #[isGranted('POST_PUBLISH', 'hangout')]
    #[Route('/publish/{id}', name: 'publish', requirements: ['id' => '\d+'])]

    public function publishHangout(Request $request, Hangout $hangout, StateRepository $stateRepository): Response
    {
        $hangout = $this->hangoutRepository->find($hangout->getId());
        $state = $stateRepository->findOneBy(['label' => 'OPEN']);

        if (!$hangout) {
            throw $this->createNotFoundException("La sortie n'existe pas.");
        }
        if ($hangout->getState()->getLabel()==="CREATE") {
            $hangout->setState($state);
        }

        $violations = $this->validator->validate($hangout);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', $violation->getMessage());
            }
        } else {
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
            $this->addFlash('success', "la sortie ".$hangout->getName()." a été publiée");
            return $this->redirectToRoute('hangout_list');
        }
        return $this->render('hangout/detail.html.twig');
    }

//    #[IsGranted('POST_DELETE', 'hangout')]//c'est les acces grace au voter ca marche pour le bouton de edition
//    #[Route('/delete/{id}', name: 'delete', requirements: ['id' => '\d+'])]
//    public function deleteHangout(int $id): Response
//    {
//        $hangout = $this->hangoutRepository->find($id);
//        if (!$hangout) {
//            throw $this->createNotFoundException("La sortie n'existe pas.");
//        }
//
//        $this->entityManager->remove($hangout);
//        $this->entityManager->flush();
//
//        $this->addFlash('sucess', 'Votre Sortie a bien été suprimmée');
//        return $this->redirectToRoute('hangout_list');
//    }

    #[ISGranted('POST_CANCEL', 'hangout')]
    #[Route('/cancel/{id}', name: 'cancel', requirements: ['id' => '\d+'])]
    public function cancelHangout(
        int $id,
        Request $request,
        Hangout $hangout,
        EntityManagerInterface $entityManager,
        HangoutRepository $hangoutRepository,
        StateRepository $stateRepository
    ): Response
    {
        $hangout = $hangoutRepository->find($id);
        $state = $stateRepository->findOneBy(['label' => 'CANCELLED']);
        $dateNow = new DateTimeImmutable();

        if (!$hangout) {
            throw $this->createNotFoundException("Hangout not found");
        }
        if($request->isMethod('POST')) {

            if ($hangout->getStartingDateTime() < $dateNow) {
                $this->addFlash('', "la sortie " . $hangout->getName() . " a déjà commencé, elle ne peut pas être annulée");
                return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
            } else {
            $cancelMotif = $request->request->get('cancelMotif', null);
            $hangoutDetail = $hangout->getDetail();
            $hangout->setDetail($hangoutDetail . '. Annulé : ' . $cancelMotif);
            $hangout->setState($state);
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
            $this->addFlash('success', "Sortie " . $hangout->getName() . " cancelled");

            return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
            }
        }

        return $this->render('hangout/cancel.html.twig', [
            'hangout'=> $hangout
        ]);

    }

    #[isGranted('POST_SUBSCRIBER', 'hangout')]
    #[Route('/subscribe/{id}', name: 'subscribe', requirements: ['id' => '\d+'])]
    public function subscribeToHangout(int $id, Hangout $hangout): Response
    {
        $hangout = $this->hangoutRepository->find($id);
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$hangout) {
            throw $this->createNotFoundException("La sortie n'existe pas.");
        }

        if ($hangout->getSubscriberLst()->contains($user)) {
            $this->addFlash('danger', $user->getFirstname() . " is already subscribed to this hangout. That's you");
            return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
        }
        if ($hangout->getState()->getLabel() == "OPEN") {
            $this->addFlash('success', "Vous êtes inscrit à la sortie " . $hangout->getName());
            $hangout->addSubscriberLst($user);
        }

        if ($hangout->getSubscriberLst()->count() == $hangout->getMaxParticipant()) {
            $hangout->setState($this->stateRepository->findOneBy(['label' => 'CLOSED']));
        }

        $violations = $this->validator->validate($hangout);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', $violation->getMessage());
            }
        } else {
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
        }

        return $this->redirectToRoute('hangout_detail', ['id' => $hangout->getId()]);
    }

    #[IsGranted('POST_UNSUBSCRIBER', 'hangout')]
    #[Route('/unsubscribe/{id}', name: 'unsubscribe', requirements: ['id' => '\d+'])]
    public function unsubscribeFromHangout(
        int $id,
        Request $request,
        Hangout $hangout): Response
    {
        /**
         * @var User $user
         */
        $user = $this->getUser();
        $hangout = $this->hangoutRepository->find($id);
        if (!$hangout) {
            throw $this->createNotFoundException("La sortie n'existe pas.");
        }

        if ($hangout->getSubscriberLst()->contains($user)) {
            $hangout->removeSubscriberLst($user);
        }

        if ($hangout->getSubscriberLst()->count() != $hangout->getMaxParticipant()) {
            $hangout->setState($this->stateRepository->findOneBy(['label' => 'OPEN']));
        }

        $violations = $this->validator->validate($hangout);
        if (count($violations) > 0) {
            foreach ($violations as $violation) {
                $this->addFlash('danger', $violation->getMessage());
            }
        } else {
            $this->addFlash('success', "Désistement avec success.");
            $this->entityManager->persist($hangout);
            $this->entityManager->flush();
        }

        $referer = $request->headers->get('referer');

        // Validate referer: must be a proper URL and same host
        if ($referer && filter_var($referer, FILTER_VALIDATE_URL)) {
            return new RedirectResponse($referer);
        }

        // I'd rather use an event listener that saves the last page in session,
        // but im pretty sure there is a better solution,
        // so it's going to stay like this for a while.
        // Right now it relies on the $referer, which may or may not exist
        return $this->redirectToRoute('hangout_list');

    }

}
