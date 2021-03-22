<?php

namespace App\Controller;

use App\Entity\Licence;
use App\Repository\CabinetRepository;
use App\Repository\LicenceRepository;
use App\Security\Encoder\MyCustomPasswordEncoder;
use DateTime;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class LicenceController extends AbstractController
{
    /**
     * @Route("api/admin/licences/{idCabinet}", name="licences_cabinet", methods={"GET"})
     */
    public function getAllLicencesByCabinet($idCabinet = -1, CabinetRepository $cabinetRepository, LicenceRepository $licenceRepository): Response
    {
        if ($idCabinet == -1) {
            return $this->json(["message" => "Veuillez renseigner un identifiant de cabinet"], 400);
        } else {
            $cabinet = $cabinetRepository->find($idCabinet);
            if (!$cabinet) {
                return $this->json(["message" => "Ce cabinet n'existe pas"], 400);
            }
            $licences = $licenceRepository->getAllLicences($cabinet);
            return $this->json($licences, 200, [], ['groups' => 'licence:read']);
        }
    }

    /**
     * @Route("api/licences/{idCabinet}", name="licences_actives_cabinet", methods={"GET"})
     */
    public function getAllActiveLicencesByCabinet($idCabinet = -1, CabinetRepository $cabinetRepository, LicenceRepository $licenceRepository): Response
    {
        if ($idCabinet == -1) {
            return $this->json(["message" => "Veuillez renseigner un identifiant de cabinet"], 400);
        } else {
            $cabinet = $cabinetRepository->find($idCabinet);
            if (!$cabinet) {
                return $this->json(["message" => "Ce cabinet n'existe pas"], 400);
            }
            $licences = $licenceRepository->getActiveLicences($cabinet);
            return $this->json($licences, 200, [], ['groups' => 'licence:read']);
        }
    }

    /**
     * @Route("api/admin/licence/create", name="licence_create", methods={"POST"})
     */
    public function createLicence(Request $request, SerializerInterface $serializer, CabinetRepository $cabinetRepository, EntityManagerInterface $entityManager): Response
    {
        $jsonRecu = $request->getContent();
        $licenceDecode = json_decode($jsonRecu, true)["licence"];
        foreach ($licenceDecode as $key => $value) { //supprimer les valeurs égalent à "" ou nul
            if (empty($value)) {
                unset($licenceDecode[$key]);
            }
        }

        $newLicence = $serializer->deserialize(json_encode($licenceDecode), Licence::class, 'json');

        if (empty($newLicence->getNombrePostes())) {
            return $this->json(["message" => "Le nombre de postes est obligatoire"], 400);
        }

        if (empty($newLicence->getDateCreation())) {
            return $this->json(["message" => "La date de création est obligatoire"], 400);
        }

        if (empty($newLicence->getDateDebut())) {
            return $this->json(["message" => "La date de début est obligatoire"], 400);
        }

        $cabinet = $cabinetRepository->find($licenceDecode["cabinetId"]);
        if (!$cabinet) {
            return $this->json(["message" => "Ce cabinet n'existe pas"], 400);
        }

        $newLicence->setCabinet($cabinet);
        $entityManager->persist($newLicence);

        $entityManager->flush();

        return $this->json(["message" => "Cabinet crée"], 201);
    }


    /**
     * @Route("api/download/licences/{idLicence}", name="licences_download", methods={"GET"})
     */
    public function downloadLicence($idLicence = -1, LicenceRepository $licenceRepository, MyCustomPasswordEncoder $encoder): Response
    {
        if ($idLicence == -1) {
            return $this->json(["message" => "Veuillez renseigner un identifiant de licence"], 400);
        } else {
            $licence = $licenceRepository->find($idLicence);

            if (!$licence) {
                return $this->json(["message" => "Cette licence n'existe pas"], 400);
            }

            $cabinet = $licence->getCabinet();
            if (!$cabinet) {
                return $this->json(["message" => "Cette licence n'est associée à aucun cabinet"], 400);
            }

            $cleLicence = [
                "nomCabinet" => str_pad($cabinet->getNomCabinet(), 64),
                "idLicence" => str_pad($cabinet->getId(), 4),
                "dateDebut" => date_format($licence->getDateDebut(), "Y") . date_format($licence->getDateDebut(), "m") . date_format($licence->getDateDebut(), "d"),
                "dateFin" => date_format($licence->getDateFin(), "Y") . date_format($licence->getDateFin(), "m") . date_format($licence->getDateFin(), "d"),
                "deltaJourFin" => str_pad($licence->getDeltaJourFin(), 4),
                "nombrePostes" => str_pad($licence->getNombrePostes(), 4),
                "deltaNombrePoste" => str_pad($licence->getDeltaNombrePostes(), 4)
            ];

            return $this->json($encoder->encodePassword(implode("|", $cleLicence), ""), 200);
        }
    }
}
