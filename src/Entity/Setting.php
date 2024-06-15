<?php

namespace App\Entity;

use App\Repository\SettingRepository;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: SettingRepository::class)]
class Setting
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    private ?string $logo = null;

    #[ORM\Column(length: 100)]
    private ?string $logomin = null;

    #[ORM\Column(length: 5)]
    private ?string $currency = null;

    #[ORM\Column(length: 100)]
    private ?string $signaturepath = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): self
    {
        $this->logo = $logo;

        return $this;
    }

    public function getLogomin(): ?string
    {
        return $this->logomin;
    }

    public function setLogomin(string $logomin): self
    {
        $this->logomin = $logomin;

        return $this;
    }

    public function getCurrency(): ?string
    {
        return $this->currency;
    }

    public function setCurrency(string $currency): self
    {
        $this->currency = $currency;

        return $this;
    }

    public function getSignaturepath(): ?string
    {
        return $this->signaturepath;
    }

    public function setSignaturepath(string $signaturepath): self
    {
        $this->signaturepath = $signaturepath;

        return $this;
    }
}
