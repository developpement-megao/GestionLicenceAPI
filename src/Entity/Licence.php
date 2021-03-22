<?php

namespace App\Entity;

use App\Repository\LicenceRepository;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Serializer\Annotation\Groups;

/**
 * @ORM\Entity(repositoryClass=LicenceRepository::class)
 */
class Licence
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     * @Groups("licence:read")
     */
    private $id;

    /**
     * @ORM\ManyToOne(targetEntity=Cabinet::class, inversedBy="licences")
     */
    private $cabinet;

    /**
     * @ORM\Column(type="date")
     * @Groups("licence:read")
     */
    private $dateDebut;

    /**
     * @ORM\Column(type="date")
     * @Groups("licence:read")
     */
    private $dateCreation;

    /**
     * @ORM\Column(type="date", nullable=true)
     * @Groups("licence:read")
     */
    private $dateFin;

    /**
     * @ORM\Column(type="integer")
     * @Groups("licence:read")
     */
    private $nombrePostes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("licence:read")
     */
    private $deltaNombrePostes;

    /**
     * @ORM\Column(type="integer", nullable=true)
     * @Groups("licence:read")
     */
    private $deltaJourFin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getCabinet(): ?Cabinet
    {
        return $this->cabinet;
    }

    public function setCabinet(?Cabinet $cabinet): self
    {
        $this->cabinet = $cabinet;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->dateDebut;
    }

    public function setDateDebut(?\DateTimeInterface $dateDebut): self
    {
        $this->dateDebut = $dateDebut;

        return $this;
    }

    public function getDateCreation(): ?\DateTimeInterface
    {
        return $this->dateCreation;
    }

    public function setDateCreation(?\DateTimeInterface $dateCreation): self
    {
        $this->dateCreation = $dateCreation;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->dateFin;
    }

    public function setDateFin(?\DateTimeInterface $dateFin): self
    {
        $this->dateFin = $dateFin;

        return $this;
    }

    public function getNombrePostes(): ?int
    {
        return $this->nombrePostes;
    }

    public function setNombrePostes(int $nombrePostes): self
    {
        $this->nombrePostes = $nombrePostes;

        return $this;
    }

    public function getDeltaNombrePostes(): ?int
    {
        return $this->deltaNombrePostes;
    }

    public function setDeltaNombrePostes(?int $deltaNombrePostes): self
    {
        $this->deltaNombrePostes = $deltaNombrePostes;

        return $this;
    }

    public function getDeltaJourFin(): ?int
    {
        return $this->deltaJourFin;
    }

    public function setDeltaJourFin(?int $deltaJourFin): self
    {
        $this->deltaJourFin = $deltaJourFin;

        return $this;
    }
}
