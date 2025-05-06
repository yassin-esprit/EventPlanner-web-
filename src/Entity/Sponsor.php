<?php

namespace App\Entity;

use App\Repository\SponsorRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use Symfony\Bridge\Doctrine\Validator\Constraints\UniqueEntity;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
#[ORM\Table(name: 'sponsor')]
#[UniqueEntity(fields: ['contact_email'], message: 'Cet email est déjà utilisé.')]
#[UniqueEntity(fields: ['contact_telephone'], message: 'Ce numéro de téléphone est déjà utilisé.')]
#[UniqueEntity(fields: ['site_web'], message: 'Ce site web est déjà utilisé.')]
class Sponsor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_sponsor = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le nom est obligatoire.')]
    private ?string $nom = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $logo = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $site_web = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'L\'email est obligatoire.')]
    #[Assert\Email(message: 'L\'adresse email "{{ value }}" n\'est pas valide.')]
    private ?string $contact_email = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: 'Le numéro de téléphone est obligatoire.')]
    #[Assert\Regex(
        pattern: '/^\d{8}$/',
        message: 'Le numéro de téléphone doit contenir exactement 8 chiffres.'
    )]
    private ?string $contact_telephone = null;

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adresse = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $secteur_activite = null;

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_inscription = null;

    #[ORM\ManyToOne(targetEntity: User::class, inversedBy: 'sponsors')]
    #[ORM\JoinColumn(name: 'iduser', referencedColumnName: 'userId', nullable: false)]
    private ?User $user = null;

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    #[ORM\OneToMany(targetEntity: Contrat::class, mappedBy: 'sponsor')]
    private Collection $contrats;

    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'sponsor')]
    private Collection $evenements;

    public function __construct()
    {
        $this->contrats = new ArrayCollection();
        $this->evenements = new ArrayCollection();
    }

    public function getId_sponsor(): ?int
    {
        return $this->id_sponsor;
    }

    public function setId_sponsor(int $id_sponsor): self
    {
        $this->id_sponsor = $id_sponsor;
        return $this;
    }
    
public function getIdSponsor(): ?int
{
    return $this->id_sponsor;
}

public function setIdSponsor(int $id_sponsor): self
{
    $this->id_sponsor = $id_sponsor;
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

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    public function getSiteweb(): ?string
    {
        return $this->site_web;
    }

    public function setSiteweb(?string $site_web): self
    {
        $this->site_web = $site_web;
        return $this;
    }

    public function getContactemail(): ?string
    {
        return $this->contact_email;
    }

    public function setContactemail(string $contact_email): self
    {
        $this->contact_email = $contact_email;
        return $this;
    }

    public function getContacttelephone(): ?string
    {
        return $this->contact_telephone;
    }

    public function setContacttelephone(string $contact_telephone): self
    {
        $this->contact_telephone = $contact_telephone;
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

    public function getSecteuractivite(): ?string
    {
        return $this->secteur_activite;
    }

    public function setSecteuractivite(?string $secteur_activite): self
    {
        $this->secteur_activite = $secteur_activite;
        return $this;
    }

    public function getDateinscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDateinscription(?\DateTimeInterface $date_inscription): self
    {
        $this->date_inscription = $date_inscription;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    public function getContrats(): Collection
    {
        return $this->contrats;
    }

    public function addContrat(Contrat $contrat): self
    {
        if (!$this->contrats->contains($contrat)) {
            $this->contrats->add($contrat);
        }
        return $this;
    }

    public function removeContrat(Contrat $contrat): self
    {
        $this->contrats->removeElement($contrat);
        return $this;
    }

    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        $this->evenements->removeElement($evenement);
        return $this;
    }
}
