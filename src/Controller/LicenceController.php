<?php

namespace App\Controller;

use App\Entity\Licence;
use App\Form\LicenceFormType;
use App\Repository\CabinetRepository;
use App\Repository\LicenceRepository;
use App\Security\Encoder\MyCustomEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Serializer\SerializerInterface;

class LicenceController extends AbstractController
{
    /**
     * @Route("admin/licences/{idCabinet}", name="licences_cabinet", methods={"GET"})
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
     * @Route("licences/{idCabinet}", name="licences_actives_cabinet", methods={"GET"})
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
     * @Route("admin/licence/create", name="licence_create", methods={"POST"})
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

        if (!empty($newLicence->getDateFin()) && $newLicence->getDateFin() <= $newLicence->getDateDebut()) {
            return $this->json(["message" => "La date de fin doit être supérieur à la date de début"], 400);
        }

        if (intval($newLicence->getNombrePostes()) > 1000) {
            return $this->json(["message" => "La nombre de postes doit être inférieur à 1001"], 400);
        }

        if (intval($newLicence->getDeltaNombrePostes()) > 30) {
            return $this->json(["message" => "Le delta du nombre de postes doit être inférieur à 31"], 400);
        }

        if (intval($newLicence->getDeltaJourFin()) > 30) {
            return $this->json(["message" => "Le delta du nombre de jours avant l'expiration de la licence doit être inférieur à 31"], 400);
        }

        if (empty($licenceDecode["cabinetId"])) {
            return $this->json(["message" => "L'id du cabinet est obligatoire"], 400);
        }

        $cabinet = $cabinetRepository->find($licenceDecode["cabinetId"]);
        if (!$cabinet) {
            return $this->json(["message" => "Ce cabinet n'existe pas"], 400);
        }

        $newLicence->setCabinet($cabinet);
        $entityManager->persist($newLicence);

        $entityManager->flush();

        return $this->json(["message" => "Licence créée"], 201);
    }

     /**
     * @Route("admin/licence/update", name="licence_update", methods={"PUT"})
     */
    public function updateLicence(Request $request, EntityManagerInterface $entityManager,  SerializerInterface $serializer, LicenceRepository $licenceRepository): Response
    {

        $jsonRecu = $request->getContent();

        $updatedLicence = json_decode($jsonRecu, true)["licence"];


        if (empty($updatedLicence["nombrePostes"])) {
            return $this->json(["message" => "Le nombre de postes est obligatoire"], 400);
        }

        if (empty($updatedLicence["dateCreation"])) {
            return $this->json(["message" => "La date de création est obligatoire"], 400);
        }

        if (empty($updatedLicence["dateDebut"])) {
            return $this->json(["message" => "La date de début est obligatoire"], 400);
        }

        if (!empty($updatedLicence["dateFin"]) && $updatedLicence["dateFin"] <= $updatedLicence["dateDebut"]) {
            return $this->json(["message" => "La date de fin doit être supérieur à la date de début"], 400);
        }

        if (intval($updatedLicence["nombrePostes"]) > 1000) {
            return $this->json(["message" => "La nombre de postes doit être inférieur à 1001"], 400);
        }

        if (intval($updatedLicence["deltaNombrePostes"]) > 30) {
            return $this->json(["message" => "Le delta du nombre de postes doit être inférieur à 31"], 400);
        }

        if (intval($updatedLicence["deltaJourFin"]) > 30) {
            return $this->json(["message" => "Le delta du nombre de jours avant l'expiration de la licence doit être inférieur à 31"], 400);
        }

        $licenceToUpdate = $licenceRepository->find($updatedLicence['id']);


        if (!$licenceToUpdate) {
            return $this->json(["message" => "Cette licence n'existe pas"], 400);
        }

        $form = $this->createForm(LicenceFormType::class, $licenceToUpdate);
        $form->submit($updatedLicence);

        $entityManager->persist($licenceToUpdate);
        $entityManager->flush();

        return $this->json(["message" => "Licence modifiée"], 200);
    }


    /**
     * @Route("download/licences/{idLicence}", name="licences_download", methods={"GET"})
     */
    public function downloadLicence($idLicence = -1, LicenceRepository $licenceRepository, MyCustomEncoder $encoder): Response
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

            $dateFin = $licence->getDateFin();
            $deltaJourFin = $licence->getDeltaJourFin();
            $deltaNombrePostes = $licence->getDeltaNombrePostes();

            $cleLicence = [
                "nomCabinet" => str_pad($cabinet->getNomCabinet(), 64),
                "dateDebut" => date_format($licence->getDateDebut(), "Y") . date_format($licence->getDateDebut(), "m") . date_format($licence->getDateDebut(), "d"),
                "dateFin" => (!empty($dateFin)) ? date_format($licence->getDateFin(), "Y") . date_format($licence->getDateFin(), "m") . date_format($licence->getDateFin(), "d") : null,
                "deltaJourFin" => (!empty($deltaJourFin)) ? str_pad(intval($licence->getDeltaJourFin()), 4) : null,
                "nombrePostes" => str_pad($licence->getNombrePostes(), 4),
                "deltaNombrePostes" => (!empty($deltaNombrePostes)) ? str_pad(intval($licence->getDeltaNombrePostes()), 4) : null
            ];

            return $this->json($encoder->encodePassword(implode("|", $cleLicence), ""), 200);
        }
    }

    /**
     * @Route("admin/licence/delete/{idLicence}", name="licence_delete", methods={"DELETE"})
     */
    public function deleteLicence($idLicence = -1, EntityManagerInterface $entityManager, LicenceRepository $licenceRepository): Response
    {

        if ($idLicence == -1) {
            return $this->json(["message" => "Veuillez renseigner un identifiant de licence"], 400);
        }

        $licenceToDelete = $licenceRepository->find($idLicence);

        if (!$licenceToDelete) {
            return $this->json(["message" => "Cette licence n'existe pas"], 400);
        }

        $entityManager->remove($licenceToDelete);

        $entityManager->flush();

        return $this->json(["message" => "licence supprimée"], 200);
    }
}
