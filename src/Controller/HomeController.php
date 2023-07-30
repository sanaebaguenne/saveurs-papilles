<?php

namespace App\Controller;

use App\Repository\ProductRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Security;
use Doctrine\ORM\EntityManagerInterface;

class HomeController extends AbstractController
{
    #[Route('/', name: 'home')]
    public function home(ProductRepository $repo): Response
    {
        $products = $repo->findAll();
        return $this->render('home/index.html.twig', [
            'title' => 'Bienvenue sur notre Boutique',
            'products' => $products
        ]);
    }
    #[Route('/accueil', name: 'accueil')]
    public function accuiel(ProductRepository $repo): Response
    {
        $products = $repo->findAll();
        return $this->render('home/accueil.html.twig', [
            'title' => 'Bienvenue sur notre Boutique',
            'products' => $products
        ]);
    }

    #[Route('/product/show/{id}', name: 'product_show')]
    public function show(ProductRepository $repo, $id)
    {
        /*
        Pour sélectionner un produit dans la BDD, nous utilisons le principe de route paramétrée
        Dans la route, on définit un paramètre de type {id}
        Lorsque nous transmettons dans l'URL par exemple une route '/blog/9', on envoie un id conne en BDD dans l'URL
        Symfony va automatiquement récupérer ce paramètre et le transmettre en argument de la méthode show()
        */
        $produit = $repo->find($id);
        return $this->render('product/show.html.twig', [
            'produit' => $produit
        ]);
    }

    #[Route('/profil', name: 'profil')]
    public function profil(Security $security): Response
    {
        $user = $security->getUser();
        dump($user);
        return $this->render('home/profil.html.twig', [
            'user' => $user
        ]);
    }

    // #[Route('/chocolats', name: 'chocolats')]
    // public function chocolats(ProductRepository $repo): Response
    // {
    //     $chocolat = $repo->findByCategory('chocolats');
    //     dump($user);
    //     return $this->render('home/chocolats.html.twig', [
    //         'chocolat' => $chocolat
    //     ]);
    // }

    #[Route('/chocolats', name: 'chocolats')]
public function chocolats(ProductRepository $repo): Response
{
    $chocolat = $repo->findByCategory('chocolats');
    
    return $this->render('home/chocolats.html.twig', [
        'chocolat' => $chocolat
    ]);
}

#[Route('/macarons', name: 'macarons')]
public function macarrons(ProductRepository $repo): Response
{
    $macaron = $repo->findByCategory('macarons');
    
    return $this->render('home/macaron.html.twig', [
        'macaron' => $macaron
    ]);
}

#[Route('/patisseries', name: 'patisseries')]
public function patisseries(ProductRepository $repo): Response
{
    $patisserie = $repo->findByCategory('patisseries');
    
    return $this->render('home/patisserie.html.twig', [
        'patisserie' => $patisserie
    ]);
}


#[Route('/gouters', name: 'gouters')]
public function gouters(ProductRepository $repo): Response
{
    $gouters = $repo->findByCategory('gouters');
    
    return $this->render('home/gouters.html.twig', [
        'gouters' => $gouters
    ]);
}

#[Route('/confiseries', name: 'confiseries')]
public function confiseries(ProductRepository $repo): Response
{
    $confiseries = $repo->findByCategory('confiseries');
    
    return $this->render('home/confiseries.html.twig', [
        'confiseries' => $confiseries
    ]);
}

// ...
// public function index(ProductRepository $productRepository): Response
// {
//     // Récupérer le produit depuis la base de données (vous devez adapter cette partie en fonction de votre logique)
//     $product = $productRepository->find($productId);

//     // ...

//     // Rendre le template en passant la variable `product` au contexte
//     return $this->render('cart/index.html.twig', [
//         'product' => $product,
//         // Autres variables de contexte si nécessaire
//     ]);
// }
// ...





}
