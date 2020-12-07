<?php

namespace App\Entity;

use App\Repository\Fichier2Repository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=Fichier2Repository::class)
 */
class Fichier2
{
    /**
     * @ORM\Id
     * @ORM\GeneratedValue
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $num_pens;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $rub;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $montant;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $date_debut;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $date_fin;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getNumPens(): ?string
    {
        return $this->num_pens;
    }

    public function setNumPens(?string $num_pens): self
    {
        $this->num_pens = $num_pens;

        return $this;
    }

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(?string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    public function getRub(): ?string
    {
        return $this->rub;
    }

    public function setRub(?string $rub): self
    {
        $this->rub = $rub;

        return $this;
    }

    public function getMontant(): ?string
    {
        return $this->montant;
    }

    public function setMontant(?string $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getDateDebut(): ?string
    {
        return $this->date_debut;
    }

    public function setDateDebut(?string $date_debut): self
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?string
    {
        return $this->date_fin;
    }

    public function setDateFin(?string $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }
}
