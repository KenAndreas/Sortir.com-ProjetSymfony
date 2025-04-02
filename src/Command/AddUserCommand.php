<?php

namespace App\Command;

use App\Entity\Participant;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputArgument;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Output\OutputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AddUserCommand extends Command
{
private $entityManager;
private $passwordHasher;

public function __construct(EntityManagerInterface $entityManager, UserPasswordHasherInterface $passwordHasher)
{
parent::__construct();
$this->entityManager = $entityManager;
$this->passwordHasher = $passwordHasher;
}

protected function configure(): void
{
$this
->setName('app:add-user')
->setDescription('Ajoute un utilisateur avec un mot de passe haché')
->addArgument('email', InputArgument::REQUIRED, 'L\'email de l\'utilisateur')
->addArgument('motDePasse', InputArgument::REQUIRED, 'Le mot de passe de l\'utilisateur');
}

protected function execute(InputInterface $input, OutputInterface $output): int
{
$io = new SymfonyStyle($input, $output);

// Récupérer les arguments
$email = $input->getArgument('email');
$motDePasse = $input->getArgument('motDePasse');

// Vérifier si l'email existe déjà
$existingParticipant = $this->entityManager->getRepository(Participant::class)->findOneBy(['mail' => $email]);
if ($existingParticipant) {
$io->error("Un participant avec cet email existe déjà.");
return Command::FAILURE;
}

// Créer un nouvel objet Participant
$participant = new Participant();
$participant->setMail($email);

// Hacher le mot de passe
$hashedPassword = $this->passwordHasher->hashPassword($participant, $motDePasse);
$participant->setMotDePasse($hashedPassword);

// Assigner des valeurs par défaut aux autres champs
$participant->setNom('Non renseigné');
$participant->setPrenom('Non renseigné');
$participant->setPseudo($email);
$participant->setActif(true);
$participant->setAdministrateur(false);

// Sauvegarder dans la base de données
$this->entityManager->persist($participant);
$this->entityManager->flush();

// Afficher un message de succès
$io->success("Participant avec l'email {$email} ajouté avec succès!");

return Command::SUCCESS;
}
}
