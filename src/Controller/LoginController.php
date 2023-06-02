<?php

namespace App\Controller;

use App\Entity\Property;
use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Exception;
use Psr\Log\LoggerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Http\Authentication\AuthenticationUtils;

class LoginController extends AbstractController
{
    #[Route('/login', name: 'app_login')]
    public function login(AuthenticationUtils $authenticationUtils, LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        // get the login error if there is one
        $error = $authenticationUtils->getLastAuthenticationError();

        // last username entered by the user
        $lastUsername = $authenticationUtils->getLastUsername();

        return $this->render('signin.html', [
            'last_username' => $lastUsername,
            'error' => $error,
        ]);
    }

    #[Route('/register', name: 'app_register')]
    public function register(Request $request, UserPasswordHasherInterface $userPasswordHasher, EntityManagerInterface $entityManager, LoggerInterface $logger): Response
    {
        $logger->info("Starting Method: " . __METHOD__);
        if (!$request->isMethod('post')) {
            $logger->error("Invalid HTTP Method");
            return $this->render('signup.html', [
                'error' => "Invalid HTTP Method",
            ]);
        }
        try{
            if ($request->get("_password") === null || $request->get("_password") === ''
                || $request->get("_username") === null || $request->get("_username") === ''
                || $request->get("_name") === null || $request->get("_name") === ''
                || $request->get("_confirm_password") === null || $request->get("_confirm_password") === '') {
                $logger->error("All fields are mandatory");
                return $this->render('signup.html', [
                    'error' => "All fields are mandatory",
                ]);
            }

            $pattern = "/^[_a-z0-9-]+(\.[_a-z0-9-]+)*@[a-z0-9-]+(\.[a-z0-9-]+)*(\.[a-z]{2,})$/i";

            if (!preg_match($pattern,$request->get("_username"))) {
                $logger->error("Username must be a valid email address");

                return $this->render('signup.html', [
                    'error' => "Username must be a valid email address",
                ]);
            }

            if(strcmp($request->get("_password"), $request->get("_confirm_password")) !== 0){
                $logger->error("Passwords are not the same");

                return $this->render('signup.html', [
                    'error' => "Passwords are not the same",
                ]);
            }

            $passwordErrors = $this->validatePassword($request->get("_password"));
            $logger->info("Size of errors: " . sizeof($passwordErrors));
            if(sizeof($passwordErrors) > 0){
                $logger->error($passwordErrors[0]);

                return $this->render('signup.html', [
                    'error' => $passwordErrors[0],
                ]);
            }

            $user = new User();
            $user->setName($request->get("_name"));

            $user->setPassword(
                $userPasswordHasher->hashPassword(
                    $user,
                    $request->get("_password")
                )
            );

            $user->setEmail($request->get("_username"));
            $roles = [ $request->get("_role")];
            $user->setRoles($roles);
            try{
                $entityManager->persist($user);
                $entityManager->flush();
            }catch (Exception $exception){
                $logger->error($exception->getMessage());
                if(str_contains($exception->getMessage(), "Duplicate")){
                    return $this->render('signup.html', [
                        'error' => "Failed to register the user. Email address already registered "
                    ]);
                }else{
                    return $this->render('signup.html', [
                        'error' => "Failed to register the user. please contact administrator. " . $exception->getMessage(),
                    ]);
                }

            }

            return $this->render('signup.html', [
                'error' => "Successfully registered, Please sign in",
            ]);
        }catch(\Exception $exception){
            $logger->info($exception->getMessage());
            return $this->render('signup.html', [
                'error' => $exception->getMessage(),
            ]);
        }
    }

    public function validatePassword($pass): array
    {
        $errors = array();
        if (strlen($pass) < 8 || strlen($pass) > 16) {
            $errors[] = "Password should be min 8 characters and max 16 characters";
        }
        if (!preg_match("/\d/", $pass)) {
            $errors[] = "Password should contain at least one digit";
        }
        if (!preg_match("/[A-Z]/", $pass)) {
            $errors[] = "Password should contain at least one Capital Letter";
        }
        if (!preg_match("/[a-z]/", $pass)) {
            $errors[] = "Password should contain at least one small Letter";
        }
        if (!preg_match("/\W/", $pass)) {
            $errors[] = "Password should contain at least one special character";
        }
        if (preg_match("/\s/", $pass)) {
            $errors[] = "Password should not contain any white space";
        }
        return $errors;
    }

    /**
     * @Route("/logout", name="app_logout", methods={"GET"})
     */
    public function logout(): void
    {

    }

    /**
     * @Route("/public/me")
     */
    public function meAction(): JsonResponse
    {
        $responseArray[] = array(
            'authenticated' => $this->getUser() !== null,
            'result_code' => 0
        );

        return new JsonResponse($responseArray);
    }

}
