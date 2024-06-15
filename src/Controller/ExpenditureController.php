<?php

namespace App\Controller;

use App\Entity\Expenditure;
use App\Form\ExpenditureType;
use App\Repository\ExpenditureRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/expenditure')]
class ExpenditureController extends AbstractController
{
    #[Route('/', name: 'app_expenditure_index', methods: ['GET','POST'])]
    public function index(Request $request,ExpenditureRepository $expenditureRepository): Response
    {
        $paymentType = [
            'Espèce'=> '3',
            'Chèque'=> '1',
            'Effet' => '2',
            'Autre' => '4',
        ];
        $paymentTypeKeys = array_flip($paymentType);
        $expenditureType = [
            1 => "",
            2 => "Dépenses de transport",
            3 => "Dépenses d'hébergement",
            4 => "Dépenses de repas et divertissements",
            5 => "Dépenses de fournitures de bureau",
            6 => "Dépenses de services publics",
            7 => "Dépenses de communication",
            8 => "Dépenses de marketing et publicité",
            9 => "Dépenses de voyage",
            10 => "Dépenses d'équipement",
            11 => "Dépenses d'entretien et de réparation",
            12 => "Dépenses de services professionnels",
            13 => "Dépenses d'assurance",
            14 => "Dépenses de taxes et frais de licence",
            15 => "Dépenses de formation et de développement",
            16 => "Dépenses diverses",
            17 => "Autres",
        ];

        if ($request->query->has("export") and $request->query->get("export")){
            $data = $request->query->all();

            $resultExport = $expenditureRepository->findByCriteriaReporting($data);

            // Create a new Excel spreadsheet
            $spreadsheet = new Spreadsheet();
            $sheet = $spreadsheet->getActiveSheet();
            $sheet->setCellValue('A1', 'Réference');
            $sheet->setCellValue('B1', 'Date');
            $sheet->setCellValue('C1', 'Type dépense');
            $sheet->setCellValue('D1', 'Description');
            $sheet->setCellValue('E1', 'Mode paiement');
            $sheet->setCellValue('F1', 'N° (CHÈQUE/EFFET)');
            $sheet->setCellValue('G1', 'N° facture externe');
            $sheet->setCellValue('H1', 'Montant (DH)');
            $row = 2;
            /** @var Expenditure $Expenditure*/
            foreach ($resultExport as $Expenditure) {
                $dateString = $Expenditure->getDate();
                $date = new \DateTime($dateString);
                $formattedDate = $date->format('d/m/Y');
                $sheet->setCellValue('A' . $row, $Expenditure->getRef());
                $sheet->setCellValue('B' . $row, $formattedDate);
                $sheet->setCellValue('C' . $row, $expenditureType[$Expenditure->getType()]);
                $sheet->setCellValue('D' . $row, $Expenditure->getDescription());
                $sheet->setCellValue('E' . $row, $paymentTypeKeys[$Expenditure->getPaymentMode()]);
                $sheet->setCellValue('F' . $row, $Expenditure->getPaymentReference());
                $sheet->setCellValue('G' . $row, $Expenditure->getInvoiceReference());
                $sheet->setCellValue('H' . $row, $Expenditure->getPrice());

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
                'Liste Dépenses.xlsx'
            );
            $response->deleteFileAfterSend(true);

            return $response;
        }

        $data = $request->request->all();
        return $this->render('expenditure/index.html.twig', [
            'expenditures' => $expenditureRepository->findByCriteriaReporting($data),
            'expenditureType' => $expenditureType,
            'paymentType' => array_flip($paymentType)
        ]);
    }

    #[Route('/new', name: 'app_expenditure_new', methods: ['GET', 'POST'])]
    public function new(Request $request, ExpenditureRepository $expenditureRepository): Response
    {
        $expenditure = new Expenditure();
        $form = $this->createForm(ExpenditureType::class, $expenditure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $expenditureRepository->save($expenditure, true);

            return $this->redirectToRoute('app_expenditure_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('expenditure/expenditure.html.twig', [
            'expenditure' => $expenditure,
            'form' => $form
        ]);
    }

    #[Route('/{id}', name: 'app_expenditure_show', methods: ['GET'])]
    public function show(Expenditure $expenditure): Response
    {
        return $this->render('expenditure/show.html.twig', [
            'expenditure' => $expenditure,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_expenditure_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Expenditure $expenditure, ExpenditureRepository $expenditureRepository): Response
    {
        $form = $this->createForm(ExpenditureType::class, $expenditure);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $expenditureRepository->save($expenditure, true);

            return $this->redirectToRoute('app_expenditure_index', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('expenditure/expenditure.html.twig', [
            'expenditure' => $expenditure,
            'form' => $form,
        ]);
    }

    #[Route('/{id}', name: 'app_expenditure_delete', methods: ['POST'])]
    public function delete(Request $request, Expenditure $expenditure, ExpenditureRepository $expenditureRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$expenditure->getId(), $request->request->get('_token'))) {
            $expenditureRepository->remove($expenditure, true);
        }

        return $this->redirectToRoute('app_expenditure_index', [], Response::HTTP_SEE_OTHER);
    }
}
