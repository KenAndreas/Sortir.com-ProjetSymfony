<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Lieu;
use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\EntityRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
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

    #[Route('/administration/campus', name: 'admin_campus_update')]
    public function updateCampus(EntityManagerInterface $em): Response
    {
        return $this->render('administration/listCampus.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'user' => $this->getUser(),
        ]);
    }

    #[Route('/administration/lieux', name: 'admin_lieux')]
    public function getLieux(EntityManagerInterface $em): Response
    {
        return $this->render('administration/listLieux.html.twig', [
            'lieux' => $em->getRepository(Lieu::class)->findAll(),
            'user' => $this->getUser(),
        ]);
    }
}
