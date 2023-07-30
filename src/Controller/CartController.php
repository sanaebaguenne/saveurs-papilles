<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\ProductRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Stripe\Stripe;
use Stripe\Charge;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;
use App\Controller\PaymentController;
use App\Entity\Transporter;
use App\Repository\TransporterRepository;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;

class CartController extends AbstractController
{

    #[Route('/cart', name: 'app_cart')]
    public function index(CartService $cs): Response
    {
        $cartWithData = $cs->getCartWithData();
        $total = $cs->getTotal();


        return $this->render('cart/cart.html.twig', [
            'items' => $cartWithData,
            'total' => $total
        ]);
    }

    #[Route('/cart-validation', name: 'cart_validation')]
    public function cartValidation(CartService $cs, TransporterRepository $repo, Request $request,EntityManagerInterface $em): Response
    {
        $cartWithData = $cs->getCartWithData();
        $total = $cs->getTotal();
       


    $cartWithData = $cs->getCartWithData();

    $total = 0; // Variable pour stocker le prix total de tous les produits
    $quantityTotal = 0; // Variable pour stocker la quantité totale de tous les produits
    $totalAmount = 0; // Définissez une valeur par défaut
    

    // Créez une seule commande
    $commande = new Order();
    $commande->setStatut('en cours de traitement');
    $commande->setUser($cs->user->getUser());
    $commande->setCreatedAt(new \DateTime());
    
    foreach ($cartWithData as $item) {
        $product = $item['product'];
        $quantity = $item['quantity'];

        // Vérifiez si le stock est suffisant pour le produit
        if ($product->getStock() < $quantity) {
            // Gérez le cas où le stock est insuffisant
            // ...
        } else {
            // Créez une instance d'OrderDetail pour chaque produit
            $orderDetail = new OrderDetail();
            $orderDetail->setProduct($product);
            $orderDetail->setQuantity($quantity);
            $orderDetail->setTotal($product->getPrice() * $quantity);

            // Ajoutez l'OrderDetail à la commande
            $commande->addOrderDetail($orderDetail);

            // Mettez à jour la quantité totale et le prix total
            $quantityTotal += $quantity;
            $total += $orderDetail->getTotal();

            // Mettez à jour le stock du produit
            $product->setStock($product->getStock() - $quantity);
            $em->persist($product);
        }
    }

    // Récupère l'utilisateur actuellement connecté
    $user = $this->getUser();
    
    if (!$user) {
        // Gérer le cas où l'utilisateur n'est pas connecté
        // Par exemple, rediriger vers la page de connexion
        return $this->redirectToRoute('app_login');
    }

    

    $firstName = $user->getFirstname();
    $lastName = $user->getLastname();
    $address = $user->getAddress();
    $codepostal = $user->getZipcode();
    $city = $user->getCity();

    $transporters = $repo->findAll();

    $form = $this->createFormBuilder()
        ->add('transporter', EntityType::class, [
            'class' => Transporter::class,
            'expanded' => true,
            'multiple' => false,
        ])
        ->getForm();

    $form->handleRequest($request);
   
    if ($form->isSubmitted() && $form->isValid()) {
        $selectedTransporter = $form->get('transporter')->getData();

        // Récupérer le prix de livraison du transporteur sélectionné
        $deliveryPrice = $selectedTransporter->getPrice();

        // Calculer le montant total de la facture en ajoutant le prix de livraison au montant total des produits commandés
        $orderTotal = 0; // Remplacez cette valeur par le montant total des produits commandés
        $totalAmount = $total + $deliveryPrice;

        // Stocker le montant total de la facture dans la session ou une variable temporaire
        $this->get('session')->set('totalAmount', $totalAmount);

        // Rediriger vers la page de paiement ou toute autre action appropriée
        return $this->redirectToRoute('payment_process');
    }

    $selectedTransporter = null;

    return $this->render('cart/index.html.twig', [
        'firstName' => $firstName,
        'lastName' => $lastName,
        'address' => $address,
        'codepostal' => $codepostal,
        'city' => $city,
        'transporters' => $transporters,
        'selectedTransporter' => $selectedTransporter,
        'form' => $form->createView(),
        'items' => $cartWithData,
        'total' => $total,
        'totalAmount' => $totalAmount,
    ]);
   




       
    }

   
    // #[Route('/cart-validation', name: 'cart_validation')]
    // public function cart(CartService $cs): Response
    // {
    //     $cartWithData = $cs->getCartWithData();
    //     $total = $cs->getTotal();


