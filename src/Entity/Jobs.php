<?php

namespace App\Entity;

use App\Repository\JobsRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: JobsRepository::class)]
class Jobs
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $logo = null;

    #[ORM\Column(length: 255)]
    private ?string $company = null;

    #[ORM\Column(length: 255)]
    private ?string $contract = null;

    #[ORM\Column(length: 55)]
    private ?string $location = null;

    #[ORM\Column(length: 55)]
    private ?string $position = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $postedAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(string $logo): static
    {
        $this->logo = $logo;

        return $this;
    }

    public function getCompany(): ?string
    {
        return $this->company;
    }

    public function setCompany(string $company): static
    {
        $this->company = $company;

        return $this;
    }

    public function getContract(): ?string
    {
        return $this->contract;
    }

    public function setContract(string $contract): static
    {
        $this->contract = $contract;

        return $this;
    }

    public function getLocation(): ?string
    {
        return $this->location;
    }

    public function setLocation(string $location): static
    {
        $this->location = $location;

        return $this;
    }

    public function getPosition(): ?string
    {
        return $this->position;
    }

    public function setPosition(string $position): static
    {
        $this->position = $position;

        return $this;
    }

    public function getPostedAt(): ?\DateTimeImmutable
    {
        return $this->postedAt;
    }

    public function setPostedAt(\DateTimeImmutable $postedAt): static
    {
        $this->postedAt = $postedAt;

        return $this;
    }
}
