<?php

namespace App\Controller;

use App\Entity\Cabinet;
use App\Entity\User;
use App\Form\CabinetFormType;
use App\Repository\CabinetRepository;
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
     * @Route("api/admin/cabinets", name="cabinets", methods={"GET"})
     */
    public function getAllCabinets(CabinetRepository $cabinetRepository): Response
    {
        $cabinets = $cabinetRepository->findAll();

        return $this->json($cabinets, 200, [], ['groups' => 'cabinet:read']);
    }

    /**
     * @Route("api/admin/cabinets/nomCabinet", name="cabinets_name", methods={"GET"})
     */
    public function getAllCabinetsNom(CabinetRepository $cabinetRepository): Response
    {
        $cabinetsNom = $cabinetRepository->getAllCabinetsNom();
        return $this->json($cabinetsNom, 200);
    }

    /**
     * @Route("api/admin/cabinet/{id}", name="cabinet", methods={"GET"})
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
     * @Route("api/admin/cabinet/create", name="cabinet_create", methods={"POST"})
     */
    public function createCabinet(Request $request, SerializerInterface $serializer, EntityManagerInterface $entityManager, UserPasswordEncoderInterface $encoder): Response
    {

        $jsonRecu = $request->getContent();
        $newCabinet = $serializer->deserialize(json_encode(json_decode($jsonRecu, true)["cabinet"]), Cabinet::class, 'json');

        if ($newCabinet->getNomCabinet() === "") {
            return $this->json(["message" => "Le nom du cabinet est obligatoire"], 400);
        }

        if ($newCabinet->getIsActive() === null) {
            return $this->json(["message" => "Le statut (actif/inactif) est obligatoire"], 400);
        }

        $newUser = new User();
        $newUser->setUsername($newCabinet->getNomCabinet());
        $newUser->setPassword($encoder->encodePassword($newUser, $this->randomPassword()));

        $newUser->setCabinet($newCabinet);

        $entityManager->persist($newCabinet);
        $entityManager->persist($newUser);
        $entityManager->flush();


        return $this->json(["message" => "Cabinet cr??e"], 201);
    }

    function randomPassword()
    {
        $alphabet = 'abcdefghijklmnopqrstuvwxyzABCDEFGHIJKLMNOPQRSTUVWXYZ1234567890/@?.#-|_$%??!';
        $pass = array();
        $alphaLength = strlen($alphabet) - 1;
        for ($i = 0; $i < 8; $i++) {
            $n = rand(0, $alphaLength);
            $pass[] = $alphabet[$n];
        }
        return implode($pass);
    }

    /**
     * @Route("api/admin/cabinet/update", name="cabinet_update", methods={"PUT"})
     */
    public function updateCabinet(Request $request, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository): Response
    {

        $jsonRecu = $request->getContent();

        $updatedCabinet = json_decode($jsonRecu, true)["cabinet"];

        if ($updatedCabinet["nomCabinet"] === "") {
            return $this->json(["message" => "Le nom du cabinet est obligatoire"], 400);
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


        return $this->json(["message" => "Cabinet modifi??"], 200);
    }

    /**
     * @Route("api/admin/cabinet/delete/{idCabinet}", name="cabinet_delete", methods={"DELETE"})
     */
    public function deleteCabinet($idCabinet = -1, Request $request, EntityManagerInterface $entityManager, CabinetRepository $cabinetRepository): Response
    {
        //Suppression en cascade, cela supprime le cabinet, le user et les licences associ??es

        if ($idCabinet == -1) {
            return $this->json(["message" => "Veuillez renseigner un identifiant de cabinet"], 400);
        }

        $cabinetToDelete = $cabinetRepository->find($idCabinet);

        if (!$cabinetToDelete) {
            return $this->json(["message" => "Ce cabinet n'existe pas"], 400);
        }

        $entityManager->remove($cabinetToDelete);

        $entityManager->flush();

        return $this->json(["message" => "Cabinet supprim??"], 200);
    }
}
