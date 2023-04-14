<?php

namespace App\Controller;

use App\Entity\Article;
use App\Entity\Commentary;
use App\Entity\User;
use App\Form\ChangePasswordFormType;
use App\Form\RegisterFormType;
use App\Repository\UserRepository;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class UserController extends AbstractController
{
    # Exercice : Créer l'action qui permet d'inscrire un nouvel utilisateur en BDD.
    #[Route('/inscription', name: 'app_register', methods: ['GET', 'POST'])]
    public function register(Request $request, UserRepository $repository, UserPasswordHasherInterface $hasher): Response
    {
        $user = new User();

        $form = $this->createForm(RegisterFormType::class, $user)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            $user->setCreatedAt(new DateTime());
            $user->setUpdatedAt(new DateTime());
            $user->setRoles(['ROLE_USER']);

            $user->setPassword(
                $hasher->hashPassword($user, $user->getPassword())
            );

            $repository->save($user, true);

            $this->addFlash('success', "Votre inscription a été effectuée avec succès !");
            return $this->redirectToRoute('show_home');
        }

        return $this->render('user/register_form.html.twig', [
            'form' => $form->createView()
        ]);

    } // end register()

    #[Route('/profile/voir-mon-compte', name: 'show_profile', methods: ['GET'])]
    public function showProfile(EntityManagerInterface $entityManager): Response
    {
        $articles = $entityManager->getRepository(Article::class)->findBy(['author' => $this->getUser()]);
        $commentaries = $entityManager->getRepository(Commentary::class)->findBy(['author' => $this->getUser()]);

        return $this->render('user/show_profile.html.twig', [
            'articles' => $articles,
            'commentaries' => $commentaries
        ]);
    } // end showProfile()
    #[Route('/changer-mon-mot-de-passe', name: 'change_password', methods: ['GET', 'POST'])]
    public function changePassword(Request $request, UserRepository $repository, UserPasswordHasherInterface $hasher): Response
    {
        $form = $this->createForm(ChangePasswordFormType::class)
            ->handleRequest($request);

        if($form->isSubmitted() && $form->isValid()) {

            # Récupérer le User en BDD
            $user = $repository->find($this->getUSer());

            /* Listing des étapes :
            1 - Un nouvel input => ChangePasswordFormType
            2 - Récupérer la valeur de cet input
            3 - Hasher le $currentPassword pour comparaison en BDD
            4 - Condition de vérification
            5 - Si la condition est vérifiée, alors on exécute le code.
            */

            // ------------------- VERIFICATION DU MDP --------------------//
            $currentPassword = $form->get('currentPassword')->getData();

            if(! $hasher->isPasswordValid($user, $currentPassword)) {
                $this->addFlash('warning', "Le mot de passe actuel n'est pas valide.");
                return $this->redirectToRoute('show_profile');
            }

            $user->setUpdatedAt(new DateTime());

            $plainPassword = $form->get('plainPassword')->getData();

            $user->setPassword($hasher->hashPassword($user, $plainPassword));

            $repository->save($user, true);

            $this->addFlash('success', "Votre mot de passe a bien été modifié !");
            return $this->redirectToRoute('show_profile');

        }

        return $this->render('user/change_password_form.html.twig', [
            'form' => $form->createView()
        ]);
    }

} // end class