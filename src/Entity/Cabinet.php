<?php

namespace App\Entity;

use App\Repository\CabinetRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=CabinetRepository::class)
 */
class Cabinet
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("cabinet:read")
     */
    private $id;

    /**
     * @ORM\OneToOne(targetEntity=User::class, mappedBy="cabinet", cascade={"persist", "remove"})
     */
    private $cabinetUser;

    /**
     * @ORM\Column(type="string", length=64)
     * @Groups("cabinet:read")
     */
    private $nomCabinet;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups("cabinet:read")
     */
    private $nomContact;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups("cabinet:read")
     */
    private $nomClient;

    /**
     * @ORM\Column(type="string", length=10, nullable=true)
     * @Groups("cabinet:read")
     */
    private $tel;

    /**
     * @ORM\Column(type="string", length=128, nullable=true)
     * @Groups("cabinet:read")
     */
    private $adresse;

    /**
     * @ORM\Column(type="string", length=64, nullable=true)
     * @Groups("cabinet:read")
     */
    private $email;

    /**
     * @ORM\OneToMany(targetEntity=Licence::class, mappedBy="cabinet", orphanRemoval=true)
     */
    private $licences;

    /**
     * @ORM\Column(type="boolean")
     * @Groups("cabinet:read")
     */
    private $isActive;

    public function __construct()
    {
        $this->licences = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCabinetUser(): ?User
    {
        return $this->cabinetUser;
    }

    public function setCabinetUser(User $cabinetUser): self
    {
        // set the owning side of the relation if necessary
        if ($cabinetUser->getCabinet() !== $this) {
            $cabinetUser->setCabinet($this);
        }

        $this->cabinetUser = $cabinetUser;

        return $this;
    }

    public function getNomCabinet(): ?string
    {
        return $this->nomCabinet;
    }

    public function setNomCabinet(string $nomCabinet): self
    {
        $this->nomCabinet = $nomCabinet;

        return $this;
    }

    public function getNomContact(): ?string
    {
        return $this->nomContact;
    }

    public function setNomContact(?string $nomContact): self
    {
        $this->nomContact = $nomContact;

        return $this;
    }

    public function getNomClient(): ?string
    {
        return $this->nomClient;
    }

    public function setNomClient(?string $nomClient): self
    {
        $this->nomClient = $nomClient;

        return $this;
    }

    public function getTel(): ?string
    {
        return $this->tel;
    }

    public function setTel(?string $tel): self
    {
        $this->tel = $tel;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(?string $email): self
    {
        $this->email = $email;

        return $this;
    }

    /**
     * @return Collection|Licence[]
     */
    public function getLicences(): Collection
    {
        return $this->licences;
    }

    public function addLicence(Licence $licence): self
    {
        if (!$this->licences->contains($licence)) {
            $this->licences[] = $licence;
            $licence->setCabinet($this);
        }

        return $this;
    }

    public function removeLicence(Licence $licence): self
    {
        if ($this->licences->removeElement($licence)) {
            // set the owning side to null (unless already changed)
            if ($licence->getCabinet() === $this) {
                $licence->setCabinet(null);
            }
        }

        return $this;
    }

    public function getIsActive(): ?bool
    {
        return $this->isActive;
    }

    public function setIsActive(bool $isActive): self
    {
        $this->isActive = $isActive;

        return $this;
    }
}
