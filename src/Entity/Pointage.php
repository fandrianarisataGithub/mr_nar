<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\PointageRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

/**
 * @ORM\Entity(repositoryClass=PointageRepository::class)
 * @UniqueEntity("nom", message = "Le mois de pointage est déjà utiliser")
 * 
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
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom;

    /**
     * @ORM\ManyToMany(targetEntity=Client::class, inversedBy="pointages")
     */
    private $client;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_lit;

    /**
     * @ORM\Column(type="integer")
     */
    private $annee_actuelle;

    public function __construct()
    {
        $this->client = new ArrayCollection();
    }


    public function getId(): ?int
    {
        return $this->id;
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

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;

        return $this;
    }

    /**
     * @return Collection|Client[]
     */
    public function getClient(): Collection
    {
        return $this->client;
    }

    public function addClient(Client $client): self
    {
        if (!$this->client->contains($client)) {
            $this->client[] = $client;
        }

        return $this;
    }

    public function removeClient(Client $client): self
    {
        if ($this->client->contains($client)) {
            $this->client->removeElement($client);
        }

        return $this;
    }

    public function getNomLit(): ?string
    {
        return $this->nom_lit;
    }

    public function setNomLit(string $nom_lit): self
    {
        $this->nom_lit = $nom_lit;

        return $this;
    }

    public function getAnneeActuelle(): ?int
    {
        return $this->annee_actuelle;
    }

    public function setAnneeActuelle(int $annee_actuelle): self
    {
        $this->annee_actuelle = $annee_actuelle;

        return $this;
    }



}
