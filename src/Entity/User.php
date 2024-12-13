<?php

namespace App\Entity;

use App\Repository\UserRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Component\HttpFoundation\File\File;
use Vich\UploaderBundle\Mapping\Annotation as Vich;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\UniqueConstraint(name: 'UNIQ_IDENTIFIER_EMAIL', fields: ['email'])]
#[ORM\HasLifecycleCallbacks]
#[ORM\EntityListeners(['App\EntityListener\UserListener'])]
#[Vich\Uploadable]
class User implements UserInterface, PasswordAuthenticatedUserInterface
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 180)]
    #[Assert\Email()]
    #[Assert\NotBlank( 
        message: 'Veuillez entrer e-mail'
    )]
    #[Assert\Length(
        min: 2,
        max: 180,
        minMessage: '2 caractères minimum.',
        maxMessage: '180 caractères maximum.'
    )]
    private ?string $email = null;

    /**
     * @var list<string> The user roles
     */
    #[ORM\Column]
    #[Assert\NotNull()]
    private array $roles = [];

    /**
     * @var string The hashed password
     */
    #[ORM\Column]
    #[Assert\NotBlank(
        message: 'Veuillez entrer un password.'
    )]
    private ?string $password = 'password';

    #[ORM\Column(length: 25, nullable: true)]
    private ?string $title = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer votre nom de famille.'
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
        message: 'Veuillez entrer votre prénom.'
    )]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $firstName = null;

    #[ORM\Column(length: 50, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 50,
        minMessage: '2 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $pseudo = null;

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

    #[ORM\Column(length: 255, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: '2 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $address = null;

    #[ORM\Column(length: 25, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 25,
        minMessage: '2 caractères minimum.',
        maxMessage: '25 caractères maximum.'
    )]
    private ?string $city = null;

    #[ORM\Column(length: 10, nullable: true)]
    #[Assert\Length(
        min: 2,
        max: 10,
        minMessage: '2 caractères minimum.',
        maxMessage: '10 caractères maximum.'
    )]
    private ?string $zipCode = null;

    #[Vich\UploadableField(mapping: 'users', fileNameProperty: 'imageName', size: 'imageSize')]
    private ?File $imageFile = null;

    #[ORM\Column(nullable: true)]
    private ?string $imageName = null;

    #[ORM\Column(nullable: true)]
    private ?int $imageSize = null;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    private ?string $pleinPassword = null;

    #[ORM\Column(nullable: true)]
    private ?\DateTimeImmutable $updatedAt = null;

    /**
     * @var Collection<int, Formation>
     */
    #[ORM\ManyToMany(targetEntity: Formation::class, inversedBy: 'users')]
    private Collection $formation;

    /**
     * @var Collection<int, Point>
     */
    #[ORM\OneToMany(targetEntity: Point::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $points;

    #[ORM\Column(length: 255, nullable: true)]
    private ?string $tokenRegistration = null;

    #[ORM\Column(type: Types::DATETIME_MUTABLE)]
    private ?\DateTimeInterface $tokenRegistrationLifeTime = null;

    #[ORM\Column]
    private ?bool $isVerifed = false;

    /**
     * @var Collection<int, TransportAddress>
     */
    #[ORM\OneToMany(targetEntity: TransportAddress::class, mappedBy: 'user')]
    private Collection $transportAddresses;

    /**
     * @var Collection<int, Order>
     */
    #[ORM\OneToMany(targetEntity: Order::class, mappedBy: 'user', orphanRemoval: true)]
    private Collection $orders;

    public function __construct()
    {
        $this->formation = new ArrayCollection();
        $this->points = new ArrayCollection();
        $this->isVerifed = false;
        $this->tokenRegistrationLifeTime = (new \DateTime('now'))->add(new \DateInterval("P1D"));
        $this->transportAddresses = new ArrayCollection();
        $this->orders = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
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

    /**
     * A visual identifier that represents this user.
     *
     * @see UserInterface
     */
    public function getUserIdentifier(): string
    {
        return (string) $this->email;
    }

    /**
     * @see UserInterface
     *
     * @return list<string>
     */
    public function getRoles(): array
    {
        $roles = $this->roles;
        // guarantee every user at least has ROLE_USER
        $roles[] = 'ROLE_USER';

        return array_unique($roles);
    }

    /**
     * @param list<string> $roles
     */
    public function setRoles(array $roles): static
    {
        $this->roles = $roles;

        return $this;
    }

    /**
     * @see PasswordAuthenticatedUserInterface
     */
    public function getPassword(): string
    {
        return $this->password;
    }

    public function setPassword(string $password): static
    {
        $this->password = $password;

        return $this;
    }

    /**
     * @see UserInterface
     */
    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
        // $this->plainPassword = null;
    }

    public function getTitle(): ?string
    {
        return $this->title;
    }

    public function setTitle(?string $title): static
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

    public function getPseudo(): ?string
    {
        return $this->pseudo;
    }

    public function setPseudo(string $pseudo): static
    {
        $this->pseudo = $pseudo;

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

   /**
     * If manually uploading a file (i.e. not using Symfony Form) ensure an instance
     * of 'UploadedFile' is injected into this setter to trigger the update. If this
     * bundle's configuration parameter 'inject_on_load' is set to 'true' this setter
     * must be able to accept an instance of 'File' as the bundle will inject one here
     * during Doctrine hydration.
     *
     * @param File|\Symfony\Component\HttpFoundation\File\UploadedFile|null $imageFile
     */
    public function setImageFile(?File $imageFile = null): void
    {
        $this->imageFile = $imageFile;

        if (null !== $imageFile) {
            // It is required that at least one field changes if you are using doctrine
            // otherwise the event listeners won't be called and the file is lost
            $this->updatedAt = new \DateTimeImmutable();
        }
    }

    public function getImageFile(): ?File
    {
        return $this->imageFile;
    }

    public function setImageName(?string $imageName): void
    {
        $this->imageName = $imageName;
    }

    public function getImageName(): ?string
    {
        return $this->imageName;
    }

    public function setImageSize(?int $imageSize): void
    {
        $this->imageSize = $imageSize;
    }

    public function getImageSize(): ?int
    {
        return $this->imageSize;
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

    public function getPleinPassword(): ?string
    {
        return $this->pleinPassword;
    }

    public function setPleinPassword(string $pleinPassword): static
    {
        $this->pleinPassword = $pleinPassword;

        return $this;
    }

    public function getUpdatedAt(): ?\DateTimeImmutable
    {
        return $this->updatedAt;
    }

    public function setUpdatedAt(?\DateTimeImmutable $updatedAt): static
    {
        $this->updatedAt = $updatedAt;

        return $this;
    }


    /**
     * @return Collection<int, Formation>
     */
    public function getFormation(): Collection
    {
        return $this->formation;
    }

    public function addFormation(Formation $formation): static
    {
        if (!$this->formation->contains($formation)) {
            $this->formation->add($formation);
        }

        return $this;
    }

    public function removeFormation(Formation $formation): static
    {
        $this->formation->removeElement($formation);

        return $this;
    }

    /**
     * @return Collection<int, Point>
     */
    public function getPoints(): Collection
    {
        return $this->points;
    }

    public function addPoint(Point $point): static
    {
        if (!$this->points->contains($point)) {
            $this->points->add($point);
            $point->setUser($this);
        }

        return $this;
    }

    public function removePoint(Point $point): static
    {
        if ($this->points->removeElement($point)) {
            // set the owning side to null (unless already changed)
            if ($point->getUser() === $this) {
                $point->setUser(null);
            }
        }

        return $this;
    }

    public function getTokenRegistration(): ?string
    {
        return $this->tokenRegistration;
    }

    public function setTokenRegistration(string $tokenRegistration): static
    {
        $this->tokenRegistration = $tokenRegistration;

        return $this;
    }

    public function getTokenRegistrationLifeTime(): ?\DateTimeInterface
    {
        return $this->tokenRegistrationLifeTime;
    }

    public function setTokenRegistrationLifeTime(\DateTimeInterface $date): static
    {
        $this->tokenRegistrationLifeTime = $tokenRegistrationLifeTime;

        return $this;
    }

    public function isVerifed(): ?bool
    {
        return $this->isVerifed;
    }

    public function setVerifed(bool $isVerifed): static
    {
        $this->isVerifed = $isVerifed;

        return $this;
    }

    /**
     * @return Collection<int, TransportAddress>
     */
    public function getTransportAddresses(): Collection
    {
        return $this->transportAddresses;
    }

    public function addTransportAddress(TransportAddress $transportAddress): static
    {
        if (!$this->transportAddresses->contains($transportAddress)) {
            $this->transportAddresses->add($transportAddress);
            $transportAddress->setUser($this);
        }

        return $this;
    }

    public function removeTransportAddress(TransportAddress $transportAddress): static
    {
        if ($this->transportAddresses->removeElement($transportAddress)) {
            // set the owning side to null (unless already changed)
            if ($transportAddress->getUser() === $this) {
                $transportAddress->setUser(null);
            }
        }

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
            $order->setUser($this);
        }

        return $this;
    }

    public function removeOrder(Order $order): static
    {
        if ($this->orders->removeElement($order)) {
            // set the owning side to null (unless already changed)
            if ($order->getUser() === $this) {
                $order->setUser(null);
            }
        }

        return $this;
    }
}
