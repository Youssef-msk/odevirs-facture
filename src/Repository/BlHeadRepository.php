<?php

namespace App\Repository;

use App\Entity\BlHead;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<BlHead>
 *
 * @method BlHead|null find($id, $lockMode = null, $lockVersion = null)
 * @method BlHead|null findOneBy(array $criteria, array $orderBy = null)
 * @method BlHead[]    findAll()
 * @method BlHead[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class BlHeadRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, BlHead::class);
    }

    public function save(BlHead $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(BlHead $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByTerm($term)
    {
        return $this->createQueryBuilder('p')
            ->select("p.name as name,p.id,p.picture as picture,p.nameCommerciale, p.ref")
            ->andWhere("(p.nameCommerciale like :term or p.name like :term or p.ref like :term) and p.enabled = 1 and p.deleted = 0")
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getArrayResult()
            ;
    }
//    /**
//     * @return BlHead[] Returns an array of BlHead objects
//     */
//    public function findByExampleField($value): array
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->orderBy('b.id', 'ASC')
//            ->setMaxResults(10)
//            ->getQuery()
//            ->getResult()
//        ;
//    }

//    public function findOneBySomeField($value): ?BlHead
//    {
//        return $this->createQueryBuilder('b')
//            ->andWhere('b.exampleField = :val')
//            ->setParameter('val', $value)
//            ->getQuery()
//            ->getOneOrNullResult()
//        ;
//    }
}
