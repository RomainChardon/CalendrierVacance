<?php

namespace App\Controller;

use App\Entity\Vacances;
use App\Event\MailEvent;
use App\Repository\UserRepository;
use App\Repository\VacancesRepository;
use App\Service\ICSGenerator;
use DateInterval;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vacances')]
class Controller extends AbstractController
{
    #[Route('/ajoutVacance', name: 'index')]
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
    public function create_vacances(Request $request, UserRepository $repoUser, MailerInterface $mailer, ICSGenerator $ICSGenerator, EventDispatcherInterface $dispatcher): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $utilisateur = $repoUser->find($this->getUser());
        $vacances = new Vacances();

        $dateDebut = new DateTimeImmutable($request->request->get('date_debut'));
        if (null == $request->request->get('demiJournee')) {
            $dateFin = new DateTimeImmutable($request->request->get('date_fin'));

            $ics = $ICSGenerator->getICS($dateDebut, $dateFin);

            if ('true' == $request->request->get('maladie')) {
                $vacances->setMaladie('1');
            } elseif ($request->request->get('congesSansSoldes')) {
                $vacances->setSansSoldes('1');
            } else {
                $diff = $dateDebut->diff($dateFin);
                $diff = intval($diff->format('%a'));

                // Géré le cas ou la vacances est pour une seule journée
                if (0 == $diff) {
                    ++$diff;
                }
                $nbConges = $utilisateur->getNbConges() - $diff;

                $utilisateur->setNbConges($nbConges);
            }

            if (true == $request->request->get('rtt')) {
                $vacances->setRtt('1');
            }
        } else {
            $demiJournee = $request->request->get('demiJournee');
            $dateFin = $dateDebut;
            if ('matin' == $demiJournee) {
                $horraire = 'Matin';
                $vacances->setDemiJournee($horraire);
                $dateDebut = $dateDebut->add(new DateInterval('PT8H'));
                $dateFin = $dateFin->add(new DateInterval('PT12H'));
            } elseif ('aprem' == $demiJournee) {
                $horraire = 'Aprés-Midi';
                $vacances->setDemiJournee($horraire);
                $dateDebut = $dateDebut->add(new DateInterval('PT12H'));
                $dateFin = $dateFin->add(new DateInterval('PT18H'));
            }

            if ('true' == $request->request->get('maladie')) {
                $vacances->setMaladie('1');
            } elseif ($request->request->get('congesSansSoldes')) {
                // Ne fait rien
            } else {
                $nbConges = $utilisateur->getNbConges() - 0.5;

                $utilisateur->setNbConges($nbConges);
            }
            if (true == $request->request->get('rtt')) {
                $vacances->setRtt('1');
            }
            $ics = $ICSGenerator->getICS($dateDebut, $dateFin, true);
        }

        $ajd = new DateTime('now');
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

        $dispatcher->dispatch(new MailEvent($dateDebut, $dateFin, $utilisateur, $ics), 'mailICS.event');

        if ($diffAjd < 14) {
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

        return $this->redirectToRoute('index');
    }

    #[Route('/removeVacances/{id}/delete', name: 'remove_vacance')]
    public function removeVacances(Vacances $vacances, EntityManagerInterface $manager, UserRepository $repoUser): Response
    {
        $utilisateur = $repoUser->find($this->getUser());

        $dateAjd = new \DateTime('now');
        $dateDebut = $vacances->getDateDebut();
        $dateFin = $vacances->getDateFin();

        $diff = $dateDebut->diff($dateFin);
        $diffAjd = $dateAjd->diff($dateDebut);

        $diffAjd = intval($diffAjd->format('%R%a'));
        $diff = intval($diff->format('%a'));

        // Géré le cas ou la vacances est pour une seule journée
        if (0 == $diff) {
            ++$diff;
        }

        if ($diffAjd >= 0) {
            $nbConges = $utilisateur->getNbConges();
            $utilisateur->setNbConges($nbConges + $diff);
        }

        $manager->remove($vacances);
        $manager->flush();

        $this->addFlash(
            'msg',
            'Vacances supprimé !!');

        return $this->redirectToRoute('index');
    }

    // PARTIE ANNULER

    #[Route('/annulerVacances/{id}/delete', name: 'refuserAnnuler_vacance')]
    public function refuserAnnuler(Vacances $vacances, EntityManagerInterface $manager): Response
    {
        $manager->remove($vacances);
        $manager->flush();

        $this->addFlash(
            'msg',
            'Demande refusé !!');

        return $this->redirectToRoute('index');
    }

