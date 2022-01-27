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
    public function create_vacances(Request $request, UserRepository $repoUser,MailerInterface $mailer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $utilisateur = $repoUser->find($this->getUser());
        $vacances = new Vacances();

        $dateDebut = new DateTimeImmutable($request->request->get('date_debut'));

        $ajd = new DateTimeImmutable('now');
        $diffAjd = $dateDebut->diff($ajd);
        $diffAjd = intval($diffAjd->format('%a'));


        if ($request->request->get('demiJournee') == null) {
            $dateFin = new DateTimeImmutable($request->request->get('date_fin'));
            $diff = $dateDebut->diff($dateFin);
            $diff = intval($diff->format('%a'));

            if ( $request->request->get('maladie') == 'true') 
            {
                $vacances->setMaladie('1');
            } 
            elseif ($request->request->get('congesSansSoldes')) 
            {
                $vacances->setSansSoldes('1');
            } 
            else 
            {
                $nbConges = $utilisateur->getNbConges() - $diff;  
                $utilisateur->setNbConges($nbConges);
            }
            if ($diff == 0) 
            {
                $nbConges = $utilisateur->getNbConges() - 1;  
                $utilisateur->setNbConges($nbConges);
            }

            if($request->request->get('rtt') == true){
                $vacances->setRtt('1');
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
            if($request->request->get('rtt') == true){
                $vacances->setRtt('1');
            }
        return $this->redirectToRoute("index");
        }

        $vacances->setDateDebut($dateDebut);
        $vacances->setDateFin($dateFin);
        $vacances->setAutoriser('0');
        $vacances->setAttente('1');
        $vacances->setDateDemande($ajd);


        // Autre méthode d'actualisation de congés
        
        // $interval = $dateDebut->diff($dateFin);
        // $interval = intval($interval->format('%a'));
        // $nbCongesActual = $utilisateur->getNbConges();
        // $utilisateur->setNbConges($nbCongesActual - $interval);

        $utilisateur->addVacance($vacances);
        $entityManager->persist($utilisateur);
        $entityManager->flush();
        if ($diffAjd <= 14){
            $this->addFlash(
                'msg',
                "Vacances ajouté \n Rappel : Il est préférable d'annoncer ses congés 15j avant"
            );
        } else {
            $this->addFlash(
                'succes',
                'Vacances ajouté !!'
            );
        }

        $dateDebut = $dateDebut->format('Y-m-d');
        $dateFin = $dateFin->format('Y-m-d');
        // Envoie de mail 

        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($utilisateur->getMail())
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject("Confirmation de création de vacances");
        if ($diffAjd <= 14){
            $email->html("<p> Vos vacances du $dateDebut au $dateFin ont bien étaient enregistrées, elles vont être traitées par la direction ! La prochaine fois veuillez les demandées
            au minimum 15j avant !</p>");
        } else {
            $email->html("<p> Vos vacances du $dateDebut au $dateFin ont bien étaient enregistrées, elles vont être traitées par la direction !");
        }

        $mailer->send($email);

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

    #[Route('/annulerVacances/{id}/delete', name:'refuserAnnuler_vacance')]
    public function refuserAnnuler(Vacances $vacances,EntityManagerInterface $manager): Response
    {
        $manager->remove($vacances);
        $manager->flush();

        $this->addFlash(
            'msg',
            "Demande refusé !!");

        return $this->redirectToRoute("index");
    }

    #[Route('/annulerVacances/{id}/validation', name:'validAnnuler_vacance')]
    public function validAnnulerVacances(Vacances $vacances,EntityManagerInterface $manager): Response
    {
        $dateAnnulation = new \DateTime('now');
        $vacances->setDateAnnulation($dateAnnulation);
        $vacances->setAnnuler("1");      
        $manager->flush();

        $this->addFlash(
            'msg',
            "Cette vacances à bien était annulée");

        return $this->redirectToRoute("index");
    }

    #[Route('/annulerVacances/{id}/annuler', name:'annule_vacance')]
    public function annulerVacances(Vacances $vacances,EntityManagerInterface $manager, UserRepository $repoUser): Response
    {
        $utilisateur = $repoUser->find($this->getUser());
        $dateDebut = $vacances->getDateDebut();
        $dateFin = $vacances->getDateFin();
        $diff = $dateDebut->diff($dateFin);
        $diff = intval($diff->format('%a'));
        
        // Géré le cas ou la vacances est pour une seule journée
        if ($diff == 0) {
            $diff += 1;
        }

        $nbConges = $utilisateur->getNbConges() + $diff;  
        $utilisateur->setNbConges($nbConges);

        $vacances->setAnnuler("0"); 
        
        if ($vacances->getAnnuler() == false and $vacances->getAnnuler() == null) {
            $manager->flush();
            dd($vacances->getAnnuler());
            $this->addFlash(
                'succes',
                "Votre demande à était enregistrée !!");
        } elseif ($vacances->getAnnuler() == "0" and $vacances->getAnnuler() != null) 
        {
            $this->addFlash(
                'msg',
                "Votre demande d'annulation à déjà était enregistrée");

        }


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
    public function modif_vacance(Vacances $vacances, Request $request, EntityManagerInterface $manager, UserRepository $userRepo): Response
    {
        $utilisateur = $userRepo->find($this->getUser());
        $nbConges = $utilisateur->getNbConges();

        //Recup des anciennes valeurs
        $debutOLD = $vacances->getDateDebut();
        $finOLD = $vacances->getDateFin();
        $diffOLD = $debutOLD->diff($finOLD);
        $diffOLD = intval($diffOLD->format('%a'));

        $dateDebut = new DateTimeImmutable($request->request->get('date_debut'));
        $dateFin = new DateTimeImmutable($request->request->get('date_fin'));
        
        $vacances->setDateDebut($dateDebut);
        $vacances->setDateFin($dateFin);

        $diffNEW = $dateDebut->diff($dateFin);
        $diffNEW = intval($diffNEW->format('%a'));

        if($diffOLD > $diffNEW)
        {
            $diff = intval($diffOLD - $diffNEW);
            $nbConges = $utilisateur->getNbConges() + $diff;
            $utilisateur->setNbConges($nbConges); 
        } elseif ($diffOLD < $diffNEW)
        {
            $diff = intval($diffNEW - $diffOLD);
            $nbConges = $utilisateur->getNbConges() - $diff;
            $utilisateur->setNbConges($nbConges); 
        }

        $manager->persist($utilisateur);
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
        ->subject("Confirmation d'accords de vos congés");

        if ($maladie == "1") {
            $email->html("<p> Votre arrêt maladie du $dateDébut au $dateFin sont sont accordées. </p>");
        } elseif ($sansSoldes == "1") {
            $email->html("<p> Vos congés sans soldes du $dateDébut au $dateFin sont sont accordées. </p>");
        } else {
            $email->html("<p> Vos Vacances du $dateDébut au $dateFin sont accordées. </p>");
        }

        $mailer->send($email);

        $this->addFlash(
            'succes',
            'Mail envoyé!'
        );

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
