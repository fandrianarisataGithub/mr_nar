<?php

namespace App\Entity;

use App\Repository\Fichier3Repository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=Fichier3Repository::class)
 */
class Fichier3
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
    private $code_rub;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ben;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $montant;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $date_fin;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $nom;

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

    public function getCodeRub(): ?string
    {
        return $this->code_rub;
    }

    public function setCodeRub(?string $code_rub): self
    {
        $this->code_rub = $code_rub;

        return $this;
    }

    public function getBen(): ?string
    {
        return $this->ben;
    }

    public function setBen(?string $ben): self
    {
        $this->ben = $ben;

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

    public function getDateFin(): ?string
    {
        return $this->date_fin;
    }

    public function setDateFin(?string $date_fin): self
    {
        $this->date_fin = $date_fin;

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
}
