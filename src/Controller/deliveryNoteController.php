<?php

namespace App\Controller;

use App\Entity\DeliveryNote;
use App\Entity\DeliveryNoteProducts;
use App\Entity\Products;
use App\Entity\Purchases;
use App\Form\DeliveryNoteType;
use App\Repository\DeliveryNoteProductsRepository;
use App\Repository\DeliveryNoteRepository;
use App\Repository\ProductsRepository;
use DateTimeImmutable;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpKernel\KernelInterface;
use Symfony\Component\Routing\Annotation\Route;
use Dompdf\Dompdf;
use Knp\Snappy\Pdf;
use Dompdf\Options;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/crm/delivery/note')]
class deliveryNoteController extends AbstractController
{
    private $kernel;

    private $productsRepository;
    private $deliveryNoteProductRepository;
    public function __construct(KernelInterface $kernel,ProductsRepository $productsRepository,DeliveryNoteProductsRepository $deliveryNoteProductsRepository)
    {
        $this->kernel = $kernel;
        $this->productsRepository = $productsRepository;
        $this->deliveryNoteProductRepository = $deliveryNoteProductsRepository;

    }

    #[Route('/', name: 'app_delivery_note_index', methods: ['GET'])]
    public function index(DeliveryNoteRepository $deliveryNoteRepository): Response
    {
        $queryBuilder = $deliveryNoteRepository->createQueryBuilder('e');
        $queryBuilder->orderBy('e.id', 'DESC');
        $sortedEntities = $queryBuilder->getQuery()->getResult();

        return $this->render('delivery_note/index.html.twig', [
            'deliveryNoteList' => $sortedEntities,
        ]);
    }

    #[Route('/new', name: 'app_delivery_note_new', methods: ['GET', 'POST'])]
    public function new(Request $request, DeliveryNoteRepository $deliveryNoteRepository): Response
    {
        $deliveryNote = new DeliveryNote();
        $deliveryNoteReference = date('hsdmy');
        $currentDate = date('Y-m-d');
        $deliveryNote->setReference("BS-".$deliveryNoteReference);
        $deliveryNote->setGeneratedSale(false);

        $data = $request->request->all();
        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote,["edit" => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateString = $data["deliveryNoteDate"];
            $format = 'Y-m-d';
            $dateTime = DateTimeImmutable::createFromFormat($format, $dateString);
            $deliveryNote->setCreatedAt($dateTime);

            $deliveryNote->setUpdatedAt(new \DateTimeImmutable());
            $deliveryNote->setEnabled(1);
            $deliveryNote->setDeleted(0);

            $deliveryNoteRepository->save($deliveryNote, true);
            return $this->redirectToRoute('app_delivery_note_edit', ["id" => $deliveryNote->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('delivery_note/edit.html.twig', [
            'deliveryNote' => $deliveryNote,
            'currentDate' => $currentDate,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_delivery_note_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DeliveryNote $deliveryNote,DeliveryNoteProductsRepository $deliveryNoteProductsRepository, DeliveryNoteRepository $deliveryNoteRepository,ProductsRepository $productsRepository): Response
    {
        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote,["edit" => true]);
        $form->handleRequest($request);
        $data = $request->request->all();

        $productsDeliveryNoteObjects = $deliveryNoteProductsRepository->findBy([
            "deliveryNote" => $deliveryNote
        ],orderBy: ["id" => "DESC"]);

        $productListToManage = $data["dataDeliveryNoteSalesProducts"] ?? [];
        $dataDeliveryNoteSalesProductsAmounts = $data["dataDeliveryNoteSalesProductsAmounts"] ?? [];

        if ($form->isSubmitted() && $form->isValid()) {
            $dateString = $data["deliveryNoteDate"];
            $format = 'Y-m-d';
            $dateTime = DateTimeImmutable::createFromFormat($format, $dateString);
            $deliveryNote->setCreatedAt($dateTime);

            $deliveryNote->setUpdatedAt(new \DateTimeImmutable());
            $deliveryNote->setEnabled(1);
            $deliveryNote->setDeleted(0);

            $deliveryNoteRepository->save($deliveryNote, true);

            //delete prev products
            $this->deleteProducts($deliveryNote);
            //insertProducts
            $this->updateProductsFromRequest($deliveryNote,$productListToManage);


            return $this->redirectToRoute('app_delivery_note_edit', ["id" => $deliveryNote->getId()], Response::HTTP_SEE_OTHER);

        }

        return $this->renderForm('delivery_note/edit.html.twig', [
            'deliveryNote' => $deliveryNote,
            'form' => $form,
            'deliveryNoteProducts' => $productsDeliveryNoteObjects
        ]);
    }


    private function updateProductsFromRequest(DeliveryNote $deliveryNote,$productListToAdd)
    {
        if ($productListToAdd and is_array($productListToAdd) and count($productListToAdd)){
            foreach ($productListToAdd as $productId => $productData){
                $productId = intval($productData["id"]);
                $deliveryNoteProductstored = $this->productsRepository->find($productId);
                $deliveryNoteProductstored->setQuantity($deliveryNoteProductstored->getQuantity() - intval($productData["quantity"]));
                $deliveryNoteProduct = new DeliveryNoteProducts();
                $deliveryNoteProduct->setDeliveryNote($deliveryNote);
                $deliveryNoteProduct->setQuantity(intval($productData["quantity"]));
                $deliveryNoteProduct->setCreatedAt(new \DateTimeImmutable());
                $deliveryNoteProduct->setUpdatedAt(new \DateTimeImmutable());
                $deliveryNoteProduct->setProduct($this->productsRepository->find($productId));
                $deliveryNoteProduct->setPriceHt(floatval(str_replace(",",".",$productData["priceHt"])));
                $deliveryNoteProduct->setPriceTotalHt(floatval(str_replace(",",".",$productData["priceHt"])) * intval($productData["quantity"]));
                $deliveryNoteProduct->setPriceTtc(floatval(str_replace(",",".",$productData["priceTtc"])));
                $deliveryNoteProduct->setPriceTotalTtc(floatval(str_replace(",",".",$productData["priceTotalTtc"])));
                $deliveryNoteProduct->setTaxeType("default_taxe");
                $deliveryNoteProduct->setTaxe(floatval(str_replace(",",".",$productData["taxe"])));

                $this->productsRepository->save($deliveryNoteProductstored);
                $this->deliveryNoteProductRepository->save($deliveryNoteProduct,true);

            }
        }
    }


    private function deleteProducts(DeliveryNote $deliveryNote): bool
    {
        $salesProducts = $this->deliveryNoteProductRepository->findBy(["deliveryNote" => $deliveryNote]);
        foreach ($salesProducts as $product){
            $productsalestored = $this->productsRepository->find($product->getProduct()->getId());
            $productsalestored->setQuantity($productsalestored->getQuantity() + $product->getQuantity());
            $this->productsRepository->save($productsalestored);

            $this->deliveryNoteProductRepository->remove($product,true);
        }

        return true;
    }

}
