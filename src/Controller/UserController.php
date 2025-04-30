<?php

namespace App\Controller;

use App\Entity\User;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpKernel\Exception\BadRequestHttpException;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\Security\Http\Attribute\IsGranted;

class UserController extends AbstractController
{

    private $passwordHasher;
    private $jwtManager;
    private $entityManager;

    public function __construct(UserPasswordHasherInterface $passwordHasher, JWTTokenManagerInterface $jwtManager, EntityManagerInterface $entityManager)
    {
        $this->passwordHasher = $passwordHasher;
        $this->jwtManager = $jwtManager;
        $this->entityManager = $entityManager;
    }

    /**
     * @Route("/api/me", name="api_me", methods={"POST"})
     */
    #[IsGranted("ROLE_USER")]
    public function me()
    {

        return $this->json([
            'id' => $this->getUser()->getId(),
            'username' => $this->getUser()->getUsername(),
            'email' => $this->getUser()->getEmail(),
            'firstname' => $this->getUser()->getFirstname(),
            'lastname' => $this->getUser()->getLastname(),
        ]);
    }

    #[Route('/api/users', name: 'get_all_users', methods: ['GET'])]
    public function index(EntityManagerInterface $entityManager)
    {
        $users = $entityManager->getRepository(User::class)->findAll();
        $data = array_map(function(User $user) {
            return [
                'id' => $user->getId(),
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ];
        }, $users);

        return new JsonResponse($data);
    }

    #[Route('/api/register', name: 'api_register', methods: ['POST'])]
    public function register(Request $request, UserPasswordHasherInterface $passwordHasher, EntityManagerInterface $entityManager): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username'], $data['email'], $data['password'])) {
            throw new BadRequestHttpException('Missing required fields');
        }

        $user = new User();
        $user->setUsername($data['username']);
        $user->setEmail($data['email']);

        $encodedPassword = $passwordHasher->hashPassword($user, $data['password']);
        $user->setPassword($encodedPassword);

        $entityManager->persist($user);
        $entityManager->flush();

        return new JsonResponse([
            'status' => 'User created successfully!',
            'user' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
            ]
        ], JsonResponse::HTTP_CREATED);
    }

    #[Route('/api/login', name: 'api_login', methods: ['POST'])]
    public function login(Request $request): JsonResponse
    {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['username']) || !isset($data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);

        if (!$user || !$this->passwordHasher->isPasswordValid($user, $data['password'])) {
            return new JsonResponse(['error' => 'Invalid credentials'], JsonResponse::HTTP_UNAUTHORIZED);
        }

        $token = $this->jwtManager->create($user);

        $user->setApiToken($token);
        $this->entityManager->flush();

        $user = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['username' => $data['username']]);

        return new JsonResponse([
            'token' => $token,
            'user' => [
                'username' => $user->getUsername(),
                'email' => $user->getEmail(),
                'id' => $user->getId(),
            ]
        ]);
    }

}
