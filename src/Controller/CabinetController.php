<?php

namespace App\Controller;

use App\Entity\Cabinet;
use App\Entity\User;
use App\Form\CabinetFormType;
use App\Repository\CabinetRepository;
use App\Repository\LicenceRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use Symfony\Component\Serializer\SerializerInterface;

class CabinetController extends AbstractController
{
    /**
     * @Route("admin/cabinets", name="cabinets", methods={"GET"})
     */

    public function getAllCabinets(CabinetRepository $cabinetRepository, LicenceRepository $licenceRepository): Response
    {
        $result = [];
        $cabinets = $cabinetRepository->findAll();
        foreach($cabinets as $cabinet){
            $nbActiveLicences = $licenceRepository->getNbActiveLicence($cabinet);
            $nbActiveForNoLongerLicences = $licenceRepository->getNbActiveForNoLongerLicence($cabinet);    
            $nbExpiredLicences = $licenceRepository->getNbExpiredLicence($cabinet);
            $nbLicencesStatus = ["nbActiveLicences" => $nbActiveLicences,
                        "nbActiveForNoLongerLicences" => $nbActiveForNoLongerLicences,
                        "nbExpiredLicences" => $nbExpiredLicences];
            array_push($result, ["cabinet" => $cabinet, "nbLicencesStatus" => $nbLicencesStatus]);
        }
        return $this->json($result, 200, [], ['groups' => 'cabinet:read']);
    }

    /**
     * @Route("admin/cabinets/nomCabinet", name="cabinets_name", methods={"GET"})
     */
    public function getAllCabinetsNom(CabinetRepository $cabinetRepository): Response
    {
        $cabinetsNom = $cabinetRepository->getAllCabinetsNom();
        return $this->json($cabinetsNom, 200);
    }

    /**
     * @Route("admin/cabinet/{id}", name="cabinet", methods={"GET"})
     */
    public function getOneCabinet(int $id = -1, CabinetRepository $cabinetRepository, SerializerInterface $serializer): Response
    {
        if ($id == -1) {
            return $this->json(["message" => "Veuillez renseigner un identifiant"], 400);
        } else {
            $cabinet = $cabinetRepository->find($id);

            if ($cabinet) {
                return $this->json($cabinet, 200, [], ['groups' => 'cabinet:read']);
            } else {
                return $this->json(["message" => "Ce cabinet n'existe pas"], 204);
            }
        }
    }

