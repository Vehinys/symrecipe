<?php

namespace App\Controller;


use App\Entity\Ingredient;
use App\Repository\IngredientRepository;
use Doctrine\ORM\EntityManagerInterface;
use Knp\Component\Pager\PaginatorInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Form\IngredientType;
use Doctrine\ORM\EntityManager;

class IngredientController extends AbstractController
{
    /**
     * this function display all ingredients
     *
     * @param IngredientRepository $repository
     * @param PaginatorInterface $paginator
     * @param Request $request
     * @return Response
     */

    #[Route('/ingredient', name: 'ingredient')]
    public function index(IngredientRepository $repository, PaginatorInterface $paginator,Request $request): Response
    {
        $ingredients = $paginator->paginate(
            $repository ->findAll(),
            $request->query->getInt('page', 1),
            10
        );

        return $this->render('pages/ingredient/index.html.twig', [
            'ingredients' => $ingredients
        ]);
    }
    
    #[Route('/ingredient/nouveau', name: 'ingredient.new', methods: ['GET', 'POST'])]
    public function new(
        Request $request,
        EntityManagerInterface $manager): Response
    {
        $ingredient = new Ingredient();
        $form = $this->createForm(IngredientType::class, $ingredient);

        $form->handleRequest($request);
        if($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();

            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Votre ingrédient a été créé avec succès !'
            );

            return $this->redirectToRoute('ingredient');

        }

        return $this->render('pages/ingredient/new.html.twig', [
            'form' => $form->createView()
        ]);

    }
    

    #[Route('/ingredient/edition/{id}', name: 'ingredient.edit', methods: ['GET', 'POST'])]
    public function edit(Ingredient $ingredient, Request $request, EntityManagerInterface $manager): Response

    {

        $form = $this->createForm(IngredientType::class, $ingredient);
        $form->handleRequest($request);
        if ($form->isSubmitted() && $form->isValid()) {
            $ingredient = $form->getData();
            $manager->persist($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Modification avec succès de : ' . $ingredient->getName()
            );
            return $this->redirectToRoute('ingredient');
        }
        return $this->render('pages/ingredient/edit.html.twig', [
            'form' => $form->createView(),
        ]);
    }

    #[Route('/ingredient/suppression/{id}', name: 'ingredient.delete', methods: ['GET', 'POST'])]
    public function delete(Ingredient $ingredient, Request $request, EntityManagerInterface $manager): Response

    {

        if ($ingredient) {

            $manager->remove($ingredient);
            $manager->flush();

            $this->addFlash(
                'success',
                'Suppression effectuée de : ' . $ingredient->getName()
            );

            return $this->redirectToRoute('ingredient');
            
        } else {
            $this->addFlash(
                'warning',
                'Echec lors de la suppression de l\'ingrédient'
            );
            return $this->redirectToRoute('ingredient');
        }
    }

}
