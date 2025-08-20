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
use App\Repository\PurchasesRepository;
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

    public $leChiffreSaisi;
    public $enLettre='';
    public $chiffre=array(1=>"un ",2=>"deux ",3=>"trois ",4=>"quatre ",5=>"cinq ",6=>"six ",7=>"sept ",8=>"huit ",9=>"neuf ",10=>"dix ",11=>"onze ",12=>"douze ",13=>"treize ",14=>"quatorze ",15=>"quinze ",16=>"seize ",17=>"dix-sept ",18=>"dix-huit ",19=>"dix-neuf ",20=>"vingt ",30=>"trente ",40=>"quarante ",50=>"cinquante ",60=>"soixante ",70=>"soixante-dix ",80=>"quatre-vingt ",90=>"quatre-vingt-dix ");


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

            $deliveryNote->setAmountTotalTtc(0);
            $deliveryNote->setAmountTotalTaxe(0);
            $deliveryNote->setAmoutTotalHt(0);

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
            $deliveryNote->setAmountTotalTtc(floatval(str_replace(",",".",$dataDeliveryNoteSalesProductsAmounts["sumPriceTotalTtcLabel"])));
            $deliveryNote->setAmountTotalTaxe(floatval(str_replace(",",".",$dataDeliveryNoteSalesProductsAmounts["sumTotalTaxeLabel"])));
            $deliveryNote->setAmoutTotalHt(floatval(str_replace(",",".",$dataDeliveryNoteSalesProductsAmounts["sumPriceTotalHtLabel"])));

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

    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }


    #[Route('/{id}/print', name: 'app_delivery_note_print', methods: ['GET'])]
    public function print(DeliveryNote $deliveryNote,DeliveryNoteRepository $deliveryNoteRepository): Response
    {
        $paymentMode = [
            '3' => 'Espèce',
            '1' => 'Chèque',
            '2' => 'Effet',
            '4' => 'Autre',
        ];

        $lettersAmount = $this->NumberToLetter($deliveryNote->getAmountTotalTtc());
        if ($lettersAmount[1] == "zÃ©ro"){
            $lettersAmount[1] = "";
        }else{
            $lettersAmount[1] = "ET". $lettersAmount[1]." centimes";
        }
        $data = [
            'imageSrc'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/images/crm/logo/logo_main.png'),
            'imageSrcNew'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/images/crm/logo/logo_main_new.png'),
            'deliveryNote'         => $deliveryNote,
            'paymentMode'         => $paymentMode,
            'pdf_frame'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/images/crm/logo/print_frame.png'),
            'amountText'         => strtoupper(str_replace("é","e",utf8_decode("$lettersAmount[0] DIRHAMS"))). strtoupper(str_replace("é","e",utf8_decode($lettersAmount[1]))),
        ];
        $html =  $this->renderView('models/delivery_note/pdf.html.twig', $data);

        $dompdf = new Dompdf();
        $options = new Options([
            'isPhpEnabled' => true,
            'isHtml5ParserEnabled' => true,
            'defaultFont' => 'Arial',
            'defaultPaperSize' => 'A4',
            'defaultPaperOrientation' => 'portrait',
            'margin_top' => 1,
            'margin_right' => 1,
            'margin_bottom' => 1,
            'margin_left' => 1,
        ]);

        $dompdf->setPaper('A4', 'portrait');
        $dompdf->setOptions($options);
        $dompdf->loadHtml($html);
        $dompdf->render();

        // Get the generated PDF content
        $output = $dompdf->output();

// Choose a file path to save the PDF
        $basePath = $this->getParameter('kernel.project_dir') . '/public/';

        $filePath = $basePath.'/data/file.pdf';

// Save the PDF file
        file_put_contents($filePath, $output);

        // Create a BinaryFileResponse with the PDF file path
        $response = new BinaryFileResponse($filePath);

// Set the desired response headers
        $response->headers->set('Content-Type', 'application/pdf');
        $fileNameDawnload = 'Bons de sortie N°'.$deliveryNote->getReference().'.pdf';
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileNameDawnload);

