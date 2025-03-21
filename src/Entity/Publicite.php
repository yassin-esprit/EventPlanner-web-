<?php

namespace App\Entity;

use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;

use App\Repository\PubliciteRepository;

#[ORM\Entity(repositoryClass: PubliciteRepository::class)]
#[ORM\Table(name: 'publicites')]
class Publicite
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private ?int $id_pub = null;

    public function getId_pub(): ?int
    {
        return $this->id_pub;
    }

    public function setId_pub(int $id_pub): self
    {
        $this->id_pub = $id_pub;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $titre_pub = null;

    public function getTitre_pub(): ?string
    {
        return $this->titre_pub;
    }

    public function setTitre_pub(string $titre_pub): self
    {
        $this->titre_pub = $titre_pub;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $type_pub = null;

    public function getType_pub(): ?string
    {
        return $this->type_pub;
    }

    public function setType_pub(string $type_pub): self
    {
        $this->type_pub = $type_pub;
        return $this;
    }

    #[ORM\Column(type: 'text', nullable: false)]
    private ?string $contenu_pub = null;

    public function getContenu_pub(): ?string
    {
        return $this->contenu_pub;
    }

    public function setContenu_pub(string $contenu_pub): self
    {
        $this->contenu_pub = $contenu_pub;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $emplacement_pub = null;

    public function getEmplacement_pub(): ?string
    {
        return $this->emplacement_pub;
    }

    public function setEmplacement_pub(string $emplacement_pub): self
    {
        $this->emplacement_pub = $emplacement_pub;
        return $this;
    }

    #[ORM\Column(type: 'string', nullable: false)]
    private ?string $statut_pub = null;

    public function getStatut_pub(): ?string
    {
        return $this->statut_pub;
    }

    public function setStatut_pub(string $statut_pub): self
    {
        $this->statut_pub = $statut_pub;
        return $this;
    }

    #[ORM\OneToMany(targetEntity: CampagnesPublicitaire::class, mappedBy: 'publicite')]
    private Collection $campagnesPublicitaires;

    public function __construct()
    {
        $this->campagnesPublicitaires = new ArrayCollection();
    }

    /**
     * @return Collection<int, CampagnesPublicitaire>
     */
    public function getCampagnesPublicitaires(): Collection
    {
        if (!$this->campagnesPublicitaires instanceof Collection) {
            $this->campagnesPublicitaires = new ArrayCollection();
        }
        return $this->campagnesPublicitaires;
    }

    public function addCampagnesPublicitaire(CampagnesPublicitaire $campagnesPublicitaire): self
    {
        if (!$this->getCampagnesPublicitaires()->contains($campagnesPublicitaire)) {
            $this->getCampagnesPublicitaires()->add($campagnesPublicitaire);
        }
        return $this;
    }

    public function removeCampagnesPublicitaire(CampagnesPublicitaire $campagnesPublicitaire): self
    {
        $this->getCampagnesPublicitaires()->removeElement($campagnesPublicitaire);
        return $this;
    }

    public function getIdPub(): ?int
    {
        return $this->id_pub;
    }

    public function getTitrePub(): ?string
    {
        return $this->titre_pub;
    }

    public function setTitrePub(string $titre_pub): static
    {
        $this->titre_pub = $titre_pub;

        return $this;
    }

    public function getTypePub(): ?string
    {
        return $this->type_pub;
    }

    public function setTypePub(string $type_pub): static
    {
        $this->type_pub = $type_pub;

        return $this;
    }

    public function getContenuPub(): ?string
    {
        return $this->contenu_pub;
    }

    public function setContenuPub(string $contenu_pub): static
    {
        $this->contenu_pub = $contenu_pub;

        return $this;
    }

    public function getEmplacementPub(): ?string
    {
        return $this->emplacement_pub;
    }

    public function setEmplacementPub(string $emplacement_pub): static
    {
        $this->emplacement_pub = $emplacement_pub;

        return $this;
    }

    public function getStatutPub(): ?string
    {
        return $this->statut_pub;
    }

    public function setStatutPub(string $statut_pub): static
    {
        $this->statut_pub = $statut_pub;

        return $this;
    }

}
