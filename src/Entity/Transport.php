<?php

namespace App\Entity;

use App\Repository\TransportRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: TransportRepository::class)]
class Transport
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le title de transport.'
    )]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: '2 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $title = null;

    #[ORM\Column(type: Types::TEXT)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le description.'
    )]
    private ?string $content = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\Positive(
        message: 'Veuillez entrer un prix correct.'
    )]
    #[Assert\NotNull(
        message: 'Veuillez entrer le prix.'
    )]
    private ?string $price = null;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'transportWay')]
    private Collection $orders;

    #[ORM\Column]
    private ?bool $isPickup = null;

    public function __construct()
    {
        $this->orders = new ArrayCollection();
    }

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

    public function getContent(): ?string
    {
        return $this->content;
    }

    public function setContent(string $content): static
    {
        $this->content = $content;

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

    /**
     * @return Collection<int, Order>
     */
    public function getOrders(): Collection
    {
        return $this->orders;
    }

    public function addOrder(Order $order): static
    {
        if (!$this->orders->contains($order)) {
            $this->orders->add($order);
            $order->setTransportWay($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getTransportWay() === $this) {
                $order->setTransportWay(null);
            }
        }

        return $this;
    }

    public function isPickup(): ?bool
    {
        return $this->isPickup;
    }

    public function setPickup(bool $isPickup): static
    {
        $this->isPickup = $isPickup;

        return $this;
    }

    public function getIsPickup(): ?bool 
    { 
        return $this->isPickup; 
    }
}
