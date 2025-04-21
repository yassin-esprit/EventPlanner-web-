<?php

namespace App\Entity;

use App\Repository\ReservationEquipementRepository;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: ReservationEquipementRepository::class)]
#[ORM\Table(name: 'reservation_equipement')]
class ReservationEquipement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    #[Assert\NotNull(message: "Veuillez sélectionner un équipement")]
    #[ORM\ManyToOne(targetEntity: Equipement::class, inversedBy: 'reservationEquipements')]
    #[ORM\JoinColumn(name: 'id_eq', referencedColumnName: 'id_eq', nullable: false)]
    private ?Equipement $equipement = null;

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'reservationEquipements')]
    #[ORM\JoinColumn(name: 'id_event', referencedColumnName: 'id', nullable: true)]
    private ?Evenement $evenement = null;

    #[Assert\NotBlank(message: "La date de réservation est obligatoire")]
    #[Assert\Type(type: \DateTimeInterface::class, message: "Date invalide")]
    #[Assert\GreaterThanOrEqual("today", message: "La date ne peut pas être dans le passé")]
    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: false)]
    private ?\DateTimeInterface $date_reservation = null;

    #[Assert\NotBlank(message: "Le statut est obligatoire")]
    #[Assert\Choice(
        choices: ['confirme', 'annule', 'attente'],
        message: "Statut invalide. Choisissez un statut valide."
    )]
    #[ORM\Column(type: 'string', length: 20)]
    private ?string $statut = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getEquipement(): ?Equipement
    {
        return $this->equipement;
    }

    public function setEquipement(?Equipement $equipement): self
    {
        $this->equipement = $equipement;
        return $this;
    }

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    public function getDateReservation(): ?\DateTimeInterface
    {
        return $this->date_reservation;
    }

    public function setDateReservation(\DateTimeInterface $date_reservation): self
    {
        $this->date_reservation = $date_reservation;
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
    public function __construct()
    {
    $this->statut = 'attente';
    }
}
