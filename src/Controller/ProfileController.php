<?php
// src/Controller/ProfileController.php

namespace App\Controller;

use App\Entity\Participant;
use App\Form\ProfileType;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\User\UserInterface;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;  // Importation de UserPasswordHasherInterface

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

// Rediriger vers la page du profil après la mise à jour
return $this->redirectToRoute('app_sortie');
}

// Rendu du formulaire pour afficher les champs et les erreurs si nécessaire
return $this->render('profile/edit.html.twig', [
'form' => $form->createView(),
]);
}
}
