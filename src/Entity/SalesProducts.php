<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Repository\SalesProductsRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SalesProductsRepository::class)]
class SalesProducts
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'salesProducts', fetch: 'EAGER')]
    private ?Products $product = null;

    #[ORM\ManyToOne(inversedBy: 'salesProducts', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Sales $sale = null;

    #[ORM\Column]
    private ?int $quantity = null;

    #[ORM\Column]
    private ?float $taxe = null;

    #[ORM\Column]
    private ?string $taxeType = null;

    #[ORM\Column]
    private ?float $priceHt = null;

    #[ORM\Column]
    private ?float $priceTotalHt = null;

    #[ORM\Column]
    private ?float $priceTtc = null;

    #[ORM\Column]
    private ?float $priceTotalTtc = null;


    public function getId(): ?int
    {
        return $this->id;
    }

    public function getProduct(): ?Products
    {
        return $this->product;
    }

    public function setProduct(?Products $product): self
    {
        $this->product = $product;

        return $this;
    }

    public function getSale(): ?Sales
    {
        return $this->sale;
    }

    public function setSale(?Sales $sale): self
    {
        $this->sale = $sale;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }

    /**
     * @return float|null
     */
    public function getTaxe(): ?float
    {
        return $this->taxe;
    }

    /**
     * @param float|null $taxe
     */
    public function setTaxe(?float $taxe): void
    {
        $this->taxe = $taxe;
    }



    /**
     * @return float|null
     */
    public function getPriceHt(): ?float
    {
        return $this->priceHt;
    }

    /**
     * @param float|null $priceHt
     */
    public function setPriceHt(?float $priceHt): void
    {
        $this->priceHt = $priceHt;
    }

    /**
     * @return float|null
     */
    public function getPriceTotalHt(): ?float
    {
        return $this->priceTotalHt;
    }

    /**
     * @param float|null $priceTotalHt
     */
    public function setPriceTotalHt(?float $priceTotalHt): void
    {
        $this->priceTotalHt = $priceTotalHt;
    }

    /**
     * @return float|null
     */
    public function getPriceTtc(): ?float
    {
        return $this->priceTtc;
    }

    /**
     * @param float|null $priceTtc
     */
    public function setPriceTtc(?float $priceTtc): void
    {
        $this->priceTtc = $priceTtc;
    }

    /**
     * @return float|null
     */
    public function getPriceTotalTtc(): ?float
    {
        return $this->priceTotalTtc;
    }

    /**
     * @param float|null $priceTotalTtc
     */
    public function setPriceTotalTtc(?float $priceTotalTtc): void
    {
        $this->priceTotalTtc = $priceTotalTtc;
    }

    /**
     * @return string|null
     */
    public function getTaxeType(): ?string
    {
        return $this->taxeType;
    }

    /**
     * @param string|null $taxeType
     */
    public function setTaxeType(?string $taxeType): void
    {
        $this->taxeType = $taxeType;
    }




}
