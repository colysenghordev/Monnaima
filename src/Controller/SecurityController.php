<?php

namespace App\Controller;

use App\Entity\User;
use App\Entity\Compte;
use App\Entity\Depot;
use App\Entity\Partenaire;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Serializer\SerializerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Validator\Validator\ValidatorInterface;





/**
 * @Route("/api")
 */
class SecurityController extends AbstractController
{
    /**
     * @Route("/register", name="register", methods={"POST"})
     */
    public function register(Request $request, UserPasswordEncoderInterface $passwordEncoder, EntityManagerInterface $entityManager, SerializerInterface $serializer, ValidatorInterface $validator)
    {
        $values = json_decode($request->getContent());
        $entityManager = $this->getDoctrine()->getManager();

        if(isset($values->username, $values->password, $values->roles, $values->prenom, $values->nom, $values->adresse, $values->telephone, $values->email, $values->photo)) {
            $user = new User();
            $user->setUsername($values->username);
            $user->setPassword($passwordEncoder->encodePassword($user, $values->password));
            $user->setRoles($values->roles);
            $user->setPrenom($values->prenom);
            $user->setNom($values->nom);
            $user->setAdresse($values->adresse);
            $user->setTelephone($values->telephone);
            $user->setEmail($values->email);
            $user->setPhoto($values->photo);

            $partenaire = new Partenaire();
            $partenaire->setRaisonSociale($values->raisonSociale);
            $partenaire->setNinea($values->ninea);
            $partenaire->setStatut($values->statut);
            $partenaire->setUser($user);

            $depot = new Depot();
            $depot->setuser($user);
            $depot->setMontant($values->montant);
            $depot->setDateDepot(new \DateTime());

            $compte = new Compte();
            $random = random_int(100000, 10000000000);
            $compte->setDepot($depot);
            $compte->setPartenaire($partenaire);
            $compte->setNumeroCompte("$random");
            $compte->setSolde($values->solde);

            $errors = $validator->validate($user);
            if(count($errors)) {
                $errors = $serializer->serialize($errors, 'json');
                return new Response($errors, 500, [
                    'Content-Type' => 'application/json'
                ]);
            }

            $entityManager->persist($user);
            $entityManager->persist($partenaire);
            $entityManager->persist($depot);
            $entityManager->persist($compte);
            $entityManager->flush();

            $data = [
                'status' => 201,
                'message' => 'Le partenaire: '.$partenaire->getNinea().' a bien été créer avec un muméro de compte : '.$compte->getNumerocompte()
            ];

            return new JsonResponse($data, 201);
        }
        $data = [
            'status' => 500,
            'message' => 'Vérifier les clés de renseignement'
        ];
        return new JsonResponse($data, 500);
    }

    /**
     * @Route("/caissier", name="ajout-caissier")
     */
    public function caissier(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $values = json_decode($request->getContent());
        $user->setPassword($passwordEncoder->encodePassword($user, $values->password));

        $entityManager->persist($user);
        $entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'Le caissier a bien été ajouté'
        ];

        return new JsonResponse($data, 201);
    }  
}