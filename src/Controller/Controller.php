<?php

namespace App\Controller;

use DateTime;
use DateTimeImmutable;
use App\Entity\Vacances;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use App\Repository\UserRepository;
use App\Repository\VacancesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Symfony\Component\Routing\Annotation\Route;
use phpDocumentor\Reflection\PseudoTypes\False_;
use Symfony\Component\Mime\Email;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;


#[Route('/vacances')]
class Controller extends AbstractController
{
   
    #[Route('/vacances', name: 'index')]
    public function index(UserRepository $repoUser, VacancesRepository $repoVacances): Response
    {
        return $this->render('/index.html.twig', [
            'tousLesUtilisateurs' => $repoUser->findAll(),
            'toutesLesVacances' => $this->getUser()->getVacances(),
        ]);
    }

    #[Route('/createVacance', name: 'create_vacances')]
    public function create_vacances(Request $request, UserRepository $repoUser): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $utilisateur = $repoUser->find($this->getUser());
        $vacances = new Vacances();

        $dateDebut = new DateTimeImmutable($request->request->get('date_debut'));
        $h0 = new DateTimeImmutable('0:0:0');
        $h12 = new DateTimeImmutable('12:0:0');
        if ($request->request->get('demiJournee') == null) {
            $dateFin = new DateTimeImmutable($request->request->get('date_fin'));

            if ( $request->request->get('maladie') == 'true') {
                $vacances->setMaladie('1');
            } elseif ($request->request->get('congesSansSoldes')) {
                $vacances->setSansSoldes('1');
                
            } else {
                $diff = $dateDebut->diff($dateFin);
                $nbConges = $utilisateur->getNbConges() - $diff->d;
    
                $utilisateur->setNbConges($nbConges);
            }


        } else {
            
            $demiJournee = $request->request->get('demiJournee');
            $dateFin = $dateDebut;
            if ($demiJournee == "matin") {
                $horraire = "Matin";
                $vacances->setDemiJournee($horraire);
            } elseif ($demiJournee == "aprem") {
                $horraire = "Aprés-Midi";
                $vacances->setDemiJournee($horraire);
            }

            if ( $request->request->get('maladie') == 'true') {
                $vacances->setMaladie('1');
            } elseif ($request->request->get('congesSansSoldes')) {
                // Ne fait rien
            } else {
                $nbConges = $utilisateur->getNbConges() - 0.5;
    
                $utilisateur->setNbConges($nbConges);
            }
        }
        $vacances->setDateDebut($dateDebut);
        $vacances->setDateFin($dateFin);
        $vacances->setAutoriser('0');
        $vacances->setAttente('1');

        

        $utilisateur->addVacance($vacances);
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        $this->addFlash(
            'succes',
            'Vacances ajouté !!'
        );

        return $this->redirectToRoute("index");
    }

    #[Route('/removeVacances/{id}/delete', name:'remove_vacance')]
    public function removeVacances(Vacances $vacances,EntityManagerInterface $manager): Response
    {
        $manager->remove($vacances);
        $manager->flush();

        $this->addFlash(
            'msg',
            "Vacances supprimé !!");

        return $this->redirectToRoute("index");
    }

    #[Route('/modifVacance/{id}/modif', name: 'modif_vacance')]
    public function afficherVacance(Vacances $vacances, EntityManagerInterface $manager): Response
    {
        return $this->render('modifier.html.twig', [
            'vacanceID' => $vacances,
        ]);
           
    }

    #[Route('/modifierVacance/{id}/modif', name: 'modifier_vacance')]
    public function modif_vacance(Vacances $vacances, Request $request, EntityManagerInterface $manager): Response
    {
        
        $dateDebut = new DateTimeImmutable($request->request->get('date_debut'));
        $dateFin = new DateTimeImmutable($request->request->get('date_fin'));
        
        $vacances->setDateDebut($dateDebut);
        $vacances->setDateFin($dateFin);

        $manager->flush();

        $this->addFlash(
            'succes',
            'Vacances modifié !!'
        );

        return $this->redirectToRoute("index");
    }

    #[Route('/demandeVacance/{id}/demande', name: 'demande_vacance')]
    public function demandeVacance(Vacances $vacances, EntityManagerInterface $manager): Response
    {

        return $this->render('demandeVacance.html.twig', [
            'vacanceID' => $vacances,
        ]);
           
    }

    #[Route('/autoriseVacance/{id}/modif', name: 'autorise_vacance')]
    public function autoriseVacances( UserRepository $userRepo, Vacances $vacances, Request $request, EntityManagerInterface $manager,MailerInterface $mailer): Response
    {
        $utilisateur = $userRepo->find($this->getUser());
        $vacances->setAttente('0');
        $vacances->setAutoriser('1');
        $dateDébut = $vacances->getDateDebut();
        $dateFin = $vacances->getDateFin();
        $dateDébut = $dateDébut->format('d/m/Y');
        $dateFin = $dateFin->format('d/m/Y');
        $maladie = $vacances->getMaladie();
        $sansSoldes = $vacances->getSansSoldes();


        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($utilisateur->getMail())
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject("Confirmation d'autorisation de vos congés");

        if ($maladie == "1") {
            $email->html("<p> Votre arrêt du $dateDébut au $dateFin sont autorisé par la direction. </p>");
        } elseif ($sansSoldes == "1") {
            $email->html("<p> Vos congés sans soldes du $dateDébut au $dateFin sont autorisé par la direction. </p>");
        } else {
            $email->html("<p> Vos Vacances du $dateDébut au $dateFin sont autorisé par la direction. </p>");
        }

        $mailer->send($email);

        $manager->flush();

       
        return $this->redirectToRoute("calendrier");    
    }

    #[Route('/nonAutoriseVacance/{id}/modif', name: 'nonAutorise_vacance')]
    public function nonAutoriseVacances( Vacances $vacances, Request $request, EntityManagerInterface $manager): Response
    {

        $vacances->setAttente('0');
        $vacances->setAutoriser('0');

        $manager->flush();
        return $this->redirectToRoute("calendrier");    
    }

    #[Route('/etat_vacance/{id}/confirmation', name:'etat_vacance')]
    public function afficherEtat(Vacances $vacances,EntityManagerInterface $manager): Response
    {
        return $this->render('/etatVacance.html.twig', [
            'vacanceID' => $vacances
        ]);
    }
}
