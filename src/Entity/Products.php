<?php

namespace App\Entity;

use App\Entity\Trait\CreatedAtTrait;
use App\Entity\Trait\statusTrait;
use App\Repository\ProductsRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Vich\UploaderBundle\Mapping\Annotation as Vich;
use Symfony\Component\HttpFoundation\File\File;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ProductsRepository::class)]
#[Vich\Uploadable]
class Products
{
    use CreatedAtTrait;

    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $name = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank]
    private ?string $nameCommerciale = null;

    #[ORM\ManyToOne(inversedBy: 'products', fetch: 'EAGER')]
    private ?Distributor $distributor = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?float $priceReduced = null;

    #[ORM\Column]
    #[Assert\NotBlank]
    private ?int $quantity = null;

    #[ORM\Column(length: 50)]
    private ?string $brand = null;

    #[ORM\Column(length: 100)]
    private ?string $ref = null;

    /**
     * NOTE: This is not a mapped field of entity metadata, just a simple property.
     */
    #[Vich\UploadableField(mapping: 'products_images', fileNameProperty: 'picture')]
    private ?File $imageFile = null;

    #[ORM\Column(length: 100, nullable: true)]
    private ?string $picture = null;

    #[ORM\ManyToMany(targetEntity: Sales::class, mappedBy: 'products')]
    private Collection $sales;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: SalesProducts::class)]
    private Collection $salesProducts;

    #[ORM\Column]
    private ?bool $enabled = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(nullable: true)]
    private ?bool $deleted = null;

    #[ORM\Column]
    private ?float $rate = null;

    #[ORM\Column]
    private ?string $rateType = null;

    #[ORM\Column]
    private ?float $priceHt = null;

    #[ORM\Column]
    private ?float $priceRevient = null;


    public function __construct()
    {
        $this->sales = new ArrayCollection();
        $this->salesProducts = new ArrayCollection();
        $this->enabled = true;
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getNameCommerciale(): ?string
    {
        return $this->nameCommerciale;
    }

    public function setNameCommerciale(string $nameCommerciale): self
    {
        $this->nameCommerciale = $nameCommerciale;

        return $this;
    }

    public function getDistributor(): ?Distributor
    {
        return $this->distributor;
    }

    public function setDistributor(?Distributor $distributor): self
    {
        $this->distributor = $distributor;

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

    public function getPriceReduced()
    {
        return $this->priceReduced;
    }

    public function setPriceReduced(float $priceReduced)
    {
        $this->priceReduced = $priceReduced;

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

    public function getBrand(): ?string
    {
        return $this->brand;
    }

    public function setBrand(string $brand): self
    {
        $this->brand = $brand;

        return $this;
    }

    public function getRef(): ?string
    {
        return $this->ref;
    }

    public function setRef(string $ref): self
    {
        $this->ref = $ref;

        return $this;
    }

    public function getPicture()
    {
        return $this->picture;
    }

    public function setPicture($picture): self
    {
        $this->picture = $picture;

        return $this;
    }

    /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updated_at = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
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
            $sale->addProduct($this);
        }

        return $this;
    }

    public function removeSale(Sales $sale): self
    {
        if ($this->sales->removeElement($sale)) {
            $sale->removeProduct($this);
        }

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
            $salesProduct->setProduct($this);
        }

        return $this;
    }

    public function removeSalesProduct(SalesProducts $salesProduct): self
    {
        if ($this->salesProducts->removeElement($salesProduct)) {
            // set the owning side to null (unless already changed)
            if ($salesProduct->getProduct() === $this) {
                $salesProduct->setProduct(null);
            }
        }

        return $this;
    }

    public function isEnabled(): ?bool
    {
        return $this->enabled;
    }

    public function setEnabled(bool $enabled): self
    {
        $this->enabled = $enabled;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function isDeleted(): ?bool
    {
        return $this->deleted;
    }

    public function setDeleted(?bool $deleted): self
    {
        $this->deleted = $deleted;

        return $this;
    }

    public function getRate(): ?float
    {
        return $this->rate;
    }

    public function setRate(float $rate): self
    {
        $this->rate = $rate;

        return $this;
    }

    public function getPriceHt(): ?float
    {
        return $this->priceHt;
    }

    public function setPriceHt(float $priceHt): self
    {
        $this->priceHt = $priceHt;

        return $this;
    }

    /**
     * @return string|null
     */
    public function getRateType(): ?string
    {
        return $this->rateType;
    }

    /**
     * @param string|null $rateType
     */
    public function setRateType(?string $rateType): void
    {
        $this->rateType = $rateType;
    }

    public function getPriceRevient(): ?float
    {
        return $this->priceRevient;
    }

    public function setPriceRevient(float $priceRevient): self
    {
        $this->priceRevient = $priceRevient;

        return $this;
    }


   
}
