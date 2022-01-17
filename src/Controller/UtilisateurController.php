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
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Security\Core\Validator\Constraints\UserPasswordValidator;
use Symfony\Component\Security\Http\Authenticator\Passport\UserPassportInterface;

#[Route('/vacances/gestionUtilisateur')]
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
    public function create_utilisateur(Request $request, GroupeRepository $repoGroupe, UserPasswordEncoderInterface $userPass): Response
    {

        $entityManager = $this->getDoctrine()->getManager();

        $groupe = $repoGroupe->find($request->request->get('groupe'));

        $utilisateur = new User();

        $passHash = ($userPass->encodePassword($utilisateur, $request->request->get('password')));

        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setPrenom($request->request->get('prenom'));
        $utilisateur->setUsername(strtolower(substr($request->request->get('prenom'),0,1) . ($request->request->get('nom'))));
        $utilisateur->setPassword($passHash);
        $utilisateur->setGroupe($groupe);
        $utilisateur->setMail($request->request->get('mail'));
        $utilisateur->setNbConges(0);

        if (($utilisateur->getGroupe()->getNomGroupe()) == "Cadre"){
            $utilisateur->setNbConges(10);
        }

        
        if ( $request->request->get('admin') == 'true') {
            $role[]= 'ROLE_ADMIN';
            $utilisateur->setRoles($role);
        }
        
        $entityManager->persist($utilisateur);
        $entityManager->flush();

        $this->addFlash(
            'succes',
            'Utilisateur ajouté !!'
        );

        return $this->redirectToRoute("utilisateur");
    }

    #[Route('/supprimerUtilisateur/{id}/confirmation', name:'suppr_user')]
    public function supprUtilisateur(User $user,EntityManagerInterface $manager): Response
    {
        return $this->render('/utilisateur/confSupprUser.html.twig', [
            'utilisateurID' => $user
        ]);
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
 
    }

    #[Route('/modifierUtilisateur/{id}/modif', name: 'modifier_utilisateur')]
    public function modif_utilisateur(User $user, GroupeRepository $repoGroupe, Request $request, EntityManagerInterface $manager): Response
    {
        $nbCongesOLD = $user->getNbConges();
        // if ($nbCongesOLD + $request->request->get('nbConges') == ""){
        //     $user->setNbConges($nbCongesOLD);
        // }
        $nbCongesNEW = ($nbCongesOLD + (float)$request->request->get('nbConges'));
        $groupe = $repoGroupe->find($request->request->get('groupe'));
        $user->setNom($request->request->get('nom'));
        $user->setMail($request->request->get('mail'));  
        $user->setPrenom($request->request->get('prenom'));
        $user->setNbConges($nbCongesNEW);


        $groupe->addUser($user);
        $manager->flush();

        $this->addFlash(
            'msg',
            'Utilisateur modifié !!'
        );

        return $this->redirectToRoute("utilisateur");
    }

    #[Route('/user/{id}/modif', name: 'modif_user')]
    public function afficherUser(User $user, GroupeRepository $repoGroupe, EntityManagerInterface $manager): Response
    {
        return $this->render('/utilisateur/modifierUser.html.twig', [
            'utilisateurID' => $user,
            "tousLesGroupes" => $repoGroupe->findAll()
        ]); 
    }

    #[Route('/modifierUser/{id}/modif', name: 'modifier_user')]
    public function modif_user(User $user, GroupeRepository $repoGroupe, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $userPass): Response
    {
        $oldPassword = $request->request->get('oldPassword');

        if ($oldPassword != null) {
            if ($userPass->isPasswordValid($user, $oldPassword)) {
                $newPassword = ($userPass->encodePassword($user, $request->request->get('newPassword')));
                $user->setPassword($newPassword);

                $this->addFlash(
                    'succes',
                    'Mot de passe modifié !!'
                );
            } else {
                $this->addFlash(
                    'msg',
                    "Votre mot de passe actuel n'est pas bon !!"
                );
            }
        } 
        
        if ($request->request->get('username') != null) {
            $user->setUsername($request->request->get('username'));

            $this->addFlash(
                'succes',
                "Utilisateur modifié !!"
            );
        }

        $manager->flush($user);

        return $this->redirectToRoute("calendrier");
    }
}
