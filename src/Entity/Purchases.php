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

    #[ORM\Column(length: 300)]
    private ?string $invoiceNumber = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentMode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentReference = null;

    #[ORM\OneToMany(mappedBy: 'purchases', targetEntity: Products::class)]
    private Collection $products;

    #[ORM\Column]
    private ?float $amoutTotalHt = null;

    #[ORM\Column]
    private ?float $amountTotalTtc = null;


    public function __construct()
    {
        $this->products = new ArrayCollection();
    }



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

    /**
     * @return Collection<int, Products>
     */
    public function getProducts(): Collection
    {
        return $this->products;
    }

    public function addProduct(Products $product): static
    {
        if (!$this->products->contains($product)) {
            $this->products->add($product);
            $product->setPurchases($this);
        }

        return $this;
    }

    public function removeProduct(Products $product): static
    {
        if ($this->products->removeElement($product)) {
            // set the owning side to null (unless already changed)
            if ($product->getPurchases() === $this) {
                $product->setPurchases(null);
            }
        }

        return $this;
    }

    public function getAmoutTotalHt(): ?float
    {
        return $this->amoutTotalHt;
    }

    public function setAmoutTotalHt(?float $amoutTotalHt): void
    {
        $this->amoutTotalHt = $amoutTotalHt;
    }

    public function getAmountTotalTtc(): ?float
    {
        return $this->amountTotalTtc;
    }

    public function setAmountTotalTtc(?float $amountTotalTtc): void
    {
        $this->amountTotalTtc = $amountTotalTtc;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): void
    {
        $this->invoiceNumber = $invoiceNumber;
    }




}
