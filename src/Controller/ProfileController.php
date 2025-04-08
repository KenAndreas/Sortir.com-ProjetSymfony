<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\Filesystem\Exception\IOExceptionInterface;
use Symfony\Component\HttpFoundation\File\UploadedFile;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

// Importation de UserPasswordHasherInterface

class ProfileController extends AbstractController
{
    #[Route('/mon-profil', name: 'app_mon-profile', methods: ['GET', 'POST'])]
    public function edit(Request $request, UserInterface $user, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager) :Response
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
            return $this->redirectToRoute('home');
        }

        // Rendu du formulaire pour afficher les champs et les erreurs si nécéssaire
        return $this->render('profile/edit.html.twig', [
            'form' => $form->createView(),
            'user' => $participant,  // Pass the 'user' variable to the template
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
        ]);
    }
}
