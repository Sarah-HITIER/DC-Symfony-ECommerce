<?php

namespace App\Entity;

use App\Repository\CartRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;

#[ORM\Entity(repositoryClass: CartRepository::class)]
class Cart
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\ManyToOne(inversedBy: 'carts')]
    #[ORM\JoinColumn(nullable: false)]
    private ?User $user = null;

    #[ORM\Column]
    private ?bool $state = null;

    #[ORM\Column(type: Types::DATE_MUTABLE, nullable: true)]
    private ?\DateTimeInterface $purchaseDate = null;

    #[ORM\OneToMany(mappedBy: 'cart', targetEntity: CartContents::class, orphanRemoval: true)]
    private Collection $cartContents;

    public function __construct()
    {
        $this->cartContents = new ArrayCollection();
    }

    public function getId(): ?int
    {
        return $this->id;
    }

    public function getUser(): ?User
    {
        return $this->user;
    }

    public function setUser(?User $user): self
    {
        $this->user = $user;

        return $this;
    }

    public function isState(): ?bool
    {
        return $this->state;
    }

    public function setState(bool $state): self
    {
        $this->state = $state;

        return $this;
    }

    public function getPurchaseDate(): ?\DateTimeInterface
    {
        return $this->purchaseDate;
    }

    public function setPurchaseDate(?\DateTimeInterface $purchaseDate): self
    {
        $this->purchaseDate = $purchaseDate;

        return $this;
    }

    /**
     * @return Collection<int, CartContents>
     */
    public function getCartContents(): Collection
    {
        return $this->cartContents;
    }

    public function addCartContent(CartContents $cartContent): self
    {
        if (!$this->cartContents->contains($cartContent)) {
            $this->cartContents->add($cartContent);
            $cartContent->setCart($this);
        }

        return $this;
    }

    public function removeCartContent(CartContents $cartContent): self
    {
        if ($this->cartContents->removeElement($cartContent)) {
            // set the owning side to null (unless already changed)
            if ($cartContent->getCart() === $this) {
                $cartContent->setCart(null);
            }
        }

        return $this;
    }

    public function __toString()
    {
        return $this->id;
    }
}
