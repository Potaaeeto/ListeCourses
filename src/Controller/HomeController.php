<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use App\Repository\ListeRepository;

class HomeController extends AbstractController
{
    /**
     * @Route("/", name="app_home")
     * @Route("/accueil/", name="app_accueil", methods={"GET"})
     */
    public function index(ListeRepository $listeRepository): Response
    {
        return $this->render('home/index.html.twig', [
            'listes' => $listeRepository->findBy(['utilisateur' => $this->getUser()]),
        ]);
    }

    /**
     * @Route("/", name="app_home")
     * @Route("/accueil/", name="app_accueil", methods={"GET"})
     */
    public function statistiquesGeneral(ListeRepository $listeRepository): Response
    {
        $listes = $listeRepository->findBy(['utilisateur' => $this->getUser()]);
        $produits = [];

        foreach ($listes as $liste) {
            $produits = array_merge($produits, $liste->getProduit()->toArray());
        }

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

        return $this->render('home/index.html.twig', [
            'listes' => $listes,
            'total' => $total,
            'nombreProduits' => $nombreProduits,
            'moyenne' => $moyenne,
            'produitPlusCher' => $produitPlusCher,
            'produitMoinsCher' => $produitMoinsCher,
            'produits' => $produits,
        ]);
    }
}


