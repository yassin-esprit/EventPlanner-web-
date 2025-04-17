<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\ContratRepository;

#[ORM\Entity(repositoryClass: ContratRepository::class)]
#[ORM\Table(name: 'contrat')]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_contrat = null;

    #[ORM\ManyToOne(targetEntity: Sponsor::class, inversedBy: 'contrats')]
    #[ORM\JoinColumn(name: 'id_sponsor', referencedColumnName: 'id_sponsor', nullable: false)]
    #[Assert\NotNull(message: "Le sponsor est obligatoire.")]
    private ?Sponsor $sponsor = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le type de contrat est obligatoire.")]
    private ?string $type_contrat = null;

    #[ORM\Column(type: 'decimal', nullable: false)]
    #[Assert\NotNull(message: "Le montant est obligatoire.")]
    #[Assert\GreaterThan(value: 0, message: "Le montant doit être supérieur à 0.")]
    private ?float $montant = null;

    #[ORM\Column(type: 'text', nullable: false)]
    #[Assert\NotBlank(message: "La description est obligatoire.")]
    private ?string $description = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull(message: "La date de début est obligatoire.")]
    #[Assert\GreaterThan("today", message: "La date de début doit être supérieure à la date d'aujourd'hui.")]
    private ?\DateTimeInterface $date_debut = null;

    #[ORM\Column(type: 'date', nullable: false)]
    #[Assert\NotNull(message: "La date de fin est obligatoire.")]
    #[Assert\Expression(
        "this.getDateFin() > this.getDateDebut()",
        message: "La date de fin doit être postérieure à la date de début."
    )]
    private ?\DateTimeInterface $date_fin = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le statut est obligatoire.")]
    private ?string $statut = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank(message: "Le mode de paiement est obligatoire.")]
    private ?string $mode_paiement = null;

    // Getters/Setters (tu peux garder les tiens actuels)

    public function getId_contrat(): ?int
    {
        return $this->id_contrat;
    }

    public function setId_contrat(int $id_contrat): self
    {
        $this->id_contrat = $id_contrat;
        return $this;
    }

    public function getSponsorId(): ?int
    {
        return $this->sponsor?->getIdSponsor();
    }

    public function getSponsor(): ?Sponsor
    {
        return $this->sponsor;
    }

    public function setSponsor(?Sponsor $sponsor): self
    {
        $this->sponsor = $sponsor;
        return $this;
    }

    public function getTypecontrat(): ?string
    {
        return $this->type_contrat;
    }

    public function setTypecontrat(string $type_contrat): self
    {
        $this->type_contrat = $type_contrat;
        return $this;
    }

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    public function getDatedebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDatedebut(\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    public function getDatefin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDatefin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
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

    public function getModepaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModepaiement(?string $mode_paiement): self
    {
        $this->mode_paiement = $mode_paiement;
        return $this;
    }
}
