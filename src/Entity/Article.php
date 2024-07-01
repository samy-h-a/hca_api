<?php

namespace App\Entity;

use App\Repository\ArticleRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: ArticleRepository::class)]
class Article
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $textFr = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $textAr = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $textAm = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $titleFr = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $titleAr = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $titleAm = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $cover;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTextFr(): ?string
    {
        return $this->textFr;
    }

    public function setTextFr(?string $textFr): static
    {
        $this->textFr = $textFr;

        return $this;
    }

    public function getTextAr(): ?string
    {
        return $this->textAr;
    }

    public function setTextAr(?string $textAr): static
    {
        $this->textAr = $textAr;

        return $this;
    }

    public function getTextAm(): ?string
    {
        return $this->textAm;
    }

    public function setTextAm(?string $textAm): static
    {
        $this->textAm = $textAm;

        return $this;
    }

    public function getTitleFr(): ?string
    {
        return $this->titleFr;
    }

    public function setTitleFr(?string $titleFr): static
    {
        $this->titleFr = $titleFr;

        return $this;
    }

    public function getTitleAr(): ?string
    {
        return $this->titleAr;
    }

    public function setTitleAr(?string $titleAr): static
    {
        $this->titleAr = $titleAr;

        return $this;
    }

    public function getTitleAm(): ?string
    {
        return $this->titleAm;
    }

    public function setTitleAm(?string $titleAm): static
    {
        $this->titleAm = $titleAm;

        return $this;
    }

    public function getCover()
    {
        return $this->cover;
    }

    public function setCover($cover): static
    {
        $this->cover = $cover;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeImmutable
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeImmutable $createdAt): static
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
