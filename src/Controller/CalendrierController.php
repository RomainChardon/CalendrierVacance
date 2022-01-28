<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\Utilisateur;
use App\Entity\Vacances;
use App\Repository\GroupeRepository;
use App\Repository\UtilisateurRepository;
use App\Repository\VacancesRepository;
use DateInterval;
use DatePeriod;
use DateTime;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vacances')]
class CalendrierController extends AbstractController
{
    private string $moisActuel;
    private string $anneeActuel;
    private string $jourActuel;
    private int $anneeUtiliser;
    private int $moisUtiliser;

    public function __construct()
    {
        $this->moisActuel = date('n');
        $this->anneeActuel = date('Y');
        $this->jourActuel = date('j');
        $this->anneeUtiliser = date('Y'); 
        $this->moisUtiliser = date('n');
    }

    #[Route('/calendrier', name: 'calendrier')]
    public function aff_mois(Request $request,GroupeRepository $repoGroupe, VacancesRepository $repoVacances): Response
    {  
        // Appele de la modification de l'annee et le mois
        if ($request->query->get('moisSelectionner')) {
            $this->afficherMois($request);
        }
        
        if ($request->query->get('anneeSelectionner')) {
            $this->afficherAnnee($request);
        }

        $moisUtiliserFormatLettre = $this->traductionMois($this->moisUtiliser);
        if ($this->moisUtiliser + 1 >= 13) {
            $moisUtiliserFormatLettre2 = $this->traductionMois(1);
        } else {
            $moisUtiliserFormatLettre2 = $this->traductionMois($this->moisUtiliser + 1);
        }
        

        // Premier jour du mois actuel
        $debut = new DateTime($this->anneeUtiliser . '-' . $this->moisUtiliser. '-1');
        // Nombre de jours contenu dans le mois actuel
        $nbjour = $debut->format("t");
        // Dernier jour du mois actuel
        $prochain = new DateTime($this->anneeUtiliser . '-' . $this->moisUtiliser . '-' .$nbjour);
        // Création de l'interval du $debut a $prochain
        $prochain = $prochain->modify( '+1 day' ); 
        $interval = new DateInterval('P1D');
        $periode = new DatePeriod($debut, $interval, $prochain);

        if ($this->moisUtiliser + 1 >= 13 ) {
            $debut2 = new DateTime($this->anneeUtiliser + 1 . '-' .  1 . '-1');
            // Nombre de jours contenu dans le mois suivant
            $nbjour2 = $debut2->format("t");
            // Dernier jour du mois actuel
            $prochain2 = new DateTime($this->anneeUtiliser + 1 . '-' . 1 . '-' .$nbjour);
        } else {
            $debut2 = new DateTime($this->anneeUtiliser . '-' . $this->moisUtiliser + 1 . '-1');
            // Nombre de jours contenu dans le mois suivant
            $nbjour2 = $debut2->format("t");
            // Dernier jour du mois actuel
            $prochain2 = new DateTime($this->anneeUtiliser . '-' . $this->moisUtiliser + 1 . '-' .$nbjour);
        }
        
        // Création de l'interval du $debut a $prochain
        $prochain2 = $prochain2->modify( '+1 day' ); 
        $interval2 = new DateInterval('P1D');
        $periode2 = new DatePeriod($debut2, $interval2, $prochain2);

        if ($this->anneeUtiliser + 1 >= 13) {
            $anneeSuivante = $this->anneeUtiliser+1; 
        } else {
            $anneeSuivante = $this->anneeUtiliser;
        }

        return $this->render('/calendrier/index.html.twig', [
            // Congés user
            'nbConges' => $this->getUser()->getNbConges(),

            // Date actuel
            'anneeActuel' => $this->anneeActuel,
            'moisActuel' => $this->moisActuel,
            'jourActuel' => $this->jourActuel,

            // Jour et mois a afficher
            'listeDays'=>$this->createListDays($periode, $repoGroupe),
            'listeDays2'=>$this->createListDays2($periode2, $repoGroupe),
            'listeAnnee'=>$this->createListeGenererAnnee(),
            'listeMois'=>$this->createlisteGenererMois(),
            'moisUtiliser'=>$this->moisUtiliser,
            'moisSuivant' => $this->moisUtiliser + 1,
            'moisUtiliserFormatLettre'=>$moisUtiliserFormatLettre,
            'moisUtiliserFormatLettre2'=>$moisUtiliserFormatLettre2,
            'anneeUtiliser'=>$this->anneeUtiliser,
            'anneeSuivante' => $anneeSuivante,
            

            // Groupe d'utilisateur
            'tousLesGroupes' => $repoGroupe->findAll()
        ]);
    }



