<?php

namespace App\Controller;

use App\Entity\Cart;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class StripeController extends AbstractController
{
    #[Route('/stripe', name: 'app_stripe')]
    public function index(): Response
    {
        return $this->render('stripe/index.html.twig', [
            'controller_name' => 'StripeController',
        ]);
    }

    #[Route('/stripe/payment', name: 'app_stripe_payment')]
    public function payment(EntityManagerInterface $em): Response
    {
        $stripeSecretKey = $this->getParameter('stripe_sk');
        \Stripe\Stripe::setApiKey($stripeSecretKey);


        try {
            // Calculate total amount
            $total = 0;
            $cart = $em->getRepository(Cart::class)->findOneBy(['user' => $this->getUser(), 'state' => false]);
            $cartContents = $cart->getCartContents();
            foreach ($cartContents as $content) {
                $total += $content->getQuantity() * $content->getProduct()->getPrice();
                $product = $content->getProduct();
                $product->setStock($product->getStock() - $content->getQuantity());
                $em->persist($product);
            }

            // Create a PaymentIntent with amount and currency
            $paymentIntent = \Stripe\PaymentIntent::create([
                'amount' => $total * 100, // amount in cents
                'currency' => 'eur',
                'automatic_payment_methods' => [
                    'enabled' => true,
                ],
            ]);

            $cart->setState(true);
            $cart->setPurchaseDate(new \DateTime());
            $em->persist($cart);
            $em->flush();

            $output = [
                'clientSecret' => $paymentIntent->client_secret,
            ];
            return new JsonResponse($output);
        } catch (\Error $e) {
            http_response_code(500);
            return new JsonResponse(['error' => $e->getMessage()], 500);
        }
    }
}
