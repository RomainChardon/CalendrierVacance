<?php

namespace App\Tests;

use App\Entity\Groupe;
use App\Entity\User;
use Doctrine\ORM\Tools\SchemaTool;

trait UserTrait
{
    public function login($user)
    {
        $client = static::createClient();
        $doctrine = $client->getContainer()->get('doctrine');

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
        $doctrine->getManager()->flush();

        return $client;
    }

    public function createGroupe()
    {
        $groupe = (new Groupe())
            ->setNomGroupe('Dev')
            ->setCouleur('#FFF');

        return $groupe;
    }

    public function createUser()
    {
        $user = (new User())
            ->setUsername('rchardon')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$p7qP/12IPAz543KO1yymmQ$VESelxr6bDGigUeOpbIGc7ydFJHUcVpCogModOZD4t8') // toto
            ->setNom('Chadon')
            ->setPrenom('Romain')
            ->setNbConges(2)
            ->setMail('rchardon@gmail.com')
            ->setDesactiver(0)
            ->setCadre(0);
        // ->setGroupe($groupe);

        return $user;
    }
}
