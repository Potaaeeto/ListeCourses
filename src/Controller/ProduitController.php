<?php

namespace App\Controller;

use App\Entity\Produit;
use App\Form\ProduitType;
use App\Entity\Liste;
use App\Repository\ProduitRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\String\Slugger\SluggerInterface;


#[Route('/produit')]
class ProduitController extends AbstractController
{

    #[Route('/liste/{listeId}', name: 'app_produit_index', methods: ['GET'])]
    public function index(Liste $liste): Response
    {
        return $this->render('produit/index.html.twig', [
            'liste' => $liste,
        ]);
    }

    #[Route('/liste/{listeId}/new', name: 'app_produit_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProduitRepository $produitRepository, Liste $listeId, SluggerInterface $slugger): Response
    {
        $produit = new Produit();
        $produit->setListe($listeId);
        $produit->setAchete(false);
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {

            //enregistrement de l'image
            /** @var UploadedFile $image */
            $image = $form->get('image')->getData();
            if ($image) {   
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $produit->setImage($newFilename);
            }
            
            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_liste_show', [
            'id' => $listeId->getId(),
        ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/new.html.twig', [
            'produit' => $produit,
            'form' => $form,
            'liste' => $listeId,
        ]);
    }

    #[Route('/show-produit/{id}', name: 'app_produit_show', methods: ['GET'])]
    public function show(Produit $produit): Response
    {
        return $this->render('produit/show.html.twig', [
            'produit' => $produit,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_produit_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Produit $produit, ProduitRepository $produitRepository, SluggerInterface $slugger): Response
    {
        $form = $this->createForm(ProduitType::class, $produit);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            
            //enregistrement de l'image
            /** @var UploadedFile $image */
            $image = $form->get('image')->getData();
            if ($image) {   
                $originalFilename = pathinfo($image->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename.'-'.uniqid().'.'.$image->guessExtension();
                $image->move(
                    $this->getParameter('images_directory'),
                    $newFilename
                );
                $produit->setImage($newFilename);
            }

            $produitRepository->save($produit, true);

            return $this->redirectToRoute('app_liste_show', [
                'id' => ($produit->getListe())->getId(),
            ], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('produit/edit.html.twig', [
            'produit' => $produit,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_produit_delete', methods: ['POST'])]
    public function delete(Request $request, Produit $produit, ProduitRepository $produitRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$produit->getId(),
            $request->request->get('_token'))) {
            $produitRepository->remove($produit, true);
        }

        return $this->redirectToRoute('app_liste_show', [
            'id' => ($produit->getListe())->getId(),
        ], Response::HTTP_SEE_OTHER);
    }

    #[Route('/{id}/achete', name: 'app_produit_achete', methods: ['POST'])]
    public function achete(Request $request, Produit $produit, EntityManagerInterface $entityManager): Response
    {
        if ($this->isCsrfTokenValid('achete'.$produit->getId(),
            $request->request->get('_token'))) {
            $produit->setAchete(!$produit->getAchete());//met l'attribut achete de produit à l'inverse de ce qu'il est actuellement
            $entityManager->flush();//pour sauvegarder les modifications dans la base de données
        }

        return $this->redirectToRoute('app_liste_show', [
            'id' => ($produit->getListe())->getId(),
        ], Response::HTTP_SEE_OTHER);
    }
}
