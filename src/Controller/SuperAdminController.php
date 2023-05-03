<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Entity\User;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class SuperAdminController extends AbstractController
{
    #[Route('/super-admin', name: 'app_super_admin')]
    public function index(EntityManagerInterface $em): Response
    {
        $carts = $em->getRepository(Cart::class)->findBy(['state' => false]);

        $users = $em
            ->createQuery(
                'SELECT u
                FROM App\Entity\User u
                WHERE u.registrationDate >= :today
                ORDER BY u.registrationDate DESC'
            )
            ->setParameter('today', new \DateTime('today'))
            ->getResult();

        return $this->render('super_admin/index.html.twig', [
            'controller_name' => 'SuperAdminController',
            'carts' => $carts,
            'users' => $users
        ]);
    }
}