    /**
     * createListDays
     *
     * @return array
     */
    private function createListDays(DatePeriod $periode, GroupeRepository $repoGroupe): array
    {
        foreach ($periode as $date) {
            $jourAfficher = $date->format("N");
            $numJours = (int)$date->format("d");

            $jourUtiliser = new DateTime($numJours . '-' . $this->moisUtiliser . '-' . $this->anneeUtiliser);
            $jourAujourdhui = new DateTime($this->jourActuel . '-' . $this->moisActuel . '-' . $this->anneeActuel);

            if ($jourAujourdhui == $jourUtiliser) {
                $siAujourdhui = true;
            } else {
                $siAujourdhui = false;
            }
           
            $siSamedi = false;
            $siDimanche = false;
            if ($this->traductionJour($jourAfficher) == "Samedi") {
                $siSamedi = true;
            } else if ($this->traductionJour($jourAfficher) == "Dimanche") {
                $siDimanche = true;
            }

            $siFerier = false;
            foreach ($this->createListeFerier($this->anneeUtiliser) as $jourFerier) {
                if ($jourFerier->format('d/m/Y') == $jourUtiliser->format('d/m/Y')) {
                    $siFerier = true;
                }

            
                
                $listDetailUser = [];
                $listeGroupe = $repoGroupe->findAll();
                foreach ($listeGroupe as $groupe) {
                    $groupeUser = $groupe->getUsers();
                    $nomGroupe = $groupe->getNomGroupe();
                    $couleurGroupe = $groupe->getCouleur();

                    foreach ($groupeUser as $user) {
                        $prenomUser = $user->getPrenom();
                        $nomUser = $user->getNom();
                        $userVacances = $user->getVacances();
                        $cadre = $user->getCadre();
                        
                        $listDetailVacances = [];
                        foreach ($userVacances as $vacance) {
                            
                            $dateDebutVacances = $vacance->getDateDebut();
                            $dateFinVacances = $vacance->getDateFin();
                            $autoriser = $vacance->getAutoriser();
                            $attente = $vacance->getAttente();
                            $id = $vacance->getId();
                            $maladie =$vacance->getMaladie();
                            $sansSoldes = $vacance->getSansSoldes();
                            $rtt = $vacance->getRtt();
                            $demiJournee = $vacance->getDemiJournee();
                            $annuler = $vacance->getAnnuler();
                            $dateAnnulation = $vacance->getDateAnnulation();
                            $dateDemande = $vacance->getDateDemande();


                            if (($dateDebutVacances <= $jourUtiliser) && ($dateFinVacances >= $jourUtiliser)) {
                                $enVacances = true;
                            } else {
                                $enVacances = false;
                            }

                            $listDetailVacances[]= array(
                                'id' => $id,
                                'dateDebut' => $dateDebutVacances,
                                'dateFin' => $dateFinVacances,
                                'autoriser' => $autoriser,
                                'attente' => $attente,
                                'enVacances' => $enVacances,
                                'attente' => $attente,
                                'autoriser' => $autoriser,
                                'id' => $id,
                                'maladie'=> $maladie,
                                'sansSoldes' => $sansSoldes,
                                'rtt' => $rtt,
                                'demiJournee' => $demiJournee,
                                'annuler' => $annuler,
                                'dateDemande' => $dateDemande,
                                'dateAnnulation' => $dateAnnulation,

                            );
                        }
                        

                        $listDetailUser[$nomUser.'-'.$prenomUser] = array(
                            'nomUser' => $nomUser,
                            'prenomUser' => $prenomUser,
                            'groupe' => $nomGroupe,
                            'couleurGroupe' => $couleurGroupe,
                            'vacances' => $listDetailVacances,
                            'cadre' => $cadre,
                        );
                    } 
                }

                
                $listDaysLigne = array(
                    'jourNumero' => $numJours,
                    'jourLettre' => $this->traductionJour($jourAfficher),
                    'jourUtiliser' => $jourUtiliser,
                    'jourActuel' => $jourAujourdhui,
                    'siAujourdhui' => $siAujourdhui,
                    'siSamedi' => $siSamedi,
                    'siDimanche' => $siDimanche,
                    'siFerier' => $siFerier,
                    'listDetail' => $listDetailUser,
                );

                $listDays[$numJours] = $listDaysLigne;
            } 
        }
        return $listDays;
    }

/**
     * createListDays
     *
     * @return array
     */
    private function createListDays2(DatePeriod $periode, GroupeRepository $repoGroupe): array
    {
        foreach ($periode as $date) {
            $jourAfficher2 = $date->format("N");
            $numJours = (int)$date->format("d");

            if ($this->moisUtiliser + 1 >= 13 ) {
                $jourUtiliser2 = new DateTime($numJours . '-' . 1 . '-' . $this->anneeUtiliser + 1);
            } else {
                $jourUtiliser2 = new DateTime($numJours . '-' . $this->moisUtiliser + 1 . '-' . $this->anneeUtiliser);
            }
            
            $jourAujourdhui = new DateTime($this->jourActuel . '-' . $this->moisActuel . '-' . $this->anneeActuel);

            if ($jourAujourdhui == $jourUtiliser2) {
                $siAujourdhui = true;
            } else {
                $siAujourdhui = false;
            }
           
            $siSamedi = false;
            $siDimanche = false;
            if ($this->traductionJour($jourAfficher2) == "Samedi") {
                $siSamedi = true;
            } else if ($this->traductionJour($jourAfficher2) == "Dimanche") {
                $siDimanche = true;
            }

            $siFerier = false;
            foreach ($this->createListeFerier($this->anneeUtiliser) as $jourFerier) {
            
                if ($jourFerier->format('d/m/Y') == $jourUtiliser2->format('d/m/Y')) {
                    $siFerier = true;
                } else {
                    $siFerier = false;
                }

            }
            
            $listDetailUser = [];
            $listeGroupe = $repoGroupe->findAll();
            foreach ($listeGroupe as $groupe) {
                $groupeUser = $groupe->getUsers();
                $nomGroupe = $groupe->getNomGroupe();
                $couleurGroupe = $groupe->getCouleur();

                foreach ($groupeUser as $user) {
                    $prenomUser = $user->getPrenom();
                    $nomUser = $user->getNom();
                    $userVacances = $user->getVacances();
                    
                    $listDetailVacances = [];
                    foreach ($userVacances as $vacance) {
                        
                        $dateDebutVacances = $vacance->getDateDebut();
                        $dateFinVacances = $vacance->getDateFin();
                        $attente = $vacance->getAttente();
                        $autoriser = $vacance->getAutoriser();
                        $id = $vacance->getId();
                        $maladie = $vacance->getMaladie();
                        $sansSoldes = $vacance->getSansSoldes();
                        $rtt = $vacance->getRtt();
                        $demiJournee = $vacance->getDemiJournee();
                        $annuler = $vacance->getAnnuler();
                        $dateAnnulation = $vacance->getDateAnnulation();
                        $dateDemande = $vacance->getDateDemande();
                        


                        if (($dateDebutVacances <= $jourUtiliser2) && ($dateFinVacances >= $jourUtiliser2)) {
                            $enVacances = true;
                        } else {
                            $enVacances = false;
                        }

                        $listDetailVacances[]= array(
                            'dateDebut' => $dateDebutVacances,
                            'dateFin' => $dateFinVacances,
                            'autoriser' => $autoriser,
                            'id' => $id,
                            'attente' => $attente,
                            'enVacances' => $enVacances,
                            'attente' => $attente,
                            'autoriser' => $autoriser,
                            'id' => $id,
                            'maladie' => $maladie,
                            'sansSoldes' => $sansSoldes,
                            'rtt' => $rtt,
                            'demiJournee' => $demiJournee,
                            'annuler' => $annuler,
                            'dateDemande' => $dateDemande,
                            'dateAnnulation' => $dateAnnulation,
                        );

                    }
                    

                    $listDetailUser[$nomUser.'-'.$prenomUser] = array(
                        'nomUser' => $nomUser,
                        'prenomUser' => $prenomUser,
                        'groupe' => $nomGroupe,
                        'couleurGroupe' => $couleurGroupe,
                        'vacances' => $listDetailVacances,
                    );
                } 
            }

            
            $listDaysLigne = array(
                'jourNumero' => $numJours,
                'jourLettre' => $this->traductionJour($jourAfficher2),
                'jourUtiliser' => $jourUtiliser2,
                'jourActuel' => $jourAujourdhui,
                'siAujourdhui' => $siAujourdhui,
                'siSamedi' => $siSamedi,
                'siDimanche' => $siDimanche,
                'siFerier' => $siFerier,
                'listDetail' => $listDetailUser,
            );

            $listDays2[$numJours] = $listDaysLigne;
        }
        return $listDays2;
    }
    
