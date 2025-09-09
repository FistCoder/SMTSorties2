<?php

namespace App\Controller;

use App\Entity\Location;
use App\Form\PlaceType;
use App\Repository\LocationRepository;
use App\Utils\APICallsService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/places', name: 'places_')]
final class PlaceController extends AbstractController
{


    public function __construct(
        private readonly LocationRepository $locationRepository,
        private readonly APICallsService    $apicallsService,
        private readonly EntityManagerInterface $entityManager
    )
    {
    }

    #[Route('/', name: 'list')]
    public function list(Request $request): Response
    {
        $places = $this->locationRepository->findAll();
        $form = $this->createForm(PlaceType::class);

        $form->handleRequest($request);

        return $this->render('places/list.html.twig',
        ['places' => $places, 'formPlace' => $form]);
    }

    #[Route('/add', name: 'add')]
    public function add(Request $request): Response
    {
        $place = new Location();
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);


        if ($form->isSubmitted() && $form->isValid()) {
            $coords = $this->apicallsService->getCoordsFromPlace($place);
            $place->setLatitude($coords['latitude']);
            $place->setLongitude($coords['longitude']);

            $this->entityManager->persist($place);
            $this->entityManager->flush();

            return $this->redirectToRoute('hangout_add');
        }

        return $this->render('places/add.html.twig',
        ['formPlace' => $form]);
    }

    #[Route('/modify/{id}', name: 'modify', requirements: ['id'=>'\d+'])]
    public function modify(int $id, Request $request): Response
    {
        $place = $this->locationRepository->find($id);

        if (!$place) {
            throw $this->createNotFoundException('Place not found');
        }
        $form = $this->createForm(PlaceType::class, $place);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $coords = $this->apicallsService->getCoordsFromPlace($place);
            $place->setLatitude($coords['latitude']);
            $place->setLongitude($coords['longitude']);
            $this->entityManager->persist($place);
            $this->entityManager->flush();

            return $this->redirectToRoute('places_list');
        }
        return $this->render('places/modify.html.twig', [
            'formPlace' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'delete', requirements: ['id'=>'\d+'])]
    public function delete(int $id): Response
    {
        $place = $this->locationRepository->find($id);

        if (!$place) {
            $this->addFlash('danger', 'Le lieu n\'existe pas');
            throw $this->createNotFoundException('Le lieu n\'existe pas');
        }
        $this->entityManager->remove($place);
        $this->entityManager->flush();

            return $this->redirectToRoute('places_list');
    }
}
