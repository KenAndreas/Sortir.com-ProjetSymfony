<?php

namespace App\Service;

use App\Entity\Etat;

final class SortieService
{

    public function filterByDates($sorties, $dateDebut, $dateFin): array
    {
        $filteredSorties = [];
        $index = 0;
        foreach ($sorties as $sortie) {
            if ($dateDebut != "" && $dateFin != "") {
                if ($dateDebut <= $sortie->getDateHeureDebut()->format('Y-m-d') && $sortie->getDateHeureDebut()->format('Y-m-d') <= $dateFin) {
                    $filteredSorties[$index] = $sortie;
                }
            } elseif ($dateDebut != "") {
                if ($dateDebut <= $sortie->getDateHeureDebut()->format('Y-m-d')) {
                    $filteredSorties[$index] = $sortie;
                }
            } elseif ($dateFin != "") {
                if ($sortie->getDateHeureDebut()->format('Y-m-d') <= $dateFin) {
                    $filteredSorties[$index] = $sortie;
                }
            } else {
                $filteredSorties[$index] = $sortie;
            }
            $index += 1;
        }
        return $filteredSorties;
    }

    public function filterByOrga($sorties, $user): array
    {
        $filteredSorties = [];
        $index = 0;
        foreach ($sorties as $sortie) {
            if ($sortie->getOrganisateur()->getPseudo() == $user->getPseudo()) {
                $filteredSorties[$index] = $sortie;
            }
            $index += 1;
        }
        return $filteredSorties;
    }

    public function filterByInscrit($sorties, $user): array
    {
        $filteredSorties = [];
        $index = 0;
        foreach ($sorties as $sortie) {
            $participants = $sortie->getParticipants();
            $hasparticipant = false;
            foreach ($participants as $participant) {
                if ($participant->getPseudo() == $user->getPseudo()) {
                    $hasparticipant = true;
                }
            }
            if ($hasparticipant == true) {
                $filteredSorties[$index] = $sortie;
            }
            $index += 1;
        }
        return $filteredSorties;
    }

    public function filterByNonInscrit($sorties, $user): array
    {
        $filteredSorties = [];
        $index = 0;
        foreach ($sorties as $sortie) {
            $participants = $sortie->getParticipants();
            $hasparticipant = false;
            foreach ($participants as $participant) {
                if ($participant->getPseudo() == $user->getPseudo()) {
                    $hasparticipant = true;
                }
            }
            if ($hasparticipant == false) {
                $filteredSorties[$index] = $sortie;
            }
            $index += 1;
        }
        return $filteredSorties;
    }

    public function filterByEtatClose($sorties, Etat $etat): array
    {
        $filteredSorties = [];
        $index = 0;
        foreach ($sorties as $sortie) {
            if ($sortie->getEtat()->getLibelle() == $etat->getLibelle()) {
                $filteredSorties[$index] = $sortie;
            }
            $index += 1;
        }
        return $filteredSorties;
    }
}