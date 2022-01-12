<?php

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;

class CalendrierControllerTest extends WebTestCase
{

    use CreateUser;

    public function testPageCalendier(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vacances/calendrier');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    public function testConnexion(): void
    {
        $client = static::createClient();
        $crawler = $client->request('GET', '/');
        $form = $crawler->selectButton('Connexion')->form([
            'username' => 'rchardon',
            'password' => 'toto'
        ]);
        $client->submit($form);
        // $client->followRedirect();
        // $this->assertResponseRedirects('/vacance/calendrier');

        dd($client->getRequest()->getUri());
        
    }
}
