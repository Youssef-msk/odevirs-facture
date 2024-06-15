<?php

namespace App\Repository;

use App\Entity\Products;
use App\Entity\Sales;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Sales>
 *
 * @method Sales|null find($id, $lockMode = null, $lockVersion = null)
 * @method Sales|null findOneBy(array $criteria, array $orderBy = null)
 * @method Sales[]    findAll()
 * @method Sales[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class SalesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Sales::class);
    }

    public function save(Sales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Sales $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByCriteriaReporting($criteria)
    {
        $queryBuilder = $this->createQueryBuilder('s');
        $queryBuilder->andWhere('s.generatedInvoice = 1');

        // Add more conditions as needed based on your criteria
        if (isset($criteria['customerFilter']) and $criteria['customerFilter'] != "") {
            $queryBuilder->andWhere('s.customer = :customerFilter')
                ->setParameter('customerFilter', $criteria['customerFilter']);
        }

        if (isset($criteria['dateFromFilter']) and $criteria['dateFromFilter'] != "") {
            $queryBuilder->andWhere('s.created_at >= :dateFromFilter')
                ->setParameter('dateFromFilter', $criteria['dateFromFilter']." 00:00:00");
        }

        if (isset($criteria['dateToFilter']) and $criteria['dateToFilter'] != "") {
            $queryBuilder->andWhere('s.created_at <= :dateToFilter')
                ->setParameter('dateToFilter', $criteria['dateToFilter']." 00:00:00");
        }

        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }


    public function findByCriteriaReportingTrimestrielle($criteria)
    {
        $entityManager = $this->getEntityManager();
        $connection = $entityManager->getConnection();

        $sqlRecap = '
            SELECT YEAR(created_at) AS year, 
                   SUM(amount_total_ttc) AS total_amount_ttc, 
                   SUM(amout_total_ht) AS total_amount_ht, 
                   SUM(amount_total_taxe) AS total_amount_taxe
            FROM sales
            WHERE YEAR(created_at) = :year and  MONTH(created_at) >= :monthFrom and MONTH(created_at) <= :monthTo
            GROUP BY YEAR(created_at)
        ';

        $statement = $connection->prepare($sqlRecap);
        $statement->bindValue('year', $criteria["year"]);
        $statement->bindValue('monthFrom', $criteria["monthFrom"]);
        $statement->bindValue('monthTo', $criteria["monthTo"]);
        $resultsRecap = $statement->executeQuery()->fetchAllAssociative();


        $sqlSales = '
            SELECT *
            FROM sales
            WHERE YEAR(created_at) = :year and  MONTH(created_at) >= :monthFrom and MONTH(created_at) <= :monthTo
        ';

        $statement = $connection->prepare($sqlSales);
        $statement->bindValue('year', $criteria["year"]);
        $statement->bindValue('monthFrom', $criteria["monthFrom"]);
        $statement->bindValue('monthTo', $criteria["monthTo"]);
        $resultsSales = $statement->executeQuery()->fetchAllAssociative();


        return [
            "criteria" => $criteria,
            "recap" => $resultsRecap[0] ?? false,
            "sales" => $resultsSales
        ];
    }

//    /**
//     * @return Sales[] Returns an array of Sales objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('s.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Sales
//    {
//        return $this->createQueryBuilder('s')
//            ->andWhere('s.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
