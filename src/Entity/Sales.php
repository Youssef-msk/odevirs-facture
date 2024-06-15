<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\statusTrait;
use App\Repository\SalesRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalesRepository::class)]
class Sales
{

    use CreatedAtTrait;
    use statusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer = null;

    #[ORM\OneToMany(mappedBy: 'sale', targetEntity: SalesProducts::class)]
    private Collection $salesProducts;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;

    #[ORM\OneToOne(inversedBy: 'sales', cascade: ['persist', 'remove'])]
    private ?BlHead $BlHead = null;

    #[ORM\ManyToOne(inversedBy: 'sales')]
    #[ORM\JoinColumn(nullable: true)]
    private ?SalesStatus $status = null;

    #[ORM\Column]
    private ?float $amoutTotalHt = null;

    #[ORM\Column]
    private ?float $amountTotalTaxe = null;

    #[ORM\Column]
    private ?float $amountTotalTtc = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentMode = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $paymentReference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $bonCommande = null;


    #[ORM\Column(length: 255, nullable: true)]
    private ?string $echeance = null;

    #[ORM\Column(length: 255)]
    private ?int $invoiceNumber = null;

    #[ORM\Column]
    private ?bool $generatedInvoice = null;


    public function __construct()
    {
        $this->salesProducts = new ArrayCollection();
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

    /**
     * @return Collection<int, SalesProducts>
     */
    public function getSalesProducts(): Collection
    {
        return $this->salesProducts;
    }

    public function addSalesProduct(SalesProducts $salesProduct): self
    {
        if (!$this->salesProducts->contains($salesProduct)) {
            $this->salesProducts->add($salesProduct);
            $salesProduct->setSale($this);
        }

        return $this;
    }

    public function removeSalesProduct(SalesProducts $salesProduct): self
    {
        if ($this->salesProducts->removeElement($salesProduct)) {
            // set the owning side to null (unless already changed)
            if ($salesProduct->getSale() === $this) {
                $salesProduct->setSale(null);
            }
        }

        return $this;
    }

    public function removeAllSalesProduct()
    {
        $this->salesProducts->clear();
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

    public function getBlHead(): ?BlHead
    {
        return $this->BlHead;
    }

    public function setBlHead(?BlHead $BlHead): self
    {
        $this->BlHead = $BlHead;

        return $this;
    }

    public function getStatus(): ?SalesStatus
    {
        return $this->status;
    }

    public function setStatus(?SalesStatus $status): self
    {
        $this->status = $status;

        return $this;
    }

    public function getAmoutTotalHt(): ?float
    {
        return $this->amoutTotalHt;
    }

    public function setAmoutTotalHt(float $amoutTotalHt): self
    {
        $this->amoutTotalHt = $amoutTotalHt;

        return $this;
    }

    public function getAmountTotalTaxe(): ?float
    {
        return $this->amountTotalTaxe;
    }

    public function setAmountTotalTaxe(float $amountTotalTaxe): self
    {
        $this->amountTotalTaxe = $amountTotalTaxe;

        return $this;
    }

    public function getAmountTotalTtc(): ?float
    {
        return $this->amountTotalTtc;
    }

    public function setAmountTotalTtc(float $amountTotalTtc): self
    {
        $this->amountTotalTtc = $amountTotalTtc;

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

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(?string $paymentReference): self
    {
        $this->paymentReference = $paymentReference;

        return $this;
    }

    public function getBonCommande(): ?string
    {
        return $this->bonCommande;
    }

    public function setBonCommande(?string $bonCommande): self
    {
        $this->bonCommande = $bonCommande;

        return $this;
    }

    public function getEcheance()
    {
        return $this->echeance;
    }

    public function setEcheance( $echeance): self
    {
        $this->echeance = $echeance;

        return $this;
    }

    public function getInvoiceNumber(): ?int
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(int $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function isGeneratedInvoice(): ?bool
    {
        return $this->generatedInvoice;
    }

    public function setGeneratedInvoice(bool $generatedInvoice): self
    {
        $this->generatedInvoice = $generatedInvoice;

        return $this;
    }


}
