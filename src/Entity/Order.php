<?php

namespace App\Entity;

use App\Repository\OrderRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: OrderRepository::class)]
#[ORM\Table(name: '`order`')]
#[ORM\HasLifecycleCallbacks]
class Order
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer votre civilité particulière ou votre régime professionnel.'
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
        message: 'Veuillez entrer votre nom.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $receiverName = null;

    #[ORM\Column(length: 255)]
    #[Assert\Email()]
    #[Assert\NotBlank( 
        message: 'Veuillez entrer e-mail'
    )]
    #[Assert\Length(
        min: 5,
        max: 255,
        minMessage: '5 caractères minimum.',
        maxMessage: '180 caractères maximum.'
    )]
    private ?string $receiverEmail = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer votre numéro de téléphone.'
    )]
    #[Assert\Length(
        min: 10,
        max: 50,
        minMessage: '10 caractères minimum.',
        maxMessage: '25 caractères maximum.'
    )]
    private ?string $receiverPhone = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(groups: ["new_address"])]
    #[Assert\Length(
        min: 10,
        max: 255,
        minMessage: '10 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $receiverAddress = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank(groups: ["new_address"])]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: '2 caractères minimum.',
        maxMessage: '100 caractères maximum.'
    )]
    private ?string $city = null;

    #[ORM\Column(length: 20)]
    #[Assert\NotBlank(groups: ["new_address"])]
    #[Assert\Length(
        min: 5,
        max: 20,
        minMessage: '5 caractères minimum.',
        maxMessage: '20 caractères maximum.'
    )]
    private ?string $zipCode = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    private ?Transport $transportWay = null;

    #[ORM\Column(length: 10)]
    private ?string $transportPrice = null;

    #[ORM\Column]
    private ?bool $isPaid = null;

    #[ORM\Column(length: 50)]
    private ?string $paymentMethod = null;

    #[ORM\Column(length: 255)]
    private ?string $reference = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $stripSessionId = null;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $PaypalOrderId = null;

    /**
     * @var Collection<int, OrderDetails>
     */
    #[ORM\OneToMany(targetEntity: OrderDetails::class, mappedBy: 'orderNumber')]
    private Collection $orderDetails;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private ?string $totalPrice = null;

    #[ORM\ManyToOne(inversedBy: 'orders')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\OneToOne(mappedBy: 'orderInvoice', cascade: ['persist', 'remove'])]
    private ?Invoice $invoice = null;

    #[ORM\Column(length: 255)]
    private ?string $status = null;

    public function __construct()
    {
        $this->orderDetails = new ArrayCollection();
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

    public function getReceiverName(): ?string
    {
        return $this->receiverName;
    }

    public function setReceiverName(string $receiverName): static
    {
        $this->receiverName = $receiverName;

        return $this;
    }

    public function getReceiverEmail(): ?string
    {
        return $this->receiverEmail;
    }

    public function setReceiverEmail(string $receiverEmail): static
    {
        $this->receiverEmail = $receiverEmail;

        return $this;
    }

    public function getReceiverPhone(): ?string
    {
        return $this->receiverPhone;
    }

    public function setReceiverPhone(string $receiverPhone): static
    {
        $this->receiverPhone = $receiverPhone;

        return $this;
    }

    public function getReceiverAddress(): ?string
    {
        return $this->receiverAddress;
    }

    public function setReceiverAddress(string $receiverAddress): static
    {
        $this->receiverAddress = $receiverAddress;

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

    public function getTransportWay(): ?Transport
    {
        return $this->transportWay;
    }

    public function setTransportWay(?Transport $transportWay): static
    {
        $this->transportWay = $transportWay;

        return $this;
    }

    public function getTransportPrice(): ?string
    {
        return $this->transportPrice;
    }

    public function setTransportPrice(string $transportPrice): static
    {
        $this->transportPrice = $transportPrice;

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

    public function getPaymentMethod(): ?string
    {
        return $this->paymentMethod;
    }

    public function setPaymentMethod(string $paymentMethod): static
    {
        $this->paymentMethod = $paymentMethod;

        return $this;
    }

    public function getReference(): ?string
    {
        return $this->reference;
    }

    public function setReference(string $reference): static
    {
        $this->reference = $reference;

        return $this;
    }

    public function getStripSessionId(): ?string
    {
        return $this->stripSessionId;
    }

    public function setStripSessionId(?string $stripSessionId): static
    {
        $this->stripSessionId = $stripSessionId;

        return $this;
    }

    public function getPaypalOrderId(): ?string
    {
        return $this->PaypalOrderId;
    }

    public function setPaypalOrderId(?string $PaypalOrderId): static
    {
        $this->PaypalOrderId = $PaypalOrderId;

        return $this;
    }

    /**
     * @return Collection<int, OrderDetails>
     */
    public function getOrderDetails(): Collection
    {
        return $this->orderDetails;
    }

    public function addOrderDetail(OrderDetails $orderDetail): static
    {
        if (!$this->orderDetails->contains($orderDetail)) {
            $this->orderDetails->add($orderDetail);
            $orderDetail->setOrderNumber($this);
        }

        return $this;
    }

    public function removeOrderDetail(OrderDetails $orderDetail): static
    {
        if ($this->orderDetails->removeElement($orderDetail)) {
            // set the owning side to null (unless already changed)
            if ($orderDetail->getOrderNumber() === $this) {
                $orderDetail->setOrderNumber(null);
            }
        }

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

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): static
    {
        $this->user = $user;

        return $this;
    }

    public function getInvoice(): ?Invoice
    {
        return $this->invoice;
    }

    public function setInvoice(?Invoice $invoice): static
    {
        // unset the owning side of the relation if necessary
        if ($invoice === null && $this->invoice !== null) {
            $this->invoice->setOrderInvoice(null);
        }

        // set the owning side of the relation if necessary
        if ($invoice !== null && $invoice->getOrderInvoice() !== $this) {
            $invoice->setOrderInvoice($this);
        }

        $this->invoice = $invoice;

        return $this;
    }

    public function getStatus(): ?string
    {
        return $this->status;
    }

    public function setStatus(string $status): static
    {
        $this->status = $status;

        return $this;
    }

}
