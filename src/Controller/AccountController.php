<?php

namespace App\Controller;

use App\Entity\Cart;
use App\Form\RegistrationFormType;
use App\Form\UserType;
use App\Security\UserAuthenticator;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\UserAuthenticatorInterface;
use Symfony\Component\Security\Core\User\PasswordAuthenticatedUserInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

#[Route('/account')]
class AccountController extends AbstractController
{
    #[Route('/', name: 'app_account')]
    public function index(
        Request $request,
        EntityManagerInterface $em,
        UserPasswordHasherInterface $userPasswordHasher,
        TranslatorInterface $translator
    ): Response {
        $user = $this->getUser();
        $form = $this->createForm(UserType::class, $user);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($user);
            $em->flush();
            $this->addFlash('success', $translator->trans('user.edited'));
        }

        $orders = $em->getRepository(Cart::class)->findBy(['user' => $user, 'state' => true]);

        return $this->render('account/index.html.twig', [
            'controller_name' => 'AccountController',
            'user' => $user,
            'edit' => $form->createView(),
            'orders' => $orders
        ]);
    }

    #[Route('/orders/{id}', name: 'app_account_order_detail')]
    public function orderDetail(EntityManagerInterface $em, $id): Response
    {
        $user = $this->getUser();
        $order = $em->getRepository(Cart::class)->findOneBy(['user' => $user, 'state' => true, 'id' => $id]);

        return $this->render('account/order_detail.html.twig', [
            'controller_name' => 'AccountController',
            'user' => $user,
            'order' => $order
        ]);
        // return $this->render('cart/index.html.twig', [
        //     'controller_name' => 'CartController',
        //     'cart' => $order,
        // ]);
    }
}
