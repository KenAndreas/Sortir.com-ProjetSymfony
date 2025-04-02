<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Sortie;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/', name: 'app_sortie', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {

        return $this->render('sortie/index.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'today' => new \DateTime(),
        ]);
    }

    #[Route('/', name: 'app_sorties_filter', methods: ['POST'])]
    public function getFiltre(Request $request, EntityManagerInterface $em): Response
    {
        $params = [];
        if ($request->request->has('campus')) {
            $params['campus'] = $request->request->get('campus');
        }
        $service = new SortieService();
        $sorties = $em->getRepository(Sortie::class)->findBy($params);
        $sorties = $service->filterByDates($sorties,
            $request->request->get('dateDebut'),
            $request->request->get('dateFin'));
        if ($request->request->has('checkOrga')) {
            //TODO ajouter USER
            //$service->filterByOrga($sorties, )
        }
        if ($request->request->has('checkInscrit')) {
            $params['checkInscrit'] = $request->request->get('checkInscrit');
        }
        if ($request->request->has('checkNoInscrit')) {
            $params['checkNoInscrit'] = $request->request->get('checkNoInscrit');
        }
        if ($request->request->has('checkClose')) {
            $params['checkClose'] = $request->request->get('checkClose');
        }

        return $this->render('sortie/index.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $sorties,
            'today' => new \DateTime(),
            'filterForm' => $request,
        ]);
    }
}
