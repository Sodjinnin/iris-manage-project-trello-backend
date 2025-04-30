<?php

namespace App\Controller;

use App\Entity\Project;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Lexik\Bundle\JWTAuthenticationBundle\Services\JWTTokenManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\PasswordHasher\Hasher\UserPasswordHasherInterface;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

final class ProjectController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }

    #[Route('/project', name: 'app_project', methods: ['GET'])]
    public function index(ProjectRepository $projectRepository): JsonResponse
    {
        $projects = $projectRepository->findAll();

        $projectData = array_map(function (Project $project) {
            return [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
                'date' => $project->getDate(),
                'createdAt' => $project->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $project->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'owner' => [
                    'id' => $project->getOwner()?->getId(),
                    'username' => $project->getOwner()?->getUsername(),
                ],
                'members' => array_map(function (User $member) {
                    return [
                        'id' => $member->getId(),
                        'username' => $member->getUsername(),
                    ];
                }, $project->getMembers()->toArray()),
            ];
        }, $projects);

        return $this->json($projectData);
    }

    #[Route('/project/create', name: 'create_project', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
        ValidatorInterface $validator,
        UserRepository $userRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['description'], $data['status'], $data['owner_id'])) {
            return $this->json(['error' => 'Missing data'], 400);
        }

        $owner = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $data['owner_id']]);


        if (!$owner) {
            return $this->json(['error' => 'Owner not found'], 404);
        }

        $project = new Project();
        $project->setName($data['name']);
        $project->setDescription($data['description']);
        $project->setStatus($data['status']);
        $project->setDate(new \DateTime($data['date']));
        $project->setCreatedAt(new \DateTimeImmutable());
        $project->setUpdatedAt(new \DateTimeImmutable());
        $project->setOwner($owner);

        if (isset($data['member_ids']) && is_array($data['member_ids'])) {
            foreach ($data['member_ids'] as $memberId) {
                $member = $userRepository->find($memberId);
                if ($member) {
                    $project->addMember($member);
                }
            }
        }

        $errors = $validator->validate($project);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->persist($project);
        $entityManager->flush();

        return $this->json([
            'message' => 'Project created successfully!',
            'project' => [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
                'createdAt' => $project->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $project->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'owner' => [
                    'id' => $owner->getId(),
                    'username' => $owner->getUsername(),
                ],
                'members' => array_map(function (User $member) {
                    return [
                        'id' => $member->getId(),
                        'username' => $member->getUsername(),
                    ];
                }, $project->getMembers()->toArray()),
            ]
        ], 201);
    }

    #[Route('/project/update/{id}', name: 'update_project', methods: ['PUT'])]
    public function update(
        Project $project,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $project->setName($data['name']);
        }
        if (isset($data['description'])) {
            $project->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $project->setStatus($data['status']);
        }
        if (isset($data['date'])) {
            $project->setStatus($data['date']);
        }

        if (isset($data['owner_id'])) {
            $owner = $userRepository->find($data['owner_id']);
            if (!$owner) {
                return $this->json(['error' => 'Owner not found'], 404);
            }
            $project->setOwner($owner);
        }

        if (isset($data['member_ids']) && is_array($data['member_ids'])) {
            $project->getMembers()->clear();
            foreach ($data['member_ids'] as $memberId) {
                $member = $userRepository->find($memberId);
                if ($member) {
                    $project->addMember($member);
                }
            }
        }

        $project->setUpdatedAt(new \DateTimeImmutable());

        $errors = $validator->validate($project);
        if (count($errors) > 0) {
            $errorMessages = [];
            foreach ($errors as $error) {
                $errorMessages[] = $error->getPropertyPath() . ': ' . $error->getMessage();
            }
            return $this->json(['errors' => $errorMessages], 400);
        }

        $entityManager->flush();

        return $this->json([
            'message' => 'Project updated successfully!',
            'project' => [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
                'createdAt' => $project->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $project->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'owner' => [
                    'id' => $project->getOwner()?->getId(),
                    'username' => $project->getOwner()?->getUsername(),
                ],
                'members' => array_map(function (User $member) {
                    return [
                        'id' => $member->getId(),
                        'username' => $member->getUsername(),
                    ];
                }, $project->getMembers()->toArray()),
            ]
        ], 200);
    }

    #[Route('/project/find/{id}', name: 'find_project', methods: ['GET'])]
    public function find(
        Project $project,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
    ): JsonResponse {

        return $this->json([
            'message' => 'Project updated successfully!',
            'project' => [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
                'createdAt' => $project->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $project->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'owner' => [
                    'id' => $project->getOwner()?->getId(),
                    'username' => $project->getOwner()?->getUsername(),
                ],
                'members' => array_map(function (User $member) {
                    return [
                        'id' => $member->getId(),
                        'username' => $member->getUsername(),
                    ];
                }, $project->getMembers()->toArray()),
            ]
        ], 200);
    }

    #[Route('/api/project/delete/{id}', name: 'delete_project', methods: ['DELETE'])]
    public function delete(
        Project $project,
        EntityManagerInterface $entityManager
    ): JsonResponse {
        $entityManager->remove($project);
        $entityManager->flush();

        return $this->json(['message' => 'Project deleted successfully.'], 200);
    }
}
