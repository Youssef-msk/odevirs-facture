<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\statusTrait;
use App\Repository\PurchasesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: PurchasesRepository::class)]
class Purchases
{

    use CreatedAtTrait;
    use statusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'purchases')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Distributor $distributor = null;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentMode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentReference = null;



    public function getId(): ?int
    {
        return $this->id;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): self
    {
        $this->reference = $reference;

        return $this;
    }

    public function getComment(): ?string
    {
        return $this->comment;
    }

    public function getPaymentMode(): ?string
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(?string $paymentMode): void
    {
        $this->paymentMode = $paymentMode;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $paymentReference): void
    {
        $this->paymentReference = $paymentReference;
    }

    public function getDistributor(): ?Distributor
    {
        return $this->distributor;
    }

    public function setDistributor(?Distributor $distributor): void
    {
        $this->distributor = $distributor;
    }

    public function setId(?int $id): void
    {
        $this->id = $id;
    }

    public function setComment(?string $comment): void
    {
        $this->comment = $comment;
    }




}
