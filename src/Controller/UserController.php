<?php

namespace App\Controller;

use App\Entity\User;
use App\Form\UserType;
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
}


