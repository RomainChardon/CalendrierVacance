<?php

namespace App\Controller;

use App\Entity\Groupe;
use App\Repository\GroupeRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;

#[Route('/vacances/gestionGroupe')]
class GroupeController extends AbstractController
{
    #[Route('/groupe', name: 'groupe')]
    public function index(GroupeRepository $repoGroupe): Response
    {
        return $this->render('groupe/index.html.twig', [
            'tousLesGroupes' => $repoGroupe->findAll()
        ]);
    }

    #[Route('/createGroupe', name: 'create_groupe')]
    public function create_groupe(Request $request, GroupeRepository $repoGroupe): Response
    {
        $entityManager = $this->getDoctrine()->getManager();
        $groupe = new Groupe();
        $groupe ->setNomGroupe($request->request->get('nom'));
        $groupe ->setCouleur($request->request->get('couleur'));
        
        $entityManager->persist($groupe);
        $entityManager->flush();

        $this->addFlash(
            'succes',
            'Groupe ajouté !!'
        );

        return $this->redirectToRoute("groupe");
    }

    #[Route('/removeGroupe/{id}/delete', name:'remove_groupe')]
    public function remove_groupe(Groupe $groupe,EntityManagerInterface $manager): Response
    {
        if (($groupe->getUsers()->isEmpty()) == false ) {
            $this->addFlash(
                'msg',
                "Attention des utilisateurs sont encore présent dans le groupe. Veuillez changer leurs groupe avant de le supprimer !!!!");          
        } else {
            $manager->remove($groupe);
            $manager->flush();

            $this->addFlash(
                'msg',
                "Groupe supprimé !!");
        }

        return $this->redirectToRoute("groupe");
    }
}
