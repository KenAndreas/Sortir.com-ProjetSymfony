<?php

namespace App\Controller;

use App\Entity\Campus;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/sortie', name: 'app_sortie')]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('sortie/index.html.twig', [
            'controller_name' => 'SortieController',
            'campus' => $em->getRepository(Campus::class)->findAll(),
        ]);
    }
}
