<?php

namespace App\Entity;
use Knp\Bundle\TimeBundle\DateTimeFormatter;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EvenementRepository;

#[ORM\Entity(repositoryClass: EvenementRepository::class)]
#[ORM\Table(name: 'evenement')]
class Evenement
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id = null;

    public function getId(): ?int
    {
        return $this->id;
    }

    public function setId(int $id): self
    {
        $this->id = $id;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $titre = null;

    public function getTitre(): ?string
    {
        return $this->titre;
    }

    public function setTitre(string $titre): self
    {
        $this->titre = $titre;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $type = null;

    public function getType(): ?string
    {
        return $this->type;
    }

    public function setType(string $type): self
    {
        $this->type = $type;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $description = null;

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;
        return $this;
    }

    #[ORM\Column(type: 'date', nullable: false)]
    private ?\DateTimeInterface $date = null;

    public function getDate(): ?\DateTimeInterface
    {
        return $this->date;
    }

    public function setDate(\DateTimeInterface $date): self
    {
        $this->date = $date;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $lieu = null;

    public function getLieu(): ?string
    {
        return $this->lieu;
    }

    public function setLieu(string $lieu): self
    {
        $this->lieu = $lieu;
        return $this;
    }

    #[ORM\Column(type: 'integer', nullable: false)]
    private ?int $nbrplaces = null;

    public function getNbrplaces(): ?int
    {
        return $this->nbrplaces;
    }

    public function setNbrplaces(int $nbrplaces): self
    {
        $this->nbrplaces = $nbrplaces;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Sponsor::class, inversedBy: 'evenements')]
    #[ORM\JoinColumn(name: 'sponsor', referencedColumnName: 'id_sponsor')]
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

    #[ORM\OneToMany(targetEntity: Engagement::class, mappedBy: 'evenement')]
    private Collection $engagements;

    /**
     * @return Collection<int, Engagement>
     */
    public function getEngagements(): Collection
    {
        if (!$this->engagements instanceof Collection) {
            $this->engagements = new ArrayCollection();
        }
        return $this->engagements;
    }

    public function addEngagement(Engagement $engagement): self
    {
        if (!$this->getEngagements()->contains($engagement)) {
            $this->getEngagements()->add($engagement);
        }
        return $this;
    }

    public function removeEngagement(Engagement $engagement): self
    {
        $this->getEngagements()->removeElement($engagement);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'evenement')]
    private Collection $ratings;

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        if (!$this->ratings instanceof Collection) {
            $this->ratings = new ArrayCollection();
        }
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->getRatings()->contains($rating)) {
            $this->getRatings()->add($rating);
        }
        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        $this->getRatings()->removeElement($rating);
        return $this;
    }

    #[ORM\OneToMany(targetEntity: ReservationEquipement::class, mappedBy: 'evenement')]
    private Collection $reservationEquipements;

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

    #[ORM\ManyToMany(targetEntity: User::class, inversedBy: 'evenements')]
    #[ORM\JoinTable(
        name: 'participation',
        joinColumns: [
            new ORM\JoinColumn(name: 'evenement_id', referencedColumnName: 'id')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'userId')
        ]
    )]
    private Collection $users;

    public function __construct()
    {
        $this->engagements = new ArrayCollection();
        $this->ratings = new ArrayCollection();
        $this->reservationEquipements = new ArrayCollection();
        $this->users = new ArrayCollection();
    }

    /**
     * @return Collection<int, User>
     */
    public function getUsers(): Collection
    {
        if (!$this->users instanceof Collection) {
            $this->users = new ArrayCollection();
        }
        return $this->users;
    }

    public function addUser(User $user): self
    {
        if (!$this->getUsers()->contains($user)) {
            $this->getUsers()->add($user);
        }
        return $this;
    }

    public function removeUser(User $user): self
    {
        $this->getUsers()->removeElement($user);
        return $this;
    }


    public function getTimeUntilStart(DateTimeFormatter $formatter): string
{
    $now = new \DateTime();
    if ($this->date > $now) {
        return 'Starts ' . $formatter->formatDiff($this->date, $now);
    }
    return 'Started ' . $formatter->formatDiff($this->date, $now);
}
}
