<?php

namespace App\Tests\Controller;

use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CalendrierControllerTest extends WebTestCase
{

    use CreateUser;

    public function testPageCalendier(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vacances/calendrier');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testConnexion(EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $csrf_token = $client->getContainer()->get("security.csrf.token_manager")->getToken('authenticate');
        $form = $crawler->selectButton('Connexion')->form([
            'username' => 'rchardon',
            'password' => 'toto',
            'csrf_token' => $csrf_token
        ]);
        $client->submit($form);
        $this->assertResponseRedirects();
        $client->followRedirect();
    }

    public function testInscription(){

    }
   
}
