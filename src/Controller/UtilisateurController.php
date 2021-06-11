<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Entity\User;
use App\Entity\Vacances;
use App\Repository\GroupeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'utilisateur')]
    public function index(UserRepository $repoUser, GroupeRepository $repoGroupe): Response
    {
        #dd($repoUtilisateur->findAll());
        return $this->render('utilisateur/index.html.twig', [
            'tousLesUtilisateurs' => $repoUser->findAll(),
            'tousLesGroupes' => $repoGroupe->findAll()
        ]);
    }

    #[Route('/create_utilisateur', name: 'create_utilisateur')]
    public function create_utilisateur(Request $request, GroupeRepository $repoGroupe): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $groupe = $repoGroupe->find($request->request->get('groupe'));

        $utilisateur = new User();

        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setPrenom($request->request->get('prenom'));
        $utilisateur->setUsername($request->request->get('nom') .'.'. $request->request->get('prenom'));
        $utilisateur->setPassword('$argon2id$v=19$m=65536,t=4,p=1$eQJQnTvHBBb5RLWfZ3r+YQ$rm5V07vG/dzhrLDm7Xii3J4m+Ln5xPegPqY1XXG55C4');
        $utilisateur->setGroupe($groupe);
        
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        $this->addFlash(
            'msg',
            'Utilisateur ajouté !!'
        );

        return $this->redirectToRoute("home");
    }

    #[Route('/supprimerUtilisateur/{id}/delete', name:'remove_user')]
    public function removeUtilisateur(User $user,EntityManagerInterface $manager): Response
    {
        $manager->remove($user);
        $manager->flush();

        $this->addFlash(
            'msg',
            "Utilisateur supprimé !!");

        return $this->redirectToRoute("utilisateur");
    }

    #[Route('/modifUtilisateur/{id}/modif', name: 'modif_utilisateur')]
    public function afficherUtilisateur(User $user, GroupeRepository $repoGroupe, EntityManagerInterface $manager): Response
    {
        return $this->render('/utilisateur/modifierUtilisateur.html.twig', [
            'utilisateurID' => $user,
            "tousLesGroupes" => $repoGroupe->findAll()
        ]);
        
        return $this->redirectToRoute("home");    
    }

    #[Route('/modifierUtilisateur/{id}/modif', name: 'modifier_utilisateur')]
    public function modif_utilisateur(User $user, GroupeRepository $repoGroupe, Request $request, EntityManagerInterface $manager): Response
    {
        $groupe = $repoGroupe->find($request->request->get('groupe'));
        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));

        $groupe->addUser($user);
        $manager->flush();

        $this->addFlash(
            'msg',
            'Utilisateur modifié !!'
        );

        return $this->redirectToRoute("home");
    }
}
