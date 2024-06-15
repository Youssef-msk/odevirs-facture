<?php

namespace App\Controller;

use App\Repository\ExpenditureRepository;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/crm/estimate')]
class EstimateController extends AbstractController
{

    #[Route('/edit', name: 'app_estimate_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, ExpenditureRepository $expenditureRepository): Response
    {
        return $this->renderForm('estimate/expenditure.html.twig');
    }
}
