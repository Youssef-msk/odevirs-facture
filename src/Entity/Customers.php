<?php

namespace App\Entity;

use App\Entity\Trait\statusTrait;
use App\Repository\CustomersRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use App\Entity\Trait\CreatedAtTrait;

#[ORM\Entity(repositoryClass: CustomersRepository::class)]
class Customers
{
    use CreatedAtTrait;
    use statusTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $company = null;

    #[ORM\Column(length: 50)]
    private ?string $ice = null;

    #[ORM\Column(length: 20, nullable: true)]
    private ?string $phone = null;

    #[ORM\Column(length: 150, nullable: true)]
    private ?string $email = null;

    #[ORM\Column(length: 300,nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $ville = null;

    #[ORM\Column(length: 10, nullable: true)]
    private ?string $zipcode = null;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: Sales::class)]
    private Collection $sales;

    #[ORM\OneToMany(mappedBy: 'customer', targetEntity: BlHead::class)]
    private Collection $blHeads;

    public function __construct()
    {
        $this->sales = new ArrayCollection();
        $this->blHeads = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): self
    {
        $this->company = $company;

        return $this;
    }

    public function getIce(): ?string
    {
        return $this->ice;
    }

    public function setIce(string $ice): self
    {
        $this->ice = $ice;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(?string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getVille(): ?string
    {
        return $this->ville;
    }

    public function setVille(string $ville): self
    {
        $this->ville = $ville;

        return $this;
    }

    public function getZipcode(): ?string
    {
        return $this->zipcode;
    }

    public function setZipcode(string $zipcode): self
    {
        $this->zipcode = $zipcode;

        return $this;
    }

    /**
     * @return Collection<int, Sales>
     */
    public function getSales(): Collection
    {
        return $this->sales;
    }

    public function addSale(Sales $sale): self
    {
        if (!$this->sales->contains($sale)) {
            $this->sales->add($sale);
            $sale->setCustomer($this);
        }

        return $this;
    }

    public function removeSale(Sales $sale): self
    {
        if ($this->sales->removeElement($sale)) {
            // set the owning side to null (unless already changed)
            if ($sale->getCustomer() === $this) {
                $sale->setCustomer(null);
            }
        }

        return $this;
    }

    /**
     * @return Collection<int, BlHead>
     */
    public function getBlHeads(): Collection
    {
        return $this->blHeads;
    }

    public function addBlHead(BlHead $blHead): self
    {
        if (!$this->blHeads->contains($blHead)) {
            $this->blHeads->add($blHead);
            $blHead->setCustomer($this);
        }

        return $this;
    }

    public function removeBlHead(BlHead $blHead): self
    {
        if ($this->blHeads->removeElement($blHead)) {
            // set the owning side to null (unless already changed)
            if ($blHead->getCustomer() === $this) {
                $blHead->setCustomer(null);
            }
        }

        return $this;
    }
}
