<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use App\Entity\Cat;
use App\Repository\CatRepository;
use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

/**
 * @Route("/atelierapi")
 */
class CatController extends AbstractController
{
    // Point d'entrée pour l'affichage de tous les chat
    /**
     * @Route("/cats", name="cats_list", methods={"GET"})
     */
    public function list(CatRepository $catRepository): Response
    {
        // Je récupère tous les objets chats sous forme de tableau d'objets
        $cats = $catRepository->findAll();
        // J'instancie mon sérializer (nb : vous pouvez le charger plus simplement en passant par l'interface)
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        // Je sérialize cad que je transforme les objets en une chaîne de caractères au format JSON
        $cats = $serializer->serialize($cats, "json");
        // Je crée une réponse de type json
        $response = new Response($cats);
        $response->headers->set('Content-Type', 'application/json');
        // Je renvoie ma réponse au client qui recoit les objets au format JSON
        return $response;
        // Version raccourcie qui sérialize et envoie automatiquement tout au format JSON
        // return $this->json($cats);
    }

    // Point d'entrée pour l'affichage d'un chat par son id
    /**
     * @Route("/cats/{id}", name="cats_show", methods={"GET"})
     */
    public function show(CatRepository $catRepository, int $id): Response
    {
        // Je récupère le chat demandé en BDD à l'aide de son id passé à la requête
        $cat = $catRepository->find($id);
        // je le retourne au format JSON
        return $this->json($cat);
    }

    // Point d'entrée pour la création d'un chat par post (formulaire chez le client)
    /**
     * @Route("/cats", name="cats_new", methods={"POST"})
     */
    public function new(Request $request): Response
    {
        $serializer = new Serializer([new ObjectNormalizer()], [new JsonEncoder()]);
        // On transforme la chaîne JSON contenu dans la requête en un objet de classe Cat
        $cat = $serializer->deserialize($request->getContent(), Cat::class ,"json");
        // On enregistre ce chat en BDD
        $manager = $this->getDoctrine()->getManager();
        $manager->persist($cat);
        $manager->flush();
        // On retourune une réponse avec le code 201 correspondant à la création d'une ressources
        return new Response('Cat created !', Response::HTTP_CREATED);
    }
}
