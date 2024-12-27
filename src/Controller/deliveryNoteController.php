<?php

namespace App\Controller;

use App\Entity\DeliveryNote;
use App\Entity\DeliveryNoteProducts;
use App\Entity\Sales;
use App\Entity\SalesProducts;
use App\Form\DeliveryNoteType;
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

    public function __construct(KernelInterface $kernel)
    {
        $this->kernel = $kernel;
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
        $productListToAdd = $data["dataDeliveryNoteProducts"] ?? [];

        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote,["edit" => false]);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $dateString = $data["salesDate"];
            $format = 'Y-m-d';
            $dateTime = DateTimeImmutable::createFromFormat($format, $dateString);
            $deliveryNote->setCreatedAt($dateTime);

            $deliveryNote->setUpdatedAt(new \DateTimeImmutable());
            $deliveryNote->setEnabled(1);
            $deliveryNote->setDeleted(0);

            $deliveryNoteRepository->save($deliveryNote, true);

            //insertProducts
            //$this->updateProductsFromRequest($deliveryNote,$productListToAdd);
            return $this->redirectToRoute('app_delivery_note_edit', ["id" => $deliveryNote->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('delivery_note/edit.html.twig', [
            'deliveryNote' => $deliveryNote,
            'currentDate' => $currentDate,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/edit', name: 'app_delivery_note_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, DeliveryNote $deliveryNote, DeliveryNoteRepository $deliveryNoteRepository,ProductsRepository $productsRepository): Response
    {
        $form = $this->createForm(DeliveryNoteType::class, $deliveryNote,["edit" => true]);
        $form->handleRequest($request);
        $data = $request->request->all();


        if ($form->isSubmitted() && $form->isValid()) {
            $deliveryNote->setUpdatedAt(new \DateTimeImmutable());
            $dateString = $data["salesDate"];
            $format = 'Y-m-d';

            $dateTime = DateTimeImmutable::createFromFormat($format, $dateString);

            $deliveryNoteRepository->save($deliveryNote, true);

            //delete prev products
            //$this->deleteProductsSles($sale);
            //insertProducts
            //$this->updateProductsFromRequest($sale,$productListToAdd,true);

            return $this->redirectToRoute('app_delivery_note_edit', ["id" => $deliveryNote->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('delivery_note/edit.html.twig', [
            'deliveryNote' => $deliveryNote,
            'form' => $form,
            'products' => $productsRepository->findByOrder("0")
        ]);
    }


    #[Route('/products/view_new_rows', name: 'app_delivery_note_rows', methods: ['GET','POST'])]
    public function salesViewNewRow(Request $request,ProductsRepository $productsRepository): Response
    {
        $newProductData = $request->request->all();
        return $this->renderForm('delivery_note/rowSalesProduct.html.twig', [
            'productsObjects' => $productsRepository->findBy([
                "id" => array_values($newProductData["options"])
            ])
        ]);
    }


    private function updateProductsFromRequest(DeliveryNote $deliveryNote,$productListToAdd,$update = false)
    {
        if ($productListToAdd and is_array($productListToAdd) and count($productListToAdd)){
            foreach ($productListToAdd as $productId => $productData){
                //$productsalestored = $this->productsRepository->find($productId);
                //$productsalestored->setQuantity($productsalestored->getQuantity() - intval($productData["quantity"]));
                $productSale = new DeliveryNoteProducts();
                $productSale->setSale($sale);
                $productSale->setQuantity(intval($productData["quantity"]));
                $productSale->setCreatedAt(new \DateTimeImmutable());
                $productSale->setUpdatedAt(new \DateTimeImmutable());
                $productSale->setProduct($this->productsRepository->find($productId));
                $productSale->setPriceHt(floatval(str_replace(",",".",$productData["priceHt"])));
                $productSale->setPriceTotalHt(floatval(str_replace(",",".",$productData["priceTotalHt"])));
                $productSale->setPriceTtc(floatval(str_replace(",",".",$productData["priceTtc"])));
                $productSale->setPriceTotalTtc(floatval(str_replace(",",".",$productData["priceTotalTtc"])));
                $productSale->setTaxeType($productData["taxeType"]);
                $productSale->setTaxe(floatval(str_replace(",",".",$productData["taxe"])));

                //$this->productsRepository->save($productsalestored);
                $this->salesProductsRepository->save($productSale,true);

            }
        }
    }


}
