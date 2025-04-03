<?php

namespace App\Controller;

use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Lieu;
use App\Entity\Participant;
use App\Entity\Sortie;
use App\Entity\Ville;
use App\Form\SortieType;
use App\Repository\SortieRepository;
use App\Service\SortieService;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        return $this->render('sortie/home.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'today' => new \DateTime(),
        ]);
    }

    #[Route('/', name: 'app_sorties_filter', methods: ['POST'])]
    public function getFiltre(Request $request, EntityManagerInterface $em): Response
    {
        $params = [];

        //Filtre par campus
        if ($request->request->get('campus') != "") {
            $params['campus'] = $request->request->get('campus');
        }

        $service = new SortieService();
        $sorties = $em->getRepository(Sortie::class)->findBy($params);

        //Filtre par dates
        $sorties = $service->filterByDates($sorties,
            $request->request->get('dateDebut'),
            $request->request->get('dateFin'));

        //TODO ajouter USER
        $falseUser = $em->getRepository(Participant::class)->findOneBy(['pseudo' => 'Gégé']);

        if ($request->request->has('checkOrga')) {
            $sorties = $service->filterByOrga($sorties, $falseUser);
        }
        if ($request->request->has('checkInscrit')) {
            $sorties = $service->filterByInscrit($sorties, $falseUser);
        }
        if ($request->request->has('checkNoInscrit')) {
            $sorties = $service->filterByNonInscrit($sorties, $falseUser);
        }
        if ($request->request->has('checkClose')) {
            $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Terminée']);
            $sorties = $service->filterByEtatClose($sorties, $etat);
        }

        return $this->render('home.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $sorties,
            'today' => new \DateTime(),
            'filterForm' => $request,
        ]);
    }

    #[Route('/sortie/{id}', name: 'show_sortie', requirements: ['id' =>'\d+'], methods: ['GET'])]
    public function getSortie(int $id,SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        if($sortie) {
            return $this->render('sortie/showSortie.html.twig', [
                'sortie' => $sortie,
            ]);
        }else{
            return $this->redirectToRoute('app_error');
        }
    }

    #[Route('/sortie/create', name: 'create_sortie', methods: ['GET', 'POST'])]
    public function createSortie(Request $request,EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();
        //vérifier utilisateur

        //vérifier sortie
        //si ok pousser
        return $this->render('sortie/sortieForm.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'villes' => $em->getRepository(Ville::class)->findAll(),
            'lieux' => $em->getRepository(Lieu::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'today' => new \DateTime(),
            'create' => true,
            'form' => $this->createForm(SortieType::class, $sortie ),
        ]);
    }
}
