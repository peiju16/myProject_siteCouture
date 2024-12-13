<?php

namespace App\Entity;

use App\Repository\TransportAddressRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransportAddressRepository::class)]
class TransportAddress
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'transportAddresses')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le title de cet addresse.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $title = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer l\'addresse.'
    )]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: '2 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $address = null;

    #[ORM\Column(length: 25)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer la ville.'
    )]
    #[Assert\Length(
        min: 2,
        max: 25,
        minMessage: '2 caractères minimum.',
        maxMessage: '25 caractères maximum.'
    )]
    private ?string $city = null;

    #[ORM\Column(length: 10)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le code postale.'
    )]
    #[Assert\Length(
        min: 2,
        max: 10,
        minMessage: '2 caractères minimum.',
        maxMessage: '10 caractères maximum.'
    )]
    private ?string $zipCode = null;

    #[ORM\Column(length: 25)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer votre numéro de téléphone.'
    )]
    #[Assert\Length(
        min: 10,
        max: 25,
        minMessage: '10 caractères minimum.',
        maxMessage: '25 caractères maximum.'
    )]
    private ?string $telephone = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
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

    public function getAddress(): ?string
    {
        return $this->address;
    }

    public function setAddress(string $address): static
    {
        $this->address = $address;

        return $this;
    }

    public function getCity(): ?string
    {
        return $this->city;
    }

    public function setCity(string $city): static
    {
        $this->city = $city;

        return $this;
    }

    public function getZipCode(): ?string
    {
        return $this->zipCode;
    }

    public function setZipCode(string $zipCode): static
    {
        $this->zipCode = $zipCode;

        return $this;
    }

    public function getTelephone(): ?string
    {
        return $this->telephone;
    }

    public function setTelephone(string $telephone): static
    {
        $this->telephone = $telephone;

        return $this;
    }
}