// Return the BinaryFileResponse
        return $response;
    }

    public function NumberToLetter($number){

        $nel = array();

        $enl_n = $this->Conversion(intval($number));
        $virgule = round(($number - intval($number))*100);

        $enl_v = $this->Conversion($virgule);

        if($virgule < 10 && $virgule > 0) $enl_v = "zéro ".$enl_v;

        $nel[0] = utf8_encode($enl_n);
        $nel[1] = utf8_encode($enl_v);

        return $nel;

    }

    #Fonction de conversion appelée dans la feuille principale
    public function Conversion($sasie)
    {
        $this->enLettre='';
        $sasie=trim($sasie);

#suppression des espaces qui pourraient exister dans la saisie
        $nombre='';
        $laSsasie=explode(' ',$sasie);
        foreach ($laSsasie as $partie)
            $nombre.=$partie;

#suppression des zéros qui précéderaient la saisie
        $nb=strlen($nombre);
        for ($i=0;$i<=$nb;)
        {
            if(substr($nombre,$i,1)==0)
            {
                $nombre=substr($nombre,$i+1);
                $nb=$nb-1;
            }
            elseif(substr($nombre,$i,1)<>0)
            {
                $nombre=substr($nombre,$i);
                break;
            }
        }
#echo $nombre;
#$this->SupZero($nombre);
#le nombre de caract que comporte le nombre saisi de sa forme sans espace et sans 0 au début
        $nb=strlen($nombre);
#echo $nb.'<br/ >';
#$this->leChiffreSaisi=$nombre;
#conversion du chiffre saisi en lettre selon les cas
        switch ($nb)
        {
            case 0:
                $this->enLettre='zéro';
            case 1:
                if ($nombre==0)
                {
                    $this->enLettre='zéro';
                    break;
                }
                elseif($nombre<>0)
                {
                    $this->Unite($nombre);
                    break;
                }

            case 2:
                $unite=substr($nombre,1);
                $dizaine=substr($nombre,0,1);
                $this->Dizaine(0,$nombre,$unite,$dizaine);
                break;

            case 3:
                $unite=substr($nombre,2);
                $dizaine=substr($nombre,1,1);
                $centaine=substr($nombre,0,1);
                $this->Centaine(0,$nombre,$unite,$dizaine,$centaine);
                break;

#cas des milles
            case ($nb>3 and $nb<=6):
                $unite=substr($nombre,$nb-1);
                $dizaine=substr($nombre,($nb-2),1);
                $centaine=substr($nombre,($nb-3),1);
                $mille=substr($nombre,0,($nb-3));
                $this->Mille($nombre,$unite,$dizaine,$centaine,$mille);
                break;

#cas des millions
            case ($nb>6 and $nb<=9):
                $unite=substr($nombre,$nb-1);
                $dizaine=substr($nombre,($nb-2),1);
                $centaine=substr($nombre,($nb-3),1);
                $mille=substr($nombre,-6);
                $million=substr($nombre,0,$nb-6);
                $this->Million($nombre,$unite,$dizaine,$centaine,$mille,$million);
                break;

#cas des milliards
            /*case ($nb>9 and $nb<=12):
            $unite=substr($nombre,$nb-1);
            $dizaine=substr($nombre,($nb-2),1);
            $centaine=substr($nombre,($nb-3),1);
            $mille=substr($nombre,-6);
            $million=substr($nombre,-9);
            $milliard=substr($nombre,0,$nb-9);
            Milliard($nombre,$unite,$dizaine,$centaine,$mille,$million,$milliard);
            break;*/

        }
        if (!empty($this->enLettre))
            return $this->enLettre;
    }

#Gestion des miiliards
    /*
    function Milliard($nombre,$unite,$dizaine,$centaine,$mille,$million,$milliard)
    {

    }
    */

