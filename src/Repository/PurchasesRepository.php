<?php

namespace App\Repository;

use App\Entity\Products;
use App\Entity\Purchases;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<Purchases>
 *
 * @method Purchases|null find($id, $lockMode = null, $lockVersion = null)
 * @method Purchases|null findOneBy(array $criteria, array $orderBy = null)
 * @method Purchases[]    findAll()
 * @method Purchases[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class PurchasesRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, Purchases::class);
    }

    public function save(Purchases $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(Purchases $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }
}
