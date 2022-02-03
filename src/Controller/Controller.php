<?php

namespace App\Controller;

use DateTime;
use DateInterval;
use DateTimeImmutable;
use App\Entity\Vacances;
use App\Entity\Utilisateur;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\Mapping\Entity;
use Symfony\Component\Mime\Email;
use App\Repository\UserRepository;
use App\Repository\VacancesRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Filesystem\Filesystem;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\HttpFoundation\Response;
use phpDocumentor\Reflection\PseudoTypes\True_;
use Symfony\Component\Routing\Annotation\Route;
use phpDocumentor\Reflection\PseudoTypes\False_;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Mailer\Exception\TransportExceptionInterface;


#[Route('/vacances')]
class Controller extends AbstractController
{
   
    #[Route('/vacances', name: 'index')]
    public function index(UserRepository $repoUser, VacancesRepository $repoVacances): Response
    {
        $jourActuel = new DateTime('now');
        return $this->render('/index.html.twig', [
            'jourActuel' => $jourActuel,
            'tousLesUtilisateurs' => $repoUser->findAll(),
            'toutesLesVacances' => $this->getUser()->getVacances(),
        ]);
    }

    #[Route('/recap', name: 'recap')]
    public function recap(UserRepository $repoUser): Response
    {
        $utilisateur = $repoUser->find($this->getUser());
        $nbConges = $utilisateur->getNbConges();
        $jourRestant = 25 - $nbConges;
        return $this->render('/recap.html.twig', [
            'user' => $utilisateur,
            'jourRestant' => $jourRestant,
        ]);
    }
    #[Route('/createVacance', name: 'create_vacances')]
    public function create_vacances(Request $request, UserRepository $repoUser,MailerInterface $mailer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $utilisateur = $repoUser->find($this->getUser());
        $vacances = new Vacances();

        $dateDebut = new DateTimeImmutable($request->request->get('date_debut'));

        if ($request->request->get('demiJournee') == null) {
            $dateFin = new DateTimeImmutable($request->request->get('date_fin'));

            if ( $request->request->get('maladie') == 'true') {
                $vacances->setMaladie('1');
            } elseif ($request->request->get('congesSansSoldes')) {
                $vacances->setSansSoldes('1');
            } else {
                $diff = $dateDebut->diff($dateFin);
                $diff = intval($diff->format('%a'));

                // Géré le cas ou la vacances est pour une seule journée
                if ($diff == 0) {
                    $diff += 1;
                }    
                $nbConges = $utilisateur->getNbConges() - $diff;


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
        }

        $ajd = new DateTime("now");
        $vacances->setDateDemande($ajd);
        $vacances->setDateDebut($dateDebut);
        $vacances->setDateFin($dateFin);
        $vacances->setAutoriser('0');
        $vacances->setAttente('1');
        
        $utilisateur->addVacance($vacances);
        $entityManager->persist($utilisateur);
        $entityManager->flush();
        
        $diffAjd = $dateDebut->diff($ajd);
        $diffAjd = intval($diffAjd->format('%a'));

        $dateEnd = $dateFin->add(new DateInterval('P1D'));

        // Create the ics file
        $fs = new Filesystem();

        //temporary folder, it has to be writable
        $tmpFolder = $this->getParameter('kernel.project_dir') . '/tmp/';

        //the name of your file to attach
        $fileName = 'meeting.ics';

$icsContent = "
BEGIN:VCALENDAR
VERSION:2.0
CALSCALE:GREGORIAN
METHOD:REQUEST
BEGIN:VEVENT
DTSTART:".$dateDebut->format('Ymd')."
DTEND:".$dateEnd->format('Ymd')."
ORGANIZER;CN=Adeo Informatique:mailto:adeo-informatique@gmail.com
UID:".rand(5, 1500)."
DESCRIPTION:"." Vacances du ".$dateDebut->format('d/m/Y')." au ".$dateFin->format('d/m/Y')."
SEQUENCE:0
STATUS:CONFIRMED
SUMMARY:Vacances
TRANSP:OPAQUE
END:VEVENT
END:VCALENDAR"
;

        //creation of the file on the server
        $icfFile = $fs->dumpFile($tmpFolder.$fileName, $icsContent);

        $dateDebut = $dateDebut->format('d/m/Y');
        $dateFin = $dateFin->format('d/m/Y');

        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($utilisateur->getMail())
        ->subject("Confirmation d'enregistrement de vos congés")
        ->html("<p> Vos Vacances du $dateDebut au $dateFin sont enregistrées par la direction. </p>")
        ->attachFromPath($tmpFolder.$fileName, null, 'text/calendar');

        $mailer->send($email);

        if ($diffAjd < 15) {
            $this->addFlash(
                'msg',
                'Vacances ajouté mais les poser 15j avant est le bienvenue !!'
            );
        } else {
            $this->addFlash(
                'succes',
                'Vacances ajouté !!'
            );
        }

        return $this->redirectToRoute("index");
    }

    #[Route('/removeVacances/{id}/delete', name:'remove_vacance')]
    public function removeVacances(Vacances $vacances,EntityManagerInterface $manager,UserRepository $repoUser): Response
    {
        $utilisateur = $repoUser->find($this->getUser()); 

        $dateAjd = new \DateTime("now");
        $dateDebut = $vacances->getDateDebut();
        $dateFin = $vacances->getDateFin();

        $diff = $dateDebut->diff($dateFin);
        $diffAjd = $dateAjd->diff($dateDebut);

        $diffAjd = intval($diffAjd->format('%R%a'));
        $diff = intval($diff->format('%a'));

        // Géré le cas ou la vacances est pour une seule journée
        if ($diff == 0) {
            $diff += 1;
        }

        if ($diffAjd >= 0) {
            $nbConges = $utilisateur->getNbConges();
            $utilisateur->setNbConges($nbConges + $diff);
        }

        $manager->remove($vacances);
        $manager->flush();

        $this->addFlash(
            'msg',
            "Vacances supprimé !!");

        return $this->redirectToRoute("index");
    }

    // PARTIE ANNULER

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

    
    #[Route('/annulerVacance/{id}/demande', name: 'demande_annulation')]
    public function textAnnulation(Vacances $vacances, EntityManagerInterface $manager): Response
    {

        return $this->render('textAnnuler.html.twig', [
            'vacanceID' => $vacances,
        ]);
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
    public function annulerVacances(Request $request, Vacances $vacances,EntityManagerInterface $manager, UserRepository $repoUser): Response
    {
        $utilisateur = $repoUser->find($this->getUser()); 
        
        if ($vacances->getAnnuler() != null) {
            $this->addFlash(
                'msg',
                "Votre demande à déjà était enregistrée");
        } else {
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

        // Texte d'explication
        $textAnnuler = $request->request->get('explication');
        $vacances->setTextAnnuler($textAnnuler);
        $manager->flush();

        $this->addFlash(
            'succes',
            "Votre demande à était enregistrée !!");
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
            $email->html("<p> Votre arrêt maladie du $dateDébut au $dateFin sont autorisé par la direction. </p>");
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
