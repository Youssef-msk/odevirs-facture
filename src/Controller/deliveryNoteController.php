<?php

namespace App\Controller;

use App\Entity\DeliveryNote;
use App\Entity\Sales;
use App\Entity\SalesProducts;
use App\Form\DeliveryNoteType;
use App\Form\SalesType;
use App\Repository\DeliveryNoteRepository;
use App\Repository\ProductsRepository;
use App\Repository\SalesProductsRepository;
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
    //NE GERE PAS TOUT (les pluriels...)
#Variables
    public $leChiffreSaisi;
    public $enLettre='';
    public $chiffre=array(1=>"un ",2=>"deux ",3=>"trois ",4=>"quatre ",5=>"cinq ",6=>"six ",7=>"sept ",8=>"huit ",9=>"neuf ",10=>"dix ",11=>"onze ",12=>"douze ",13=>"treize ",14=>"quatorze ",15=>"quinze ",16=>"seize ",17=>"dix-sept ",18=>"dix-huit ",19=>"dix-neuf ",20=>"vingt ",30=>"trente ",40=>"quarante ",50=>"cinquante ",60=>"soixante ",70=>"soixante-dix ",80=>"quatre-vingt ",90=>"quatre-vingt-dix ");


    private $kernel;

    private $productsRepository;

    private $salesProductsRepository;

    public function __construct(KernelInterface $kernel,ProductsRepository $productsRepository,SalesProductsRepository $salesProductsRepository)
    {
        $this->kernel = $kernel;
        $this->productsRepository = $productsRepository;
        $this->salesProductsRepository = $salesProductsRepository;
    }

    #[Route('/', name: 'app_delivery_note_index', methods: ['GET'])]
    public function index(DeliveryNoteRepository $deliveryNoteRepository): Response
    {
        $queryBuilder = $deliveryNoteRepository->createQueryBuilder('e');
        $queryBuilder->orderBy('e.id', 'DESC');
        $sortedEntities = $queryBuilder->getQuery()->getResult();

        return $this->render('delivery_note/index.html.twig', [
            'sales' => $sortedEntities,
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

}
