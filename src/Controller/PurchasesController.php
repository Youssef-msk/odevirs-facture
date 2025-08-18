<?php

namespace App\Controller;

use App\Entity\Products;
use App\Entity\Purchases;
use App\Entity\Sales;
use App\Entity\SalesProducts;
use App\Form\PurchasesType;
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

#[Route('/crm/purchases')]
class PurchasesController extends AbstractController
{
    //NE GERE PAS TOUT (les pluriels...)
#Variables
    public $leChiffreSaisi;
    public $enLettre='';
    public $chiffre=array(1=>"un ",2=>"deux ",3=>"trois ",4=>"quatre ",5=>"cinq ",6=>"six ",7=>"sept ",8=>"huit ",9=>"neuf ",10=>"dix ",11=>"onze ",12=>"douze ",13=>"treize ",14=>"quatorze ",15=>"quinze ",16=>"seize ",17=>"dix-sept ",18=>"dix-huit ",19=>"dix-neuf ",20=>"vingt ",30=>"trente ",40=>"quarante ",50=>"cinquante ",60=>"soixante ",70=>"soixante-dix ",80=>"quatre-vingt ",90=>"quatre-vingt-dix ");


    private $kernel;

    private $productsRepository;


    public function __construct(KernelInterface $kernel,ProductsRepository $productsRepository)
    {
        $this->kernel = $kernel;
        $this->productsRepository = $productsRepository;
    }


    #[Route('/', name: 'app_purchases_index', methods: ['GET'])]
    public function index(PurchasesRepository $purchasesRepository): Response
    {
        $queryBuilder = $purchasesRepository->createQueryBuilder('e');
        $queryBuilder->orderBy('e.id', 'DESC');
        $sortedEntities = $queryBuilder->getQuery()->getResult();

        return $this->render('purchases/index.html.twig', [
            'purchases' => $sortedEntities,
        ]);
    }

