<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\CampagnesPublicitaireRepository;

#[ORM\Entity(repositoryClass: CampagnesPublicitaireRepository::class)]
#[ORM\Table(name: 'campagnes_publicitaires')]
class CampagnesPublicitaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_camp = null;

    public function getId_camp(): ?int
    {
        return $this->id_camp;
    }

    public function setId_camp(int $id_camp): self
    {
        $this->id_camp = $id_camp;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom_camp = null;

    public function getNom_camp(): ?string
    {
        return $this->nom_camp;
    }

    public function setNom_camp(string $nom_camp): self
    {
        $this->nom_camp = $nom_camp;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $budget_camp = null;

    public function getBudget_camp(): ?int
    {
        return $this->budget_camp;
    }

    public function setBudget_camp(int $budget_camp): self
    {
        $this->budget_camp = $budget_camp;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_debut_camp = null;

    public function getDate_debut_camp(): ?\DateTimeInterface
    {
        return $this->date_debut_camp;
    }

    public function setDate_debut_camp(\DateTimeInterface $date_debut_camp): self
    {
        $this->date_debut_camp = $date_debut_camp;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date_fin_camp = null;

    public function getDate_fin_camp(): ?\DateTimeInterface
    {
        return $this->date_fin_camp;
    }

    public function setDate_fin_camp(\DateTimeInterface $date_fin_camp): self
    {
        $this->date_fin_camp = $date_fin_camp;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $statut_camp = null;

    public function getStatut_camp(): ?string
    {
        return $this->statut_camp;
    }

    public function setStatut_camp(string $statut_camp): self
    {
        $this->statut_camp = $statut_camp;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Publicite::class, inversedBy: 'campagnesPublicitaires')]
    #[ORM\JoinColumn(name: 'publicite_id', referencedColumnName: 'id_pub')]
    private ?Publicite $publicite = null;

    public function getPublicite(): ?Publicite
    {
        return $this->publicite;
    }

    public function setPublicite(?Publicite $publicite): self
    {
        $this->publicite = $publicite;
        return $this;
    }

    public function getIdCamp(): ?int
    {
        return $this->id_camp;
    }

    public function getNomCamp(): ?string
    {
        return $this->nom_camp;
    }

    public function setNomCamp(string $nom_camp): static
    {
        $this->nom_camp = $nom_camp;

        return $this;
    }

    public function getBudgetCamp(): ?int
    {
        return $this->budget_camp;
    }

    public function setBudgetCamp(int $budget_camp): static
    {
        $this->budget_camp = $budget_camp;

        return $this;
    }

    public function getDateDebutCamp(): ?\DateTimeInterface
    {
        return $this->date_debut_camp;
    }

    public function setDateDebutCamp(\DateTimeInterface $date_debut_camp): static
    {
        $this->date_debut_camp = $date_debut_camp;

        return $this;
    }

    public function getDateFinCamp(): ?\DateTimeInterface
    {
        return $this->date_fin_camp;
    }

    public function setDateFinCamp(\DateTimeInterface $date_fin_camp): static
    {
        $this->date_fin_camp = $date_fin_camp;

        return $this;
    }

    public function getStatutCamp(): ?string
    {
        return $this->statut_camp;
    }

    public function setStatutCamp(string $statut_camp): static
    {
        $this->statut_camp = $statut_camp;

        return $this;
    }

}