#Gestion des millions

    public function Million($nombre,$unite,$dizaine,$centaine,$mille,$million)
    {
#si les mille comportent un seul chiffre
#$cent represente les 3 premiers chiffres du chiffre ex: 321 dans 12502321
#$mille represente les 3 chiffres qui suivent les cents ex: 502 dans 12502321
#reste represente les 6 premiers chiffres du chiffre ex: 502321 dans 12502321

        $cent=substr($nombre,-3);
        $reste=substr($nombre,-6);

        if (strlen($million)==1)
        {
            $mille=substr($nombre,1,3);
            $this->enLettre.=$this->chiffre[$million];
            if ($million == 1){
                $this->enLettre.=' million ';
            }else{
                $this->enLettre.=' millions ';
            }
        }
        elseif (strlen($million)==2)
        {
            $mille=substr($nombre,2,3);
            $nombre=substr($nombre,0,2);
//echo $nombre;
            $this->Dizaine(0,$nombre,$unite,$dizaine);
            $this->enLettre.='millions ';
        }
        elseif (strlen($million)==3)
        {
            $mille=substr($nombre,3,3);
            $nombre=substr($nombre,0,3);
            $this->Centaine(0,$nombre,$unite,$dizaine,$centaine);
            $this->enLettre.='millions ';
        }

#recuperation des cens dans nombre

#suppression des zéros qui précéderaient le $reste
        $nb=strlen($reste);
        for ($i=0;$i<=$nb;)
        {
            if(substr($reste,$i,1)==0)
            {
                $reste=substr($reste,$i+1);
                $nb=$nb-1;
            }
            elseif(substr($reste,$i,1)<>0)
            {
                $reste=substr($reste,$i);
                break;
            }
        }
        $nb=strlen($reste);
#si tous les chiffres apres les milions =000000 on affiche x million
        if ($nb==0)
            ;
        else
        {
#Gestion des milles
#suppression des zéros qui précéderaient les milles dans $mille
            $nb=strlen($mille);
            for ($i=0;$i<=$nb;)
            {
                if(substr($mille,$i,1)==0)
                {
                    $mille=substr($mille,$i+1);
                    $nb=$nb-1;
                }
                elseif(substr($mille,$i,1)<>0)
                {
                    $mille=substr($mille,$i);
                    break;
                }
            }
#le nombre de caract que comporte le nombre saisi de sa forme sans espace et sans 0 au début
            $nb=strlen($mille);
#echo '<br />nb='.$nb.'<br />';
            if ($nb==0)
                ;
#AffichageResultat($enLettre);
            elseif ($nb==1)
            {
                if ($mille==1)
                    $this->enLettre.='mille ';
                else
                {
                    $this->Unite($mille);
                    $this->enLettre.='mille ';
                }
            }
            elseif ($nb==2)
            {
                $this->Dizaine(1,$mille,$unite,$dizaine);
                $this->enLettre.='mille ';
            }
            elseif ($nb==3)
            {
                $this->Centaine(1,$mille,$unite,$dizaine,$centaine);
                $this->enLettre.='mille ';
            }
#Gestion des cents
#suppression des zéros qui précéderaient les cents dans $cent
            $nb=strlen($cent);
            for ($i=0;$i<=$nb;)
            {
                if(substr($cent,$i,1)==0)
                {
                    $cent=substr($cent,$i+1);
                    $nb=$nb-1;
                }
                elseif(substr($cent,$i,1)<>0)
                {
                    $cent=substr($cent,$i);
                    break;
                }
            }
#le nombre de caract que comporte le nombre saisi de sa forme sans espace et sans 0 au début
            $nb=strlen($cent);
#echo '<br />nb='.$nb.'<br />';
            if ($nb==0)
                ;
#AffichageResultat($enLettre);
            elseif ($nb==1)
                $this->Unite($cent);
            elseif ($nb==2)
                $this->Dizaine(0,$cent,$unite,$dizaine);
            elseif ($nb==3)
                $this->Centaine(0,$cent,$unite,$dizaine,$centaine);
        }
    }

#Gestion des milles

    public function Mille($nombre,$unite,$dizaine,$centaine,$mille)
    {
#si les mille comportent un seul chiffre
#$cent represente les 3 premiers chiffres du chiffre ex: 321 dans 12321
        if (strlen($mille)==1)
        {
            $cent=substr($nombre,1);
#si ce chiffre=1
            if ($mille==1)
                $this->enLettre.='';
#si ce chiffre<>1
            elseif($mille<>1)
                $this->enLettre.=$this->chiffre[$mille];
        }
        elseif (strlen($mille)>1)
        {
            if (strlen($mille)==2)
            {
                $cent=substr($nombre,2);
                $nombre=substr($nombre,0,2);
#echo $nombre;
                $this->Dizaine(1,$nombre,$unite,$dizaine);
            }
            if (strlen($mille)==3)
            {
                $cent=substr($nombre,3);
                $nombre=substr($nombre,0,3);
#echo $nombre;
                $this->Centaine(1,$nombre,$unite,$dizaine,$centaine);
            }
        }

        $this->enLettre.='mille ';
#recuperation des cens dans nombre
#suppression des zéros qui précéderaient la saisie
        $nb=strlen($cent);
        for ($i=0;$i<=$nb;)
        {
            if(substr($cent,$i,1)==0)
            {
                $cent=substr($cent,$i+1);
                $nb=$nb-1;
            }
            elseif(substr($cent,$i,1)<>0)
            {
                $cent=substr($cent,$i);
                break;
            }
        }
#le nombre de caract que comporte le nombre saisi de sa forme sans espace et sans 0 au début
        $nb=strlen($cent);
#echo '<br />nb='.$nb.'<br />';
        if ($nb==0)
            ;//AffichageResultat($enLettre);
        elseif ($nb==1)
            $this->Unite($cent);
        elseif ($nb==2)
            $this->Dizaine(0,$cent,$unite,$dizaine);
        elseif ($nb==3)
            $this->Centaine(0,$cent,$unite,$dizaine,$centaine);

    }

