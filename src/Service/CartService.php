<?php 


namespace App\Service;

use App\Entity\Commande;
use App\Entity\Order;
use App\Entity\OrderDetail;
use App\Repository\ProductRepository;
use App\Repository\ProduitRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\RequestStack;
use Symfony\Component\Security\Core\Security;
use Symfony\Component\Security\Core\User\UserInterface;

class CartService
{
    private $rs;
    private $repo;
    public $user;

    public function __construct(RequestStack $rs, ProductRepository $repo, Security $user)
    {   
        // hors d'un controller, nous devons faire les injections de dépendances dans un constructeur
        $this->rs = $rs;
        $this->repo = $repo;
        $this->user = $user;
    }

    public function add($id)
    {
        // nous allons récupérer ou créer une session grâce à la classe RequestStack
        $session = $this->rs->getSession();

        // je récupère l'attribut de session 'cart' s'il existe ou un tableau vide
        $cart = $session->get('cart', []);

        // si le produit existe déjà dans mon panier, j'incrémente sa quantité
        if(!empty($cart[$id]))
        {
            $cart[$id]++;
        }
        else
        {
            $cart[$id] = 1;
            // dans mon tableau $cart, à la case $id (qui correspond à l'id d'un produit), je donne la valeur 1
        }

        // je sauvegarde l'état de mon panier en session à l'attribut de session 'cart'
        $session->set('cart', $cart);
    }

    public function remove($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);

        // si le produit existe déjà dans mon panier, je le supprime du tableau $cart via unset()
        if(!empty($cart[$id]))
        {
            unset($cart[$id]);
        }
        $session->set('cart', $cart);
    }

    public function decrement($id)
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);
        if(!empty($cart[$id]))
        {
            if($cart[$id] > 1)
            {
                $cart[$id]--;
            }
            else
            {
                unset($cart[$id]);
            }
        }
        $session->set('cart', $cart);
    }

    public function empty()
    {
        $session = $this->rs->getSession();

        // solution 1 : remplacer le panier en session par un tableau vide
        $session->set('cart', []);
        $totalQuantity = 0;
        $session->set('qt', $totalQuantity);
    }

    public function getCartWithData()
    {
        $session = $this->rs->getSession();
        $cart = $session->get('cart', []);
        $totalQuantity = 0;
        // $totalQuantity va contenir le nombre total de produits de mon panier

        // je vais crée un nouveau tableau qui contiendra des objets Product et les quantités de chaque objet
        $cartWithData = [];

        // $cartWithData[] est un tableau multidimensionnel : pour chaque id qui se trouve dans le panier, nous allons créer un nouveau tableau dans $cartWithData[] qui contiendra 2 cases : product et quantity

        foreach($cart as $id => $quantity)
        {
            // cette syntaxe signifie : je crée une nouvelle case dans $cartWithData
            $cartWithData[] = [
                'product' => $this->repo->find($id),
                'quantity' => $quantity
            ];
            $totalQuantity += $quantity;
        }

        $session->set('qt', $totalQuantity);
        // je crée l'attribut de session 'totalQuantity' ayant la valeur de $totalQuantity

        return $cartWithData;
    }

    public function getTotal()
    {
        $total = 0; // j'initialise mon total

        // pour chaque produit dans mon paniern je récupère le total par produit puis je l'joute au total final
        $cartWithData = $this->getCartWithData(); // je récupère $cartWithData via la méthode su Service getCartWithData()

        foreach($cartWithData as $item)
        {
            if ($item['product'] !== null) {
                $totalItem = $item['product']->getPrice() * $item['quantity'];
                $total += $totalItem;
            }
            // équivaut à $total = $total + $totalItem
        }

        return $total;
    }

    // public function validation($em)
    // {
    //     $cartWithData = $this->getCartWithData();
    //     $total = $this->getTotal();

    //     for ($i = 0; $i < count($cartWithData ); $i++) 
    //     {
    //         if($cartWithData[$i]['product']->getStock() < $cartWithData[$i]['quantity'])
    //         {
    //             if($cartWithData[$i]['product']->getStock() > 0)
    //             {
    //                 $cartWithData[$i]['quantity'] = $cartWithData[$i]['product']->getStock();
    //             }
    //             else
    //             {
    //                 $this->remove($cartWithData[$i]['product']->getId());
    //             }
    //         }
    //         else
    //         {
    //             $commande = new Order();
    //             $commande->addProduct($cartWithData[$i]['product']);
    //             $commande->setQuantity($cartWithData[$i]['quantity']);
    //             $commande->setTotal($cartWithData[$i]['product']->getPrice() * $cartWithData[$i]['quantity']);
    //             $commande->setStatut('en cours de traitement');
    //             $commande->setUser($this->user->getUser());
    //             $commande->setCreatedAt(new \DateTime());
    //             $em->persist($commande);
    //         }
    //     }   
    //     // dd($commande);
    //     $em->flush();
    //     $this->empty();



    //     // dd($commande);

    // }

    // public function validation($em)
    // {
    //     $cartWithData = $this->getCartWithData();
    //     $total = $this->getTotal();
    
    //     $commande = new Order(); // Créer une seule instance de la classe Order en dehors de la boucle
    
    //     for ($i = 0; $i < count($cartWithData); $i++) 
    //     {
    //         if ($cartWithData[$i]['product']->getStock() < $cartWithData[$i]['quantity'])
    //         {
    //             if ($cartWithData[$i]['product']->getStock() > 0)
    //             {
    //                 $cartWithData[$i]['quantity'] = $cartWithData[$i]['product']->getStock();
    //             }
    //             else
    //             {
    //                 $this->remove($cartWithData[$i]['product']->getId());
    //             }
    //         }
    //         else
    //         {
    //             $commande->addProduct($cartWithData[$i]['product']); // Ajouter le produit à la commande existante
    //             $commande->setQuantity($commande->getQuantity() + $cartWithData[$i]['quantity']); // Mettre à jour la quantité totale
    //             $commande->setTotal($commande->getTotal() + ($cartWithData[$i]['product']->getPrice() * $cartWithData[$i]['quantity'])); // Mettre à jour le montant total
    //         }
    //     }   
    
    //     $commande->setStatut('en cours de traitement');
    //     $commande->setUser($this->user->getUser());
    //     $commande->setCreatedAt(new \DateTime());
    
    //     $em->persist($commande);
    //     $em->flush();
    //     $this->empty();
    // }

