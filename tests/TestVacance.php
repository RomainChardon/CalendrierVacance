<?php
namespace App\Tests;

use App\Entity\User;
use App\Entity\Vacances;
use DateTimeImmutable;
use PHPUnit\Framework\TestCase;

class TestVacance extends TestCase
{
    public function testVacance()
    {
        $vacance = new Vacances();
        $user = new User();

        $vacance->setDateDebut(new DateTimeImmutable(date('19-02-1999')))
                ->setDateFin(new DateTimeImmutable(date('20-09-2000')));
        
        $user->addVacance($vacance);

        $this->assertEquals($vacance->getDateDebut(),$user->getVacances()->toArray()[0]->getDateDebut());
        $this->assertEquals($vacance->getDateFin(),$user->getVacances()->toArray()[0]->getDateFin());
    }
}