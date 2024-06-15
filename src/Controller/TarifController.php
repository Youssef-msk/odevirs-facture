<?php

namespace App\Controller;

use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class TarifController extends AbstractController
{
    #[Route('/tarif', name: 'app_tarif')]
    public function index(ProductsRepository $productsRepository): Response
    {
        return $this->render('tarif/index.html.twig', [
            'controller_name' => 'TarifController',
            'products' => $productsRepository->findAllActivated(),
        ]);
    }
}
