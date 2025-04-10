<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\Form\FormError;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Core\User\UserInterface;

// Importation de UserPasswordHasherInterface

class ProfileController extends AbstractController
{
    #[Route('/mon-profil', name: 'app_mon-profile', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserInterface $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): Response
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
            // Gestion de l'upload de la photo
            /** @var UploadedFile $photoFile */
            $photoFile = $form->get('photo')->getData();
            if ($photoFile) {
                // Générer un nom unique pour le fichier
                $newFilename = uniqid() . '.' . $photoFile->guessExtension();

                // Déplacer le fichier dans le répertoire de stockage
                try {
                    $photoFile->move(
                        $this->getParameter('uploads_directory'), // Répertoire où vous voulez stocker les photos
                        $newFilename
                    );
                } catch (IOExceptionInterface $e) {
                    $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                    return $this->redirectToRoute('app_mon-profile');
                }

                // Mettre à jour le chemin de la photo dans l'entité
                $participant->setPhoto($newFilename);
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

        // Rendu du formulaire pour afficher les champs et les erreurs si nécéssaire
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser(),
            'mode' => 'EDIT',
            'participant' => $participant,
        ]);
    }

    #[Route('/detail-profil/{id}', name: 'app_detail_participant')]
    public function show(int $id, EntityManagerInterface $em): Response
    {
        // Récupérer l'entité Participant par ID
        $participant = $em->getRepository(Participant::class)->find($id);

        if (!$participant) {
            throw $this->createNotFoundException('Participant non trouvé');
        }

        return $this->render('profile/detail.html.twig', [
            'participant' => $participant,
            'user' => $this->getUser()
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

        if (array_find($user->getRoles(), function (string $value) {
                return $value == 'ROLE_ADMIN';
            }) || $user->getUserIdentifier() == $participant->getUserIdentifier()) {
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
                $this->addFlash('success', 'Le profil a été mis à jour avec succès.');

                // Rediriger vers la page de l'accueil après la mise à jour
                return $this->redirectToRoute('home', [
                    'user' => $this->getUser()
                ]);
            }
        }
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $this->getUser(),
            'participant' => $participant,
            'mode' => 'EDIT'
        ]);
    }

    #[Route('/administration/users/create', name: 'create_user', methods: ['GET', 'POST'])]
    public function createUser(Request                     $request, UserInterface $user,
                               UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $em): \Symfony\Component\HttpFoundation\RedirectResponse|Response
    {
        $participant = new Participant();

        // Créer le formulaire
        $form = $this->createForm(ProfileType::class, $participant);

        // Traiter la soumission du formulaire
        $form->handleRequest($request);

        if (array_find($user->getRoles(), function (string $value) {
            return $value == 'ROLE_ADMIN';
        })) {
            if ($form->isSubmitted() && $form->isValid()) {
                // Récupérer les données du formulaire
                $participant = $form->getData();
                $participant->setAdministrateur(false);
                $hasError = false;
                if ($em->getRepository(Participant::class)->findOneBy(['pseudo' => $participant->getpseudo()]) !== null) {
                    $form->get('pseudo')->addError(new  FormError('Ce pseudo existe déjà'));
                    $hasError = true;
                }
                if ($em->getRepository(Participant::class)->findOneBy(['mail' => $participant->getMail()]) !== null) {
                    $form->get('mail')->addError(new  FormError('Cet email existe déjà'));
                    $hasError = true;
                }


                // Gestion de l'upload de la photo
                /** @var UploadedFile $photoFile */
                $photoFile = $form->get('photo')->getData();
                if ($photoFile) {
                    // Générer un nom unique pour le fichier
                    $newFilename = uniqid() . '.' . $photoFile->guessExtension();

                    // Déplacer le fichier dans le répertoire de stockage
                    try {
                        $photoFile->move(
                            $this->getParameter('uploads_directory'), // Répertoire où vous voulez stocker les photos
                            $newFilename
                        );
                    } catch (IOExceptionInterface $e) {
                        $this->addFlash('error', 'Erreur lors du téléchargement de la photo');
                        return $this->redirectToRoute('create_user');
                    }

                    // Mettre à jour le chemin de la photo dans l'entité
                    $participant->setPhoto($newFilename);
                }

                if ($hasError) {
                    return $this->render('profile/edit.html.twig', [
                        'form' => $form->createView(),
                        'user' => $this->getUser(),
                        'mode' => 'CREATE',
                        'participant' => $participant
                    ]);
                }
                // Hacher le mot de passe
                $hashedPassword = $passwordHasher->hashPassword($participant, $participant->getMotDePasse());
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
            'user' => $this->getUser(),
            'participant' => $participant,
            'mode' => 'CREATE'
        ]);
    }
}