//     public function validation($em)
// // {
// //     $cartWithData = $this->getCartWithData();
// //     $total = $this->getTotal();

// //     $commande = new Order(); // Créer une seule instance de la classe Order en dehors de la boucle
// //     $commande->setStatut('en cours de traitement');
// //     $commande->setUser($this->user->getUser());
// //     $commande->setCreatedAt(new \DateTime());

// //     for ($i = 0; $i < count($cartWithData); $i++) 
// //     {
// //         if ($cartWithData[$i]['product']->getStock() < $cartWithData[$i]['quantity'])
// //         {
// //             if ($cartWithData[$i]['product']->getStock() > 0)
// //             {
// //                 $cartWithData[$i]['quantity'] = $cartWithData[$i]['product']->getStock();
// //             }
// //             else
// //             {
// //                 $this->remove($cartWithData[$i]['product']->getId());
// //             }
// //         }
// //         else
// //         {   $commandeProduct = new OrderDetail();
// //             $commandeProduct = setProduct($product);
// //             $commandeProduct = setQuantité($quantité);
// //             $commandeProduct = setTotal($product->getPrice() * $quantité);


// //             $commande->addProduct($cartWithData[$i]['product']); // Ajouter le produit à la commande existante
// //             $commande->setQuantity($commande->getQuantity() + $cartWithData[$i]['quantity']); // Mettre à jour la quantité totale
// //             $commande->setTotal($commande->getTotal() + ($cartWithData[$i]['product']->getPrice() * $cartWithData[$i]['quantity'])); // Mettre à jour le montant total
// //         }
// //     }   

// //     $em->persist($commande);
// //     $em->flush();
// //     $this->empty();
// // }


public function validation($em)
{
    $cartWithData = $this->getCartWithData(); // Récupération des données du panier
    $total = $this->getTotal();// Récupération du total du panier

    $commande = new Order(); // Création d'une nouvelle commande
    $commande->setStatut('en cours de traitement');// Définition de l'état de la commande
    $commande->setUser($this->user->getUser());  // Association de l'utilisateur actuel à la commande
    $commande->setCreatedAt(new \DateTime());  // Définition de la date et l'heure de création de la commande

    for ($i = 0; $i < count($cartWithData); $i++) // Boucle pour parcourir les éléments du panier
    {
        if ($cartWithData[$i]['product']->getStock() < $cartWithData[$i]['quantity'])// Vérification des stocks pour le produit actuel dans pan
        {
            if ($cartWithData[$i]['product']->getStock() > 0)// Vérification si le stock est supérieur à zéro
            {
                $cartWithData[$i]['quantity'] = $cartWithData[$i]['product']->getStock();  // Ajustement de la quantité dans le panier pour correspondre au stock disponible
            }
            else
            {
                $this->remove($cartWithData[$i]['product']->getId());   // Suppression du produit du panier s'il n'y a plus de stock disponible
            }
        }
        else
        {   
            $orderDetail = new OrderDetail();// Création d'un nouvel objet OrderDetail pour représenter les détails de commande pour le produit actuel
            $orderDetail->setProduct($cartWithData[$i]['product']);// Définition du produit associé à l'OrderDetail
            $orderDetail->setQuantity($cartWithData[$i]['quantity']);  // Définition de la quantité pour le produit dans l'OrderDetail
            $orderDetail->setTotal($cartWithData[$i]['product']->getPrice() * $cartWithData[$i]['quantity']);// Calcul du total pour le produit dans l'OrderDetail (prix du produit * quantité)

            $commande->addOrderDetail($orderDetail);// Ajout de l'OrderDetail à la commande

            $commande->setQuantity($commande->getQuantity() + $cartWithData[$i]['quantity']);// Mise à jour de la quantité totale de la commande en ajoutant la quantité du produit actuel
            $commande->setTotal($commande->getTotal() + ($cartWithData[$i]['product']->getPrice() * $cartWithData[$i]['quantity']));// Mise à jour du total de la commande en ajoutant le total du produit actuel
        }
    }   

    // $em->persist($commande);// Persistance de la commande dans la base de données
    // $em->flush();// Enregistrement des modifications dans la base de données
    $this->empty();// Vidage du panier
}





    




}