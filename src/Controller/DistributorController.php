<?php

namespace App\Controller;

use App\Entity\Distributor;
use App\Form\DistributorType;
use App\Repository\DistributorRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/crm/distributor')]
class DistributorController extends AbstractController
{
    #[Route('/', name: 'app_distributor_index', methods: ['GET'])]
    public function index(DistributorRepository $distributorRepository): Response
    {
        return $this->render('distributor/index.html.twig', [
            'distributors' => $distributorRepository->findAll(),
        ]);
    }

    #[Route('/new', name: 'app_distributor_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DistributorRepository $distributorRepository): Response
    {
        $distributor = new Distributor();
        $form = $this->createForm(DistributorType::class, $distributor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $distributor->setCreatedAt(new \DateTimeImmutable());
            $distributor->setUpdatedAt(new \DateTimeImmutable());
            $distributor->setEnabled(1);
            $distributor->setDeleted(0);
            $distributorRepository->save($distributor, true);

            return $this->redirectToRoute('app_distributor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('distributor/new.html.twig', [
            'distributor' => $distributor,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_distributor_show', methods: ['GET'])]
    public function show(Distributor $distributor): Response
    {
        return $this->render('distributor/show.html.twig', [
            'distributor' => $distributor,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_distributor_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Distributor $distributor, DistributorRepository $distributorRepository): Response
    {
        $form = $this->createForm(DistributorType::class, $distributor);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $distributor->setUpdatedAt(new \DateTimeImmutable());
            $distributorRepository->save($distributor, true);

            return $this->redirectToRoute('app_distributor_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('distributor/edit.html.twig', [
            'distributor' => $distributor,
            'form' => $form,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_distributor_delete', methods: ['POST','GET'])]
    public function delete(Request $request, Distributor $distributor, DistributorRepository $distributorRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$distributor->getId(), $request->request->get('_token')) or $this->isCsrfTokenValid('delete'.$distributor->getId(), $request->query->get('_token'))) {
            $distributorRepository->remove($distributor, true);
        }

        return $this->redirectToRoute('app_distributor_index', [], Response::HTTP_SEE_OTHER);
    }


    #[Route('/add/distributor/sales', name: 'app_distributor_sales_add', methods: ['POST','GET'])]
    public function addDistributor(Request $request, DistributorRepository $distributorRepository)
    {
        $customerData = $request->request->all("newCustomer");
        if(!$customerData["company"] or trim($customerData["company"]) == ""){
            return new JsonResponse("nok_rs");
        }
        $customer = new Distributor();
        $customer->setCompany($customerData["company"]);
        $customer->setAdresse($customerData["adresse"]);
        $customer->setPhone($customerData["tel"]);
        $customer->setIce($customerData["ice"]);
        $customer->setEnabled(1);
        $customer->setDeleted(0);
        $customer->setUpdatedAt(new \DateTimeImmutable());
        $customer->setCreatedAt(new \DateTimeImmutable());

        $distributorRepository->save($customer,true);
        $customerData["id"] = $customer->getId();
        return new JsonResponse($customerData);
    }
}
