<?php

namespace App\Entity;

use App\Repository\ExpenditureRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ExpenditureRepository::class)]
class Expenditure
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $date = null;

    #[ORM\Column]
    private ?int $type = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $otherType = null;

    #[ORM\Column]
    private ?float $price = null;

    #[ORM\Column]
    private ?bool $hasInvoice = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $invoiceNumber = null;

    #[ORM\Column]
    private ?int $paymentMode = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $paymentReference = null;

    #[ORM\Column(length: 255)]
    private ?string $description = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $invoiceReference = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(string $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getType(): ?int
    {
        return $this->type;
    }

    public function setType(int $type): self
    {
        $this->type = $type;

        return $this;
    }

    public function getOtherType(): ?string
    {
        return $this->otherType;
    }

    public function setOtherType(string $otherType): self
    {
        $this->otherType = $otherType;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function isHasInvoice(): ?bool
    {
        return $this->hasInvoice;
    }

    public function setHasInvoice(bool $hasInvoice): self
    {
        $this->hasInvoice = $hasInvoice;

        return $this;
    }

    public function getInvoiceNumber(): ?string
    {
        return $this->invoiceNumber;
    }

    public function setInvoiceNumber(?string $invoiceNumber): self
    {
        $this->invoiceNumber = $invoiceNumber;

        return $this;
    }

    public function getPaymentMode(): ?int
    {
        return $this->paymentMode;
    }

    public function setPaymentMode(int $paymentMode): self
    {
        $this->paymentMode = $paymentMode;

        return $this;
    }

    public function getPaymentReference(): ?string
    {
        return $this->paymentReference;
    }

    public function setPaymentReference(string $paymentReference): self
    {
        $this->paymentReference = $paymentReference;

        return $this;
    }

    public function getRef()
    {
        return "DP-".$this->id + 100;
    }

    public function setRef($ref)
    {
        $this->ref = $ref;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getInvoiceReference(): ?string
    {
        return $this->invoiceReference;
    }

    public function setInvoiceReference(?string $invoiceReference): self
    {
        $this->invoiceReference = $invoiceReference;

        return $this;
    }
}
