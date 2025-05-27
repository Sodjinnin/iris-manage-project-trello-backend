<?php

namespace App\Service;

use Symfony\Component\Mailer\MailerInterface;
use Symfony\Component\Mime\Email;
use App\Entity\Task;
use App\Entity\User;

class EmailService
{
    private $mailer;

    public function __construct(MailerInterface $mailer)
    {
        $this->mailer = $mailer;
    }

    public function sendTaskAssignmentEmail(Task $task, User $assignedUser): void
    {
        $email = (new Email())
            ->from('noreply@trelloapp.com')
            ->to($assignedUser->getEmail())
            ->subject('Nouvelle tâche assignée')
            ->html("
                <h1>Nouvelle tâche assignée</h1>
                <p>Bonjour {$assignedUser->getUsername()},</p>
                <p>Une nouvelle tâche vous a été assignée :</p>
                <ul>
                    <li><strong>Nom :</strong> {$task->getName()}</li>
                    <li><strong>Description :</strong> {$task->getDescription()}</li>
                    <li><strong>Priorité :</strong> {$task->getPriority()}</li>
                    <li><strong>Date prévue :</strong> {$task->getScheduleDate()->format('d/m/Y H:i')}</li>
                </ul>
                <p>Connectez-vous à l'application pour plus de détails.</p>
            ");

        $this->mailer->send($email);
    }
} 