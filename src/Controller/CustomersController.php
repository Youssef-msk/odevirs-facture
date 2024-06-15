<?php

namespace App\Controller;

use App\Entity\Customers;
use App\Form\CustomersType;
use App\Repository\CustomersRepository;
use App\Repository\ProductsRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use PhpOffice\PhpSpreadsheet\Spreadsheet;
use PhpOffice\PhpSpreadsheet\Writer\Xlsx;
use Symfony\Component\HttpFoundation\BinaryFileResponse;
use Symfony\Component\HttpFoundation\ResponseHeaderBag;

#[Route('/crm/customers')]
class CustomersController extends AbstractController
{
    #[Route('/', name: 'app_customers', methods: ['GET'])]
    public function index(CustomersRepository $customersRepository): Response
    {
        return $this->render('customers/index.html.twig', [
            'customers' => $customersRepository->findAllActivated(),
        ]);
    }

    #[Route('/new', name: 'app_customers_new', methods: ['GET', 'POST'])]
    public function new(Request $request, CustomersRepository $customersRepository): Response
    {
        $customer = new Customers();
        $form = $this->createForm(CustomersType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setCreatedAt(new \DateTimeImmutable());
            $customer->setUpdatedAt(new \DateTimeImmutable());
            $customer->setEnabled(1);
            $customer->setDeleted(0);
            $customersRepository->save($customer, true);
            $this->addFlash("success", "Nouveau client ajouté avec succes");
            return $this->redirectToRoute('app_customers', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('customers/new.html.twig', [
            'customer' => $customer,
            'form' => $form,
        ]);
    }

    #[Route('/{id}/edit', name: 'app_customers_edit', methods: ['GET', 'POST'])]
    public function edit(Request $request, Customers $customer, CustomersRepository $customersRepository): Response
    {
        $form = $this->createForm(CustomersType::class, $customer);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $customer->setUpdatedAt(new \DateTimeImmutable());
            $customersRepository->save($customer, true);

            $this->addFlash("success", "Client modifié avec succes");

            return $this->redirectToRoute('app_customers', [], Response::HTTP_SEE_OTHER);
        }

        return $this->renderForm('customers/edit.html.twig', [
            'customer' => $customer,
            'salesCustomer' => $customer->getSales(),
            'form' => $form,
        ]);
    }


    #[Route('/{id}/show', name: 'app_customers_show', methods: ['GET', 'POST'])]
    public function show(Customers $customer): Response
    {
        return $this->renderForm('customers/show.html.twig', [
            'customer' => $customer,
            'salesCustomer' => $customer->getSales()
        ]);
    }

    #[Route('/{id}', name: 'app_customers_delete', methods: ['POST','GET'])]
    public function delete(Request $request, Customers $customer, CustomersRepository $customersRepository): Response
    {
        if ($this->isCsrfTokenValid('delete'.$customer->getId(), $request->request->get('_token')) or $this->isCsrfTokenValid('delete'.$customer->getId(), $request->query->get('_token'))) {
            $customersRepository->remove($customer, true);
            $this->addFlash("success", "Client supprimé avec succes");
        }

        return $this->redirectToRoute('app_customers', [], Response::HTTP_SEE_OTHER);
    }

    #[Route('/infos/{id}', name: 'app_customers_infos', methods: ['POST','GET'])]
    public function customerInfos(Request $request, Customers $customer, CustomersRepository $customersRepository): Response
    {
       return new JsonResponse([
           'address' => $customer->getAdresse(),
           'phone' => $customer->getPhone(),
           'email' => $customer->getEmail(),
           'ice' => $customer->getIce(),
           'zipcode' => $customer->getZipcode(),
       ]);
    }

    #[Route('/search/customers', name: 'app_customers_search', methods: ['POST','GET'])]
    public function searchCustomers(Request $request, CustomersRepository $customersRepository)
    {
        $term = $request->request->get("query");
        $products = $customersRepository->findByTerm($term);

        return new JsonResponse($products);
    }

    #[Route('/add/customers/sales', name: 'app_customers_sales_add', methods: ['POST','GET'])]
    public function addSalesCutomers(Request $request, CustomersRepository $customersRepository)
    {
        $customerData = $request->request->all("newCustomer");
        if(!$customerData["company"] or trim($customerData["company"]) == ""){
            return new JsonResponse("nok_rs");
        }
        $customer = new Customers();
        $customer->setCompany($customerData["company"]);
        $customer->setAdresse($customerData["adresse"]);
        $customer->setPhone($customerData["tel"]);
        $customer->setIce($customerData["ice"]);
        $customer->setEnabled(1);
        $customer->setDeleted(0);
        $customer->setUpdatedAt(new \DateTimeImmutable());
        $customer->setCreatedAt(new \DateTimeImmutable());

        $customersRepository->save($customer,true);
        $customerData["id"] = $customer->getId();
        return new JsonResponse($customerData);
    }


    #[Route('/export/all', name: 'app_customers_exportallcustomers', methods: ['GET'])]
    public function exportAllCustomers(CustomersRepository $customersRepository): Response
    {
        // Fetch data from the database (using Doctrine ORM or any other database library)
        $customers = $customersRepository->findAllActivated();

        // Create a new Excel spreadsheet
        $spreadsheet = new Spreadsheet();

        // Add data to the spreadsheet
        $sheet = $spreadsheet->getActiveSheet();
        $sheet->setCellValue('A1', 'Raison sociale');
        $sheet->setCellValue('B1', 'ICE');
        $sheet->setCellValue('C1', 'Téléphone');
        $sheet->setCellValue('D1', 'E-mail');
        $sheet->setCellValue('E1', 'Adresse');
        $sheet->setCellValue('F1', 'Code postale');

        // Loop through the data and populate the spreadsheet
        $row = 2;
        /** @var Customers $customer */
        foreach ($customers as $customer) {
            $sheet->setCellValue('A' . $row, $customer->getCompany());
            $sheet->setCellValue('B' . $row, $customer->getIce());
            $sheet->setCellValue('C' . $row, $customer->getPhone());
            $sheet->setCellValue('D' . $row, $customer->getEmail());
            $sheet->setCellValue('E' . $row, $customer->getAdresse(). " - ".$customer->getVille());
            $sheet->setCellValue('F' . $row, $customer->getZipcode());
            // Add more columns as needed
            $row++;
        }

        // Create a temporary file to save the spreadsheet
        $tempFile = tempnam(sys_get_temp_dir(), 'excel');

        // Save the spreadsheet to the temporary file
        $writer = new Xlsx($spreadsheet);
        $writer->save($tempFile);

        // Prepare the response
        $response = new BinaryFileResponse($tempFile);
        $response->headers->set('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $response->setContentDisposition(
            ResponseHeaderBag::DISPOSITION_ATTACHMENT,
            'Liste des clients.xlsx'
        );

        // Delete the temporary file after sending the response
        $response->deleteFileAfterSend(true);

        return $response;
    }

}
