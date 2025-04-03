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
    $user = $this->getUser(); // Récupère l'utilisateur connecté
    $conversations = $conversationRepository->findBy(['user' => $user]);

    $result = [];

    foreach ($conversations as $conversation) {
        $formattedMessages = [];
        $messages = $conversation->getMessages()->toArray();

        for ($i = 0; $i < count($messages); $i++) {
            $message = $messages[$i];

            if (!$message->isChatResponse()) {
                $response = null;

                if (isset($messages[$i + 1]) && $messages[$i + 1]->isChatResponse() && $messages[$i + 1]->getRepliesTo() === $message) {
                    $response = $messages[$i + 1];
                }

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
