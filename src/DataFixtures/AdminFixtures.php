<?php

namespace App\DataFixtures;

use App\Entity\User;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Common\Persistence\ObjectManager;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AdminFixtures extends Fixture
{
    private $encoder;

    public function __construct(UserPasswordEncoderInterface $encoder)
    {
    $this->encoder = $encoder;
    }
    public function load(ObjectManager $manager)
    {
        // $product = new Product();
        $user = new User();
        $user->setUsername('adminsup');
        $user->setRoles(['Admingénéral']);
        $password = $this->encoder->encodePassword($user, 'coly');
        $user->setPassword($password);
        $user->setPrenom('Coly');
        $user->setNom('Senghor');
        $user->setAdresse('Keur Massar');
        $user->setTelephone('77 193 31 21');
        $user->setEmail('sakouthiang@gmail.com');

        // $manager->persist($product);
        $manager->persist($user);
        $manager->flush();
    }
}
