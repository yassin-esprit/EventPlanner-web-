<?php
namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use App\Repository\UserRepository;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: UserRepository::class)]
#[ORM\Table(name: 'user')]
class User implements UserInterface, PasswordAuthenticatedUserInterface, \Serializable
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(name: 'userId' ,type: 'integer')]
    private ?int $userId = null;

    #[ORM\Column(name: 'firstName', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $firstName = null;

    #[ORM\Column(name: 'lastName', type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 2, max: 50)]
    private ?string $lastName = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Email]
    private ?string $email = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    #[Assert\Length(min: 6)]
    private ?string $password = null;

    #[ORM\Column(type: 'string', nullable: false)]
    #[Assert\NotBlank]
    private ?string $phone = null;

#[ORM\Column(type: 'string', length: 20, nullable: false)]
private ?string $Role = null; // Ensure the property name is lowercase 'role'



    #[ORM\OneToMany(targetEntity: Rating::class, mappedBy: 'user')]
    private Collection $ratings;

    #[ORM\ManyToMany(targetEntity: Evenement::class, inversedBy: 'users')]
    #[ORM\JoinTable(
        name: 'participation',
        joinColumns: [
            new ORM\JoinColumn(name: 'user_id', referencedColumnName: 'userId')
        ],
        inverseJoinColumns: [
            new ORM\JoinColumn(name: 'evenement_id', referencedColumnName: 'id')
        ]
    )]
    private Collection $evenements;

    public function __construct()
    {
        $this->ratings = new ArrayCollection();
        $this->evenements = new ArrayCollection();
    }

    public function getUserId(): ?int
    {
        return $this->userId;
    }

    public function setUserId(int $userId): self
    {
        $this->userId = $userId;
        return $this;
    }

    public function getFirstName(): ?string
    {
        return $this->firstName;
    }

    public function setFirstName(string $firstName): self
    {
        $this->firstName = $firstName;
        return $this;
    }

    public function getLastName(): ?string
    {
        return $this->lastName;
    }

    public function setLastName(string $lastName): self
    {
        $this->lastName = $lastName;
        return $this;
    }

    public function getPhone(): ?string
    {
        return $this->phone;
    }

    public function setPhone(string $phone): self
    {
        $this->phone = $phone;
        return $this;
    }

    public function getEmail(): ?string
    {
        return $this->email;
    }

    public function setEmail(string $email): self
    {
        $this->email = $email;
        return $this;
    }

    public function getPassword(): ?string
    {
        return $this->password;
    }

    public function setPassword(string $password): self
    {
        $this->password = $password;
        return $this;
    }

    public function getRole(): ?string
    {
        return $this->Role;
    }

    public function setRole(string $Role): self
    {
        $this->Role = $Role;
        return $this;
    }

public function getRoles(): array
    {
        // Ensure role starts with ROLE_ and is uppercase
        $role = strtoupper($this->Role);
        if (!str_starts_with($role, 'ROLE_')) {
            $role = 'ROLE_' . $role;
        }
        return [$role];
    }

    public function getUserIdentifier(): string
    {
        return $this->email;
    }

    public function eraseCredentials(): void
    {
        // If you store any temporary, sensitive data on the user, clear it here
    }

    public function getSalt(): ?string
    {
        return null;
    }


    public function serialize(): string
    {
        return serialize([$this->userId, $this->email, $this->password]);
    }

    public function unserialize($serialized): void
    {
        [$this->userId, $this->email, $this->password] = unserialize($serialized, ['allowed_classes' => false]);
    }

    /**
     * @return Collection<int, Rating>
     */
    public function getRatings(): Collection
    {
        return $this->ratings;
    }

    public function addRating(Rating $rating): self
    {
        if (!$this->ratings->contains($rating)) {
            $this->ratings->add($rating);
            $rating->setUser($this);
        }
        return $this;
    }

    public function removeRating(Rating $rating): self
    {
        if ($this->ratings->removeElement($rating)) {
            if ($rating->getUser() === $this) {
                $rating->setUser(null);
            }
        }
        return $this;
    }

    /**
     * @return Collection<int, Evenement>
     */
    public function getEvenements(): Collection
    {
        return $this->evenements;
    }

    public function addEvenement(Evenement $evenement): self
    {
        if (!$this->evenements->contains($evenement)) {
            $this->evenements->add($evenement);
        }
        return $this;
    }

    public function removeEvenement(Evenement $evenement): self
    {
        $this->evenements->removeElement($evenement);
        return $this;
    }
}