<?php

namespace App\Entity;

use App\Repository\ClientFactureRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ClientFactureRepository::class)]
#[ORM\HasLifecycleCallbacks]
class ClientFacture
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'clientFactures')]
    #[ORM\JoinColumn(nullable: false)]
    private ?Client $client = null;

    /**
     * @var Collection<int, Service>
     */
    #[ORM\ManyToMany(targetEntity: Service::class)]
    private Collection $service;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?string $totalPrice = null;

    #[ORM\Column]
    private ?bool $isPdf = false;

    #[ORM\Column]
    private ?bool $isPaid = false;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne]
    #[ORM\JoinColumn(nullable: false)]
    private ?ClientContact $contact = null;

    public function __construct()
    {
        $this->service = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * @return Collection<int, Service>
     */
    public function getService(): Collection
    {
        return $this->service;
    }

    public function addService(Service $service): static
    {
        if (!$this->service->contains($service)) {
            $this->service->add($service);
        }

        return $this;
    }

    public function removeService(Service $service): static
    {
        $this->service->removeElement($service);

        return $this;
    }

    public function getTotalPrice(): ?float
    {
        return $this->totalPrice !== null ? (float) $this->totalPrice : null;
    }
    
    public function setTotalPrice(float $totalPrice): self
    {
        $this->totalPrice = number_format($totalPrice, 2, '.', ''); // Ensure 2 decimal places
        return $this;
    }
    
    public function isPdf(): ?bool
    {
        return $this->isPdf;
    }

    public function setPdf(bool $isPdf): static
    {
        $this->isPdf = $isPdf;

        return $this;
    }

    public function isPaid(): ?bool
    {
        return $this->isPaid;
    }

    public function setPaid(bool $isPaid): static
    {
        $this->isPaid = $isPaid;

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

    public function getContact(): ?ClientContact
    {
        return $this->contact;
    }

    public function setContact(?ClientContact $contact): static
    {
        $this->contact = $contact;

        return $this;
    }
}
