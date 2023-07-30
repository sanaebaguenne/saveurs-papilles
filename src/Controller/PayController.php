<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Stripe\Checkout\Session as CheckoutSession;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Stripe\Stripe;
use Stripe\Checkout\Session;
use Symfony\Component\HttpFoundation\RedirectResponse;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Routing\Generator\UrlGeneratorInterface;

class PayController extends AbstractController
{
    #[Route('/stripe/payment', name: 'stripe_payment')]
    public function payment(CartService $cs, EntityManagerInterface $em, RequestStack $rs, UrlGeneratorInterface $urlGenerator): Response
    {
        // Récupère les informations du panier
        $cartWithData = $cs->getCartWithData();

        // Calcule le montant total du panier
        $total = 0;
        $quantityTotal = 0;
        foreach ($cartWithData as $item) {
            $total += $item['product']->getPrice() * $item['quantity'];
        }

        // Configure les clés d'API de Stripe
        Stripe::setApiKey('sk_test_51NLiQ3ExeFhTPBzxNr7NxieBSEzebuf5eNWq5JaZKPNx0r1TK3es9NAt5CfnEv9cRfOuvKPCBTovbq2lRPgkyHmV00wbTNJvGV');

        // Crée une session de paiement avec Stripe
        $session = CheckoutSession::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency' => 'EUR',
                    'unit_amount' => $total * 100, // Le montant doit être en centimes (donc * 100)
                    'product_data' => [
                        'name' => 'Achat sur votre site', // Remplacez par le nom de votre produit
                    ],
                ],
                'quantity' => 1, // Tu peux ajuster cette quantité si nécessaire
            ]],
            'mode' => 'payment',
            'success_url' => $urlGenerator->generate('payment_success', [], UrlGeneratorInterface::ABSOLUTE_URL), // Page de succès après paiement
            'cancel_url' => $urlGenerator->generate('payment_cancel', [], UrlGeneratorInterface::ABSOLUTE_URL), // Page en cas d'annulation du paiement
        ]);

    //     $cartWithData = $cs->getCartWithData();

    // $total = 0; // Variable pour stocker le prix total de tous les produits
    // $quantityTotal = 0; // Variable pour stocker la quantité totale de tous les produits

    // // Créez une seule commande
    // $commande = new Order();
    // $commande->setStatut('en cours de traitement');
    // $commande->setUser($cs->user->getUser());
    // $commande->setCreatedAt(new \DateTime());
    
    // foreach ($cartWithData as $item) {
    //     $product = $item['product'];
    //     $quantity = $item['quantity'];

    //     // Vérifiez si le stock est suffisant pour le produit
    //     if ($product->getStock() < $quantity) {
    //         // Gérez le cas où le stock est insuffisant
    //         // ...
    //     } else {
    //         // Créez une instance d'OrderDetail pour chaque produit
    //         $orderDetail = new OrderDetail();
    //         $orderDetail->setProduct($product);
    //         $orderDetail->setQuantity($quantity);
    //         $orderDetail->setTotal($product->getPrice() * $quantity);

    //         // Ajoutez l'OrderDetail à la commande
    //         $commande->addOrderDetail($orderDetail);

    //         // Mettez à jour la quantité totale et le prix total
    //         $quantityTotal += $quantity;
    //         $total += $orderDetail->getTotal();

    //         // Mettez à jour le stock du produit
    //         $product->setStock($product->getStock() - $quantity);
    //         $em->persist($product);
    //     }
    // }

    // // Définissez la quantité totale et le prix total de la commande
    // $commande->setQuantity($quantityTotal);
    // $commande->setTotal($total);

    // // // Persistez la commande et les OrderDetail associés
    // $em->persist($commande);
    // $em->flush();

    // // Videz le panier
    // $cs->empty();
  

        // Redirige l'utilisateur vers la page de paiement Stripe
        return new RedirectResponse($session->url);

        // Tu peux également retourner une vue pour afficher une page de paiement, si tu en as besoin
        // return $this->render('payment/payment.html.twig', [
        //     'controller_name' => 'PaymentController',
        // ]);
    }

    #[Route('/payment/success', name: 'payment_success', methods: ['GET'])]
    public function successAction(CartService $cs, EntityManagerInterface $em, RequestStack $rs, UrlGeneratorInterface $urlGenerator): Response
    {
         $cartWithData = $cs->getCartWithData();

        $total = 0; // Variable pour stocker le prix total de tous les produits
        $quantityTotal = 0; // Variable pour stocker la quantité totale de tous les produits
    
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
    
        // Définissez la quantité totale et le prix total de la commande
        $commande->setQuantity($quantityTotal);
        $commande->setTotal($total);
    
        // // Persistez la commande et les OrderDetail associés
        $em->persist($commande);
        $em->flush();
    
        // Videz le panier
        $cs->empty();
      
        return $this->render('success/success.html.twig', [
            'title' => 'Thanks for your order!', // Passer la variable 'title' au modèle
        ]);
    }

    #[Route('/payment/cancel', name: 'payment_cancel', methods: ['GET'])]
    public function cancelAction(): Response
    {
        // Code pour la page d'annulation du paiement
        // ...

        return $this->render('success/cancel.html.twig', [
             'title' => 'Thanks for your order!', // Passer la variable 'title' au modèle
        ]);
    }

   
}
