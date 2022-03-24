<?php

namespace App\Controller;

use DateTime;
use App\Entity\User;
use App\Form\RegisterFormType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController
{

    /**
     * @Route ("/inscription", name="user_register", methods={"GET|POST"})
     */
    public function register(Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher): Response
    {

        if ($this->getUser()) {
            $this->addFlash('warning', "Vous êtes déjà inscrit");

            return $this->redirectToRoute('default_home');
        }
        $user = new User();

        $form = $this->createForm(RegisterFormType::class, $user)
            ->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            $user->setRoles(['ROLE_USER']);
            $user->setCreatedAt(new DateTime);
            $user->setUpdatedAt(new DateTime);

            //    Hash du password en clair
            $user->setPassword($passwordHasher->hashPassword($user, $user->getPassword()));

            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', 'Vous êtes maintenant inscrit.Bienvenue!');

            return $this->redirectToRoute('default_home');
        }  // END if $form

        return $this->render('user/register.html.twig', [
            'form' => $form->createView()
        ]);
    }


    /**
     * @Route("/modifier-un-utilisateur_{id}", name="update_user", methods{"GET|POST"})
     */
    public function updateProfile(request $request, EntityManagerInterface $entityManager, User $user): Response
    {
        $form = $this->createForm(RegisterFormType::class, $user)->handleRequest($request);
        return $this->render('user/update_user.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    /**
     * @Route("profile/modifier-mon-profil_{id}", name="update_user", methods{"GET|POST"})
     */
    public function updateUser(User $user, Request $request, EntityManagerInterface $entityManager): Response
    {
        $form = $this->createForm(RegisterFormType::class, $user)
            ->handleRequest($request);
        // $form->get('nom')->getData())
        # pour récupérer la valeur d'un input

        if ($form->isSubmitted() && $form->isValid()) {


            $user->setUpdatedAt(new DateTime());


            $entityManager->persist($user);
            $entityManager->flush();

            $this->addFlash('success', "Vous avez modifié votre compte avec succès !");
            return $this->redirectToRoute('show_profile');
        }

        return $this->render('user/update_user.html.twig', [
            'form' => $form->createView()
        ]);
    }
}
