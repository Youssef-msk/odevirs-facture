<?php

namespace App\Controller;

use App\Entity\Distributor;
use App\Entity\Products;
use App\Entity\Sales;
use App\Repository\CustomersRepository;
use App\Repository\SalesRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

class ReportingController extends AbstractController
{
    #[Route('/reporting/bl', name: 'app_reporting_bl')]
    public function index(Request $request,CustomersRepository $customersRepository,SalesRepository $repository): Response
    {
        $paymentMode = [
            '3' => 'Espèce',
            '1' => 'Chèque',
            '2' => 'Effet',
            '4' => 'Autre',
        ];

        if ($request->query->has("export") and $request->query->get("export")){
            $data = $request->query->all();
            /** @var Sales $result */
            $resultExport = $repository->findByCriteriaReporting($data);
            // Create a new Excel spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'N° facture');
            $sheet->setCellValue('B1', 'Client');
            $sheet->setCellValue('C1', 'Date');
            $sheet->setCellValue('D1', 'Mode paiement');
            $sheet->setCellValue('E1', 'Total HT');
            $sheet->setCellValue('F1', 'Total TVA');
            $sheet->setCellValue('G1', 'Total TTC');
            $row = 2;
            /** @var Sales $sales */
            foreach ($resultExport as $sales) {
                $sheet->setCellValue('A' . $row, $sales->getInvoiceNumber());
                $sheet->setCellValue('B' . $row, $sales->getCustomer()->getCompany());
                $sheet->setCellValue('C' . $row, $sales->getCreatedAt());
                $sheet->setCellValue('D' . $row, $paymentMode[$sales->getPaymentMode()]);
                $sheet->setCellValue('E' . $row, $sales->getAmoutTotalHt());
                $sheet->setCellValue('F' . $row, $sales->getAmountTotalTaxe());
                $sheet->setCellValue('G' . $row, $sales->getAmountTotalTtc());

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
                'Liste BL.xlsx'
            );
            $response->deleteFileAfterSend(true);

            return $response;
        }
        $data = $request->request->all();
        /** @var Sales $result */
        $result = $repository->findByCriteriaReporting($data);

        return $this->render('reporting/bl/index.html.twig', [
            'customers' => $customersRepository->findAllActivated(),
            'result' => $result,
            'paymentMode' => $paymentMode,
        ]);
    }

    #[Route('/crm/reporting/trimestrielle', name: 'reporting_trimestrielle')]
    public function reportingTrimestrielle(Request $request,SalesRepository $repository): Response
    {
        $year = "2023";
        $monthFrom = 10;
        $monthTo = 12;
        $res = $repository->findByCriteriaReportingTrimestrielle([
            "year" => $year,
            "monthFrom" => $monthFrom,
            "monthTo" => $monthTo,
        ]);


        return $this->render('reporting/bl/reporting_trimestrielle.html.twig');
    }
}
