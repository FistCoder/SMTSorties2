<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\City;
use App\Entity\User;
use App\Form\CityType;
use App\Form\UserType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;

#[Route('/admin', name: 'admin_')]
final class AdminController extends AbstractController

{



    public function __construct(
        private readonly EntityManagerInterface $entityManager,
        private readonly UserPasswordHasherInterface $userPasswordHasher
    )
    {
    }

    #[Route('/', name: 'dashboard')]
    public function dashboard(): Response
    {
        $cities = $this->entityManager->getRepository(City::class)->findAll();
        $campuses = $this->entityManager->getRepository(Campus::class)->findAll();
        $users = $this->entityManager->getRepository(User::class)->findAll();

        return $this->render('admin/dashboard.html.twig', [
            'citiesCount' => count($cities),
            'campusesCount' => count($campuses),
            'usersCount' => count($users),
        ]);
    }

    #[Route('/cities', name: 'cities_list')]
    public function citiesLst(): Response
    {
        $cities = $this->entityManager->getRepository(City::class)->findAll();
        return $this->render('/admin/cities/list.html.twig', [
            'cities' => $cities,
            'citiesCount' => count($cities),
        ]);
    }

    #[Route('/cities/add', name: 'cities_add')]
    public function addCity(Request $request): Response
    {
        $city = new City();
        $formCity = $this->createForm(CityType::class, $city);
        $formCity->handleRequest($request);

        if ($formCity->isSubmitted() && $formCity->isValid()) {
            $this->entityManager->persist($city);
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_cities_list');
        }

        return $this->render('/admin/cities/add.html.twig', [
            'formCity' => $formCity,
        ]);
    }

    #[Route('/cities/delete/{id}', name: 'cities_delete', requirements: ['id' => '\d+'])]
    public function deleteCity(int $id): Response
    {
        $city = $this->entityManager->getRepository(City::class)->find($id);
        if (!$city) {
            throw $this->createNotFoundException();
        }
        $this->entityManager->remove($city);
        $this->entityManager->flush();
        return $this->redirectToRoute('admin_cities_list');
    }

    #[Route('/cities/modify/{id}', name: 'cities_modify', requirements: ['id' => '\d+'])]
    public function modifyCities(int $id, Request $request): Response
    {
        $city = $this->entityManager->getRepository(City::class)->find($id);
        if (!$city) {
            throw $this->createNotFoundException();
        }
        $formCity = $this->createForm(CityType::class, $city);
        $formCity->handleRequest($request);

        if ($formCity->isSubmitted() && $formCity->isValid()) {
            $this->entityManager->persist($city);
            $this->entityManager->flush();
            return $this->redirectToRoute('admin_cities_list');
        }

        return $this->render('/admin/cities/modify.html.twig', [
            'formCity' => $formCity,
        ]);
    }


    #[Route('/campus', name: 'campus_list')]
    public function campusLst(): Response
    {
        return $this->render('/admin/campus/list.html.twig', [

        ]);
    }

    #[Route('/campus/add', name: 'campus_add')]
    public function addCampus(): Response
    {
        return $this->render('/admin/campus/add.html.twig', [

        ]);
    }

//    #[Route('/campus/delete/{id}', name: 'campus_delete', requirements: ['id' => '\d+'])]
//    public function deleteCampus(int $id): Response
//    {
//
//    }

    #[Route('/campus/modify/{id}', name: 'campus_modify', requirements: ['id' => '\d+'])]
    public function modifyCampus(int $id): Response
    {
        return $this->render('/admin/campus/modify.html.twig', []);
    }


    #[Route('/users', name: 'users_list')]
    public function usersLst(): Response
    {
        $users = $this->entityManager->getRepository(User::class)->findAll();
        return $this->render('/admin/users/list.html.twig', [
            'users' => $users,
            'usersCount' => count($users),
        ]);
    }

    #[Route('/users/add', name: 'users_add')]
    public function addUsers(Request $request): Response
    {
        $user = new User();
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);
        if ($userForm->isSubmitted() && $userForm->isValid()) {
            $user->setActive('false');
            $this->entityManager->persist($user);
            $this->entityManager->flush();
        }

        if ($request->files->get('csvFile')) {
            $campus = $this->entityManager->getRepository(Campus::class)->findOneBy(['name'=>'Delahayenec_Campus']);
            $file = $request->files->get('csvFile');
            //Parse the file
            /**
             * @var UploadedFile $file
             */
            if (($handle = fopen($file->getRealPath(), 'r')) !== false) {

                $i = 0;
                while (($row = fgetcsv($handle, 1000, ';')) !== false) {
                    if ($i != 0) {
                        $user = new User();
                        $user->setUsername($row[0]);
                        $user->setLastName($row[1]);
                        $user->setFirstName($row[2]);
                        $user->setEmail($row[3]);
                        $user->setPhone($row[4]);
                        $user->setActive('false');
                        $user->setCampus($campus);
                        $user->setPassword($this->userPasswordHasher->hashPassword($user, "123456789"));
                        $this->entityManager->persist($user);
                    }
                    $i++;
                }
                $this->entityManager->flush();
            }

        }
        return $this->render('admin/users/add.html.twig', [
            'userForm' => $userForm
        ]);
    }

    #[Route('/users/delete/{id}', name: 'users_delete', requirements: ['id' => '\d+'])]
    public function deleteUsers(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException();
        }
        $this->entityManager->remove($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/users/modify/{id}', name: 'users_modify', requirements: ['id' => '\d+'])]
    public function modifyUsers(int $id): Response
    {
        return $this->render('/admin/users/modify.html.twig', []);
    }

    #[Route('/users/activate/{id}', name: 'users_activate', requirements: ['id' => '\d+'])]
    public function activateUsers(int $id): Response
    {
        /**
         * @var User $user
         */
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        $user->setActive(true);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
       return $this->redirectToRoute('admin_users_list');
    }

    #[Route('/users/deactivate/{id}', name: 'users_deactivate', requirements: ['id' => '\d+'])]
    public function deactivateUsers(int $id): Response
    {
        $user = $this->entityManager->getRepository(User::class)->find($id);
        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        $user->setActive(false);
        $this->entityManager->persist($user);
        $this->entityManager->flush();
        return $this->redirectToRoute('admin_users_list');
    }

}
