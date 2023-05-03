<?php

namespace App\Controller;

use App\Entity\Product;
use App\Form\ProductType;
use App\Repository\ProductRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\File\Exception\FileException;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\String\Slugger\SluggerInterface;
use Symfony\Contracts\Translation\TranslatorInterface;

class ProductController extends AbstractController
{
    #[Route('/', name: 'app_product_index')]
    public function index(ProductRepository $productRepository): Response
    {
        return $this->render('product/index.html.twig', [
            'products' => $productRepository->findAll(),
        ]);
    }

    #[Route('/product/new', name: 'app_product_new')]
    public function new(EntityManagerInterface $em, Request $request, SluggerInterface $slugger, TranslatorInterface $translator): Response
    {
        // Redirect if user is not admin
        // $user = $this->getUser();
        // if (!in_array("ROLE_ADMIN", $user->getRoles())) {
        //     $this->addFlash('danger', $translator->trans('errors.not_access'));
        //     return $this->redirectToRoute('app_product_index');
        // }

        $product = new Product();
        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image !');
                }
                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $product->setImage($newFilename);
            }

            $em->persist($product);
            $em->flush();
            $this->addFlash('success', $translator->trans('product.added'));
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/new.html.twig', [
            'product' => $product,
            'add' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}', name: 'app_product_show')]
    public function show(TranslatorInterface $translator, Product $product = null): Response
    {
        if ($product == null) {
            $this->addFlash('danger', $translator->trans('error.product_not_found'));
            return $this->redirectToRoute('app_product_index');
        }
        return $this->render('product/show.html.twig', [
            'product' => $product,
        ]);
    }

    #[Route('/product/{id}/edit', name: 'app_product_edit')]
    public function edit(
        Request $request,
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        SluggerInterface $slugger,
        Product $product = null
    ): Response {
        if ($product == null) {
            $this->addFlash('danger', $translator->trans('error.product_not_found'));
            return $this->redirectToRoute('app_product_index');
        }

        $form = $this->createForm(ProductType::class, $product);
        $form->handleRequest($request); // Vérification de la requête
        if ($form->isSubmitted() && $form->isValid()) { // Si le formulaire est soumis et valide
            $imageFile = $form->get('image')->getData();

            if ($imageFile) {
                $originalFilename = pathinfo($imageFile->getClientOriginalName(), PATHINFO_FILENAME);
                $safeFilename = $slugger->slug($originalFilename);
                $newFilename = $safeFilename . '-' . uniqid() . '.' . $imageFile->guessExtension();
                // Move the file to the directory where images are stored
                try {
                    $imageFile->move(
                        $this->getParameter('upload_directory'),
                        $newFilename
                    );
                } catch (FileException $e) {
                    // ... handle exception if something happens during file upload
                    $this->addFlash('danger', 'Erreur lors de l\'upload de l\'image !');
                }
                // Delete the old image file
                $oldFilename = $product->getImage();
                if ($oldFilename) {
                    $filesystem = new Filesystem();
                    $filesystem->remove($this->getParameter('upload_directory') . '/' . $oldFilename);
                }

                // updates the 'imageFilename' property to store the PDF file name
                // instead of its contents
                $product->setImage($newFilename);
            }

            $em->persist($product); // Préparation de l'insertion
            $em->flush(); // Exécution de l'insertion
            $this->addFlash('success', $translator->trans('product.edited'));
            return $this->redirectToRoute('app_product_index');
        }

        return $this->render('product/edit.html.twig', [
            'product' => $product,
            'edit' => $form->createView(),
        ]);
    }

    #[Route('/product/{id}/delete', name: 'app_product_delete')]
    public function delete(
        EntityManagerInterface $em,
        TranslatorInterface $translator,
        Product $product = null
    ): Response {
        if ($product == null) {
            $this->addFlash('danger', $translator->trans('error.product_not_found'));
            return $this->redirectToRoute('app_product_index');
        }

        $em->remove($product);
        $em->flush();
        $this->addFlash('warning', $translator->trans('product.deleted'));

        return $this->redirectToRoute('app_product_index');
    }
}
