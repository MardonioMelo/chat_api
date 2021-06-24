<?php

namespace Src\Controllers\Chat\Socket;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class responsável por administrar as salas de usuários da sessão no servidor websocket
 */
class SessionRoom
{

    private $session;
    private $pre_key;

    /**
     * Set All
     */
    public function __construct()
    {
        $this->session = new Session();
        $this->session->start();
        $this->pre_key = 'resourceId_';
        $this->setRoom('attendant');
        $this->setRoom('client');
        $this->setRoom('list');
    }

    /**
     * Set sala
     *
     * @param string $name_room
     * @return void
     */
    public function setRoom(String $name_room = "limbo"): void
    {
        $this->session->set($name_room, []);
    }

    /**
     *  Adicionar usuário em uma sala
     *
     * @param integer $resourceId
     * @param integer $user_id
     * @param string $name_room
     * @return void
     */
    public function addUserRoom(int $resourceId, int $user_id, string $name_room = "limbo"): void
    {
        $arr = $this->session->get($name_room);
        $arr[$this->pre_key . $resourceId] = $user_id;
        $this->session->set($name_room, $arr);
    }

    /**
     * Remover usuário de uma sala
     *
     * @param integer $resourceId
     * @param string $name_room
     * @return void
     */
    public function removeUserRoom(int $resourceId, string $name_room = "limbo"): void
    {
        $arr = $this->session->get($name_room);
        unset($arr[$this->pre_key . $resourceId]);
        $this->session->set($this->name_room, $arr);
    }

    /**
     * Adicionar usuário na lista de usuários geral
     *
     * @param int $resourceId
     * @param int $user_id
     * @return void
     */
    public function addUserList(int $resourceId, int $user_id): void
    {
        $this->addUserRoom($resourceId, $user_id,  'users_list');
    }

    /**
     * Remover usuário na lista de usuários geral
     *
     * @param int $resourceId
     * @return void
     */
    public function removeUserList(int $resourceId): void
    {
        $this->removeUserRoom($resourceId, 'users_list');
    }

    /**
     * Adicionar usuário na sala de clientes
     *
     * @param int $resourceId
     * @param int $user_id
     * @return void
     */
    public function addUserClient(int $resourceId, int $user_id): void
    {
        $this->addUserRoom($resourceId, $user_id,  'client');
    }

    /**
     * Remover usuário na sala de clientes
     *
     * @param int $resourceId
     * @return void
     */
    public function removeUserClient(int $resourceId): void
    {
        $this->removeUserRoom($resourceId, 'client');
    }

    /**
     * Adicionar usuário na sala de clientes
     *
     * @param int $resourceId
     * @param int $user_id
     * @return void
     */
    public function addUserAttendant(int $resourceId, int $user_id): void
    {
        $this->addUserRoom($resourceId, $user_id,  'client');
    }

    /**
     * Remover usuário na sala de clientes
     *
     * @param int $resourceId
     * @return void
     */
    public function removeUserAttendant(int $resourceId): void
    {
        $this->removeUserRoom($resourceId, 'client');
    }
    
    /**
     * Obter e retornar o id do usuário a partir do id da conexão
     *
     * @param integer $resourceId
     * @param string $name_room
     * @return integer
     */
    public function getUserId(int $resourceId, string $name_room = "list"): int
    {
        $arr = $this->session->get($name_room);
        $id = $arr[$this->pre_key . $resourceId];
        return (int) $id + 0;
    }

    /**
     * Verificar se o user está na sessão, se não estiver, será retornado false.
     *
     * @param integer $resourceId
     * @param string $name_room
     * @return bool
     */
    public function checkUserSession(int $resourceId, string $name_room = "list"): bool
    {
        if ($this->getUserId($resourceId, $name_room) > 0) {
            return true;
        } else {
            return false;
        }
    }

    /**
     * Remover usuário de todas as salas.
     *
     * @param integer $resourceId
     * @return void
     */
    public function removeUserAllRoom(int $resourceId):void
    {
        $this->removeUserAttendant($resourceId);
        $this->removeUserClient($resourceId);
        $this->removeUserList($resourceId);
    }
}