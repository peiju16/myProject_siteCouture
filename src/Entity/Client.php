<?php

namespace App\Entity;

use App\Repository\ClientRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;


#[ORM\Entity(repositoryClass: ClientRepository::class)]
#[ORM\HasLifecycleCallbacks]
class Client
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 255)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer le nom d\'entrprise.'
    )]
    #[Assert\Length(
        min: 2,
        max: 255,
        minMessage: '2 caractères minimum.',
        maxMessage: '255 caractères maximum.'
    )]
    private ?string $name = null;

    #[ORM\Column(length: 50)]
    #[Assert\NotBlank(
        message: 'Veuillez entrer numéro de SIRET.'
    )]
    #[Assert\Length(
        min: 9,
        max: 50,
        minMessage: '9 caractères minimum.',
        maxMessage: '50 caractères maximum.'
    )]
    private ?string $siret = null;

    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    #[Assert\Positive]
    #[Assert\NotNull]
    private ?string $tauxTVA = null;

    /**
     * @var Collection<int, ClientContact>
     */
    #[ORM\OneToMany(targetEntity: ClientContact::class, mappedBy: 'client')]
    private Collection $clientContacts;

    #[ORM\Column]
    private ?\DateTimeImmutable $createdAt = null;

    /**
     * @var Collection<int, ClientFacture>
     */
    #[ORM\OneToMany(targetEntity: ClientFacture::class, mappedBy: 'client')]
    private Collection $clientFactures;

    public function __construct()
    {
        $this->clientContacts = new ArrayCollection();
        $this->clientFactures = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): static
    {
        $this->name = $name;

        return $this;
    }

    public function getSiret(): ?string
    {
        return $this->siret;
    }

    public function setSiret(string $siret): static
    {
        $this->siret = $siret;

        return $this;
    }

    public function getTauxTVA(): ?float
    {
        return $this->tauxTVA !== null ? (float) $this->tauxTVA : null;
    }

    public function setTauxTVA(float $tauxTVA): self
    {
        $this->tauxTVA = number_format($tauxTVA, 2, '.', ''); // Ensure 2 decimal places
        return $this;
    }

    /**
     * @return Collection<int, ClientContact>
     */
    public function getClientContacts(): Collection
    {
        return $this->clientContacts;
    }

    public function addClientContact(ClientContact $clientContact): static
    {
        if (!$this->clientContacts->contains($clientContact)) {
            $this->clientContacts->add($clientContact);
            $clientContact->setClient($this);
        }

        return $this;
    }

    public function removeClientContact(ClientContact $clientContact): static
    {
        if ($this->clientContacts->removeElement($clientContact)) {
            // set the owning side to null (unless already changed)
            if ($clientContact->getClient() === $this) {
                $clientContact->setClient(null);
            }
        }

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

    /**
     * @return Collection<int, ClientFacture>
     */
    public function getClientFactures(): Collection
    {
        return $this->clientFactures;
    }

    public function addClientFacture(ClientFacture $clientFacture): static
    {
        if (!$this->clientFactures->contains($clientFacture)) {
            $this->clientFactures->add($clientFacture);
            $clientFacture->setClient($this);
        }

        return $this;
    }

    public function removeClientFacture(ClientFacture $clientFacture): static
    {
        if ($this->clientFactures->removeElement($clientFacture)) {
            // set the owning side to null (unless already changed)
            if ($clientFacture->getClient() === $this) {
                $clientFacture->setClient(null);
            }
        }

        return $this;
    }

    public function __toString(): string
    {
        return $this->name ?? 'N/A';
    }


}
