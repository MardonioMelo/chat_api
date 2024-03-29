<?php

namespace Src\Controllers\Chat\Socket;

use Symfony\Component\HttpFoundation\Session\Session;

/**
 * Class responsável por administrar as salas de usuários da sessão no servidor websocket
 */
class SessionRoomController
{

    private $session;
    private $pre_key;

    /**
     * Set All
     */
    public function __construct()
    {
        $this->session = new Session();     
        $this->pre_key = 'resourceId_';
        $this->setRoom('attendant');
        $this->setRoom('client');
        $this->setRoom('list');
        $this->setRoom('call');
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
     * @param string $user_uuid
     * @param string $name_room
     * @return void
     */
    public function addUserRoom(int $resourceId, string $user_uuid, string $name_room = "limbo"): void
    {
        $arr = $this->session->get($name_room);
        $arr[$this->pre_key . $resourceId] = $user_uuid;
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
        $this->session->set($name_room, $arr);
    }

    /**
     * Adicionar usuário na lista de usuários geral
     *
     * @param int $resourceId
     * @param string $user_uuid
     * @return void
     */
    public function addUserList(int $resourceId, string $user_uuid): void
    {
        $this->addUserRoom($resourceId, $user_uuid,  'list');
    }

    /**
     * Remover usuário na lista de usuários geral
     *
     * @param int $resourceId
     * @return void
     */
    public function removeUserList(int $resourceId): void
    {
        $this->removeUserRoom($resourceId, 'list');
    }

    /**
     * Adicionar usuário na sala de clientes
     *
     * @param int $resourceId
     * @param string $user_uuid
     * @return void
     */
    public function addUserClient(int $resourceId, string $user_uuid): void
    {
        $this->addUserRoom($resourceId, $user_uuid,  'client');
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
     * Adicionar usuário na sala de atendentes
     *
     * @param int $resourceId
     * @param string $user_uuid
     * @return void
     */
    public function addUserAttendant(int $resourceId, string $user_uuid): void
    {
        $this->addUserRoom($resourceId, $user_uuid,  'attendant');
    }

    /**
     * Remover usuário na sala de atendentes
     *
     * @param int $resourceId
     * @return void
     */
    public function removeUserAttendant(int $resourceId): void
    {
        $this->removeUserRoom($resourceId, 'attendant');
    }

    /**
     * Obter e retornar o uuid do usuário a partir do id da conexão
     *
     * @param int $resourceId
     * @param string $name_room    
     */
    public function getUser(int $resourceId, string $name_room = "list")
    {
        return empty($this->session->get($name_room)["{$this->pre_key}{$resourceId}"]) ? null :
            $this->session->get($name_room)["{$this->pre_key}{$resourceId}"];
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
        if (!empty($this->getUser((int)$resourceId, $name_room))) {
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
    public function removeUserAllRoom(int $resourceId): void
    {
        $this->removeUserAttendant($resourceId);
        $this->removeUserClient($resourceId);
        $this->removeUserList($resourceId);
    }

    /**
     * Obter os usuários de uma sala ou de da sala geral por padrão.
     *  
     * @param string $name_room
     * @return array
     */
    public function getUsersRoom(string $name_room = "list"): array
    {
        return  $this->session->get($name_room);
    }

    /**
     *  Adicionar usuário na sala da call
     * 
     * @param integer $call     
     * @param integer $resourceId
     * @param string $user_uuid
     * @param string $user_type
     * @param string $name_room
     * @return void
     */
    public function addUserRoomCall(int $call, string $user_uuid, string $user_type, string $name_room = "call"): void
    {
        $arr = $this->session->get($name_room);
        $arr[$name_room . '_' . $call][$user_uuid] = $user_type;
        $this->session->set($name_room, $arr);
    }

    /**
     * Remover usuário da sala da call
     * 
     * @param integer $call  
     * @param integer $user_uuid
     * @param string $name_room
     * @return void
     */
    public function removeUserRoomCall(int $call, string $user_uuid, string $name_room = "call"): void
    {
        $arr = $this->session->get($name_room);
        unset($arr[$name_room . '_' . $call][$user_uuid]);
        $this->session->set($name_room, $arr);
    }

    /**
     * Remover sala da sessão
     * 
     * @param integer $call    
     * @param string $name_room
     * @return void
     */
    public function destroyRoomCall(int $call, string $name_room = "call"): void
    {
        $arr = $this->session->get($name_room);
        unset($arr[$name_room . '_' . $call]);
        $this->session->set($name_room, $arr);
    }
    
    /**
     *  Verificar se já existe uma call aberta na sessão para o cliente
     *
     * @param string $uuid
     * @return string
     */
    public function existsCallInSession(string $uuid): string
    {
        $calls = $this->getUsersRoom("call");
        $call = "";

        if (!empty($calls)) {
            foreach ($calls as $key => $value) {
                $flip = array_flip($value);

                if ($uuid == $flip['client']) {
                    $call = $key;
                }
            }
        }
        return $call;
    }

    /**
     *  Verificar se o usuário está online
     *
     * @param string $uuid
     * @param string $room
     * @return bool
     */
    public function checkOn(string $uuid, string $room = "list"): bool
    {
        $users = $this->getUsersRoom($room);
        return in_array($uuid, $users);
    }
}
