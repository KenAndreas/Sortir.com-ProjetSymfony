<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Ville;
use App\Form\CampusType;
use App\Form\VilleType;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

final class AdministrationController extends AbstractController
{
    #[Route('/administration', name: 'administration')]
    public function index(UserInterface $user): Response
    {
        return $this->render('administration/index.html.twig', [
            'user' => $user,
        ]);
    }

    #[Route('/administration/users', name: 'admin_users')]
    public function getUsers(EntityManagerInterface $em): Response
    {
        return $this->render('administration/listUser.html.twig', [
            'users' => $em->getRepository(Participant::class)->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/administration/campus', name: 'admin_campus')]
    public function getCampus(EntityManagerInterface $em): Response
    {
        return $this->render('administration/listCampus.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/administration/villes', name: 'admin_villes')]
    public function getVilles(EntityManagerInterface $em): Response
    {
        return $this->render('administration/listVilles.html.twig', [
            'villes' => $em->getRepository(Ville::class)->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/administration/campus/create', name: 'create_campus', methods: ['GET', 'POST'])]
    public function createCampus(Request $request, EntityManagerInterface $em): Response
    {
        $campus = new Campus();
        $form = $this->createForm(CampusType::class, $campus);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($campus);
            $em->flush();
            return $this->redirectToRoute('administration',[
                'user' => $this->getUser(),
            ]);
        }

        return $this->render('administration/createCampus.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/administration/ville/create', name: 'create_ville', methods: ['GET', 'POST'])]
    public function createVille(Request $request, EntityManagerInterface $em): Response
    {
        $ville = new Ville();
        $form = $this->createForm(VilleType::class, $ville);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $em->persist($ville);
            $em->flush();
            return $this->redirectToRoute('administration',[
                'user' => $this->getUser(),
            ]);
        }

        return $this->render('administration/createVille.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser(),
        ]);
    }
}
