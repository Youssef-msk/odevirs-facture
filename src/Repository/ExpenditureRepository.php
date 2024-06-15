<?php

namespace App\Repository;

use App\Entity\Expenditure;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Expenditure>
 *
 * @method Expenditure|null find($id, $lockMode = null, $lockVersion = null)
 * @method Expenditure|null findOneBy(array $criteria, array $orderBy = null)
 * @method Expenditure[]    findAll()
 * @method Expenditure[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ExpenditureRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Expenditure::class);
    }

    public function save(Expenditure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Expenditure $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

//    /**
//     * @return Expenditure[] Returns an array of Expenditure objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('e.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Expenditure
//    {
//        return $this->createQueryBuilder('e')
//            ->andWhere('e.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }



    /**
     * @return Expenditure[] Returns an array of Products objects
     */
    public function findByCriteriaReporting($criteria): array
    {
        $queryBuilder = $this->createQueryBuilder('s');
        // Add more conditions as needed based on your criteria
        if (isset($criteria['dateFromFilter']) and $criteria['dateFromFilter'] != "") {
            $queryBuilder->andWhere('s.date >= :dateFromFilter')
                ->setParameter('dateFromFilter', $criteria['dateFromFilter']." 00:00:00");
        }

        if (isset($criteria['dateToFilter']) and $criteria['dateToFilter'] != "") {
            $queryBuilder->andWhere('s.date <= :dateToFilter')
                ->setParameter('dateToFilter', $criteria['dateToFilter']." 00:00:00");
        }

        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }
}
