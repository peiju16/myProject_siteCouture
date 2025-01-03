<?php

namespace App\Entity;

use App\Repository\ServiceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ServiceRepository::class)]
class Service
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le service.'
    )]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: '2 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?string $price = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(string $title): static
    {
        $this->title = $title;

        return $this;
    }

    public function getPrice(): ?float
    {
        return $this->price !== null ? (float) $this->price : null;
    }
    
    public function setPrice(float $price): self
    {
        $this->price = number_format($price, 2, '.', ''); // Ensure 2 decimal places
        return $this;
    }

    public function __toString(): string
    {
        return $this->title ?? 'N/A';
    }
}
