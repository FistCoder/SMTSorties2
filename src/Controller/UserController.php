<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

#[Route('/user', name: 'user_')]
final class UserController extends AbstractController
{

    public function __construct(private readonly UserPasswordHasherInterface $userPasswordHasher)
    {
    }

    #[Route('/detail/{id}', name: 'detail', requirements: ['id' => '\d+'])]
    public function userDetail(int $id, UserRepository $userRepository): Response
    {
        $user = $userRepository->find($id);
        if (!$user) {
            throw $this->createNotFoundException("User not found");
        }
        return $this->render('user/detail.html.twig', [
            "user" => $user,
        ]);
    }

    #[Route('/modify', name: 'modify')]
    public function userModify(
        Request                $request,
        EntityManagerInterface $entityManager,
        UserRepository         $userRepository
    ): Response

    {
        /**
         * @var User $user
         */
        $user = $this->getUser();

        if (!$user) {
            throw $this->createNotFoundException('User not found');
        }
        $userForm = $this->createForm(UserType::class, $user);
        $userForm->handleRequest($request);

        if ($userForm->isSubmitted() && $userForm->isValid()) {


            $image = $userForm->get('userPicture')->getData();
            if ($image) {
                $newFileName = uniqid() . '.' . $image->guessExtension();
                $image->move($this->getParameter('user_picture_dir'), $newFileName);
                $user->setUserPicture($newFileName);
            }

            if ($userForm->get('confirmPassword')->getData()) {
                $user->setPassword($this->userPasswordHasher->hashPassword($user, $userForm->get('confirmPassword')->getData()));
            }
            $entityManager->persist($user);
            $entityManager->flush();
            $this->addFlash("success", "User modified successfully");
            return $this->redirectToRoute('user_modify'); //modifier avec route vers détails

        }
        return $this->render('user/modify.html.twig', [
            'userForm' => $userForm,
        ]);
    }
}


