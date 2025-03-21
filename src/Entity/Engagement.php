<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\EngagementRepository;

#[ORM\Entity(repositoryClass: EngagementRepository::class)]
#[ORM\Table(name: 'engagements')]
class Engagement
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

    #[ORM\ManyToOne(targetEntity: Participant::class, inversedBy: 'engagements')]
    #[ORM\JoinColumn(name: 'participant_id', referencedColumnName: 'id')]
    private ?Participant $participant = null;

    public function getParticipant(): ?Participant
    {
        return $this->participant;
    }

    public function setParticipant(?Participant $participant): self
    {
        $this->participant = $participant;
        return $this;
    }

    #[ORM\ManyToOne(targetEntity: Evenement::class, inversedBy: 'engagements')]
    #[ORM\JoinColumn(name: 'evenement_id', referencedColumnName: 'id')]
    private ?Evenement $evenement = null;

    public function getEvenement(): ?Evenement
    {
        return $this->evenement;
    }

    public function setEvenement(?Evenement $evenement): self
    {
        $this->evenement = $evenement;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: true)]
    private ?string $role = null;

    public function getRole(): ?string
    {
        return $this->role;
    }

    public function setRole(?string $role): self
    {
        $this->role = $role;
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

    #[ORM\Column(type: 'datetime', nullable: true)]
    private ?\DateTimeInterface $date_engagement = null;

    public function getDate_engagement(): ?\DateTimeInterface
    {
        return $this->date_engagement;
    }

    public function setDate_engagement(?\DateTimeInterface $date_engagement): self
    {
        $this->date_engagement = $date_engagement;
        return $this;
    }

    public function getDateEngagement(): ?\DateTimeInterface
    {
        return $this->date_engagement;
    }

    public function setDateEngagement(?\DateTimeInterface $date_engagement): static
    {
        $this->date_engagement = $date_engagement;

        return $this;
    }

}