    /**
     * createListeFerier
     *
     * @return array
     */
    private function createListeFerier($annee): array
    {
        $listeFerier = array(
            new DateTime('1/1/'.$annee), #jour de l'an
            new DateTime('4/1/'.$annee), #fête du travail
            new DateTime('5/8/'.$annee), #1945
            new DateTime('7/14/'.$annee), #fête national
            new DateTime('8/15/'.$annee), #Ascension
            new DateTime('11/1/'.$annee), #Toussaint
            new DateTime('11/11/'.$annee), #Armistice
            new DateTime('12/25/'.$annee), #Noel
        );
        return $listeFerier;
    }



    /**
     * createListeGenererMois
     *
     * @return array
     */
    private function createListeGenererMois(): array
    {
        $moisAfficher = 0;
        for ($i=0; $i < 12; $i++) { 
            $moisAfficher ++;
            $listeGenererMois[$moisAfficher] = $this->traductionMois($moisAfficher);
        }

        return $listeGenererMois;
    }


    
    /**
     * createListeGenererAnnee
     *
     * @return array
     */
    private function createListeGenererAnnee(): array
    {
        $genererAnnee = (int)$this->anneeActuel;
        $genererAnnee = $genererAnnee -2;
        for ($i=0; $i < 5; $i++) { 
            $genererAnnee++;
            $listeGenererAnnee[$i]=$genererAnnee;
        }

        return $listeGenererAnnee;
    }
    

    
    /**
     * afficherAnnee
     *
     * @param  Request $request
     * @return void
     */
    private function afficherAnnee(Request $request): void
    {
        $this->anneeUtiliser = (int)$request->query->get('anneeSelectionner');
    }


        
    /**
     * afficherMois
     *
     * @param  Request $request
     * @return void
     */
    private function afficherMois(Request $request): void
    {
        $this->moisUtiliser = (int)$request->query->get('moisSelectionner');
    }


        
    /**
     * traductionMois
     *
     * @param  mixed $moisAfficher
     * @return string
     */
    private function traductionMois($moisAfficher) : string
    {
        $moisLettre = '';
        switch ($moisAfficher) {
            case 1:
                $moisLettre = "Janvier";
                break;
            case 2:
                $moisLettre = "Février";
                break;
            case 3:
                $moisLettre = "Mars";
                break;
            case 4:
                $moisLettre = "Avril";
                break;
            case 5:
                $moisLettre = "Mai";
                break;
            case 6:
                $moisLettre = "Juin";
                break;
            case 7:
                $moisLettre = "Juillet";
                break;
            case 8:
                $moisLettre = "Août";
                break;
            case 9:
                $moisLettre = "Septembre";
                break;
            case 10:
                $moisLettre = "Octobre";
                break;
            case 11:
                $moisLettre = "Novembre";
                break;
            case 12:
                $moisLettre = "Décembre";
                break;
        }

        return $moisLettre;
    }


    
    /**
     * traductionJour
     *
     * @param  mixed $jourAfficher
     * @return string
     */
    private function traductionJour($jourAfficher) : string
    {
        switch ($jourAfficher) {
            case 1:
                $jourLettre = "Lundi";
                break;
            case 2:
                $jourLettre = "Mardi";
                break;
            case 3:
                $jourLettre = "Mercredi";
                break;
            case 4:
                $jourLettre = "Jeudi";
                break;
            case 5:
                $jourLettre = "Vendredi";
                break;
            case 6:
                $jourLettre = "Samedi";
                break;
            case 7:
                $jourLettre = "Dimanche";
                break;
        }

        return $jourLettre;
    }


}