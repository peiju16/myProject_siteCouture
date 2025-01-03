<?php

namespace App\Entity;

use App\Repository\ClientContactRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientContactRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ClientContact
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez choisir le title de ce contact.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le nom.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $lastName = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le prénom.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 25)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le numéro de téléphone.'
    )]
    #[Assert\Length(
        min: 10,
        max: 25,
        minMessage: '10 caractères minimum.',
        maxMessage: '25 caractères maximum.'
    )]
    private ?string $telephone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer e-mail.'
    )]
    #[Assert\Length(
        min: 6,
        max: 255,
        minMessage: '6 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $email = null;

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

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer une ville.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $city = null;

    #[ORM\Column(length: 25)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le code postale.'
    )]
    #[Assert\Length(
        min: 2,
        max: 25,
        minMessage: '2 caractères minimum.',
        maxMessage: '25 caractères maximum.'
    )]
    private ?string $zipCode = null;

    #[ORM\ManyToOne(inversedBy: 'clientContacts')]
    private ?Client $client = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

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

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): static
    {
        $this->lastName = $lastName;

        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): static
    {
        $this->firstName = $firstName;

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

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): static
    {
        $this->email = $email;

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

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): static
    {
        $this->client = $client;

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

    #[ORM\PrePersist]
    public function setCreatedAtValue(): void
    {
        $this->createdAt = new \DateTimeImmutable();
    }

    public function __toString(): string
    {
        return $this->title ?? 'N/A';
    }
}
