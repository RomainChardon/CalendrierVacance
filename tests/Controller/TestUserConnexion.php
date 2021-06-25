<?php 

namespace App\Tests\Controller;

use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\HttpFoundation\Response;

class TestUserConnexion extends WebTestCase
{
    public function test_create_user()
    {
        
    }

    public function test_user_connexion()
    {
       $client = $this->createClient();

        $crawler = $client->request('GET', '/');

        //dd($crawler);
        $formulaire = $crawler->selectButton('Connexion')->form([
            "username" => "chardon.romain",
            "password" => "toto",   
        ]);
        
        $client->submit($formulaire);
        $this->assertResponseRedirects('/vacances/calendrier');
        $client->followRedirect();
    }
}