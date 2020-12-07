<?php

namespace App\Entity;

use App\Repository\Fichier1Repository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=Fichier1Repository::class)
 */
class Fichier1
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
    private $arr_ssd;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $date;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ben;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $ord;

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

    public function getArrSsd(): ?string
    {
        return $this->arr_ssd;
    }

    public function setArrSsd(?string $arr_ssd): self
    {
        $this->arr_ssd = $arr_ssd;

        return $this;
    }

    public function getDate(): ?string
    {
        return $this->date;
    }

    public function setDate(?string $date): self
    {
        $this->date = $date;

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

    public function getOrd(): ?string
    {
        return $this->ord;
    }

    public function setOrd(?string $ord): self
    {
        $this->ord = $ord;

        return $this;
    }
}
