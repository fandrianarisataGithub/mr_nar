<?php

namespace App\Entity;

use App\Repository\PointageRepository;
use Doctrine\ORM\Mapping as ORM;

/**
 * @ORM\Entity(repositoryClass=PointageRepository::class)
 */
class Pointage
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $p_mois;

    /**
     * @ORM\Column(type="integer")
     */
    private $p_annee;

    /**
     * @ORM\ManyToOne(targetEntity=Client::class, inversedBy="pointages")
     * @ORM\JoinColumn(nullable=false)
     */
    private $client;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getPMois(): ?string
    {
        return $this->p_mois;
    }

    public function setPMois(string $p_mois): self
    {
        $this->p_mois = $p_mois;

        return $this;
    }

    public function getPAnnee(): ?int
    {
        return $this->p_annee;
    }

    public function setPAnnee(int $p_annee): self
    {
        $this->p_annee = $p_annee;

        return $this;
    }

    public function getClient(): ?Client
    {
        return $this->client;
    }

    public function setClient(?Client $client): self
    {
        $this->client = $client;

        return $this;
    }

    public function getCreatedAt(): ?\DateTimeInterface
    {
        return $this->createdAt;
    }

    public function setCreatedAt(\DateTimeInterface $createdAt): self
    {
        $this->createdAt = $createdAt;

        return $this;
    }
}
