<?php

namespace App\Tests\Controller;

use App\Tests\UserTrait;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;

class CalendrierControllerTest extends WebTestCase
{
    use UserTrait;

    public function testPageConnexion(): void
    {
        $client = static::createClient();
        $client->request('GET', '/');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }

    public function testPageGestionUser(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vacances/gestionUtilisateur/utilisateur');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

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

        $client->request('GET', 'vacances/calendrier');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
    }
}