    #[Route('/new', name: 'app_purchases_new', methods: ['GET', 'POST'])]
    public function new(Request $request, PurchasesRepository $purchasesRepository): Response
    {
        $purchases = new Purchases();
        $purchasesReference = date('hsdmy');
        $currentDate = date('Y-m-d');
        $purchases->setReference($purchasesReference);

        $data = $request->request->all();

        $form = $this->createForm(PurchasesType::class, $purchases,["edit" => false]);
        $form->handleRequest($request);
        $productListToManage = $data["dataSalesProducts"] ?? [];

        if ($form->isSubmitted() && $form->isValid()) {
            $dateString = $data["purchaseDate"];
            $format = 'Y-m-d';
            $dateTime = DateTimeImmutable::createFromFormat($format, $dateString);
            $purchases->setCreatedAt($dateTime);
            $purchases->setAmoutTotalHt(0);
            $purchases->setAmountTotalTtc(0);
            $purchases->setUpdatedAt(new \DateTimeImmutable());
            $purchases->setEnabled(1);
            $purchases->setDeleted(0);

            $purchasesRepository->save($purchases, true);

            return $this->redirectToRoute('app_purchase_edit', ["id" => $purchases->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('purchases/edit.html.twig', [
            'purchase' => $purchases,
            'currentDate' => $currentDate,
            'products' => [],
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_purchase_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request,Purchases $purchases ,PurchasesRepository $purchasesRepository): Response
    {
        $form = $this->createForm(PurchasesType::class, $purchases,["edit" => true]);
        $form->handleRequest($request);
        $data = $request->request->all();

        $productListToManage = $data["dataSalesProducts"] ?? [];
        $dataSalesProductsAmounts = $data["dataSalesProductsAmounts"] ?? [];
        $productList = $this->productsRepository->findBy(["purchases" => $purchases], ["id" => "ASC"]);
        if ($form->isSubmitted() && $form->isValid()) {
            $purchases->setUpdatedAt(new \DateTimeImmutable());
            $dateString = $data["purchaseDate"];
            $format = 'Y-m-d';

            $dateTime = DateTimeImmutable::createFromFormat($format, $dateString);
            $purchases->setCreatedAt($dateTime);
            $purchases->setAmoutTotalHt(floatval(str_replace(",",".",$dataSalesProductsAmounts["sumPriceTotalHtLabel"])));
            $purchases->setAmountTotalTtc(floatval(str_replace(",",".",$dataSalesProductsAmounts["sumPriceTotalTtcLabel"])));

            $purchasesRepository->save($purchases, true);

            //delete prev products
            $this->deleteProductsPurchase($purchases);
            //insertProducts
            $this->updateProductsFromRequest($purchases,$productListToManage,true);

            return $this->redirectToRoute('app_purchase_edit', ["id" => $purchases->getId()], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('purchases/edit.html.twig', [
            'purchase' => $purchases,
            'products' => $productList,
            'form' => $form,
        ]);
    }


    #[Route('/{id}/print', name: 'app_purchases_invoice', methods: ['GET', 'POST'])]
    public function print(Request $request,Purchases $purchases ,PurchasesRepository $purchasesRepository): Response
    {
        $paymentMode = [
            '3' => 'Espèce',
            '1' => 'Chèque',
            '2' => 'Effet',
            '4' => 'Autre',
        ];

        $lettersAmount = $this->NumberToLetter($purchases->getAmountTotalTtc());
        if ($lettersAmount[1] == "zÃ©ro"){
            $lettersAmount[1] = "";
        }else{
            $lettersAmount[1] = "ET". $lettersAmount[1]." centimes";
        }
        $data = [
            'imageSrc'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/images/crm/logo/logo_main.png'),
            'imageSrcNew'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/images/crm/logo/logo_main_new.png'),
            'purchases'         => $purchases,
            'purchasesProducts'         => $this->productsRepository->findBy(["purchases" => $purchases], ["id" => "ASC"]),
            'paymentMode'         => $paymentMode,
            'pdf_frame'  => $this->imageToBase64($this->getParameter('kernel.project_dir') . '/public/assets/images/crm/logo/print_frame.png'),
            'amountText'         => strtoupper(str_replace("é","e",utf8_decode("$lettersAmount[0] DIRHAMS"))). strtoupper(str_replace("é","e",utf8_decode($lettersAmount[1]))),
        ];
        $html =  $this->renderView('models/purchases/pdf.html.twig', $data);

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
        $fileNameDawnload = 'Liste Achat N°'.$purchases->getReference().'.pdf';
        $response->headers->set('Content-Disposition', 'attachment; filename='.$fileNameDawnload);

// Return the BinaryFileResponse
        return $response;
    }


    private function imageToBase64($path) {
        $path = $path;
        $type = pathinfo($path, PATHINFO_EXTENSION);
        $data = file_get_contents($path);
        $base64 = 'data:image/' . $type . ';base64,' . base64_encode($data);
        return $base64;
    }

    private function updateProductsFromRequest(Purchases $purchases,$productListToManage)
    {
        if (!empty($productListToManage) and is_array($productListToManage) and count($productListToManage)){
            foreach ($productListToManage as $productId => $productData){
                if(!str_contains($productId, "temp_")){
                    $productItem =  $this->productsRepository->find($productId);
                }else{
                    //max refs
                    $count =  $this->productsRepository->createQueryBuilder('e')
                        ->select('MAX(e.id)')
                        ->getQuery()
                        ->getSingleScalarResult();
                    $productItem = new Products();
                    $productItem->setRef("OD-".$count + 1000 + 1);

                }

                $productItem->setPurchases($purchases);
                $productItem->setEnabled(true);
                $productItem->setDeleted(false);
                $productItem->setUpdatedAt(new DateTimeImmutable());
                $productItem->setCreatedAt(new DateTimeImmutable());
                $productItem->setDistributor($purchases->getDistributor());

                $productItem->setName($productData["nameCommerciale"]);
                $productItem->setNameCommerciale($productData["nameCommerciale"]);
                $productItem->setPrice($productData["price"]);
                $productItem->setPriceHt($productData["priceHt"]);
                $productItem->setPriceReduced($productData["pricePurchase"]);
                $productItem->setQuantity($productData["quantity"]);
                $productItem->setBrand($productData["brand"]);
                $productItem->setDescription("");
                $productItem->setRate(0);
                $productItem->setRateType("default_taxe");
                $productItem->setPriceRevient($productData["priceRevient"]);

                $this->productsRepository->save($productItem,true);
            }
        }
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

    private function deleteProductsPurchase(Purchases $purchase): bool
    {
        $salesProducts = $this->productsRepository->findBy(["purchases" => $purchase]);
        foreach ($salesProducts as $product){
            $product->setPurchases(null);

            $this->productsRepository->save($product);
        }

        return true;
    }

}
