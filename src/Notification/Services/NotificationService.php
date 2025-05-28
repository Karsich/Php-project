<?php

namespace App\Notification\Services;

use App\Core\Models\User;
use App\Core\Models\Post;

class NotificationService {
    private EmailService $emailService;
    private User $userModel;
    private Post $postModel;
    
    public function __construct() {
        $this->emailService = new EmailService();
        $this->userModel = new User();
        $this->postModel = new Post();
    }
    
    public function notifyAboutReply(int $userId, int $postId, string $replierName, string $postContent): void {
        $user = $this->userModel->find($userId);
        if (!$user) return;
        
        $subject = "Новый ответ на ваше сообщение";
        $body = $this->createReplyNotificationBody($replierName, $postContent);
        
        $this->emailService->sendNotification($user['email'], $subject, $body);
    }
    
    public function notifyAboutReaction(int $userId, int $postId, string $userName, string $reactionType): void {
        $user = $this->userModel->find($userId);
        if (!$user) return;
        
        $subject = "Новая реакция на ваше сообщение";
        $body = $this->createReactionNotificationBody($userName, $reactionType);
        
        $this->emailService->sendNotification($user['email'], $subject, $body);
    }
    
    private function createReplyNotificationBody(string $replierName, string $content): string {
        return "
            <h2>Здравствуйте!</h2>
            <p>{$replierName} ответил на ваше сообщение:</p>
            <blockquote>{$content}</blockquote>
        ";
    }
    
    private function createReactionNotificationBody(string $userName, string $reactionType): string {
        return "
            <h2>Здравствуйте!</h2>
            <p>{$userName} отреагировал на ваше сообщение: {$reactionType}</p>
        ";
    }
} 