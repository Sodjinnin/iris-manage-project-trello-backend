<?php

namespace App\Controller;

use App\Entity\Activity;
use App\Entity\Project;
use App\Entity\Task;
use App\Entity\User;
use App\Repository\ProjectRepository;
use App\Repository\TaskRepository;
use App\Repository\UserRepository;
use Doctrine\ORM\EntityManagerInterface;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\Validator\Validator\ValidatorInterface;

class TaskController extends AbstractController
{
    private $entityManager;
    public function __construct(EntityManagerInterface $entityManager)
    {
        $this->entityManager = $entityManager;
    }


    #[Route('/api/tasks', name: 'app_task', methods: ['GET'])]
    public function index(TaskRepository $taskRepository): JsonResponse
    {
        $projects = $taskRepository->findAll(['is_sub'=>false]);

        $projectData = array_map(function (Task $project) {
            return [
                'id' => $project->getId(),
                'name' => $project->getName(),
                'description' => $project->getDescription(),
                'status' => $project->getStatus(),
                'schedule_date' => $project->getScheduleDate()?->format('Y-m-d H:i:s'),
                'priority' => $project->getPriority(),
                'createdAt' => $project->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $project->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'creator' => [
                    'id' => $project->getCreatedBy()?->getId(),
                    'username' => $project->getCreatedBy()?->getUsername(),
                ],
                'assignTo' => [
                    'id' => $project->getAssignTo()?->getId(),
                    'username' => $project->getAssignTo()?->getUsername(),
                ],
                'project' => [
                    'id' => $project->getProject()?->getId(),
                    'name' => $project->getProject()?->getName(),
                ],
                'subTask' => array_map(function (Task $member) {
                    return [
                        'id' => $member->getId(),
                        'name' => $member->getName(),
                        'description' => $member->getDescription(),
                        'status' => $member->getStatus(),
                        'schedule_date' => $member->getScheduleDate()?->format('Y-m-d H:i:s'),
                        'priority' => $member->getPriority(),
                        'createdAt' => $member->getCreatedAt()?->format('Y-m-d H:i:s'),
                        'updatedAt' => $member->getUpdatedAt()?->format('Y-m-d H:i:s'),
                        'creator' => [
                            'id' => $member->getCreatedBy()?->getId(),
                            'username' => $member->getCreatedBy()?->getUsername(),
                        ],
                        'assignTo' => [
                            'id' => $member->getAssignTo()?->getId(),
                            'username' => $member->getAssignTo()?->getUsername(),
                        ],
                        'project' => [
                            'id' => $member->getProject()?->getId(),
                            'name' => $member->getProject()?->getName(),
                        ],
                    ];
                }, $project->getTasks()->toArray()),

            ];
        }, $projects);

        return $this->json($projectData);
    }



    #[Route('/api/tasks', name: 'create_task', methods: ['POST'])]
    public function create(
        Request $request,
        EntityManagerInterface $entityManager,
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (!isset($data['name'], $data['description'], $data['status'], $data['created_by'],
            $data['assign_to'], $data['project'])) {
            return $this->json(['error' => 'Missing data'], 400);
        }

        if (!isset($data['is_sub'])){
            $data['is_sub'] = false;
        }

        $owner = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $data['created_by']]);


        $assign_to = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $data['assign_to']]);


        $project = $this->entityManager
            ->getRepository(Project::class)
            ->findOneBy(['id' => $data['project']]);

        $this->logActivity($project, $owner, 'created_project', "Une nouvelle tâche ajouter au projet'{$project->getName()}'");


        if (!$owner) {
            return $this->json(['error' => 'Owner not found'], 404);
        }

        $result = new Task();
        $result->setName($data['name']);
        $result->setDescription($data['description']);
        $result->setStatus($data['status']);
        $result->setPriority($data['priority']);
        $result->setScheduleDate(new \DateTime($data['schedule_date']));
        $result->setCreatedAt(new \DateTime());
        $result->setUpdatedAt(new \DateTime());
        $result->setCreatedBy($owner);
        $result->setAssignTo($assign_to);
        $result->setProject($project);
        $result->setIsSub($data['is_sub']);

        if ($data['is_sub'] && isset($data['sub_task'])) {
            $task =  $this->entityManager
                ->getRepository(Task::class)
                ->findOneBy(['id' => $data['sub_task']]);
            $result->addSubTask($task);
        }

        $entityManager->persist($result);
        $entityManager->flush();

        return $this->json([
            'message' => 'Task created successfully!',
            'task' => [
                'id' => $result->getId(),
                'name' => $result->getName(),
                'description' => $result->getDescription(),
                'status' => $result->getStatus(),
                'createdAt' => $result->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $result->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'creator' => [
                    'id' => $owner->getId(),
                    'username' => $owner->getUsername(),
                ],
                'assignTo' => [
                    'id' => $assign_to->getId(),
                    'username' => $assign_to->getUsername(),
                ],
                'project' => [
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                ],

            ]
        ], 201);
    }

    private function logActivity(Project $project, User $user, string $action, ?string $details = null): void
    {
        $activity = new Activity();
        $activity->setProject($project);
        $activity->setUser($user);
        $activity->setAction($action);
        $activity->setDetails($details);
        $activity->setCreatedAt(new \DateTimeImmutable());

        $this->entityManager->persist($activity);
        $this->entityManager->flush();
    }

    #[Route('/api/tasks/{id}', name: 'update_task', methods: ['PUT'])]
    public function update(
        Task $task,
        Request $request,
        EntityManagerInterface $entityManager,
        UserRepository $userRepository,
        ValidatorInterface $validator
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        if (isset($data['name'])) {
            $task->setName($data['name']);
        }
        if (isset($data['description'])) {
            $task->setDescription($data['description']);
        }
        if (isset($data['status'])) {
            $task->setStatus($data['status']);
        }


        $owner = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $task->getCreatedBy()->getId()]);


        $assign_to = $this->entityManager
            ->getRepository(User::class)
            ->findOneBy(['id' => $data['assign_to']]);


        $project = $this->entityManager
            ->getRepository(Project::class)
            ->findOneBy(['id' => $task->getProject()->getId()]);

        $this->logActivity($project, $owner, 'created_project', $task->getName() . "mis à jour");

        if (!$owner) {
            return $this->json(['error' => 'Creator not found'], 404);
        }

        if (!$assign_to) {
            return $this->json(['error' => 'User not found'], 404);
        }

        if (!$project) {
            return $this->json(['error' => 'Project not found'], 404);
        }

        $task->setCreatedBy($owner);
        $task->setAssignTo($assign_to);
        $task->setProject($project);


        $task->setUpdatedAt(new \DateTime());

        $errors = $validator->validate($task);
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
            'task' => [
                'id' => $task->getId(),
                'name' => $task->getName(),
                'description' => $task->getDescription(),
                'status' => $task->getStatus(),
                'createdAt' => $task->getCreatedAt()?->format('Y-m-d H:i:s'),
                'updatedAt' => $task->getUpdatedAt()?->format('Y-m-d H:i:s'),
                'creator' => [
                    'id' => $owner->getId(),
                    'username' => $owner->getUsername(),
                ],
                'assignTo' => [
                    'id' => $assign_to->getId(),
                    'username' => $assign_to->getUsername(),
                ],
                'project' => [
                    'id' => $project->getId(),
                    'name' => $project->getName(),
                ],
            ]
        ], 200);
    }


}
