<?php

namespace App\Controller;

use App\Entity\BlHead;
use App\Form\BlHeadType;
use App\Repository\BlHeadRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/bl/head')]
class BlHeadController extends AbstractController
{
    #[Route('/', name: 'app_bl_head_index', methods: ['GET'])]
    public function index(BlHeadRepository $blHeadRepository): Response
    {
        return $this->render('bl_head/index.html.twig', [
            'bl_heads' => $blHeadRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_bl_head_new', methods: ['GET', 'POST'])]
    public function new(Request $request, BlHeadRepository $blHeadRepository): Response
    {
        $blHead = new BlHead();
        $form = $this->createForm(BlHeadType::class, $blHead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blHeadRepository->save($blHead, true);

            return $this->redirectToRoute('app_bl_head_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bl_head/new.html.twig', [
            'bl_head' => $blHead,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bl_head_show', methods: ['GET'])]
    public function show(BlHead $blHead): Response
    {
        return $this->render('bl_head/show.html.twig', [
            'bl_head' => $blHead,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_bl_head_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, BlHead $blHead, BlHeadRepository $blHeadRepository): Response
    {
        $form = $this->createForm(BlHeadType::class, $blHead);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $blHeadRepository->save($blHead, true);

            return $this->redirectToRoute('app_bl_head_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('bl_head/edit.html.twig', [
            'bl_head' => $blHead,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_bl_head_delete', methods: ['POST'])]
    public function delete(Request $request, BlHead $blHead, BlHeadRepository $blHeadRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$blHead->getId(), $request->request->get('_token'))) {
            $blHeadRepository->remove($blHead, true);
        }

        return $this->redirectToRoute('app_bl_head_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search/product', name: 'app_products_search', methods: ['POST','GET'])]
    public function searchProduct(Request $request, BlHeadRepository $blHeadRepository)
    {
        $term = $request->request->get("query");
        $products = $blHeadRepository->findByTerm($term);

        return new JsonResponse($products);
    }
}