    //     return $this->render('cart/cart.html.twig', [
    //         'items' => $cartWithData,
    //         'total' => $total
    //     ]);
    // }

    #[Route('/cart/add/{id}', name: 'cart_add')]
    public function add($id, CartService $cs) 
    {
      
        $cs->add($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/remove/{id}', name: 'cart_remove')]
    public function remove($id, CartService $cs)
    {
       
        $cs->remove($id);
        return $this->redirectToRoute('app_cart');
    }

    #[Route('/cart/decrement/{id}', name: 'cart_decrement')]
    public function decrement($id, CartService $cs)
    {
        
        $cs->decrement($id);
        return $this->redirectToRoute('app_cart');
    }
    
    #[Route('/cart/empty', name: 'cart_empty')]
    public function empty(CartService $cs)
    {
        // // solution 1 : remplacer le panier en session par un tableau vide
        // $session->set('cart', []);
        $cs->empty();
        return $this->redirectToRoute('app_cart');
        
        // solution 2 : utiliser remove() pour supprimer un attribut de session
        // $session->remove('cart');
        
        // solution 3 : utiliser clear() pour supprimer TOUS LES ATTRIBUTS de session
        // $session->clear();
    }
    
   

#[Route('/cart/order', name: 'cart_order')]
public function validation(CartService $cs, EntityManagerInterface $em, RequestStack $rs, UrlGeneratorInterface $urlGenerator)
{
    $cartWithData = $cs->getCartWithData();
    $stockSuffisant = true; // Variable pour vérifier si le stock est suffisant pour tous les produits

    // Créez une seule commande
    $commande = new Order();
    $commande->setStatut('en cours de traitement');
    $commande->setUser($cs->user->getUser());
    $commande->setCreatedAt(new \DateTime());

    foreach ($cartWithData as $item) {
        $product = $item['product'];
        $quantity = $item['quantity'];

        // Vérifiez si le stock est suffisant pour le produit
        if ($product->getStock() >= $quantity) {
            // Créez une instance d'OrderDetail pour chaque produit
            $orderDetail = new OrderDetail();
            $orderDetail->setProduct($product);
            $orderDetail->setQuantity($quantity);
            $orderDetail->setTotal($product->getPrice() * $quantity);

            // Ajoutez l'OrderDetail à la commande
            $commande->addOrderDetail($orderDetail);

            // Mettez à jour le stock du produit
            $product->setStock($product->getStock() - $quantity);
            $em->persist($product);
        } else {
            // Stock insuffisant pour au moins un produit, marquez le stock comme insuffisant
            $stockSuffisant = false;

            // Réduisez la quantité du produit dans le panier à la quantité en stock
            $nomProduit = $product->getTitle();
            $session = $rs->getSession();
            $cart = $session->get('cart', []);
            $cart[$product->getId()] = $product->getStock();
            $session->set('cart', $cart);

            // Affichez un message d'erreur pour le produit en rupture de stock
            $this->addFlash('info', "💬 La quantité du produit : $nomProduit a été réduite car notre stock est insuffisant, vérifiez votre panier à nouveau svp.");
        }
    }

    if (!$stockSuffisant) {
        // Si le stock est insuffisant pour au moins un produit, redirigez vers le panier pour que l'utilisateur puisse ajuster les quantités
        return $this->redirectToRoute('app_cart');
    }

    // Enregistrez la commande et les détails de la commande
    $em->persist($commande);
    $em->flush();

    // Redirection vers la page de paiement après la validation
    return new RedirectResponse($urlGenerator->generate('stripe_payment'));
}

}



// }

// }











