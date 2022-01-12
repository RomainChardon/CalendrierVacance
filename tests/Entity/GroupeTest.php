<?php

namespace App\Tests\Entity;

use App\Entity\Groupe;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class GroupeTest extends KernelTestCase
{
    public function testValidGroupe(): void
    {
        $groupe = (new Groupe())
            ->setNomGroupe("Cadre")
            ->setCouleur("#FF5733");
        self::bootKernel();
        $error = self::$container->get('validator')->validate($groupe);
        $this->assertCount(0, $error);
    }
}







