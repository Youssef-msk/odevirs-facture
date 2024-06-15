<?php

namespace App\Controller;

use App\Entity\Customers;
use App\Entity\Distributor;
use App\Entity\Products;
use App\Form\ProductsType;
use App\Repository\ProductsRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/crm/products')]
class ProductsController extends AbstractController
{
    #[Route('/', name: 'app_products_index', methods: ['GET'])]
    public function index(ProductsRepository $productsRepository): Response
    {
        return $this->render('products/index.html.twig', [
            'products' => $productsRepository->findAllActivated(),
        ]);
    }

    /**
     * @throws \Doctrine\ORM\NonUniqueResultException
     * @throws \Doctrine\ORM\NoResultException
     */
    #[Route('/new', name: 'app_products_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ProductsRepository $productsRepository): Response
    {
        $product = new Products();

        //max refs
        $count = $productsRepository->createQueryBuilder('e')
            ->select('MAX(e.id)')
            ->getQuery()
            ->getSingleScalarResult();

        $product->setRef("OD-".$count + 1000 + 1);
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setUpdatedAt(new \DateTimeImmutable());
            $product->setEnabled(1);
            $product->setDeleted(0);

            if (!$product->getPicture()){
                $product->setPicture("default.png");
            }
            $productsRepository->save($product, true);

            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
        ]);
    }

    #[Route('/export', name: 'app_products_export', methods: ['GET'])]
    public function export(ProductsRepository $productsRepository)
    {
        $products = $productsRepository->findActivatedForExport();

        header('Content-Type: text/csv; charset=utf-8');
        header('Content-Disposition: attachment; filename="FooBarFileName_' . date('Ymd') . '.csv"');
        header("Pragma: no-cache");
        header("Expires: 0");
        $this->outputCSV($products);die;
    }

    #[Route('/{id}/edit', name: 'app_products_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        if ($product->isDeleted()){
            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }
        $form = $this->createForm(ProductsType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $product->setCreatedAt(new \DateTimeImmutable());
            $product->setUpdatedAt(new \DateTimeImmutable());

            $productsRepository->save($product, true);

            return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
        }

        $listProductsSales = [];
        $total = 0;
        foreach ($product->getSalesProducts()->getValues() as $item){
            $listProductsSales[] = $item->getSale();
            $total = $total + $item->getPriceTotalTtc();
        }

        return $this->renderForm('products/edit.html.twig', [
            'product' => $product,
            'form' => $form,
            'salesForProduct' => $listProductsSales,
            'totalSales' => $total,
        ]);
    }

    #[Route('/delete/{id}', name: 'app_products_delete', methods: ['POST','GET'])]
    public function delete(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token')) or $this->isCsrfTokenValid('delete'.$product->getId(), $request->query->get('_token'))) {
            $productsRepository->markDeleted($product, true);
        }

        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/disable/{id}', name: 'app_products_delete', methods: ['POST','GET'])]
    public function disable(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token')) or $this->isCsrfTokenValid('delete'.$product->getId(), $request->query->get('_token'))) {
            $productsRepository->disable($product, true);
        }

        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/enable/{id}', name: 'app_products_enable', methods: ['POST','GET'])]
    public function enable(Request $request, Products $product, ProductsRepository $productsRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$product->getId(), $request->request->get('_token')) or $this->isCsrfTokenValid('delete'.$product->getId(), $request->query->get('_token'))) {
            $productsRepository->disable($product, true);
        }

        return $this->redirectToRoute('app_products_index', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/search/product', name: 'app_products_search', methods: ['POST'])]
    public function searchProduct(Request $request, ProductsRepository $productsRepository)
    {
        $term = $request->request->get("query");
        $alreadySelectedProducts = $request->query->get("alreadySelectedProducts");

        $products = $productsRepository->findByTerm($term,$alreadySelectedProducts);

        return new JsonResponse($products);
    }

    #[Route('/reporting/product', name: 'app_products_reporting', methods: ['POST','GET'])]
    public function searchProductForReporting(Request $request, ProductsRepository $productsRepository)
    {
        $data = $request->query->all();
        $products = $productsRepository->findByCriteriaReporting($data);
        // Create a new Excel spreadsheet
        $spreadsheet = new Spreadsheet();
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Designation');
        $sheet->setCellValue('B1', 'Fournisseur');
        $sheet->setCellValue('C1', 'Marque');
        $sheet->setCellValue('D1', 'Quantity');
        $sheet->setCellValue('E1', 'Prix HT');
        $sheet->setCellValue('F1', 'Prix TTC');
        $sheet->setCellValue('G1', 'Taxe');
        $sheet->setCellValue('H1', 'Nombre BL');
        $sheet->setCellValue('I1', 'Quantity Vendu');
        $sheet->setCellValue('J1', 'Montant totale');
        $row = 2;
        /** @var Products $product*/
        foreach ($products as $product) {
            $quantityVendu = 0;
            $totalPriceVendu = 0;
            foreach ($product->getSalesProducts()->getValues() as $item){
                $totalPriceVendu = $totalPriceVendu + $item->getPriceTotalTtc();
                $quantityVendu = $quantityVendu + $item->getQuantity();
            }

            $sheet->setCellValue('A' . $row, $product->getName());
            $sheet->setCellValue('B' . $row, ($product->getDistributor() instanceof Distributor ? $product->getDistributor()->getCompany(): ""));
            $sheet->setCellValue('C' . $row, $product->getBrand());
            $sheet->setCellValue('D' . $row, $product->getQuantity());
            $sheet->setCellValue('E' . $row, $product->getPriceHt(). " DH");
            $sheet->setCellValue('F' . $row, $product->getPrice(). " DH");
            $sheet->setCellValue('G' . $row, $product->getRate(). " DH");
            $sheet->setCellValue('H' . $row, count($product->getSalesProducts()->getValues()));
            $sheet->setCellValue('I' . $row, $quantityVendu);
            $sheet->setCellValue('J' . $row, $totalPriceVendu. " DH");

            $row ++;
        }
        // Create a temporary file to save the spreadsheet
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);
        $response = new BinaryFileResponse($tempFile);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Liste des produits.xlsx'
        );
        $response->deleteFileAfterSend(true);

        return $response;
    }

    #[Route('/search/product/informations', name: 'app_products_infos', methods: ['POST','GET'])]
    public function getArticleDetailsById(Request $request, ProductsRepository $productsRepository)
    {
        $term = $request->query->get("id");
        $product = $productsRepository->articleDetailsById($term);

        if (is_array($product) and !empty($product)){
            return new JsonResponse($product[0]);
        }

        return new JsonResponse(["product not found"]);

    }

    public function outputCSV($data, $useKeysForHeaderRow = true) {
        if ($useKeysForHeaderRow) {
            array_unshift($data, array_keys(reset($data)));
        }

        $outputBuffer = fopen("php://output", 'w');
        foreach($data as $v) {
            fputcsv($outputBuffer, $v);
        }
        fclose($outputBuffer);
    }
}
