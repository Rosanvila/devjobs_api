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

    #[ORM\Column(length: 7)]
    private ?string $logoBackground = null;

    #[ORM\Column(length: 255)]
    private ?string $apply = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column(type: Types::TEXT)]
    private ?string $requirementsContent = null;

    #[ORM\Column(type: Types::JSON)]
    private array $requirementsItems = [];

    #[ORM\Column(type: Types::TEXT)]
    private ?string $roleContent = null;

    #[ORM\Column(type: Types::JSON)]
    private array $roleItems = [];

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $website = null;

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

    public function getLogoBackground(): ?string
    {
        return $this->logoBackground;
    }

    public function setLogoBackground(string $logoBackground): static
    {
        $this->logoBackground = $logoBackground;

        return $this;
    }

    public function getApply(): ?string
    {
        return $this->apply;
    }

    public function setApply(string $apply): static
    {
        $this->apply = $apply;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): static
    {
        $this->description = $description;

        return $this;
    }

    public function getRequirementsContent(): ?string
    {
        return $this->requirementsContent;
    }

    public function setRequirementsContent(string $content): static
    {
        $this->requirementsContent = $content;

        return $this;
    }

    public function getRequirementsItems(): array
    {
        return $this->requirementsItems;
    }

    public function setRequirementsItems(array $items): static
    {
        $this->requirementsItems = $items;

        return $this;
    }

    public function getRoleContent(): ?string
    {
        return $this->roleContent;
    }

    public function setRoleContent(string $content): static
    {
        $this->roleContent = $content;

        return $this;
    }

    public function getRoleItems(): array
    {
        return $this->roleItems;
    }

    public function setRoleItems(array $items): static
    {
        $this->roleItems = $items;

        return $this;
    }

    public function getWebsite(): ?string
    {
        return $this->website;
    }

    public function setWebsite(?string $website): static
    {
        $this->website = $website;

        return $this;
    }
}