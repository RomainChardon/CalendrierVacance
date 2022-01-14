<?php

namespace App\Tests\Controller;

use App\Entity\Groupe;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CalendrierControllerTest extends WebTestCase
{

    public function testPageCalendier(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vacances/calendrier');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testConnexion(): void
    {
        $client = static::createClient();
        $doctrine = $client->getContainer()->get('doctrine');

        static $metadata = null;

        if ($metadata === null) {
            $metadata = $doctrine->getManager()->getMetadataFactory()->getAllMetadata();
        }

        $schemaTool = new SchemaTool($doctrine->getManager());
        $schemaTool->dropDatabase();

        if (!empty($metadata)) {
            $schemaTool->createSchema($metadata);
        }
        

        $user = (new User())
            ->setUsername('rchardon')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$p7qP/12IPAz543KO1yymmQ$VESelxr6bDGigUeOpbIGc7ydFJHUcVpCogModOZD4t8')
            ->setNom('Chadon')
            ->setPrenom('Romain')
            ->setMail('rchardon@gmail.com');
            //->setGroupe(new Groupe('dev', "#FFF"));

        $doctrine->getManager()->persist($user);
        $doctrine->getManager()->flush();
        
        
        $session = $client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        $client->request('GET', 'vacances/gestionGroupe/groupe');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
    }
}
