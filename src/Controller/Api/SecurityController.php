<?php

namespace App\Controller\Api;

use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Sensio\Bundle\FrameworkExtraBundle\Configuration\IsGranted;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Security\Core\Encoder\UserPasswordEncoderInterface;
use App\Entity\User;
use App\Form\UserType;
use App\Serializer\FormErrorSerializer;

class SecurityController extends AbstractController
{
    /**
     * @Route("/api/login", name="api.login", methods={"POST"})
     */
    public function login(Request $request)
    {
        $user = $this->getUser();
        $user->setApiToken(md5(random_bytes(10)));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "User {$user->getName()} was successfully logged in.",
                    'data' => [
                        'token' => $user->getApiToken()
                    ]
                ]
            ]
        );
    }

     /**
     * @Route("/api/register", name="api.register", methods={"POST"})
     */
    public function register(
        Request $request, 
        UserPasswordEncoderInterface $passwordEncoder,
        FormErrorSerializer $serializer
    ): JsonResponse
    {
        $data = json_decode($request->getContent(), true);
        $user = new User();
        $form = $this->createForm(UserType::class, $user);
        
        $form->submit($data);

        if (false === $form->isValid()) 
        {
            return new JsonResponse(
                [
                    'status' => 'error',
                    'errors' => $serializer->convertFormToArray($form),
                ],
                JsonResponse::HTTP_BAD_REQUEST
            );
        }
        
        $password = $passwordEncoder->encodePassword($user, $user->getPlainPassword());

        $user->setPassword($password);
        $user->setApiToken(md5(random_bytes(10)));

        $entityManager = $this->getDoctrine()->getManager();
        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'text' => "User {$user->getName()} was successfully registered.",
                    'data' => [
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                        'token' => $user->getApiToken()
                    ]
                ]
            ]
        );
    }

    /**
     * @Route("/api/me", name="api.me", methods={"GET"})
     * @IsGranted("ROLE_USER")
     */
    public function getAuthenticatedUser(Request $request)
    {
        $user = $this->getUser();

        return new JsonResponse(
            [
                'status' => 'success',
                'message' => [
                    'data' => [
                        'name' => $user->getName(),
                        'email' => $user->getEmail(),
                        'roles' => $user->getRoles()
                    ]
                ]
            ]
        );
    }
}