    #[Route('/annulerVacance/{id}/demande', name: 'demande_annulation')]
    public function textAnnulation(Vacances $vacances, EntityManagerInterface $manager): Response
    {
        return $this->render('textAnnuler.html.twig', [
            'vacanceID' => $vacances,
        ]);
    }

    #[Route('/annulerVacances/{id}/validation', name: 'validAnnuler_vacance')]
    public function validAnnulerVacances(Vacances $vacances, EntityManagerInterface $manager): Response
    {
        $dateAnnulation = new \DateTime('now');
        $vacances->setDateAnnulation($dateAnnulation);
        $vacances->setAnnuler('1');
        $manager->flush();

        $this->addFlash(
            'msg',
            'Cette vacances à bien était annulée');

        return $this->redirectToRoute('index');
    }

    #[Route('/annulerVacances/{id}/annuler', name: 'annule_vacance')]
    public function annulerVacances(Request $request, Vacances $vacances, EntityManagerInterface $manager, UserRepository $repoUser, MailerInterface $mailer): Response
    {
        $utilisateur = $repoUser->find($this->getUser());

        if (null != $vacances->getAnnuler()) {
            $this->addFlash(
                'msg',
                'Votre demande à déjà était enregistrée');
        } else {
            $dateDebut = $vacances->getDateDebut();
            $dateFin = $vacances->getDateFin();
            $diff = $dateDebut->diff($dateFin);
            $diff = intval($diff->format('%a'));

            // Géré le cas ou la vacances est pour une seule journée
            if (0 == $diff) {
                ++$diff;
            }

            $nbConges = $utilisateur->getNbConges() + $diff;
            $utilisateur->setNbConges($nbConges);

            $vacances->setAnnuler('0');

            // Format les dates
            $dateDebut = $dateDebut->format('d/m/Y');
            $dateFin = $dateFin->format('d/m/Y');

            $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($utilisateur->getMail())
        ->subject("Confirmation d'enregistrement de vos congés")
        ->html("<p> Votre demande d'annulation des vacances du $dateDebut au $dateFin à était enregistré. </p>");

            $mailer->send($email);

            // Texte d'explication
            $textAnnuler = $request->request->get('explication');
            $vacances->setTextAnnuler($textAnnuler);
            $manager->flush();

            $this->addFlash(
            'succes',
            'Votre demande à était enregistrée !!');
        }

        return $this->redirectToRoute('index');
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

        return $this->redirectToRoute('index');
    }

    #[Route('/demandeVacance/{id}/demande', name: 'demande_vacance')]
    public function demandeVacance(Vacances $vacances, EntityManagerInterface $manager): Response
    {
        return $this->render('demandeVacance.html.twig', [
            'vacanceID' => $vacances,
        ]);
    }

    #[Route('/autoriseVacance/{id}/modif', name: 'autorise_vacance')]
    public function autoriseVacances(UserRepository $userRepo, Vacances $vacances, Request $request, EntityManagerInterface $manager, ICSGenerator $ICSGenerator, EventDispatcherInterface $dispatcher): Response
    {
        $utilisateur = $userRepo->find($this->getUser());
        $user = $vacances->getUser();
        $vacances->setAttente('0');
        $vacances->setAutoriser('1');
        $dateDebut = $vacances->getDateDebut();
        $dateFin = $vacances->getDateFin();
        $demiJournee = $vacances->getDemiJournee();

        dd($user);

        if (true == $demiJournee) {
            $ics = $ICSGenerator->getICS($dateDebut, $dateFin, true);
        } else {
            $ics = $ICSGenerator->getICS($dateDebut, $dateFin);
        }

        dd($utilisateur);
        $dispatcher->dispatch(new MailEvent($dateDebut, $dateFin, $utilisateur, $ics), 'mailICS.event');

        $manager->flush();

        return $this->redirectToRoute('calendrier');
    }

    #[Route('/nonAutoriseVacance/{id}/modif', name: 'nonAutorise_vacance')]
    public function nonAutoriseVacances(Vacances $vacances, Request $request, EntityManagerInterface $manager): Response
    {
        $vacances->setAttente('0');
        $vacances->setAutoriser('0');

        $manager->flush();

        return $this->redirectToRoute('calendrier');
    }

    #[Route('/etat_vacance/{id}/confirmation', name: 'etat_vacance')]
    public function afficherEtat(Vacances $vacances, EntityManagerInterface $manager): Response
    {
        return $this->render('/etatVacance.html.twig', [
            'vacanceID' => $vacances,
        ]);
    }

    #[Route('/doc', name: 'doc')]
    public function doc(): Response
    {
        return $this->render('/doc.html.twig', [
        ]);
    }
}
