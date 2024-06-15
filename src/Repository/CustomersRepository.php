<?php

namespace App\Repository;

use App\Entity\Customers;
use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Customers>
 *
 * @method Customers|null find($id, $lockMode = null, $lockVersion = null)
 * @method Customers|null findOneBy(array $criteria, array $orderBy = null)
 * @method Customers[]    findAll()
 * @method Customers[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class CustomersRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Customers::class);
    }

    public function save(Customers $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function findAllActivated()
    {
        return $this->createQueryBuilder('c')
            ->andWhere('c.enabled = 1 and c.deleted = 0')
            ->getQuery()
            ->getResult()
            ;
    }

    public function remove(Customers $entity, bool $flush = false): void
    {
        $entity->setDeleted(1);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByTerm($term)
    {
        return $this->createQueryBuilder('c')
            ->select("c.company as name,c.id, c.ice")
            ->andWhere("(c.company like :term or c.ice like :term or c.phone like :term) and c.enabled = 1 and c.deleted = 0")
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('c.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult()
            ;
    }

//    /**
//     * @return Customers[] Returns an array of Customers objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('c.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?Customers
//    {
//        return $this->createQueryBuilder('c')
//            ->andWhere('c.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
