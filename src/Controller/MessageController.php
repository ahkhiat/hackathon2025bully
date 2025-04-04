<?php

namespace App\Controller;

use App\Entity\Message;
use App\Entity\Conversation;
use App\Repository\MessageRepository;
use Doctrine\ORM\EntityManagerInterface;
use App\Repository\ConversationRepository;
use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\Routing\Annotation\Route;
use Symfony\Component\HttpFoundation\JsonResponse;
use Symfony\Bundle\FrameworkBundle\Controller\AbstractController;
use Symfony\Component\HttpKernel\Exception\NotFoundHttpException;

class MessageController extends AbstractController
{
    #[Route('/api/message/new', name: 'api_messages_create', methods: ['POST'])]
    public function createMessage(
        Request $request,
        EntityManagerInterface $entityManager,
        ConversationRepository $conversationRepository,
        MessageRepository $messageRepository
    ): JsonResponse {
        $data = json_decode($request->getContent(), true);

        $content = $data['content'] ?? null;
        if (!$content) {
            return new JsonResponse(['error' => 'Le contenu du message est requis.'], JsonResponse::HTTP_BAD_REQUEST);
        }
        $isChatResponse = $data['is_chat_response'] ?? false;
        $repliesToId = $data['replies_to'] ?? null;

        $conversationId = $data['conversation_id'] ?? null;
        if (!$conversationId) {
            $conversation = new Conversation();
            $conversation->setTitle('Nouvelle conversation');
            $conversation->setCreatedAt(new \DateTimeImmutable());
            $conversation->setUser($this->getUser());
            $entityManager->persist($conversation);
            $entityManager->flush(); 
            $conversationId = $conversation->getId(); 
        } else {
            $conversation = $conversationRepository->find($conversationId);
            if (!$conversation) {
                throw new NotFoundHttpException('Conversation non trouvée');
            }
        }

        $repliesTo = null;
        if ($repliesToId) {
            $repliesTo = $messageRepository->find($repliesToId);
            if (!$repliesTo) {
                throw new NotFoundHttpException('Le message auquel vous répondez n\'existe pas');
            }
        }

        $message = new Message();
        $message->setContent($content);
        $message->setConversation($conversation);
        $message->setCreatedAt(new \DateTimeImmutable());
        $message->setRepliesTo($repliesTo);
        $message->setIsChatResponse($isChatResponse);
        $entityManager->persist($message);
        $entityManager->flush(); 

        return new JsonResponse([
            'message' => [
                'id' => $message->getId(),
                'content' => $message->getContent(),
                'conversation_id' => $conversation->getId(),
                'is_chat_response' => $message->isChatResponse(),
                'created_at' => $message->getCreatedAt()->format('Y-m-d H:i:s'),
            ]
        ], JsonResponse::HTTP_CREATED);
    }
}
