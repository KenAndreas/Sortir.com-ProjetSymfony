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
use phpDocumentor\Reflection\Types\This;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Attribute\Route;

final class SortieController extends AbstractController
{
    #[Route('/', name: 'home', methods: ['GET'])]
    public function index(EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        if ($user != null) {
            $user = $this->getUser();
            $user = $em->getRepository(Participant::class)->findOneBy(['id' => $user->getId()]);
        }
        $isModified = false;
        $sorties = $em->getRepository(Sortie::class)->findAll();
/*        foreach ($sorties as $sortie) {
            $limitDate = $sortie;
            $limitDate = date_add($limitDate->getDateHeureDebut(), date_interval_create_from_date_string("1 month"));
            printf($limitDate->format('Y-m-d') . ' pour ' . $sortie->getDateHeureDebut()->format('Y-m-d'));
            if($sortie->getDateHeureDebut()  >= $limitDate && $sortie->getEtat()->getLibelle() != 'Historisée'){
                $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Historisée']));
                $isModified = true;
            }
        }*/

        if ($isModified) {
            $em->flush();
        }

        $filter = function ($el)
        {
            if($el->getEtat()->getLibelle() != 'Historisée'){
                return true;
            }
            return false;
        };


        return $this->render('sortie/home.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => array_filter($sorties, $filter),
            'user' => $user,
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

        $user = $this->getUser();
        if ($user != null) {
            $user = $this->getUser();
            $user = $em->getRepository(Participant::class)->findOneBy(['id' => $user->getId()]);
        }

        if ($request->request->has('checkOrga')) {
            $sorties = $service->filterByOrga($sorties, $user);
        }
        if ($request->request->has('checkInscrit')) {
            $sorties = $service->filterByInscrit($sorties, $user);
        }
        if ($request->request->has('checkNoInscrit')) {
            $sorties = $service->filterByNonInscrit($sorties, $user);
        }
        if ($request->request->has('checkClose')) {
            $etat = $em->getRepository(Etat::class)->findOneBy(['libelle' => 'Terminée']);
            $sorties = $service->filterByEtatClose($sorties, $etat);
        }

        return $this->render('sortie/home.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $sorties,
            'filterForm' => $request,
            'user' => $user,
        ]);
    }

    #[Route('/sortie/{id}', name: 'show_sortie', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function getSortie(int $id, SortieRepository $sortieRepository): Response
    {
        $sortie = $sortieRepository->find($id);
        if ($sortie) {
            return $this->render('sortie/showSortie.html.twig', [
                'sortie' => $sortie,
                'annulation'=> false,
            ]);
        } else {
            return $this->redirectToRoute('app_error');
        }
    }

    #[Route('/sortie/create', name: 'create_sortie', methods: ['GET', 'POST'])]
    public function createSortie(Request $request, EntityManagerInterface $em): Response
    {

        $user = $em->getRepository(Participant::class)->findOneBy(['id' => $this->getUser()->getId()]);
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);

