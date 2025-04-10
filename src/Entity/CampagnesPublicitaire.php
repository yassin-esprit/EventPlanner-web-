<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;
use App\Repository\CampagnesPublicitaireRepository;

#[ORM\Entity(repositoryClass: CampagnesPublicitaireRepository::class)]
#[ORM\Table(name: 'campagnes_publicitaires')]
class CampagnesPublicitaire
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column( name : "id_camp" ,type: 'integer')]
    private ?int $id_camp = null;

    #[ORM\Column(type: 'string', length: 100, nullable: false)]
    #[Assert\NotBlank(message: "Le nom de la campagne ne peut pas être vide")]
    #[Assert\Length(
        min: 3,
        max: 100,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères",
        maxMessage: "Le nom ne peut pas dépasser {{ limit }} caractères"
    )]
    private ?string $nom_camp = null;

    #[ORM\Column(type: 'integer', nullable: false)]
    #[Assert\NotBlank(message: "Le budget ne peut pas être vide")]
    #[Assert\Positive(message: "Le budget doit être un nombre positif")]
    private ?int $budget_camp = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Assert\NotBlank(message: "La date de début est obligatoire")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être valide")]
    #[Assert\GreaterThanOrEqual(
        "today",
        message: "La date de début doit être aujourd'hui ou dans le futur"
    )]
    private ?\DateTimeInterface $date_debut_camp = null;

    #[ORM\Column(type: 'datetime', nullable: false)]
    #[Assert\NotBlank(message: "La date de fin est obligatoire")]
    #[Assert\Type("\DateTimeInterface", message: "La date doit être valide")]
    #[Assert\GreaterThan(
        propertyPath: "date_debut_camp",
        message: "La date de fin doit être après la date de début"
    )]
    private ?\DateTimeInterface $date_fin_camp = null;

    #[ORM\Column(type: 'string', length: 20, nullable: false)]
    #[Assert\NotBlank(message: "Le statut ne peut pas être vide")]
    #[Assert\Choice(
        choices: ["planifiée", "en_cours", "terminée", "annulée"],
        message: "Statut invalide"
    )]
    private ?string $statut_camp = null;

    #[ORM\ManyToOne(targetEntity: Publicite::class, inversedBy: 'campagnesPublicitaires')]
    #[ORM\JoinColumn(name: 'publicite_id', referencedColumnName: 'id_pub', nullable: false)]
    #[Assert\NotNull(message: "Veuillez sélectionner une publicité")]
    private ?Publicite $publicite = null;

    // Getters and Setters

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

    public function getPublicite(): ?Publicite
    {
        return $this->publicite;
    }

    public function setPublicite(?Publicite $publicite): static
    {
        $this->publicite = $publicite;
        return $this;
    }
}