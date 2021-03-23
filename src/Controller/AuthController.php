<?php

namespace App\Controller;

use App\Repository\UserRepository;
use App\Security\Encoder\MyCustomEncoder;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;

class AuthController extends AbstractController
{
    /**
     * @Route("api/user/{username}", name="login", methods={"GET"})
     */
    public function getUserWithUsername(string $username = "", UserRepository $userRepository): Response
    {

        if ($username) {
            $user = $userRepository->findOneBy(["username" => $username]);
            if (!$user) {
                return $this->json(["message" => "Cet utilisateur n'existe pas"], 400);
            }
            return $this->json($user, 200, [], ['groups' => ['user:read', 'cabinet:read']]);
        } else {
            return $this->json(["message" => "Username non renseigné"], 400);
        }
    }

    /**
     * @Route("api/admin/user/password/{nomCabinet}", name="user_password", methods={"GET"})
     */
    public function getCabinetUserPassword(string $nomCabinet = "", UserRepository $userRepository, MyCustomEncoder $encoder): Response
    {

        if ($nomCabinet) {
            $password = $userRepository->getUserPassword($nomCabinet);
            
            if (empty($password)) {
                return $this->json(["message" => "Cet utilisateur n'existe pas"], 400);
            }

            return $this->json($encoder->decodePassword($password["password"], 200));
        } else {
            return $this->json(["message" => "Username non renseigné"], 400);
        }
    }

    /**
     * @Route("api/admin/user/password/{nomCabinet}", name="cabinet_user_password_update", methods={"PUT"})
     */
    public function updatePassword(string $nomCabinet = "", Request $request, EntityManagerInterface $entityManager, UserRepository $userRepository, UserPasswordEncoderInterface $encoder): Response
    {

        if ($nomCabinet) {

            $jsonRecu = $request->getContent();

            $newPassword = json_decode($jsonRecu)->password;


            if ($newPassword === "") {
                return $this->json(["message" => "Veuillez renseigner le nouveau mot de passe"], 400);
            }

            if (strlen($newPassword) < 8) {
                return $this->json(["message" => "Le mot de passe doit faire au moins 8 caractères"], 400);
            }

            if (strlen($newPassword) > 40) {
                return $this->json(["message" => "Le mot de passe doit faire moins de 41 caractères"], 400);
            }

            $userToUpdate = $userRepository->findOneBy(["username" => $nomCabinet]);
            if (!$userToUpdate) {
                return $this->json(["message" => "Cet utilisateur n'existe pas"], 400);
            }

            $userToUpdate->setPassword($encoder->encodePassword($userToUpdate, $newPassword));
            $entityManager->persist($userToUpdate);

            $entityManager->flush();

            return $this->json(["message" => "Mot de passe modifié"], 200);
        } else {
            return $this->json(["message" => "Username non renseigné"], 400);
        }
    }
}
