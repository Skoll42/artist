<?php
/**
 * Created by PhpStorm.
 * User: akostko
 * Date: 18.09.2018
 * Time: 16:07
 */
declare(strict_types=1);

namespace ChatBundle\Document\Repository;

use Doctrine\ODM\MongoDB\DocumentRepository;

class MessagesRepository extends DocumentRepository
{
    public function getAllChatsByUser($id)
    {
        $chats = $this->createQueryBuilder('p');
        $chats = $chats->addOr($chats->expr()->field('targetId')->equals($id))
            ->addOr($chats->expr()->field('senderId')->equals($id))
            ->sort('created_at', 'DESC')
            ->getQuery()
            ->execute();

        $result = [];
        foreach ($chats as $chat) {
            $chatName = $chat->getChatName();
            if (!isset($result[$chatName])) {
                $message = $this->getChatLastMessage($chatName);
                $result[$chatName]['message'] = $message;
                $result[$chatName]['status'] = $message->getStatus();
            }
        }

        return $result;
    }

    public function getUnreadByUser($id)
    {
        $chats = $this->createQueryBuilder('p');
        $chats = $chats->addAnd($chats->expr()->field('status')->equals('unread'))
            ->addAnd($chats->expr()->field('targetId')->equals($id))
            ->sort('created_at', 'ASC')
            ->getQuery()
            ->execute();

        $result = [];
        foreach ($chats as $chat) {
            $chatName = $chat->getChatName();
            if (!isset($result[$chatName])) {
                $message = $this->getChatLastMessage($chatName);
                $result[$chatName]['message'] = $message;
                $result[$chatName]['status'] = $message->getStatus();
            }
        }

        return $result;
    }

    public function getChatLastMessage($chat)
    {
        $message = $this->createQueryBuilder('p');
        $message = $message->addAnd($message->expr()->field('chatName')->equals($chat))
            ->sort('created_at', 'DESC')
            ->limit(1)
            ->getQuery()->getSingleResult();

        return $message;
    }

    public function getAllUnread()
    {
        $chats = $this->createQueryBuilder('p');

        $chats = $chats->addAnd($chats->expr()->field('status')->equals('unread'))
            ->addAnd($chats->expr()->field('isSend')->notEqual(true))
            ->sort('created_at', 'DESC')
            ->getQuery()
            ->getIterator();

        $result = [];
        foreach ($chats as $chat) {
            $chatName = $chat->getChatName();
            if (!isset($result[$chatName])) {
                $result[$chatName] = $chat;
            } else {
                $chat->setIsSend(true);
                $this->dm->persist($chat);
                $this->dm->flush();
            }
        }

        return $result;
    }
}
