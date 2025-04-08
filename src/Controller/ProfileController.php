<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Importation de UserPasswordHasherInterface

class ProfileController extends AbstractController
{
    #[Route('/mon-profil', name: 'app_mon-profile', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserInterface $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        // Assurez-vous que $user est bien une instance de Participant
        $participant = $user; // Ici, on suppose que l'utilisateur est une instance de Participant

        // Créer le formulaire
        $form = $this->createForm(ProfileType::class, $participant);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            // Récupérer les données du formulaire
            $participant = $form->getData();
            $motDePasse = $participant->getMotDePasse();

            // Vérifier si le mot de passe est correct (vous pouvez demander à l'utilisateur de confirmer son mot de passe actuel)
            // Si un mot de passe est saisi, vous devez le hacher et le mettre à jour
            if (!empty($motDePasse)) {
            // Hacher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($participant, $motDePasse);
                $participant->setMotDePasse($hashedPassword);  // Mettre à jour le mot de passe haché dans l'entité
            }

            // Sauvegarder les modifications dans la base de données
            $entityManager->flush();

            // Message de confirmation
            $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

            // Rediriger vers la page de l'accueil après la mise à jour
            return $this->redirectToRoute('home',[
                'user'  => $this->getUser()
            ]);
        }

        // Rendu du formulaire pour afficher les champs et les erreurs si nécéssaire
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user'  => $this->getUser(),
            'mode' => 'EDIT',
            'participant' => $participant,
        ]);
    }

    #[\Symfony\Component\Routing\Attribute\Route('/detail-profil/{id}', name: 'app_detail_participant')]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        // Récupérer l'entité Participant par ID
        $participant = $em->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé');
        }

        return $this->render('profile/detail.html.twig', [
            'participant' => $participant,
            'user'  => $this->getUser()
        ]);
    }

    #[Route('/profil/{id}', name: 'edit_profil', methods: ['GET', 'POST'])]
    public function editProfil(int $id, Request $request, UserInterface $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager)
    {
        $participant = $entityManager->getRepository(Participant::class)->find($id);

        // Créer le formulaire
        $form = $this->createForm(ProfileType::class, $participant);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);

        if(array_find($user->getRoles(), function(string $value){return $value=='ROLE_ADMIN';}) || $user->getUserIdentifier() == $participant->getUserIdentifier()) {
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupérer les données du formulaire
                $participant = $form->getData();
                $motDePasse = $participant->getMotDePasse();

                // Vérifier si le mot de passe est correct (vous pouvez demander à l'utilisateur de confirmer son mot de passe actuel)
                // Si un mot de passe est saisi, vous devez le hacher et le mettre à jour
                if (!empty($motDePasse)) {
                    // Hacher le mot de passe
                    $hashedPassword = $passwordHasher->hashPassword($participant, $motDePasse);
                    $participant->setMotDePasse($hashedPassword);  // Mettre à jour le mot de passe haché dans l'entité
                }

                // Sauvegarder les modifications dans la base de données
                $entityManager->flush();

                // Message de confirmation
                $this->addFlash('success', 'Votre profil a été mis à jour avec succès.');

                // Rediriger vers la page de l'accueil après la mise à jour
                return $this->redirectToRoute('home', [
                    'user' => $this->getUser()
                ]);
            }
        }
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user'  => $this->getUser(),
            'participant' => $participant,
            'mode' => 'EDIT'
        ]);
    }

    #[Route('/administration/users/create', name: 'create_user', methods: ['GET', 'POST'])]
    public function createUser(Request $request, UserInterface $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em)
    {
        $participant = new Participant();

        // Créer le formulaire
        $form = $this->createForm(ProfileType::class, $participant);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);

        if(array_find($user->getRoles(), function(string $value){return $value=='ROLE_ADMIN';})) {
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupérer les données du formulaire
                $participant = $form->getData();
                $participant->setAdministrateur(false);
                $hasError =  false;
                if ($em->getRepository(Participant::class)->findOneBy(['pseudo' => $participant->getpseudo()]) !== null) {
                    $form->get('pseudo')->addError(new  FormError('Ce pseudo existe déjà'));
                    $hasError = true;
                }
                if ($em->getRepository(Participant::class)->findOneBy(['email' => $participant->getMail()]) !== null) {
                    $form->get('email')->addError(new  FormError('Cet email existe déjà'));
                    $hasError = true;
                }

                if($hasError) {
                    return $this->render('profile/edit.html.twig', [
                        'form' => $form->createView(),
                        'user'  => $this->getUser(),
                        'mode' => 'CREATE',
                        'participant' => $participant
                    ]);
                }
                // Hacher le mot de passe
                $hashedPassword = $passwordHasher->needsRehash($participant->getMotDePasse());
                $participant->setMotDePasse($hashedPassword);  // Mettre à jour le mot de passe haché dans l'entité

                $em->persist($participant);
                // Sauvegarder les modifications dans la base de données
                $em->flush();

                // Message de confirmation
                $this->addFlash('success', 'Cet utilisateur a été créé avec succès');

                // Rediriger vers la page de l'accueil après la mise à jour
                return $this->redirectToRoute('home', [
                    'user' => $this->getUser(),
                    'participant' => $participant
                ]);
            }
        }
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user'  => $this->getUser(),
            'participant' => $participant,
            'mode'  => 'CREATE'
        ]);
    }
}
