<?php

namespace App\Repository;

use App\Entity\Products;
use App\Entity\DeliveryNote;
use Doctrine\Bundle\DoctrineBundle\Repository\ServiceEntityRepository;
use Doctrine\Persistence\ManagerRegistry;

/**
 * @extends ServiceEntityRepository<DeliveryNote>
 *
 * @method DeliveryNote|null find($id, $lockMode = null, $lockVersion = null)
 * @method DeliveryNote|null findOneBy(array $criteria, array $orderBy = null)
 * @method DeliveryNote[]    findAll()
 * @method DeliveryNote[]    findBy(array $criteria, array $orderBy = null, $limit = null, $offset = null)
 */
class DeliveryNoteRepository extends ServiceEntityRepository
{
    public function __construct(ManagerRegistry $registry)
    {
        parent::__construct($registry, DeliveryNote::class);
    }

    public function save(DeliveryNote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->persist($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }

    public function remove(DeliveryNote $entity, bool $flush = false): void
    {
        $this->getEntityManager()->remove($entity);

        if ($flush) {
            $this->getEntityManager()->flush();
        }
    }


}
