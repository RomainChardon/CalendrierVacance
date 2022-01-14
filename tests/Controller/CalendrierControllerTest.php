<?php

namespace App\Tests\Controller;

use App\Entity\Groupe;
use App\Entity\User;
use Symfony\Bundle\FrameworkBundle\Test\WebTestCase;
use Symfony\Component\HttpFoundation\Response;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\BrowserKit\Cookie;
use Symfony\Component\Security\Core\Authentication\Token\UsernamePasswordToken;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class CalendrierControllerTest extends WebTestCase
{

    use CreateUser;

    private EntityManagerInterface $manager;
    private UserPasswordEncoderInterface $encoder;

    public function __construct(EntityManagerInterface $manager, UserPasswordEncoderInterface $encoder)
    {
        $this->manager = $manager;
        $this->encoder = $encoder;
    }

    public function testPageCalendier(): void
    {
        $client = static::createClient();
        $client->request('GET', '/vacances/calendrier');
        $this->assertResponseStatusCodeSame(Response::HTTP_UNAUTHORIZED);
    }

    // $crawler = $client->request('GET', '/');
        // $csrfToken = $client->getContainer()->get("security.csrf.token_manager")->getToken('authenticate');
        // $form = $crawler->selectButton('Connexion')->form([
        //     'username' => 'rchardon',
        //     'password' => 'toto',
        //     'csrf_token' => $csrfToken,
        // ]);
        // $client->submit($form);
        // $this->assertResponseStatusCodeSame(302);
        
        // $client->followRedirect();
        // $this->assertResponseStatusCodeSame(200);
        // //$this->assertResponseRedirects('vacances/calendrier');
        
        // dd($client);

    public function testConnexion(): void
    {
        $client = static::createClient();
        $user = (new User())
            ->setUsername('rchardon')
            ->setRoles(['ROLE_ADMIN'])
            ->setPassword('$argon2id$v=19$m=65536,t=4,p=1$hZpxC3CwbyHkj4Ldqdwx7A$j9khFofLQYiCRcKULBqYy4Bp+PCSJ5CyTmnC1nsfxqA')
            ->setNom('Chadon')
            ->setPrenom('Romain')
            ->setMail('rchardon@gmail.com')
            ->setGroupe(new Groupe('dev', "#FFF"));
        
        $session = $client->getContainer()->get('session');
        $token = new UsernamePasswordToken($user, null, 'main', $user->getRoles());
        $session->set('_security_main', serialize($token));
        $session->save();

        $cookie = new Cookie($session->getName(), $session->getId());
        $client->getCookieJar()->set($cookie);

        $client->request('GET', '/vacances/calendrier');
        $this->assertResponseStatusCodeSame(Response::HTTP_OK);
        
    }
}
