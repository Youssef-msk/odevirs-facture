<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\statusTrait;
use App\Repository\DeliveryNoteRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: DeliveryNoteRepository::class)]
class DeliveryNote
{
    use CreatedAtTrait;
    use statusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(fetch: 'EAGER', inversedBy: 'deliveryNote')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer = null;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentMode = null;

    #[ORM\Column]
    private ?bool $generatedSale = null;

    #[ORM\Column]
    private ?float $amoutTotalHt = null;

    #[ORM\Column]
    private ?float $amountTotalTaxe = null;

    #[ORM\Column]
    private ?float $amountTotalTtc = null;

    #[ORM\OneToMany(mappedBy: 'deliveryNote', targetEntity: DeliveryNoteProducts::class)]
    private Collection $deliveryNoteProducts;



    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentReference = null;

    #[ORM\OneToOne(mappedBy: 'deliveryNote', cascade: ['persist', 'remove'])]
    private ?Sales $sales = null;

    public function __construct()
    {
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCustomer(): ?Customers
    {
        return $this->customer;
    }

    public function setCustomer(?Customers $customer): self
    {
        $this->customer = $customer;

        return $this;
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

    public function setComment(string $comment): self
    {
        $this->comment = $comment;

        return $this;
    }



    public function getPaymentMode(): ?string
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(?string $paymentMode): self
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    /**
     * @return bool|null
     */
    public function getGeneratedSale(): ?bool
    {
        return $this->generatedSale;
    }

    /**
     * @param bool|null $generatedSale
     */
    public function setGeneratedSale(?bool $generatedSale): void
    {
        $this->generatedSale = $generatedSale;
    }

    /**
     * @return Collection
     */
    public function getDeliveryNoteProducts(): Collection
    {
        return $this->deliveryNoteProducts;
    }

    /**
     * @param Collection $deliveryNoteProducts
     */
    public function setDeliveryNoteProducts(Collection $deliveryNoteProducts): void
    {
        $this->deliveryNoteProducts = $deliveryNoteProducts;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $paymentReference): void
    {
        $this->paymentReference = $paymentReference;
    }

    public function getAmoutTotalHt(): ?float
    {
        return $this->amoutTotalHt;
    }

    public function setAmoutTotalHt(?float $amoutTotalHt): void
    {
        $this->amoutTotalHt = $amoutTotalHt;
    }

    public function getAmountTotalTaxe(): ?float
    {
        return $this->amountTotalTaxe;
    }

    public function setAmountTotalTaxe(?float $amountTotalTaxe): void
    {
        $this->amountTotalTaxe = $amountTotalTaxe;
    }

    public function getAmountTotalTtc(): ?float
    {
        return $this->amountTotalTtc;
    }

    public function setAmountTotalTtc(?float $amountTotalTtc): void
    {
        $this->amountTotalTtc = $amountTotalTtc;
    }

    public function getSales(): ?Sales
    {
        return $this->sales;
    }

    public function setSales(?Sales $sales): static
    {
        // unset the owning side of the relation if necessary
        if ($sales === null && $this->sales !== null) {
            $this->sales->setDeliveryNote(null);
        }

        // set the owning side of the relation if necessary
        if ($sales !== null && $sales->getDeliveryNote() !== $this) {
            $sales->setDeliveryNote($this);
        }

        $this->sales = $sales;

        return $this;
    }



}
