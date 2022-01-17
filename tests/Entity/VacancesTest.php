<?php

namespace App\Tests\Entity;

use App\Entity\Vacances;
use Symfony\Bundle\FrameworkBundle\Test\KernelTestCase;

class VacancesTest extends KernelTestCase
{
    public function testValidVacances(): void
    {
        $vacances = (new Vacances())
            ->setDateDebut(new \DateTime('now'))
            ->setDateFin(new \DateTime('now'))
            ->setAutoriser(true);
        self::bootKernel();
        $error = self::$container->get('validator')->validate($vacances);
        $this->assertCount(0, $error);
    }
}
