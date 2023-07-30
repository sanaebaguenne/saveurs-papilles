<?php

namespace App\Controller;

use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Entity\Transporter;
use App\Entity\User;
use App\Repository\TransporterRepository;
use App\Service\CartService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Doctrine\Persistence\ManagerRegistry;
use Symfony\Bridge\Doctrine\Form\Type\EntityType;
use Symfony\Component\Form\Extension\Core\Type\ChoiceType;
use Symfony\Component\HttpFoundation\Request;

// class OrderController extends AbstractController
{
    #[Route('/order/create', name: 'order_index')]
    // public function index(): Response
    // {
    //     // if (!$this->getUser()){
    //     //     return $this->redirectToRoute('app_login');
    //     // }
    //     // $form = $this->createForm( OrderType::class, null,[
    //     //     'user' => $this->getUser()
    //     // ]);
    //     // return $this->render('order/index.html.twig', [
    //     //     'form' => $form->createView(),
    //     // ]);
    // }

   
    #[Route('/order/create', name: 'order_create')]
    class OrderController extends AbstractController
    {
        
    
//         #[Route('/payment', name: 'payment_stripe')]
//         public function paymentAction(TransporterRepository $repo, Request $request): Response
//         {
//             // Récupère l'utilisateur actuellement connecté
//             $user = $this->getUser();
            
//             if ($user) {
//                 // L'utilisateur est connecté, vous pouvez accéder à ses informations
//                 $firstName = $user->getFirstname();
//                 $lastName = $user->getLastname();
//                 $address = $user->getAddress();
//                 $codepostal = $user->getZipcode();
//                 $city = $user->getCity();
//             } else {
//                 // Gérer le cas où l'utilisateur n'est pas connecté
//                 // Par exemple, rediriger vers la page de connexion
//                 return $this->redirectToRoute('app_login');
//             }
    
            
//     $transporters = $repo->findAll();
   

//     $form = $this->createFormBuilder()
//     ->add('transporter', EntityType::class, [
//         'class' => Transporter::class,
//         'expanded' => true,
//         'multiple' => false,
//     ])
//     ->getForm();

//     $form->handleRequest($request);

//     if ($form->isSubmitted() && $form->isValid()) {
//         $selectedTransporter = $form->get('transporter')->getData();

//         // Faites quelque chose avec le transporteur sélectionné

//         // Redirigez l'utilisateur vers la page de paiement ou toute autre action appropriée
//         return $this->redirectToRoute('payment_process');
//     }
//     $selectedTransporter = $form['transporter']->getData();

//     return $this->render('order/index.html.twig', [
//         'firstName' => $firstName,
//         'lastName' => $lastName,
//         'address' => $address,
//         'codepostal' => $codepostal,
//         'city' => $city,
//         'transporters' => $transporters,
//         'selectedTransporter' => $selectedTransporter,
//         'form' => $form->createView(),
//     ]);
    
// }

#[Route('/cart', name: 'app_cart')]
public function inde(CartService $cs): Response
{
    $cartWithData = $cs->getCartWithData();
    $total = $cs->getTotal();


    return $this->render('order/index.html.twig', [
        'items' => $cartWithData,
        'total' => $total
    ]);
}


#[Route('/payment', name: 'payment_stripe')]
public function paymentAction(TransporterRepository $repo, Request $request,CartService $cs,EntityManagerInterface $em): Response
{

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

    return $this->render('order/index.html.twig', [
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
}

}