    /**
     * @Route("admin/cabinet/create", name="cabinet_create", methods={"POST"})
     */
    public function createCabinet(Request $request,  SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder): Response
    {

        $jsonRecu = $request->getContent();
        $newCabinet = $serializer->deserialize(json_encode(json_decode($jsonRecu, true)["cabinet"]), Cabinet::class, 'json');

        if ($newCabinet->getNomCabinet() === "") {
            return $this->json(["message" => "Le nom du cabinet est obligatoire"], 400);
        }

        if ($newCabinet->getIsActive() === null) {
            return $this->json(["message" => "Le statut (actif/inactif) est obligatoire"], 400);
        }

        if (strlen($newCabinet->getNomCabinet()) > 64) {
            return $this->json(["message" => "Le nom du cabinet doit faire moins de 65 caractères"], 400);
        }

        if (strlen($newCabinet->getNomContact()) > 64) {
            return $this->json(["message" => "Le nom du contact doit faire moins de 65 caractères"], 400);
        }

        if (strlen($newCabinet->getNomClient()) > 64) {
            return $this->json(["message" => "Le nom du client doit faire moins de 65 caractères"], 400);
        }

        if (strlen($newCabinet->getAdresse()) > 128) {
            return $this->json(["message" => "L'adresse doit faire moins de 129 caractères"], 400);
        }

        if (strlen($newCabinet->getTel()) > 10) {
            return $this->json(["message" => "Le numéro de téléphone doit être composé de 10 chiffres"], 400);
        }

        if (strlen($newCabinet->getEmail()) > 64) {
            return $this->json(["message" => "L'adresse email doit faire moins de 65 caractères"], 400);
        }

        if (!empty($newCabinet->getEmail()) && !filter_var($newCabinet->getEmail(), FILTER_VALIDATE_EMAIL)) {
            return $this->json(["message" => "L'adresse email n'est pas au bon format"], 400);
        }

        if (!empty($newCabinet->getTel()) && !filter_var($newCabinet->getTel(), FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[0-9]{10}$/"]])) {
            return $this->json(["message" => "Le numéro de téléphone n'est pas au bon format"], 400);
        }

        $newUser = new User();
        $newUser->setUsername($newCabinet->getNomCabinet());
        $newUser->setPassword($encoder->encodePassword($newUser, $this->randomPassword()));

        $newUser->setCabinet($newCabinet);

        $entityManager->persist($newCabinet);
        $entityManager->persist($newUser);
        $entityManager->flush();


        return $this->json(["message" => "Cabinet crée"], 201);
    }

    function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890/@?!';
        $pass = array();
        $alphaLength = strlen(str_shuffle($alphabet)) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    /**
     * @Route("admin/cabinet/update", name="cabinet_update", methods={"PUT"})
     */
    public function updateCabinet(Request $request, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository): Response
    {

        $jsonRecu = $request->getContent();

        $updatedCabinet = json_decode($jsonRecu, true)["cabinet"];

        if ($updatedCabinet["nomCabinet"] === "") {
            return $this->json(["message" => "Le nom du cabinet est obligatoire"], 400);
        }

        if ($updatedCabinet["isActive"] === null) {
            return $this->json(["message" => "Le statut (actif/inactif) est obligatoire"], 400);
        }

        if (strlen($updatedCabinet["nomCabinet"]) > 64) {
            return $this->json(["message" => "Le nom du cabinet doit faire moins de 65 caractères"], 400);
        }

        if (strlen($updatedCabinet["nomContact"]) > 64) {
            return $this->json(["message" => "Le nom du contact doit faire moins de 65 caractères"], 400);
        }

        if (strlen($updatedCabinet["nomClient"]) > 64) {
            return $this->json(["message" => "Le nom du client doit faire moins de 65 caractères"], 400);
        }

        if (strlen($updatedCabinet["adresse"]) > 128) {
            return $this->json(["message" => "L'adresse doit faire moins de 129 caractères"], 400);
        }

        if (strlen($updatedCabinet["tel"]) > 10) {
            return $this->json(["message" => "Le numéro de téléphone doit être composé de 10 chiffres"], 400);
        }

        if (strlen($updatedCabinet["email"]) > 64) {
            return $this->json(["message" => "L'adresse email doit faire moins de 65 caractères"], 400);
        }

        if (!empty($updatedCabinet["email"]) && !filter_var($updatedCabinet["email"], FILTER_VALIDATE_EMAIL)) {
            return $this->json(["message" => "L'adresse email n'est pas au bon format"], 400);
        }

        if (!empty($updatedCabinet["tel"]) && !filter_var($updatedCabinet["tel"], FILTER_VALIDATE_REGEXP, ["options" => ["regexp" => "/^[0-9]{10}$/"]])) {
            return $this->json(["message" => "Le numéro de téléphone n'est pas au bon format"], 400);
        }

        $cabinetToUpdate = $cabinetRepository->find($updatedCabinet['id']);

        if (!$cabinetToUpdate) {
            return $this->json(["message" => "Ce cabinet n'existe pas"], 400);
        }

        $form = $this->createForm(CabinetFormType::class, $cabinetToUpdate);
        $form->submit($updatedCabinet);

        $cabinetUser = $cabinetToUpdate->getCabinetUser();
        $cabinetUser->setUsername($cabinetToUpdate->getNomCabinet());

        $entityManager->persist($cabinetToUpdate);
        $entityManager->persist($cabinetUser);

        $entityManager->flush();


        return $this->json(["message" => "Cabinet modifié"], 200);
    }

    /**
     * @Route("admin/cabinet/delete/{idCabinet}", name="cabinet_delete", methods={"DELETE"})
     */
    public function deleteCabinet($idCabinet = -1, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository): Response
    {
        //Suppression en cascade, cela supprime le cabinet, le user et les licences associées

        if ($idCabinet == -1) {
            return $this->json(["message" => "Veuillez renseigner un identifiant de cabinet"], 400);
        }

        $cabinetToDelete = $cabinetRepository->find($idCabinet);

        if (!$cabinetToDelete) {
            return $this->json(["message" => "Ce cabinet n'existe pas"], 400);
        }

        $entityManager->remove($cabinetToDelete);

        $entityManager->flush();

        return $this->json(["message" => "Cabinet supprimé"], 200);
    }
}
