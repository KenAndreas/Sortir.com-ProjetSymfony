<?php
// src/DataFixtures/AppFixtures.php

namespace App\DataFixtures;

use App\Entity\Ville;
use App\Entity\Lieu;
use App\Entity\Campus;
use App\Entity\Etat;
use App\Entity\Participant;
use App\Entity\Sortie;
use Doctrine\Bundle\FixturesBundle\Fixture;
use Doctrine\Persistence\ObjectManager;
use Faker\Factory;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;

class AppFixtures extends Fixture
{
private UserPasswordHasherInterface $passwordHasher;

public function __construct(UserPasswordHasherInterface $passwordHasher)
{
$this->passwordHasher = $passwordHasher;
}

public function load(ObjectManager $manager): void
{
// Utilisation de Faker pour générer des données aléatoires
$faker = Factory::create();

// Création de villes
$ville1 = new Ville();
$ville1->setNom('Paris');
$ville1->setCodePostal('75001');
$manager->persist($ville1);

$ville2 = new Ville();
$ville2->setNom('Lyon');
$ville2->setCodePostal('69001');
$manager->persist($ville2);

// Création de lieux
$lieu1 = new Lieu();
$lieu1->setNom('Tour Eiffel')
->setRue('Champ de Mars')
->setLatitude(48.8588443)
->setLongitude(2.2943506)
->setVille($ville1);
$manager->persist($lieu1);

$lieu2 = new Lieu();
$lieu2->setNom('Place Bellecour')
->setRue('Place Bellecour')
->setLatitude(45.757813)
->setLongitude(4.832013)
->setVille($ville2);
$manager->persist($lieu2);

// Création des états
$etat1 = new Etat();
$etat1->setLibelle('En création');
$manager->persist($etat1);

$etat2 = new Etat();
$etat2->setLibelle('Ouverte');
$manager->persist($etat2);

$etat3 = new Etat();
$etat3->setLibelle('Clôturée');
$manager->persist($etat3);

// Création des campus
$campus1 = new Campus();
$campus1->setNom('Université Paris 1');
$manager->persist($campus1);

$campus2 = new Campus();
$campus2->setNom('Université Lyon 2');
$manager->persist($campus2);

// Création de participants avec mot de passe haché
$participant1 = new Participant();
$participant1->setNom($faker->lastName)
->setPrenom($faker->firstName)
->setMail($faker->email)
->setAdministrateur(false)
->setActif(true)
->setPseudo($faker->userName)
->setCampus($campus1);
$hashedPassword = $this->passwordHasher->hashPassword($participant1, 'password');
$participant1->setMotDePasse($hashedPassword);
$manager->persist($participant1);

$participant2 = new Participant();
$participant2->setNom($faker->lastName)
->setPrenom($faker->firstName)
->setMail($faker->email)
->setAdministrateur(false)
->setActif(true)
->setPseudo($faker->userName)
->setCampus($campus2);
$hashedPassword = $this->passwordHasher->hashPassword($participant2, 'password');
$participant2->setMotDePasse($hashedPassword);
$manager->persist($participant2);

// Création de participants avec mot de passe haché
    $participant3 = new Participant();
    $participant3->setNom($faker->lastName)
        ->setPrenom($faker->firstName)
        ->setMail($faker->email)
        ->setAdministrateur(false)
        ->setActif(true)
        ->setPseudo($faker->userName)
        ->setCampus($campus1);
    $hashedPassword = $this->passwordHasher->hashPassword($participant3, 'password');
    $participant3->setMotDePasse($hashedPassword);
    $manager->persist($participant3);

// Création de sorties
$sortie1 = new Sortie();
$sortie1->setNom('Concert de Rock')
->setDateHeureDebut($faker->dateTimeThisYear)
->setDuree(new \DateTime('02:00:00'))
->setDateLimiteInscription($faker->dateTimeBetween('-1 week', '+3 week'))
->setNbInscriptionMax(100)
->setInfosSortie('Concert au Stade de France')
->setEtat($etat1)
->setCampus($campus1)
->setLieu($lieu1)
->setOrganisateur($participant1);

// Associer les participants à la sortie
$sortie1->addParticipant($participant1);
$sortie1->addParticipant($participant2);  // Ajoute participant2 à la sortie1

$manager->persist($sortie1);

$sortie2 = new Sortie();
$sortie2->setNom('Sortie sportive')
->setDateHeureDebut($faker->dateTimeThisYear)
->setDuree(new \DateTime('02:00:00'))
->setDateLimiteInscription($faker->dateTimeBetween('-1 week', '+1 week'))
->setNbInscriptionMax(20)
->setInfosSortie('Randonnée dans la montagne')
->setEtat($etat2)
->setCampus($campus2)
->setLieu($lieu2)
->setOrganisateur($participant2);

// Ajouter les participants à cette sortie
$sortie2->addParticipant($participant1);
$sortie2->addParticipant($participant2);  // Ajoute participant2 à la sortie2

$manager->persist($sortie2);


// Sortie sans participants supplémentaires
    $sortie3 = new Sortie();
    $sortie3->setNom('Exposition d\'art')
        ->setDateHeureDebut($faker->dateTimeBetween('now', '+3 week'))
        ->setDuree(new \DateTime('03:00:00'))
        ->setDateLimiteInscription($faker->dateTimeBetween('now', '+6 week'))
        ->setNbInscriptionMax(50)
        ->setInfosSortie('Exposition d\'art contemporain au musée')
        ->setEtat($etat2)
        ->setCampus($campus1)
        ->setLieu($lieu1)
        ->setOrganisateur($participant1);

// Ne pas ajouter de participants à cette sortie
    $manager->persist($sortie3);

// Finalisation de la persistance des données
    $manager->flush();


// Finalisation de la persistance des données
$manager->flush();
}
}
