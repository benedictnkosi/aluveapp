<?php

namespace App\Controller;

use App\Entity\Property;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;

class RegistrationController extends AbstractController
{
    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager): Response
    {
        try{
            if(strlen($request->get("_password")) < 1 || strlen($request->get("_username")) < 1){
                return $this->render('signup.html', [
                    'error' => "Username and password is mandatory",
                ]);
            }

            if(strcmp($request->get("_password"), $request->get("_confirm_password")) !== 0){
                /*return $this->render('signup.html', [
                    'error' => "Passwards are not the same",
                ]);*/
            }

            $user = new User();

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get("_password")
                )
            );

            $user->setEmail($request->get("_username"));
            $property = $entityManager->getRepository(Property::class)->findOneBy(
                array("id" => 3));
            $user->setProperty($property);
            $roles = [ $request->get("_role")];
            $user->setRoles($roles);
            $entityManager->persist($user);
            $entityManager->flush();

            return $this->render('signup.html', [
                'error' => "Successfully registered, Please sign in",
            ]);
        }catch(\Exception $exception){
            return $this->render('signup.html', [
                'error' => "Error, Failed to register user",
            ]);
        }

    }

}
