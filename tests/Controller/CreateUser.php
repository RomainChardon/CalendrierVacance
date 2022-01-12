<?php

namespace App\Tests\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

trait CreateUser
{
    public function userFixture(UserPasswordEncoderInterface $encoder, EntityManagerInterface $manager) :User
    {
        $user = new User();
        $user->setUsername('rchardon')
            ->setRoles([])
            ->setNom("Chardon")
            ->setPrenom("Romain")
            ->setNbConges(0)
            ->setPassword($encoder->encodePassword($user, 'toto'));

        $manager->persist($user);
        $manager->flush();

        return $user;
    }
}