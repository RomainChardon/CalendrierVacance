<?php

namespace App\Tests;

use App\Entity\Vacances;
use Doctrine\ORM\Tools\SchemaTool;

trait VacanceTrait
{
    public function createVacance($user)
    {
        $client = static::createClient();
        $doctrine = $client->getContainer()->get('doctrine');

        $vacance = (new Vacances())
            ->setDateDebut('2022-02-16')
            ->setDateFin('2022-02-18')
            ->setAutoriser(1)
            ->setAttente(0)
            ->setUser($user);

        
        $doctrine->getManager()->persist($vacance);
        $doctrine->getManager()->flush();

        return $vacance;
    }

    public function pushVacance($vacance, $user)
    {

        static $metadata = null;

        if (null === $metadata) {
            $metadata = $doctrine->getManager()->getMetadataFactory()->getAllMetadata();
        }

        $schemaTool = new SchemaTool($doctrine->getManager());
        $schemaTool->dropDatabase();

        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }

        $doctrine->getManager()->persist($user);

        return $vacance;
    }
}
