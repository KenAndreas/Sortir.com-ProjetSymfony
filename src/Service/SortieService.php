<?php
namespace App\Service;

use App\Entity\Participant;
use function Sodium\add;

final class SortieService{

    public function filterByDates($sorties, $dateDebut, $dateFin): array
    {
        $filteredSorties = [];
        $index = 0;
        foreach ($sorties as $sortie) {
            if($dateDebut != "" && $dateFin != ""){
                if($dateDebut <= $sortie->getDateHeureDebut() && $sortie->getDateHeureDebut() <= $sortie->getDateFin()){
                    $filteredSorties[$index] = $sortie;
                }
            }elseif ($dateDebut != ""){
                if($dateDebut <= $sortie->getDateHeureDebut()){
                    $filteredSorties[$index] = $sortie;
                }
            } elseif ($dateFin != ""){
                if($dateFin >= $sortie->getDateHeureDebut()){
                    $filteredSorties[$index] = $sortie;
                }
            }else{
                $filteredSorties[$index] = $sortie;
            }
            $index+=1;
        }
        return $filteredSorties;
    }

    public function filterByOrga($sorties, Participant $user): array
    {
        $filteredSorties = [];
        $index = 0;
        foreach ($sorties as $sortie) {
            if( $sortie->getOrganisateur()->getPseudo() == $user->getPseudo() ){
                $filteredSorties[$index] = $sortie;
            }
            $index+=1;
        }
        return $filteredSorties;
    }
}