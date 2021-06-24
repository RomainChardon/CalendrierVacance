<?php

namespace App\Controller;

use App\Entity\Utilisateur;
use App\Entity\Vacances;
use App\Repository\UserRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\Request;
use App\Repository\VacancesRepository;
use DateTime;
use DateTimeImmutable;
use Doctrine\ORM\EntityManager;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Mapping\Entity;
use phpDocumentor\Reflection\PseudoTypes\False_;
use phpDocumentor\Reflection\PseudoTypes\True_;

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
        $dateFin = new DateTimeImmutable($request->request->get('date_fin'));
        $heureDebut = new DateTimeImmutable('0:0:0');
        $heureFin = new DateTimeImmutable('0:0:0');
        $vacances->setDateDebut($dateDebut);
        $vacances->setDateFin($dateFin);
        $vacances->setHeureDebut($heureDebut);
        $vacances->setHeureFin($heureFin);
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
        
        return $this->redirectToRoute("index");    
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

    #[Route('/autoriseVacance/{id}/modif', name: 'autorise_vacance')]
    public function autoriseVacances( Vacances $vacances, Request $request, EntityManagerInterface $manager): Response
    {
        $vacances->setAttente('0');
        $vacances->setAutoriser('1');

        $manager->flush();
        return $this->redirectToRoute("index");    
    }

    #[Route('/nonAutoriseVacance/{id}/modif', name: 'nonAutorise_vacance')]
    public function nonAutoriseVacances( Vacances $vacances, Request $request, EntityManagerInterface $manager): Response
    {

        $vacances->setAttente('0');
        $vacances->setAutoriser('0');

        $manager->flush();
        return $this->redirectToRoute("index");    
    }

    #[Route('/etat_vacance/{id}/confirmation', name:'etat_vacance')]
    public function afficherEtat(Vacances $vacances,EntityManagerInterface $manager): Response
    {
        return $this->render('/utilisateur/confSupprUser.html.twig', [
            'vacanceID' => $vacances
        ]);
    }
}
