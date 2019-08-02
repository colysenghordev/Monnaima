<?php

namespace App\Controller;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\Serializer\SerializerInterface;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class MonnaimaController extends AbstractController
{
    /**
     * @Route("/ajout-usersimple", name="ajout-usersimple")
     * @IsGranted("ROLE_adminpartenaire")
     */
    public function usersimple(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $passwordEncoder, ValidatorInterface $validator)
    {
        $user = $serializer->deserialize($request->getContent(), User::class, 'json');

        $values = json_decode($request->getContent());
        $user->setPassword($passwordEncoder->encodePassword($user, $values->password));

        $users = $validator->validate($user);
        if(count($users)) {
            $users = $serializer->serialize($users, 'json');
            return new Response($users, 500, [
                'Content-Type' => 'application/json'
            ]);
        }

        $entityManager->persist($user);
        $entityManager->flush();

        $data = [
            'status' => 201,
            'message' => 'L\'utilisateur simple a bien été ajouté'
        ];

        return new JsonResponse($data, 201);
    }  
}
