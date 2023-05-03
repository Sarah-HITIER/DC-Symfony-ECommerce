<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\CartContents;
use App\Entity\Product;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class CartController extends AbstractController
{
    #[Route('/cart', name: 'app_cart')]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'state' => false]);

        return $this->render('cart/index.html.twig', [
            'controller_name' => 'CartController',
            'cart' => $cart,
        ]);
    }

    #[Route('/cart/add/{id}', name: 'app_cart_add')]
    public function addToCart(EntityManagerInterface $em, Product $product = null): Response
    {
        $user = $this->getUser();
        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'state' => false]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setState(false);
            $em->persist($cart);
            $em->flush();
        }

        $cartContents = $em->getRepository(CartContents::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if (!$cartContents) {
            $cartContents = new CartContents();
            $cartContents->setCart($cart);
            $cartContents->setProduct($product);
            $cartContents->setQuantity(1);
        } else {
            $cartContents->setQuantity($cartContents->getQuantity() + 1);
        }
        $em->persist($cartContents);
        $em->flush();

        return $this->redirectToRoute('app_product_show', ['id' => $product->getId()]);
    }

    #[Route('/cart/remove/{id}', name: 'app_cart_remove')]
    public function removeFromCart(EntityManagerInterface $em, Product $product = null): Response
    {
        $user = $this->getUser();
        $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'state' => false]);
        if (!$cart) {
            $cart = new Cart();
            $cart->setUser($user);
            $cart->setState(false);
            $em->persist($cart);
            $em->flush();
        }

        $cartContents = $em->getRepository(CartContents::class)->findOneBy(['cart' => $cart, 'product' => $product]);
        if ($cartContents) {
            $em->remove($cartContents);
            $em->flush();
        }

        $allCartContents = $em->getRepository(CartContents::class)->findBy(['cart' => $cart]);
        if (!$allCartContents) {
            $em->remove($cart);
            $em->flush();
        }

        return $this->redirectToRoute('app_cart');
    }
}