#Gestion des centaines

    public function Centaine($inmillier,$nombre,$unite,$dizaine,$centaine)
    {

        $unite=substr($nombre,2);
        $dizaine=substr($nombre,1,1);
        $centaine=substr($nombre,0,1);
#comme 700
        if ($unite==0 and $dizaine==0)
        {
            if ($centaine==1)
                $this->enLettre.='cent';
            elseif ($centaine<>1)
            {
                if ($inmillier == 0)
                    $this->enLettre.=($this->chiffre[$centaine].' cents').' ';
                if ($inmillier == 1)
                    $this->enLettre.=($this->chiffre[$centaine].' cent').' ';
            }
        }
#comme 705
        elseif ($unite<>0 and $dizaine==0)
        {
            if ($centaine==1)
                $this->enLettre.=('cent '.$this->chiffre[$unite]).' ';
            elseif ($centaine<>1)
                $this->enLettre.=($this->chiffre[$centaine].' cent '.$this->chiffre[$unite]).' ';
        }
//comme 750
        elseif ($unite==0 and $dizaine<>0)
        {
#recupération des dizaines
            $nombre=substr($nombre,1);
//echo '<br />nombre='.$nombre.'<br />';
            if ($centaine==1)
            {
                $this->enLettre.='cent ';
                $this->Dizaine(0,$nombre,$unite,$dizaine).' ';
            }
            elseif ($centaine<>1)
            {
                $this->enLettre.=$this->chiffre[$centaine].' cent ';
                $this->Dizaine(0,$nombre,$unite,$dizaine).' ';

            }

        }
#comme 695
        elseif ($unite<>0 and $dizaine<>0)
        {
            $nombre=substr($nombre,1);

            if ($centaine==1)
            {
                $this->enLettre.='cent ';
                $this->Dizaine(0,$nombre,$unite,$dizaine).' ';
            }

            elseif ($centaine<>1)
            {
                $this->enLettre.=($this->chiffre[$centaine].' cent ');
                $this->Dizaine(0,$nombre,$unite,$dizaine).' ';
            }
        }

    }


#Gestion des dizaines

    public function Dizaine($inmillier,$nombre,$unite,$dizaine)
    {
        $unite=substr($nombre,1);
        $dizaine=substr($nombre,0,1);

#comme 70
        if ($unite==0)
        {
            $val=$dizaine.'0';
            $this->enLettre.=$this->chiffre[$val];
            if ($inmillier == 0 && $val == 80){
                $this->enLettre.='s ';
            }
            $this->enLettre.=' ';
        }
#comme 71
        elseif ($unite<>0)
#dizaine different de 9
            if ($dizaine<>9 and $dizaine<>7)
            {
                if ($dizaine==1)
                {
                    $val=$dizaine.$unite;
                    $this->enLettre.=$this->chiffre[$val].' ';
                }
                else
                {
                    $val=$dizaine.'0';
                    if ($unite == 1 && $dizaine <> 8){
                        $this->enLettre.=($this->chiffre[$val].' et '.$this->chiffre[$unite]).' ';
                    }else{
                        $this->enLettre.=($this->chiffre[$val].'-'.$this->chiffre[$unite]).' ';
                    }
                }
            }
#dizaine =9
            elseif ($dizaine==9)
                $this->enLettre.=($this->chiffre[80].'-'.$this->chiffre['1'.$unite]).' ';
            elseif ($dizaine==7)
            {
                if ($unite == 1){
                    $this->enLettre.=($this->chiffre[60].' et '.$this->chiffre['1'.$unite]).' ';
                }else{
                    $this->enLettre.=($this->chiffre[60].'-'.$this->chiffre['1'.$unite]).' ';
                }
            }
    }
#Gestion des unités

    public function Unite($unite)
    {
        if($unite != ""){
            $this->enLettre.=($this->chiffre[$unite]).' ';
        }
    }


    #[Route('/{id}/generate/sales', name: 'app_delivery_note_generate', methods: ['GET'])]
    public function saveToSales(DeliveryNote $deliveryNote,DeliveryNoteRepository $deliveryNoteRepository): Response
    {

    }
}
