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

// Injectez le service UserPasswordHasherInterface via le constructeur
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

// Création d'états
$etat1 = new Etat();
$etat1->setLibelle('En création');
$manager->persist($etat1);

$etat2 = new Etat();
$etat2->setLibelle('Ouverte');
$manager->persist($etat2);

$etat3 = new Etat();
$etat3->setLibelle('Clôturée');
$manager->persist($etat3);

// Création de campus
$campus1 = new Campus();
$campus1->setNom('Université Paris 1');
$manager->persist($campus1);

$campus2 = new Campus();
$campus2->setNom('Université Lyon 2');
$manager->persist($campus2);

// Création de participants (Utilisation de Faker pour les noms, prénoms, mails, etc.)
$participant1 = new Participant();
$participant1->setNom($faker->lastName)
->setPrenom($faker->firstName)
->setMail($faker->email)
->setAdministrateur(false)
->setActif(true)
->setPseudo($faker->userName)
->setCampus($campus1);

// Hachage du mot de passe avant de le persister
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

// Hachage du mot de passe avant de le persister
$hashedPassword = $this->passwordHasher->hashPassword($participant2, 'password');
$participant2->setMotDePasse($hashedPassword);

$manager->persist($participant2);

// Création de sorties
$sortie1 = new Sortie();
$sortie1->setNom('Sortie culturelle')
->setDateHeureDebut($faker->dateTimeThisYear)
->setDuree(new \DateTime('01:30:00'))
->setDateLimiteInscription($faker->dateTimeBetween('-1 week', 'now'))
->setNbInscriptionMax(30)
->setInfosSortie('Visite guidée de la Tour Eiffel')
->setEtat($etat1)
->setCampus($campus1)
->setLieu($lieu1)
->setOrganisateur($participant1);
$manager->persist($sortie1);

$sortie2 = new Sortie();
$sortie2->setNom('Sortie sportive')
->setDateHeureDebut($faker->dateTimeThisYear)
->setDuree(new \DateTime('02:00:00'))
->setDateLimiteInscription($faker->dateTimeBetween('-1 week', 'now'))
->setNbInscriptionMax(20)
->setInfosSortie('Randonnée dans la montagne')
->setEtat($etat2)
->setCampus($campus2)
->setLieu($lieu2)
->setOrganisateur($participant2);
$manager->persist($sortie2);

// Flush les données dans la base
$manager->flush();
}
}
