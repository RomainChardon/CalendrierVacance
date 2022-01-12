<?php

namespace App\Tests\Entity;

use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class UserTest extends KernelTestCase
{
    public function testValidUser(): void
    {
        $user = (new User())
            ->setUsername('rchardon')
            ->setRoles([])
            ->setPassword("toto")
            ->setNom("Chardon")
            ->setPrenom("Romain")
            ->setNbConges(0);
        self::bootKernel();
        $error = self::$container->get('validator')->validate($user);
        $this->assertCount(0, $error);
    }
}
