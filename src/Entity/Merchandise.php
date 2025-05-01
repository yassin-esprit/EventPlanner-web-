<?php

namespace App\Entity;

use Doctrine\ORM\Mapping as ORM;
use App\Repository\MerchandiseRepository;
use Symfony\Component\Validator\Constraints as Assert;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Symfony\Component\Serializer\Annotation\Groups;

#[ORM\Entity(repositoryClass: MerchandiseRepository::class)]
class Merchandise
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    #[Groups(['merchandise:read', 'cart:read'])]
    private ?int $id = null;

    #[Assert\NotBlank(message: 'The name cannot be blank.')]
    #[Assert\Length(
        max: 255,
        maxMessage: 'The name cannot exceed {{ limit }} characters.'
    )]
    #[ORM\Column(length: 255)]
    #[Groups(['merchandise:read', 'cart:read'])]
    private ?string $name = null;

    #[ORM\Column(length: 255, nullable: true)]
    #[Groups(['merchandise:read', 'cart:read'])]
    private ?string $image = null;

    #[Assert\File(
        maxSize: "2M",
        mimeTypes: ["image/jpeg", "image/png", "image/gif"],
        mimeTypesMessage: "Please upload a valid image file (JPEG, PNG, GIF)"
    )]
    private $file;

    #[Assert\Length(
        max: 1000,
        maxMessage: 'The description cannot exceed {{ limit }} characters.'
    )]
    #[ORM\Column(length: 1000, nullable: true)]
    #[Groups(['merchandise:read', 'cart:read'])]
    private ?string $description = null;

    #[Assert\Positive(message: 'The price must be a positive number.')]
    #[ORM\Column]
    #[Groups(['merchandise:read', 'cart:read'])]
    private ?float $price = null;

    #[Assert\GreaterThanOrEqual(value: 0, message: 'The quantity cannot be negative.')]
    #[ORM\Column]
    #[Groups(['merchandise:read', 'cart:read'])]
    private ?int $quantity = null;

    #[ORM\OneToMany(mappedBy: 'merchandise', targetEntity: CartItem::class)]
    private Collection $cartItems;

    public function __construct()
    {
        $this->cartItems = new ArrayCollection();
    }

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

    public function setImage(?string $image): self
    {
        $this->image = $image;
        return $this;
    }

    public function getFile()
    {
        return $this->file;
    }

    public function setFile($file): self
    {
        $this->file = $file;
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

    public function getPrice(): ?float
    {
        return $this->price;
    }

    public function setPrice(float $price): self
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

    /**
     * @return Collection<int, CartItem>
     */
    public function getCartItems(): Collection
    {
        return $this->cartItems;
    }

    public function addCartItem(CartItem $cartItem): self
    {
        if (!$this->cartItems->contains($cartItem)) {
            $this->cartItems->add($cartItem);
            $cartItem->setMerchandise($this);
        }
        return $this;
    }

    public function removeCartItem(CartItem $cartItem): self
    {
        if ($this->cartItems->removeElement($cartItem)) {
            if ($cartItem->getMerchandise() === $this) {
                $cartItem->setMerchandise(null);
            }
        }
        return $this;
    }
}