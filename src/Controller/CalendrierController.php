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
use Doctrine\DBAL\Schema\Index;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

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

        // $listeVacances = $repoVacances -> findAll();
        // foreach ($listeVacances as $vacance) {
        //    var_dump( $vacance->getDateDebut());
        // }


        return $this->render('/calendrier/index.html.twig', [
            // Date actuel
            'anneeActuel' => $this->anneeActuel,
            'moisActuel' => $this->moisActuel,
            'jourActuel' => $this->jourActuel,

            // Jour et mois a afficher
            'listeDays'=>$this->createListDays($periode, $repoGroupe),
            'listeAnnee'=>$this->createListeGenererAnnee(),
            'listeMois'=>$this->createlisteGenererMois(),
            'moisUtiliser'=>$this->moisUtiliser,
            'moisUtiliserFormatLettre'=>$moisUtiliserFormatLettre,
            'anneeUtiliser'=>$this->anneeUtiliser,
            // 'moisAfficher' => $this->moisAfficher,
            // 'anneeAfficher' => $this->anneeAfficher,

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
                        if (($dateDebutVacances <= $jourUtiliser) && ($dateFinVacances >= $jourUtiliser)) {
                            $enVacances = true;
                        } else {
                            $enVacances = false;
                        }

                        $listDetailVacances[]= array(
                            'dateDebut' => $dateDebutVacances,
                            'dateFin' => $dateFinVacances,
                            'enVacances' => $enVacances,
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
                                'jourLettre' => $this->traductionJour($jourAfficher),
                                'jourUtiliser' => $jourUtiliser,
                                'jourActuel' => $jourAujourdhui,
                                'siAujourdhui' => $siAujourdhui,
                                'siSamedi' => $siSamedi,
                                'siDimanche' => $siDimanche,
                                'listDetail' => $listDetailUser,
                            );

            $listDays[$numJours] = $listDaysLigne;
            
        }
        return $listDays;
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