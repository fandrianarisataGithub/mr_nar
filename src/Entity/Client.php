<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\ORM\Mapping\Column;
use App\Repository\ClientRepository;
use Doctrine\Common\Collections\Collection;
use Doctrine\Common\Collections\ArrayCollection;

use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;


/**
 * @ORM\Entity(repositoryClass=ClientRepository::class)
 *  @UniqueEntity("numero_bl", message = "Ce numero BL est déjà utilisé")
 * 
 */
class Client
{
    /**
     * @ORM\Id()
     * @ORM\GeneratedValue()
     * @ORM\Column(type="integer")
     */
    private $id;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $nom;

    /**
     * @ORM\Column(type="string", length=255, nullable=true)
     */
    private $prenom;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $cin;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $image_1;

    /**
     * @ORM\Column(type="string", length=255)
     * 
     */
    private $image_2;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $adresse;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $montant;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $montant_mensuel;

    /**
     * @ORM\Column(type="integer")
     * @Assert\NotBlank
     */
    private $nbr_versement;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_debut;

    /**
     * @ORM\Column(type="datetime")
     */
    private $date_fin;

    /**
     * @ORM\ManyToOne(targetEntity=User::class, inversedBy="clients")
     * @ORM\JoinColumn(nullable=false)
     */
    private $user;


    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank(
     * 
     * message="Il faut mettre le matricule"
     * 
     * )
     */
    private $matricule;


    /**
     * @ORM\Column(type="string", length=255)
     */
    private $verifier;

    /**
     * @ORM\Column(type="string")
     */
    private $numero_bl;

    /**
     * @ORM\Column(type="datetime")
     */
    private $createdAt;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $etat_client;

    /**
     * @ORM\ManyToMany(targetEntity=Pointage::class, mappedBy="client")
     */
    private $pointages;


    /**
     * @ORM\Column(type="integer")
     */
    private $numero_pointage;

    /**
     * @ORM\Column(type="text")
     */
    private $tab_pointage;

    /**
     * @ORM\Column(type="string", length=255)
     */
    private $nom_pointage_av;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $budget;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $article;

    /**
     * @ORM\Column(type="string", length=255)
     * @Assert\NotBlank
     */
    private $chapitre;


    public function __construct()
    {
        $this->pointages = new ArrayCollection();
    }



    public function getId(): ?int
    {
        return $this->id;
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

    public function getPrenom(): ?string
    {
        return $this->prenom;
    }

    public function setPrenom(?string $prenom): self
    {
        $this->prenom = $prenom;

        return $this;
    }

    public function getCin(): ?string
    {
        return $this->cin;
    }

    public function setCin(string $cin): self
    {
        $this->cin = $cin;

        return $this;
    }

    public function getImage1(): ?string
    {
        return $this->image_1;
    }

    public function setImage1(string $image_1): self
    {
        $this->image_1 = $image_1;

        return $this;
    }

    public function getImage2(): ?string
    {
        return $this->image_2;
    }

    public function setImage2(string $image_2): self
    {
        $this->image_2 = $image_2;

        return $this;
    }

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(string $adresse): self
    {
        $this->adresse = $adresse;

        return $this;
    }

    public function getMontant(): ?int
    {
        return $this->montant;
    }

    public function setMontant(int $montant): self
    {
        $this->montant = $montant;

        return $this;
    }

    public function getMontantMensuel(): ?int
    {
        return $this->montant_mensuel;
    }

    public function setMontantMensuel(int $montant_mensuel): self
    {
        $this->montant_mensuel = $montant_mensuel;

        return $this;
    }

    public function getNbrVersement(): ?int
    {
        return $this->nbr_versement;
    }

    public function setNbrVersement(int $nbr_versement): self
    {
        $this->nbr_versement = $nbr_versement;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }



    public function getMatricule(): ?string
    {
        return $this->matricule;
    }

    public function setMatricule(string $matricule): self
    {
        $this->matricule = $matricule;

        return $this;
    }


    public function getVerifier(): ?string
    {
        return $this->verifier;
    }

    public function setVerifier(string $verifier): self
    {
        $this->verifier = $verifier;

        return $this;
    }

    public function getNumeroBl(): ?string
    {
        return $this->numero_bl;
    }

    public function setNumeroBl(string $numero_bl): self
    {
        $this->numero_bl = $numero_bl;

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

    public function getEtatClient(): ?string
    {
        return $this->etat_client;
    }

    public function setEtatClient(string $etat_client): self
    {
        $this->etat_client = $etat_client;

        return $this;
    }

    /**
     * @return Collection|Pointage[]
     */
    public function getPointages(): Collection
    {
        return $this->pointages;
    }

    public function addPointage(Pointage $pointage): self
    {
        if (!$this->pointages->contains($pointage)) {
            $this->pointages[] = $pointage;
            $pointage->addClient($this);
        }

        return $this;
    }

    public function removePointage(Pointage $pointage): self
    {
        if ($this->pointages->contains($pointage)) {
            $this->pointages->removeElement($pointage);
            $pointage->removeClient($this);
        }

        return $this;
    }


    public function getNumeroPointage(): ?int
    {
        return $this->numero_pointage;
    }

    public function setNumeroPointage(int $numero_pointage): self
    {
        $this->numero_pointage = $numero_pointage;

        return $this;
    }

    public function getTabPointage(): ?string
    {
        return $this->tab_pointage;
    }

    public function setTabPointage(string $tab_pointage): self
    {
        $this->tab_pointage = $tab_pointage;

        return $this;
    }

    public function getNomPointageAv(): ?string
    {
        return $this->nom_pointage_av;
    }

    public function setNomPointageAv(string $nom_pointage_av): self
    {
        $this->nom_pointage_av = $nom_pointage_av;

        return $this;
    }
    public function tab_mois()
    {
        $date1 = $this->date_debut->format("Y-m-d");
        $date2 = strtotime("+" . $this->nbr_versement . " months", strtotime($date1));
        $date2 = date("Y-m-d", $date2);
        $start    = new \DateTime($date1);
        $start->modify('first day of this month');
        $end      = new \DateTime($date2);
        $end->modify('first day of next month');
        $interval = \DateInterval::createFromDateString('1 month');
        $period   = new \DatePeriod($start, $interval, $end);
        $tab = [];
        foreach ($period as $dt) {
            array_push($tab, $dt->format("m-Y"));
        }
        $string = $tab[0];
        for ($i = 1; $i < count($tab); $i++) {
            $string .= "__" . $tab[$i];
        }
        //dd($string);
        return $string;
    }

    public function getBudget(): ?string
    {
        return $this->budget;
    }

    public function setBudget(string $budget): self
    {
        $this->budget = $budget;

        return $this;
    }

    public function getArticle(): ?string
    {
        return $this->article;
    }

    public function setArticle(string $article): self
    {
        $this->article = $article;

        return $this;
    }

    public function getChapitre(): ?string
    {
        return $this->chapitre;
    }

    public function setChapitre(string $chapitre): self
    {
        $this->chapitre = $chapitre;

        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;

        return $this;
    }
}
