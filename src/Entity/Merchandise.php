<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MerchandiseRepository;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\Entity(repositoryClass: MerchandiseRepository::class)]
class Merchandise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column(type: 'integer')]
    private $id;

    #[Assert\NotBlank(message: 'The name cannot be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'The name cannot exceed {{ limit }} characters.'
    )]
    #[ORM\Column(type: 'string', length: 255)]
    private $name;

    #[ORM\Column(type: 'string', length: 255)]
    private $image;

    #[Assert\Length(
        max: 1000,
        maxMessage: 'The description cannot exceed {{ limit }} characters.'
    )]
    #[ORM\Column(type: 'text')]
    private $description;

    #[Assert\Positive(message: 'The price must be a positive number.')]
    #[ORM\Column(type: 'decimal', precision: 10, scale: 2)]
    private $price;

    #[Assert\GreaterThanOrEqual(value: 0, message: 'The quantity cannot be negative.')]
    #[ORM\Column(type: 'integer')]
    private $quantity;

    // Getters and setters...

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getName(): ?string
    {
        return $this->name;
    }

    public function setName(string $name): self
    {
        $this->name = $name;

        return $this;
    }

    public function getImage(): ?string
    {
        return $this->image;
    }

    public function setImage(string $image): self
    {
        $this->image = $image;

        return $this;
    }

    public function getDescription(): ?string
    {
        return $this->description;
    }

    public function setDescription(string $description): self
    {
        $this->description = $description;

        return $this;
    }

    public function getPrice(): ?string
    {
        return $this->price;
    }

    public function setPrice(string $price): self
    {
        $this->price = $price;

        return $this;
    }

    public function getQuantity(): ?int
    {
        return $this->quantity;
    }

    public function setQuantity(int $quantity): self
    {
        $this->quantity = $quantity;

        return $this;
    }
}