<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\SponsorRepository;

#[ORM\Entity(repositoryClass: SponsorRepository::class)]
#[ORM\Table(name: 'sponsor')]
class Sponsor
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_sponsor = null;

    public function getId_sponsor(): ?int
    {
        return $this->id_sponsor;
    }

    public function setId_sponsor(int $id_sponsor): self
    {
        $this->id_sponsor = $id_sponsor;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom = null;

    public function getNom(): ?string
    {
        return $this->nom;
    }

    public function setNom(string $nom): self
    {
        $this->nom = $nom;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $logo = null;

    public function getLogo(): ?string
    {
        return $this->logo;
    }

    public function setLogo(?string $logo): self
    {
        $this->logo = $logo;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $site_web = null;

    public function getSite_web(): ?string
    {
        return $this->site_web;
    }

    public function setSite_web(?string $site_web): self
    {
        $this->site_web = $site_web;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $contact_email = null;

    public function getContact_email(): ?string
    {
        return $this->contact_email;
    }

    public function setContact_email(string $contact_email): self
    {
        $this->contact_email = $contact_email;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $contact_telephone = null;

    public function getContact_telephone(): ?string
    {
        return $this->contact_telephone;
    }

    public function setContact_telephone(string $contact_telephone): self
    {
        $this->contact_telephone = $contact_telephone;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $adresse = null;

    public function getAdresse(): ?string
    {
        return $this->adresse;
    }

    public function setAdresse(?string $adresse): self
    {
        $this->adresse = $adresse;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $secteur_activite = null;

    public function getSecteur_activite(): ?string
    {
        return $this->secteur_activite;
    }

    public function setSecteur_activite(?string $secteur_activite): self
    {
        $this->secteur_activite = $secteur_activite;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_inscription = null;

    public function getDate_inscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDate_inscription(?\DateTimeInterface $date_inscription): self
    {
        $this->date_inscription = $date_inscription;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $statut = null;

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(?string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Contrat::class, mappedBy: 'sponsor')]
    private Collection $contrats;

    /**
     * @return Collection<int, Contrat>
     */
    public function getContrats(): Collection
    {
        if (!$this->contrats instanceof Collection) {
            $this->contrats = new ArrayCollection();
        }
        return $this->contrats;
    }

    public function addContrat(Contrat $contrat): self
    {
        if (!$this->getContrats()->contains($contrat)) {
            $this->getContrats()->add($contrat);
        }
        return $this;
    }

    public function removeContrat(Contrat $contrat): self
    {
        $this->getContrats()->removeElement($contrat);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Evenement::class, mappedBy: 'sponsor')]
    private Collection $evenements;

    public function __construct()
    {
        $this->contrats = new ArrayCollection();
        $this->evenements = new ArrayCollection();
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        if (!$this->evenements instanceof Collection) {
            $this->evenements = new ArrayCollection();
        }
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->getEvenements()->contains($evenement)) {
            $this->getEvenements()->add($evenement);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        $this->getEvenements()->removeElement($evenement);
        return $this;
    }

    public function getIdSponsor(): ?int
    {
        return $this->id_sponsor;
    }

    public function getSiteWeb(): ?string
    {
        return $this->site_web;
    }

    public function setSiteWeb(?string $site_web): static
    {
        $this->site_web = $site_web;

        return $this;
    }

    public function getContactEmail(): ?string
    {
        return $this->contact_email;
    }

    public function setContactEmail(string $contact_email): static
    {
        $this->contact_email = $contact_email;

        return $this;
    }

    public function getContactTelephone(): ?string
    {
        return $this->contact_telephone;
    }

    public function setContactTelephone(string $contact_telephone): static
    {
        $this->contact_telephone = $contact_telephone;

        return $this;
    }

    public function getSecteurActivite(): ?string
    {
        return $this->secteur_activite;
    }

    public function setSecteurActivite(?string $secteur_activite): static
    {
        $this->secteur_activite = $secteur_activite;

        return $this;
    }

    public function getDateInscription(): ?\DateTimeInterface
    {
        return $this->date_inscription;
    }

    public function setDateInscription(?\DateTimeInterface $date_inscription): static
    {
        $this->date_inscription = $date_inscription;

        return $this;
    }

}
