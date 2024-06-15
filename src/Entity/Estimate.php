<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\statusTrait;
use App\Repository\EstimateRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: EstimateRepository::class)]
class Estimate
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

    #[ORM\OneToMany(mappedBy: 'estimate', targetEntity: EstimateProducts::class)]
    private Collection $EstimateProducts;

    #[ORM\Column(length: 50)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $comment = null;


    #[ORM\Column]
    private ?float $amoutTotalHt = null;

    #[ORM\Column]
    private ?float $amountTotalTaxe = null;

    #[ORM\Column]
    private ?float $amountTotalTtc = null;


    public function __construct()
    {
        $this->EstimateProducts = new ArrayCollection();
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
     * @return Collection<int, EstimateProducts>
     */
    public function getEstimateProducts(): Collection
    {
        return $this->EstimateProducts;
    }

    public function addSalesProduct(EstimateProducts $salesProduct): self
    {
        if (!$this->EstimateProducts->contains($salesProduct)) {
            $this->EstimateProducts->add($salesProduct);
            $salesProduct->setSale($this);
        }

        return $this;
    }

    public function removeSalesProduct(EstimateProducts $salesProduct): self
    {
        if ($this->EstimateProducts->removeElement($salesProduct)) {
            // set the owning side to null (unless already changed)
            if ($salesProduct->getSale() === $this) {
                $salesProduct->setSale(null);
            }
        }

        return $this;
    }

    public function removeAllSalesProduct()
    {
        $this->EstimateProducts->clear();
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

}
