<?php

namespace App\Repository;

use App\Entity\Products;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\ORM\NonUniqueResultException;
use Doctrine\ORM\NoResultException;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Products>
 *
 * @method Products|null find($id, $lockMode = null, $lockVersion = null)
 * @method Products|null findOneBy(array $criteria, array $orderBy = null)
 * @method Products[]    findAll()
 * @method Products[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class ProductsRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Products::class);
    }

    public function save(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Products $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


    public function disable(Products $entity, bool $flush = false): void
    {
        $entity->setEnabled(0);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function markDeleted(Products $entity, bool $flush = false): void
    {
        $entity->setDeleted(1);
        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByTerm($term,$alreadySelectedProducts)
    {
        $alreadySelectedProducts = explode(",",$alreadySelectedProducts);
        $qb = $this->createQueryBuilder('p');
        return $qb
            ->select("p.name as name,p.id,p.picture as picture,p.nameCommerciale, p.ref,p.rate,p.price,p.priceHt,p.brand")
            ->where($qb->expr()->notIn('p.id', $alreadySelectedProducts))
            ->andWhere("(p.nameCommerciale like :term or p.name like :term or p.ref like :term) and p.enabled = 1 and p.deleted = 0 and  p.quantity > 0")
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(15)
            ->getQuery()
            ->getArrayResult()
        ;
    }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByTermObjects($term,$alreadySelectedProducts)
    {
        $alreadySelectedProducts = explode(",",$alreadySelectedProducts);
        $qb = $this->createQueryBuilder('p');
        return $qb
            ->where($qb->expr()->notIn('p.id', $alreadySelectedProducts))
            ->andWhere("(p.nameCommerciale like :term or p.name like :term or p.ref like :term) and p.enabled = 1 and p.deleted = 0 and  p.quantity > 0")
            ->setParameter('term', '%'.$term.'%')
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(15)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByOrder($alreadySelectedProducts)
    {
        $alreadySelectedProducts = explode(",",$alreadySelectedProducts);
        $qb = $this->createQueryBuilder('p');
        return $qb
            ->where($qb->expr()->notIn('p.id', $alreadySelectedProducts))
            ->orderBy('p.id', 'DESC')
            ->setMaxResults(10)
            ->getQuery()
            ->getResult()
            ;
    }

    /**
     * @return Products[] Returns an array of Products objects
     */
    public function findByCriteriaReporting($criteria)
    {
        $queryBuilder = $this->createQueryBuilder('p');
        // Add more conditions as needed based on your criteria
        $query = $queryBuilder->getQuery();
        return $query->getResult();
    }

    public function articleDetailsById($id)
    {
        try {
            return $this->createQueryBuilder('p')
                ->andWhere("p.id = :id")
                ->setParameter('id', $id)
                ->orderBy('p.id', 'DESC')
                ->getQuery()
                ->getArrayResult();
        } catch (NoResultException|NonUniqueResultException $e) {
            return null;
        }
    }

    public function findAllActivated()
    {
        return $this->createQueryBuilder('p')
            ->andWhere('p.enabled = 1 and p.deleted = 0')
            ->getQuery()
            ->getResult()
        ;
    }

    public function findActivatedForExport()
    {
        return $this->createQueryBuilder('p')
            ->select("p.name as designation,p.id,p.picture as picture,p.nameCommerciale, p.ref")
            ->orderBy('p.id', 'DESC')
            ->getQuery()
            ->getArrayResult()
            ;
    }
}
