<?php

namespace App\Tests\Controller;

use App\Entity\Groupe;
use App\Entity\User;
use App\Tests\UserTrait;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Tools\SchemaTool;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CalendrierControllerTest extends WebTestCase
{

    use UserTrait;

    public function testPageCalendier(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vacances/calendrier');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testConnexion(): void
    {
        $user = $this->createUser();
        $client = $this->login($user);

        $session = $client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        $client->request('GET', 'vacances/gestionGroupe/groupe');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
    }

    public function testInscription() : void 
    {
        $user = $this->createUser();
        $client = $this->login($user);
        $client->request('GET', '/vacances/calendrier');

    }
}
