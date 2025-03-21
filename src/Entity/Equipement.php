<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EquipementRepository;

#[ORM\Entity(repositoryClass: EquipementRepository::class)]
#[ORM\Table(name: 'equipement')]
class Equipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_eq = null;

    public function getId_eq(): ?int
    {
        return $this->id_eq;
    }

    public function setId_eq(int $id_eq): self
    {
        $this->id_eq = $id_eq;
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $quantite_totale = null;

    public function getQuantite_totale(): ?int
    {
        return $this->quantite_totale;
    }

    public function setQuantite_totale(int $quantite_totale): self
    {
        $this->quantite_totale = $quantite_totale;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $quantite_dispo = null;

    public function getQuantite_dispo(): ?int
    {
        return $this->quantite_dispo;
    }

    public function setQuantite_dispo(int $quantite_dispo): self
    {
        $this->quantite_dispo = $quantite_dispo;
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

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $statut = null;

    public function getStatut(): ?int
    {
        return $this->statut;
    }

    public function setStatut(int $statut): self
    {
        $this->statut = $statut;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReservationEquipement::class, mappedBy: 'equipement')]
    private Collection $reservationEquipements;

    public function __construct()
    {
        $this->reservationEquipements = new ArrayCollection();
    }

    /**
     * @return Collection<int, ReservationEquipement>
     */
    public function getReservationEquipements(): Collection
    {
        if (!$this->reservationEquipements instanceof Collection) {
            $this->reservationEquipements = new ArrayCollection();
        }
        return $this->reservationEquipements;
    }

    public function addReservationEquipement(ReservationEquipement $reservationEquipement): self
    {
        if (!$this->getReservationEquipements()->contains($reservationEquipement)) {
            $this->getReservationEquipements()->add($reservationEquipement);
        }
        return $this;
    }

    public function removeReservationEquipement(ReservationEquipement $reservationEquipement): self
    {
        $this->getReservationEquipements()->removeElement($reservationEquipement);
        return $this;
    }

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
