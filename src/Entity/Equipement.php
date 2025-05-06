<?php

namespace App\Entity;

use App\Repository\EquipementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
#[ORM\Table(name: 'equipement')]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_eq = null;

    #[Assert\NotBlank(message: "Le nom de l'équipement est obligatoire")]
    #[Assert\Length(
        min: 3,
        minMessage: "Le nom doit contenir au moins {{ limit }} caractères"
    )]
    #[Assert\Regex(
        pattern: '/^[^*]*$/',
        message: "Le nom ne doit pas contenir le caractère *"
    )]
    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $nom = null;
    

    #[Assert\NotBlank(message: "Veuillez indiquer la quantité totale")]
    #[Assert\Positive(message: "La quantité totale doit être un entier positif (> 0)")]
    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $quantite_totale = null;

    #[Assert\NotNull(message: "Veuillez indiquer la quantité disponible")]
    #[Assert\GreaterThanOrEqual(value: 0, message: "La quantité disponible ne peut pas être négative")]
    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $quantite_dispo = null;
    #[Assert\Regex(
        pattern: '/^[^*]*$/',
        message: "La description ne doit pas contenir le caractère *"
    )]
    
    #[ORM\Column(type: 'text', nullable: true)]
    private ?string $description = null;

    #[Assert\NotBlank(message: "Le statut est requis")]
    #[Assert\Choice(
        choices: ['disponible', 'indisponible', 'maintenance'],
        message: "Le statut doit être 'disponible' ou 'indisponible' ou 'maintenance'"
    )]
    #[ORM\Column(type: 'string', length: 20)]
    private ?string $statut = null;

    #[ORM\OneToMany(targetEntity: ReservationEquipement::class, mappedBy: 'equipement')]
    private Collection $reservationEquipements;

    public function __construct()
    {
        $this->reservationEquipements = new ArrayCollection();
    }

    // === Getters / Setters ===

    public function getId(): ?int
    {
        return $this->id_eq;
    }

    public function getId_eq(): ?int
    {
        return $this->id_eq;
    }

    public function setId_eq(int $id_eq): self
    {
        $this->id_eq = $id_eq;
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

    public function getQuantite_totale(): ?int
    {
        return $this->quantite_totale;
    }

    public function setQuantite_totale(int $quantite_totale): self
    {
        $this->quantite_totale = $quantite_totale;
        return $this;
    }

    public function getQuantite_dispo(): ?int
    {
        return $this->quantite_dispo;
    }

    public function setQuantite_dispo(int $quantite_dispo): self
    {
        $this->quantite_dispo = $quantite_dispo;
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

    public function getStatut(): ?string
    {
        return $this->statut;
    }

    public function setStatut(string $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    /**
     * @return Collection<int, ReservationEquipement>
     */
    public function getReservationEquipements(): Collection
    {
        return $this->reservationEquipements;
    }

    public function addReservationEquipement(ReservationEquipement $reservationEquipement): self
    {
        if (!$this->reservationEquipements->contains($reservationEquipement)) {
            $this->reservationEquipements[] = $reservationEquipement;
        }
        return $this;
    }

    public function removeReservationEquipement(ReservationEquipement $reservationEquipement): self
    {
        $this->reservationEquipements->removeElement($reservationEquipement);
        return $this;
    }

    // Alias pour conventions différentes (si utilisé dans Twig ou formulaires)
    public function getIdEq(): ?int
    {
        return $this->id_eq;
    }

    public function getQuantiteTotale(): ?int
    {
        return $this->quantite_totale;
    }

    public function setQuantiteTotale(int $quantite_totale): static
    {
        $this->quantite_totale = $quantite_totale;
        return $this;
    }

    public function getQuantiteDispo(): ?int
    {
        return $this->quantite_dispo;
    }

    public function setQuantiteDispo(int $quantite_dispo): static
    {
        $this->quantite_dispo = $quantite_dispo;
        return $this;
    }
}