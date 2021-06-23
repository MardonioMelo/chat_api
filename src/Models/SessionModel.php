<?php

namespace Src\Models;

use Symfony\Component\HttpFoundation\Session\Session;

class SessionModel
{

    private $session;
    private $pre_key;
    private $name_room;

    public function __construct()
    {
        $this->session = new Session();
        $this->session->start();
        $this->pre_key = 'resourceId_';
    }

    /**
     * Armazene id do usuário na sessão
     *
     * @param int $resourceId
     * @param int $user_id
     * @return void
     */
    public function addUserSession(int $resourceId, int $user_id): void
    {
        $this->session->set($this->pre_key . $resourceId, $user_id);
    }

    /**
     * Adicionar usuário na sala de espera
     *
     * @return void
     */
    public function addUserRoom(int $resourceId, int $user_id): void
    {
        $arr = $this->session->get($this->name_room);
        $arr[$this->pre_key . $resourceId] = $user_id;
        $this->session->set($this->name_room, $arr);
    }

    /**
     * Remover usuário da sala de espera
     *
     * @param int $resourceId
     * @return void
     */
    public function removeUserRoom(int $resourceId): void
    {
        $arr = $this->session->get($this->name_room);
        unset($arr[$this->pre_key . $resourceId]);
        $this->session->set($this->name_room, $arr);
    }


    /**
     * Remover usuário da sessão e da sla de espera
     *
     * @param int $resourceId
     * @return void
     */
    public function removeUserSession($resourceId)
    {
        $this->session->remove($this->pre_key . $resourceId);
        $this->removeUserRoom($resourceId);
    }

    /**
     * Set sala de espera
     *
     * @param boolean $name_room
     * @return void
     */
    public function setRoom($name_room = ""): void
    {
        $name = $name_room === "" ? $this->name_room : $name_room;
        $this->session->set($name, []);
    }

    /**
     * Set nome da sala
     *
     * @param string $name
     * @return void
     */
    public function setNameRoom(string $name = "limbo"): void
    {
        $this->name_room = $name;
    }

    /**
     * Verificar se o user está na sessão, se não estiver, será adicionado.
     *
     * @param int $key_session
     * @param int $user_id
     * @return string
     */
    public function checkUserSession($resourceId, int $user_id): string
    {
        if ($this->session->get($this->pre_key . $resourceId) === 0) {
            //Salvar dados na sessão        
            $this->session->remove($this->pre_key . $resourceId);
            $this->session->set($this->pre_key . $resourceId, $user_id);

            return "\nNew user logged in!\n";
        } else {
            return "\nLogged in user!\n";
        }
    }

    /**
     * Obter e retornar o id do usuário a partir do id da conexão
     *
     * @param int $resourceId
     * @return int - retorna 0 se não existir 
     */
    public function getUserId($resourceId): int
    {
        return (int) $this->session->get($this->pre_key . $resourceId) + 0;
    }
}
