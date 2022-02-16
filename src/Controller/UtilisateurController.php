<?php

namespace App\Controller;

use App\Entity\User;
use App\Repository\GroupeRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

#[Route('/vacances/gestionUtilisateur')]
class UtilisateurController extends AbstractController
{
    #[Route('/utilisateur', name: 'utilisateur')]
    public function index(UserRepository $repoUser, GroupeRepository $repoGroupe): Response
    {
        //dd($repoUtilisateur->findAll());
        return $this->render('utilisateur/index.html.twig', [
            'tousLesUtilisateurs' => $repoUser->findAll(),
            'tousLesGroupes' => $repoGroupe->findAll(),
        ]);
    }

    #[Route('/create_utilisateur', name: 'create_utilisateur')]
    public function create_utilisateur(Request $request, GroupeRepository $repoGroupe, UserPasswordEncoderInterface $userPass, MailerInterface $mailer): Response
    {
        $entityManager = $this->getDoctrine()->getManager();

        $groupe = $repoGroupe->findById($request->request->get('groupe'));
        // dd($groupe[0]);
        $utilisateur = new User();

        $passHash = ($userPass->encodePassword($utilisateur, $request->request->get('password')));

        $utilisateur->setNom($request->request->get('nom'));
        $utilisateur->setPrenom($request->request->get('prenom'));
        $utilisateur->setUsername(strtolower(substr($request->request->get('prenom'), 0, 1).($request->request->get('nom'))));
        $utilisateur->setPassword($passHash);
        $utilisateur->setGroupe($groupe[0]);
        $utilisateur->setMail($request->request->get('mail'));
        $utilisateur->setNbConges(0);
        $utilisateur->setDesactiver(false);

        if (($utilisateur->getGroupe()->getNomGroupe()) == 'Cadre') {
            $utilisateur->setNbConges(10);
        }

        if ('true' == $request->request->get('admin')) {
            $role[] = 'ROLE_ADMIN';
            $utilisateur->setRoles($role);
        }

        if ('true' == $request->request->get('cadre')) {
            $cadre = '1';
            $utilisateur->setCadre($cadre);
            $utilisateur->setNbConges(10);
        }

        $entityManager->persist($utilisateur);
        $entityManager->flush();

        $username = $utilisateur->getUsername();
        $mail = $utilisateur->getMail();
        $groupe = $utilisateur->getGroupe()->getNomGroupe();

        $email = (new Email())
        ->from('enzo.mangiante.adeo@gmail.com')
        ->to($utilisateur->getMail())
        //->cc('cc@example.com')
        //->bcc('bcc@example.com')
        //->replyTo('fabien@example.com')
        //->priority(Email::PRIORITY_HIGH)
        ->subject("Confirmation d'inscription")
        ->html("Inscription sur l'application de vacances, voici un récapitulatif des vos informations :
            <br> Votre nom d'utilisateur : $username
            <br> Votre mail : $mail
            <br> Assigné au groupe : $groupe
            <br> Lien vers la documentation utilisateur : https://ljl66-my.sharepoint.com/:w:/g/personal/romain_chardon_sio2020_lurcat_fr/EVd34xrWPqFJu8KKTtobrBsB0QLxQcRh3f56uoA5dMTWiw?e=EvKCgK");

        $mailer->send($email);

        // $this->addFlash(
        //     'succes',
        //     'Utilisateur ajouté et Mail envoyé!!'
        // );

        return $this->redirectToRoute('utilisateur');
    }

    #[Route('/supprimerUtilisateur/{id}/confirmation', name: 'suppr_user')]
    public function supprUtilisateur(User $user, EntityManagerInterface $manager): Response
    {
        return $this->render('/utilisateur/confSupprUser.html.twig', [
            'utilisateurID' => $user,
        ]);
    }

    #[Route('/supprimerUtilisateur/{id}/delete', name: 'remove_user')]
    public function removeUtilisateur(User $user, EntityManagerInterface $manager): Response
    {
        $user->setDesactiver(true);

        $manager->persist($user);
        $manager->flush();

        $this->addFlash(
            'msg',
            'Utilisateur désactiver !!');

        return $this->redirectToRoute('utilisateur');
    }

    #[Route('/reativer_user/{id}/delete', name: 'reativer_user')]
    public function reativer_user(User $user, EntityManagerInterface $manager): Response
    {
        $user->setDesactiver(false);

        $manager->persist($user);
        $manager->flush();

        $this->addFlash(
            'msg',
            'Utilisateur réativer !!');

        return $this->redirectToRoute('utilisateur');
    }

    #[Route('/modifUtilisateur/{id}/modif', name: 'modif_utilisateur')]
    public function afficherUtilisateur(User $user, GroupeRepository $repoGroupe, EntityManagerInterface $manager): Response
    {
        return $this->render('/utilisateur/modifierUtilisateur.html.twig', [
            'utilisateurID' => $user,
            'tousLesGroupes' => $repoGroupe->findAll(),
        ]);
    }

    #[Route('/modifierUtilisateur/{id}/modif', name: 'modifier_utilisateur')]
    public function modif_utilisateur(User $user, GroupeRepository $repoGroupe, Request $request, EntityManagerInterface $manager): Response
    {
        $nbCongesOLD = $user->getNbConges();
        // if ($nbCongesOLD + $request->request->get('nbConges') == ""){
        //     $user->setNbConges($nbCongesOLD);
        // }
        $nbCongesNEW = ($nbCongesOLD + (float) $request->request->get('nbConges'));
        $groupe = $repoGroupe->find($request->request->get('groupe'));
        $user->setNom($request->request->get('nom'));
        $user->setPrenom($request->request->get('prenom'));
        $user->setNbConges($nbCongesNEW);

        $groupe->addUser($user);
        $manager->flush();

        $this->addFlash(
            'msg',
            'Utilisateur modifié !!'
        );

        return $this->redirectToRoute('utilisateur');
    }

    #[Route('/user/{id}/modif', name: 'modif_user')]
    public function afficherUser(User $user, GroupeRepository $repoGroupe, EntityManagerInterface $manager): Response
    {
        return $this->render('/utilisateur/modifierUser.html.twig', [
            'utilisateurID' => $user,
            'tousLesGroupes' => $repoGroupe->findAll(),
        ]);
    }

    #[Route('/modifierUser/{id}/modif', name: 'modifier_user')]
    public function modif_user(User $user, GroupeRepository $repoGroupe, Request $request, EntityManagerInterface $manager, UserPasswordEncoderInterface $userPass): Response
    {
        $oldPassword = $request->request->get('oldPassword');

        if (null != $oldPassword) {
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

        if (null != $request->request->get('username')) {
            $user->setUsername($request->request->get('username'));

            $this->addFlash(
                'succes',
                'Utilisateur modifié !!'
            );
        }

        if (null != $request->request->get('email')) {
            $user->setMail($request->request->get('email'));

            $this->addFlash(
                'succes',
                'Utilisateur modifié !!'
            );
        }

        $manager->flush($user);

        return $this->redirectToRoute('calendrier');
    }
}
