<?php

namespace App\Entity;

use App\Repository\BlHeadRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: BlHeadRepository::class)]
class BlHead
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 60)]
    private ?string $reference = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $date = null;

    #[ORM\Column(length: 70)]
    private ?string $status = null;

    #[ORM\ManyToOne(inversedBy: 'blHeads', fetch: 'EAGER')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Customers $customer = null;

    #[ORM\Column(length: 250, nullable: true)]
    private ?string $commentaire = null;

    #[ORM\OneToOne(mappedBy: 'BlHead', cascade: ['persist', 'remove'])]
    private ?Sales $sales = null;

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

    public function getDate(): ?\DateTimeImmutable
    {
        return $this->date;
    }

    public function setDate(\DateTimeImmutable $date): self
    {
        $this->date = $date;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): self
    {
        $this->status = $status;

        return $this;
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

    public function getCommentaire(): ?string
    {
        return $this->commentaire;
    }

    public function setCommentaire(?string $commentaire): self
    {
        $this->commentaire = $commentaire;

        return $this;
    }

    public function getSales(): ?Sales
    {
        return $this->sales;
    }

    public function setSales(?Sales $sales): self
    {
        // unset the owning side of the relation if necessary
        if ($sales === null && $this->sales !== null) {
            $this->sales->setBlHead(null);
        }

        // set the owning side of the relation if necessary
        if ($sales !== null && $sales->getBlHead() !== $this) {
            $sales->setBlHead($this);
        }

        $this->sales = $sales;

        return $this;
    }
}
