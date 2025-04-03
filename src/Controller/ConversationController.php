<?php

namespace App\Controller;

use App\Repository\ConversationRepository;
use App\Repository\MessageRepository;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Component\Routing\Attribute\Route;
use Symfony\Component\HttpFoundation\Request;

class ConversationController extends AbstractController
{

#[Route('/api/conversations', name: 'api_conversations', methods: ['GET'])]
public function getUserConversations(ConversationRepository $conversationRepository): JsonResponse
{
    $user = $this->getUser();
    $conversations = $conversationRepository->findBy(['user' => $user]);

    $result = [];

    foreach ($conversations as $conversation) {
        $formattedMessages = [];
        $messages = $conversation->getMessages()->toArray();

        // 1️⃣ Stocker les réponses par ID de question
        $responsesByMessageId = [];

        foreach ($messages as $message) {
            if ($message->isChatResponse() && $message->getRepliesTo()) {
                $responsesByMessageId[$message->getRepliesTo()->getId()] = $message;
            }
        }

        // 2️⃣ Construire les messages et attacher les bonnes réponses
        foreach ($messages as $message) {
            if (!$message->isChatResponse()) {
                $response = $responsesByMessageId[$message->getId()] ?? null;

                $formattedMessages[] = [
                    'promptUser' => [
                        'id' => $message->getId(),
                        'content' => $message->getContent(),
                        'response' => $response ? [
                            'id' => $response->getId(),
                            'content' => $response->getContent(),
                        ] : null
                    ]
                ];
            }
        }

        $result[] = [
            'id' => $conversation->getId(),
            'title' => $conversation->getTitle(),
            'created_at' => $conversation->getCreatedAt()->format('Y-m-d H:i:s'),
            'messages' => $formattedMessages,
        ];
    }

    return $this->json($result);
}

}