        $form->handleRequest($request);
        //vérifier utilisateur
        if ($this->getUser()) {
            $user = $this->getUser();
            $orga = $em->getRepository(Participant::class)->findOneBy(['pseudo' => $user->getPseudo()]);
            if ($form->isSubmitted() && $form->isValid()) {
                //Ajout des données validées
                $sortie->setNom($form->get('nom')->getData());
                $sortie->setDateHeureDebut($form->get('dateHeureDebut')->getData());
                $sortie->setDuree($form->get('duree')->getData());
                $sortie->setDateLimiteInscription($form->get('dateLimiteInscription')->getData());
                $sortie->setNbInscriptionMax($form->get('nbInscriptionMax')->getData());
                $sortie->setInfosSortie($form->get('infosSortie')->getData());
                if ($form->has('etatSave')) {
                    $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'En création']));
                } else {
                    $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
                }
                $sortie->setCampus($em->getRepository(Campus::class)->findOneBy(['id' => $form->get('campus')->getData()]));
                $sortie->setLieu($em->getRepository(Lieu::class)->findOneBy(['id' => $form->get('lieu')->getData()]));
                $sortie->setOrganisateur($orga);

                $em->persist($orga);
                $em->persist($sortie);

                $em->flush();
                $this->addFlash('success', 'Votre sortie a bien été' . $form->has('etatSave') ? 'enregistrée !' : 'crée !');
                return $this->redirectToRoute('home', [
                    'campus' => $em->getRepository(Campus::class)->findAll(),
                    'sorties' => $em->getRepository(Sortie::class)->findAll(),
                    'user' => $user,
                ]);
            }
        }
        return $this->render('sortie/sortieForm.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'villes' => $em->getRepository(Ville::class)->findAll(),
            'lieux' => $em->getRepository(Lieu::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'create' => true,
            'form' => $form,
            'user' => $user,
        ]);
    }

    #[Route('/sortie/update/{id}', name: 'update_sortie', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function updateSortie(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $sortie = new Sortie();
        $form = $this->createForm(SortieType::class, $sortie);
        if ($request->getMethod() == "POST") {
            $initSortie = $em->getRepository(Sortie::class)->find($request->get('id'));
            $form->handleRequest($request);
            $user = $this->getUser();
            //vérifier utilisateur
            if ($user != null && $user->getPseudo() == $initSortie->getOrganisateur()->getPseudo()) {
                $orga = $initSortie->getOrganisateur();
                if ($form->isSubmitted() && $form->isValid()) {
                    $sortie = $initSortie;
                    //Ajout des données validées
                    $sortie->setNom($form->get('nom')->getData());
                    $sortie->setDateHeureDebut($form->get('dateHeureDebut')->getData());
                    $sortie->setDuree($form->get('duree')->getData());
                    $sortie->setDateLimiteInscription($form->get('dateLimiteInscription')->getData());
                    $sortie->setNbInscriptionMax($form->get('nbInscriptionMax')->getData());
                    $sortie->setInfosSortie($form->get('infosSortie')->getData());
                    if ($form->has('etatSave')) {
                        $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'En création']));
                    } else {
                        $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));
                    }
                    $sortie->setCampus($em->getRepository(Campus::class)->findOneBy(['id' => $form->get('campus')->getData()]));
                    $sortie->setLieu($em->getRepository(Lieu::class)->findOneBy(['id' => $form->get('lieu')->getData()]));

                    $em->flush();
                    $this->addFlash('success', 'Votre sortie a bien été' . $form->has('etatSave') ? 'enregistrée !' : 'modifiée !');
                    return $this->redirectToRoute('home',[
                        'campus' => $em->getRepository(Campus::class)->findAll(),
                        'sorties' => $em->getRepository(Sortie::class)->findAll(),
                        'user' => $user,
                    ]);
                }
            } else {
                return $this->redirectToRoute('app_error', [
                    'message' => "403"
                ]);
            }
        } else {
            $form = $this->createForm(SortieType::class, $em->getRepository(Sortie::class)->find($request->get('id')));
        }

        return $this->render('sortie/sortieForm.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'villes' => $em->getRepository(Ville::class)->findAll(),
            'lieux' => $em->getRepository(Lieu::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'create' => true,
            'form' => $form,
        ]);
    }

    #[Route('/sortie/publier/{id}', name: 'post_sortie', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function postSortie(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sortie = $em->getRepository(Sortie::class)->find($request->get('id'));

        if ($user === $sortie->getOrganisateur()) {
            $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Ouverte']));

            $em->flush();
        } else {
            return $this->redirectToRoute('app_error', [
                'message' => "403"
            ]);
        }

        return $this->redirectToRoute('home', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'user' => $user,]);
    }

    #[Route('/sortie/delete/{id}', name: 'delete_sortie', requirements: ['id' => '\d+'], methods: ['GET'])]
    public function deleteSortie(Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sortie = $em->getRepository(Sortie::class)->find($request->get('id'));

        if ($user === $sortie->getOrganisateur()) {
            $em->remove($sortie);
            $em->flush();
        } else {
            return $this->redirectToRoute('app_error', [
                'message' => "403"
            ]);
        }

        return $this->redirectToRoute('home', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'user' => $user,]);
    }

    #[Route('/sortie/annuler/{id}', name: 'annuler_sortie', requirements: ['id' => '\d+'], methods: ['GET', 'POST'])]
    public function annuleSortie(int $id, Request $request, EntityManagerInterface $em): Response
    {
        $user = $this->getUser();
        $sortie = $em->getRepository(Sortie::class)->find($id);

        if ($user === $sortie->getOrganisateur()
            && $sortie->getDateHeureDebut() > new \DateTime('now')
            && ($sortie->getEtat()->getLibelle() != 'En création' || $sortie->getEtat()->getLibelle() != 'Annulée')&& $request->get('motif') != null) {

            $sortie->setEtat($em->getRepository(Etat::class)->findOneBy(['libelle' => 'Annulée']));
            $sortie->setInfosSortie('Annulée : ' . $request->get('motif') . ' - ' .
                            $em->getRepository(Sortie::class)->findOneBy(['id' => $id])->getInfosSortie());
            $em->flush();
        } else {
            return $this->render('sortie/showSortie.html.twig', [
                'sortie' => $sortie,
                'annulation' => true]);
        }

        return $this->render('sortie/home.html.twig', [
            'campus' => $em->getRepository(Campus::class)->findAll(),
            'sorties' => $em->getRepository(Sortie::class)->findAll(),
            'user' => $user,]);
    }
}
