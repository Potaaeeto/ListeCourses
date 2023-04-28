<?php

namespace App\Controller;

use App\Entity\Liste;
use App\Form\ListeType;
use App\Repository\ListeRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/liste')]
class ListeController extends AbstractController
{
    #[Route('/', name: 'app_liste_index', methods: ['GET'])]
    public function index(ListeRepository $listeRepository): Response
    {
        return $this->render('liste/index.html.twig', [
            'listes' => $listeRepository->findBy(['utilisateur' => $this->getUser()]),
        ]);
    }

    #[Route('/new', name: 'app_liste_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ListeRepository $listeRepository): Response
    {
        $liste = new Liste();
        $liste->setUtilisateur($this->getUser());
        $form = $this->createForm(ListeType::class, $liste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listeRepository->save($liste, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('liste/new.html.twig', [
            'liste' => $liste,
            'form' => $form,
            'utilisateur' => $this->getUser(),
        ]);
    }

    #[Route('/{id}', name: 'app_liste_show', methods: ['GET'])]
    public function show(Liste $liste): Response
    {
        $produits = $liste->getProduit();
        $nombreProduits = 0;
        $total = 0;
        $moyenne = 0;
        $produitMoinsCher = null;
        $produitPlusCher = null;


        if (count($produits) > 0) {
            foreach ($produits as $produit) {
                $nombreProduits += $produit->getQuantite();
            }

            foreach ($produits as $produit) {
                $total += $produit->getQuantite() * $produit->getPrix();
            }


            $moyenne = $nombreProduits > 0 ? round($total / $nombreProduits, 2) : 0;



            $prixMax = 0;
            foreach ($produits as $produit) {
                if ($produit->getPrix() > $prixMax) {
                    $prixMax = $produit->getPrix();
                    $produitPlusCher = $produit;
                }
            }


            $prixMin = PHP_INT_MAX;
            foreach ($produits as $produit) {
                if ($produit->getPrix() < $prixMin) {
                    $prixMin = $produit->getPrix();
                    $produitMoinsCher = $produit;
                }
            }



        }

        return $this->render('liste/show.html.twig', [
            'liste' => $liste,
            'total' => $total,
            'nombreProduits' => $nombreProduits,
            'moyenne' => $moyenne,
            'produitPlusCher' => $produitPlusCher,
            'produitMoinsCher' => $produitMoinsCher,
            'produits' => $produits,

        ]);
    }

    #[Route('/{id}/edit', name: 'app_liste_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Liste $liste, ListeRepository $listeRepository): Response
    {
        $form = $this->createForm(ListeType::class, $liste);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $listeRepository->save($liste, true);

            return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('liste/edit.html.twig', [
            'liste' => $liste,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_liste_delete', methods: ['POST'])]
    public function delete(Request $request, Liste $liste, ListeRepository $listeRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$liste->getId(), $request->request->get('_token'))) {
            $listeRepository->remove($liste, true);
        }

        return $this->redirectToRoute('app_home', [], Response::HTTP_SEE_OTHER);
    }


}
