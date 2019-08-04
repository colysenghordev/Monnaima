<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Depot;
use App\Entity\Partenaire;
use App\Repository\PartenaireRepository;
use App\Repository\CompteRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Repository\DepotRepository;

// ajout user-simple par le partenaire
class MonnaimaController extends AbstractController
{
    /**
     * @Route("/ajout-usersimple", name="ajout-usersimple")
     */
    public function usersimple(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $values = json_decode($request->getContent());
        $user->setPassword($passwordEncoder->encodePassword($user, $values->password));

        $entityManager->persist($user);
        $entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'L\'utilisateur simple a bien été ajouté'
        ];

        return new JsonResponse($data, 201);
    }  

    /**
     * @Route("/ajout-partenaire", name="ajout-partenaire")
     */
    public function ajoutpartenaire(Request $request)
    {
        $values        = json_decode($request->getContent());
        $entityManager = $this->getDoctrine()->getManager();

        $userRepo  = $this->getDoctrine()->getRepository(User::class);
        $user      = $userRepo->find($values->user);

        $partenaire    = new Partenaire();

        $partenaire->setRaisonSociale($values->raisonSociale);
        $partenaire->setNinea($values->ninea);
        $partenaire->setStatut($values->statut);
        $partenaire->setUser($user);

        $entityManager->persist($partenaire);
        $entityManager->flush();

        return new Response("le partenaire a été ajouté avec success");
    }

    /**
     * @Route("/partenaires", name="listerpartenaire",methods={"GET"})
     */
    public function listerPartenaire(PartenaireRepository $partenaireRepository, SerializerInterface $serializer)
    {
        $partenaire = $partenaireRepository->findAll();
        $data      = $serializer->serialize($partenaire, 'json');
        return new Response($data, 200, []);
    }

    /**
     * @Route("/depot", name="depot")
     */
    public function depot(Request $request)
    {
        $values          = json_decode($request->getContent());
        $entityManager   = $this->getDoctrine()->getManager();

        $userRepo        = $this->getDoctrine()->getRepository(User::class);
        $user            = $userRepo->find($values->user);

        $depot           = new Depot();

        $depot->setuser($user);
        $depot->setMontant($values->montant);
        $depot->setDateDepot(new \DateTime('2019-10-10'));

        $entityManager->persist($depot);
        $entityManager->flush();

        return new Response("Votre depot a été ajouté avec success");
    }

    /**
     * @Route("/depots", name="listerdepot",methods={"GET"})
     */
    public function listerdepot(DepotRepository $depotRepository, SerializerInterface $serializer)
    {
        $depots = $depotRepository->findAll();
        $data = $serializer->serialize($depots, 'json');

        return new Response($data, 200, [
            'Content-Type' => 'application/json'
        ]);
    }

    /**
     * @Route("/ajoutcompte", name="ajout_compte_bancaire")
     */
    public function compte(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager)
    {
        $values          = json_decode($request->getContent());
        $entityManager   = $this->getDoctrine()->getManager();

        $random = random_int(100000, 10000000000);

        $depotRepo  = $this->getDoctrine()->getRepository(Depot::class);
        $depot     = $depotRepo->find($values->depot);
        $partenaireRepo  = $this->getDoctrine()->getRepository(Partenaire::class);
        $partenaire      = $partenaireRepo->find($values->partenaire);

        $compte = new Compte();

        $compte->setDepot($depot);
        $compte->setPartenaire($partenaire);
        $compte->setNumeroCompte("$random");
        $compte->setSolde($values->solde);

        $entityManager->persist($compte);
        $entityManager->flush();

        return new Response("le compte a été ajouté avec success");
    }

    /**
     * @Route("/comptes", name="lister_compte_bancaire",methods={"GET"})
     */
    public function listercompte(CompteRepository $compteRepository, SerializerInterface $serializer)
    {
        $compte = $compteRepository->findAll();
        $data   = $serializer->serialize($compte, 'json');
        return new Response($data, 200, []);
    }
}
