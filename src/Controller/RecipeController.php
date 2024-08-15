<?php

namespace App\Controller;

use App\Entity\Recipe;
use App\Form\RecipeType;
use App\Repository\RecipeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;

class RecipeController extends AbstractController
{
    #[Route('/recette', name: 'recipe.index', methods:['GET'])]
    public function index(
        RecipeRepository $repository, 
        PaginatorInterface $paginator, 
        Request $request
    ): Response {
        $recipes = $paginator->paginate(
            $repository->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/recipe/index.html.twig', [
            'recipes' => $recipes,
        ]);
    }

    #[Route('/recette/creation','recipe.new', methods:['GET','POST'])]
    public function new(Request $request, EntityManagerInterface $manager): Response
    {
        $recipe = new Recipe();
        $form  = $this-> createForm(RecipeType::class, $recipe);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();

            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre recette a été créé avec succès !'
            );

            return $this->redirectToRoute('recipe.index');
        }

        return $this-> render('pages/recipe/new.html.twig', [
            'form'=> $form -> createView()
        ]);
    }

    #[Route('/recette/edition/{id}', name: 'recipe.edit', methods: ['GET', 'POST'])]
    public function edit(
        int $id, 
        RecipeRepository $repository, 
        Request $request, 
        EntityManagerInterface $manager

        ): Response {
            $recipe = $repository->find($id);

        $form = $this->createForm(RecipeType::class, $recipe);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $recipe = $form->getData();
            $manager->persist($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'Modification avec succès de la recette : ' . $recipe->getName()
            );
            return $this->redirectToRoute('recipe.index');
        }
        return $this->render('pages/recipe/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/recette/suppression/{id}', name: 'recipe.delete', methods: ['GET', 'POST'])]
    public function delete(
        int $id, 
        RecipeRepository $repository,
        EntityManagerInterface $manager
        ): Response {
        $recipe = $repository->find($id);
        if ($recipe) {
            $manager->remove($recipe);
            $manager->flush();

            $this->addFlash(
                'success',
                'La suppression de '. $recipe->getName() .' à été supprimer avec succès : '
            );
        } else {
            $this->addFlash(
                'warning',
                'Echec lors de la suppression de la recette'
            );
        }

        return $this->redirectToRoute('recipe.index');
    }

    
}
