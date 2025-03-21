<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\ContratRepository;

#[ORM\Entity(repositoryClass: ContratRepository::class)]
#[ORM\Table(name: 'contrat')]
class Contrat
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_contrat = null;

    public function getId_contrat(): ?int
    {
        return $this->id_contrat;
    }

    public function setId_contrat(int $id_contrat): self
    {
        $this->id_contrat = $id_contrat;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Sponsor::class, inversedBy: 'contrats')]
    #[ORM\JoinColumn(name: 'id_sponsor', referencedColumnName: 'id_sponsor')]
    private ?Sponsor $sponsor = null;

    public function getSponsor(): ?Sponsor
    {
        return $this->sponsor;
    }

    public function setSponsor(?Sponsor $sponsor): self
    {
        $this->sponsor = $sponsor;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type_contrat = null;

    public function getType_contrat(): ?string
    {
        return $this->type_contrat;
    }

    public function setType_contrat(string $type_contrat): self
    {
        $this->type_contrat = $type_contrat;
        return $this;
    }

    #[ORM\Column(type: 'decimal', nullable: true)]
    private ?float $montant = null;

    public function getMontant(): ?float
    {
        return $this->montant;
    }

    public function setMontant(?float $montant): self
    {
        $this->montant = $montant;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(?string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_debut = null;

    public function getDate_debut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDate_debut(\DateTimeInterface $date_debut): self
    {
        $this->date_debut = $date_debut;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_fin = null;

    public function getDate_fin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDate_fin(\DateTimeInterface $date_fin): self
    {
        $this->date_fin = $date_fin;
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

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $document_contrat = null;

    public function getDocument_contrat(): ?string
    {
        return $this->document_contrat;
    }

    public function setDocument_contrat(?string $document_contrat): self
    {
        $this->document_contrat = $document_contrat;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $mode_paiement = null;

    public function getMode_paiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setMode_paiement(?string $mode_paiement): self
    {
        $this->mode_paiement = $mode_paiement;
        return $this;
    }

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_signature = null;

    public function getDate_signature(): ?\DateTimeInterface
    {
        return $this->date_signature;
    }

    public function setDate_signature(?\DateTimeInterface $date_signature): self
    {
        $this->date_signature = $date_signature;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $conditions_annulation = null;

    public function getConditions_annulation(): ?string
    {
        return $this->conditions_annulation;
    }

    public function setConditions_annulation(?string $conditions_annulation): self
    {
        $this->conditions_annulation = $conditions_annulation;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $responsable_sponsor = null;

    public function getResponsable_sponsor(): ?string
    {
        return $this->responsable_sponsor;
    }

    public function setResponsable_sponsor(?string $responsable_sponsor): self
    {
        $this->responsable_sponsor = $responsable_sponsor;
        return $this;
    }

    public function getIdContrat(): ?int
    {
        return $this->id_contrat;
    }

    public function getTypeContrat(): ?string
    {
        return $this->type_contrat;
    }

    public function setTypeContrat(string $type_contrat): static
    {
        $this->type_contrat = $type_contrat;

        return $this;
    }

    public function getDateDebut(): ?\DateTimeInterface
    {
        return $this->date_debut;
    }

    public function setDateDebut(\DateTimeInterface $date_debut): static
    {
        $this->date_debut = $date_debut;

        return $this;
    }

    public function getDateFin(): ?\DateTimeInterface
    {
        return $this->date_fin;
    }

    public function setDateFin(\DateTimeInterface $date_fin): static
    {
        $this->date_fin = $date_fin;

        return $this;
    }

    public function getDocumentContrat(): ?string
    {
        return $this->document_contrat;
    }

    public function setDocumentContrat(?string $document_contrat): static
    {
        $this->document_contrat = $document_contrat;

        return $this;
    }

    public function getModePaiement(): ?string
    {
        return $this->mode_paiement;
    }

    public function setModePaiement(?string $mode_paiement): static
    {
        $this->mode_paiement = $mode_paiement;

        return $this;
    }

    public function getDateSignature(): ?\DateTimeInterface
    {
        return $this->date_signature;
    }

    public function setDateSignature(?\DateTimeInterface $date_signature): static
    {
        $this->date_signature = $date_signature;

        return $this;
    }

    public function getConditionsAnnulation(): ?string
    {
        return $this->conditions_annulation;
    }

    public function setConditionsAnnulation(?string $conditions_annulation): static
    {
        $this->conditions_annulation = $conditions_annulation;

        return $this;
    }

    public function getResponsableSponsor(): ?string
    {
        return $this->responsable_sponsor;
    }

    public function setResponsableSponsor(?string $responsable_sponsor): static
    {
        $this->responsable_sponsor = $responsable_sponsor;

        return $this;
    }

}
