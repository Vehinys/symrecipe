<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
use App\Form\UserPasswordType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class UserController extends AbstractController

{
    #[Route('/utilisateur/edition/{id}', name: 'user.edit', methods:['GET','POST'])]
    public function edit(int $id, Request $request, EntityManagerInterface $entityManager, UserPasswordHasherInterface $hasher): Response
    {
        // Récupérer l'utilisateur avec le UserRepository
        $user = $entityManager->getRepository(User::class)->find($id);

        // Vérifier si l'utilisateur est connecté
        if (!$this->getUser()) {
            return $this->redirectToRoute('security.login');
        }

        // Vérifier si l'utilisateur connecté est le même que celui en modification
        if ($this->getUser() !== $user) {
            return $this->redirectToRoute('recipe.index');
        }

        // Créer le formulaire pour l'utilisateur
        $form = $this->createForm(UserType::class, $user);

        // Traiter la requête
        $form->handleRequest($request);

        // Si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            if($hasher->isPasswordValid($user, $form->getData()->getPlainPassword())
            ) {
                $entityManager->flush();

                // Ajouter un message flash pour informer l'utilisateur
                $this->addFlash(
                    'success',
                    'Les informations de votre compte ont bien été modifiées.'
                );
    
                // Rediriger vers une page appropriée après la modification
                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash(
                    'warning',
                    'Le mot de passe renseigné est incorrect.'
                );
            }

        }

        // Afficher le formulaire si non soumis ou non valide
        return $this->render('pages/user/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/utilisateur/edition-mot-de-passe/{id}', name: 'user.edit.password', methods: ['GET', 'POST'])]
    public function editPassword(int $id, EntityManagerInterface $manager, Request $request, UserPasswordHasherInterface $hasher): Response
    {
        // Récupérer l'utilisateur via son ID
        $user = $manager->getRepository(User::class)->find($id);
    
        // Si l'utilisateur n'existe pas, lever une exception
        if (!$user) {
            throw $this->createNotFoundException('Utilisateur non trouvé.');
        }
    
        // Vérifier si l'utilisateur connecté est bien celui dont on essaie de modifier le mot de passe
        if ($this->getUser() !== $user) {
            // Rediriger l'utilisateur vers une autre page (par exemple, la page d'accueil)
            $this->addFlash('warning', 'Vous ne pouvez pas modifier le mot de passe d’un autre utilisateur.');
            return $this->redirectToRoute('recipe.index'); // Redirection vers la page d'accueil
        }
    
        // Créer le formulaire pour modifier le mot de passe
        $form = $this->createForm(UserPasswordType::class);
    
        // Traitement de la requête HTTP
        $form->handleRequest($request);
    
        // Vérification si le formulaire est soumis et valide
        if ($form->isSubmitted() && $form->isValid()) {
            $plainPassword = $form->get('plainPassword')->getData();
            $newPassword = $form->get('newPassword')->getData();
    
            // Vérifier si le mot de passe actuel est valide
            if ($hasher->isPasswordValid($user, $plainPassword)) {
                // Hashage du nouveau mot de passe et mise à jour
                $hashedPassword = $hasher->hashPassword($user, $newPassword);
                $user->setPassword($hashedPassword);
    
                // Enregistrement en base de données
                $manager->flush();
    
                // Ajout d'un message flash et redirection
                $this->addFlash('success', 'Votre mot de passe a bien été mis à jour.');
    
                return $this->redirectToRoute('recipe.index');
            } else {
                $this->addFlash('warning', 'Le mot de passe renseigné est incorrect.');
            }
        }
    
        // Rendu du formulaire si non soumis ou non valide
        return $this->render('pages/user/edit_password.html.twig', [
            'form' => $form->createView(),
        ]);
    }
    
    
}


