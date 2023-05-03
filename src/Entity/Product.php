<?php

namespace App\Entity;

use App\Repository\ProductRepository;
use Doctrine\Common\Collections\ArrayCollection;
use Doctrine\Common\Collections\Collection;
use Doctrine\DBAL\Types\Types;
use Doctrine\ORM\Mapping as ORM;
use Symfony\Component\Validator\Constraints as Assert;

#[ORM\HasLifecycleCallbacks]
#[ORM\Entity(repositoryClass: ProductRepository::class)]
class Product
{
    #[ORM\Id]
    #[ORM\GeneratedValue]
    #[ORM\Column]
    private ?int $id = null;

    #[ORM\Column(length: 100)]
    #[Assert\NotBlank]
    #[Assert\Length(
        min: 2,
        max: 100,
        minMessage: 'The product name must be at least {{ limit }} characters long',
        maxMessage: 'The product name cannot be longer than {{ limit }} characters',
    )]
    private ?string $name = null;

    #[ORM\Column(type: Types::TEXT, nullable: true)]
    private ?string $description = null;

    #[ORM\Column]
    #[Assert\PositiveOrZero]
    private ?float $price = null;

    #[ORM\Column]
    #[Assert\Type('integer')]
    #[Assert\PositiveOrZero]
    private ?int $stock = null;

    #[ORM\Column(length: 50, nullable: true)]
    private ?string $image = null;

    #[ORM\OneToMany(mappedBy: 'product', targetEntity: CartContents::class, orphanRemoval: true)]
    private Collection $cartContents;

    public function __construct()
    {
        $this->cartContents = new ArrayCollection();
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

    public function getStock(): ?int
    {
        return $this->stock;
    }

    public function setStock(int $stock): self
    {
        $this->stock = $stock;

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

    /**
     * Delete image file when product is deleted
     */
    #[ORM\PostRemove]
    public function deleteImage()
    {
        if ($this->image) {
            unlink(__DIR__ . '/../../public/uploads/' . $this->image);
        }
        return true;
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
            $cartContent->setProduct($this);
        }

        return $this;
    }

    public function removeCartContent(CartContents $cartContent): self
    {
        if ($this->cartContents->removeElement($cartContent)) {
            // set the owning side to null (unless already changed)
            if ($cartContent->getProduct() === $this) {
                $cartContent->setProduct(null);
            }
        }

        return $this;
    }
}
