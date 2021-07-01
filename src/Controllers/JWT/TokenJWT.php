<?php

namespace Src\Controllers\JWT;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Message;

/**
 * Class para geração e autentificação de token JWT
 */
class TokenJWT
{

    private $key;
    private $algorithms;
    private $token;
    private $payload;
    private $Result;
    private $Error;

    /**
     * Set init key e algorithms
     */
    public function __construct()
    {
        $this->key = JWT_SECRET;    # Chave secreta
        $this->algorithms = ['HS256'];
    }

    /**
     * Set payload
     *
     * @param array $data   Outros dados do user
     * @param integer $exp  Identifica o tempo de expiração a partir do qual o JWT não deve ser aceito para processamento. O valor deve ser um NumericDate   
     * @return void
     */
    public function setPayload(array $data = [], int $exp = 3600): void
    {
        $now = idate("U");
        $this->payload = array(
            "iss" => HOME, //getenv(HOME), # Emitente        
            "iat" => $now, # Identifica a hora em que o JWT foi emitido. O valor deve ser um NumericDate.
            "nbf" => $now, # Identifica a hora em que o JWT começará a ser aceito para processamento. O valor deve ser um NumericDate.
            "exp" => $now + $exp,
            "data" => $data
        );
    }

    /**
     * Obter dados do payload
     *
     * @return array
     */
    public function getPayload(): array
    {
        return $this->payload;
    }

    /**
     * Set dados NÃO SENSÍVEIS complementares do usuário
     *
     * @param array $data # Outros dados do user
     * @return void
     */
    public function setData(array $data): void
    {
        $this->payload["data"] = $data;
    }

    /**
     * Codificar JWT
     *
     * @return void
     */
    public function setEncodeJWT(): void
    {
        $this->token = JWT::encode($this->payload, $this->key);
    }

    /**
     * Decodificar JWT
     *
     * @return array
     */
    public function getDecodeJWT($token): array
    {
        return (array) JWT::decode($token, $this->key, $this->algorithms);
    }

    /**
     * Obter o Token
     *
     * @return string
     */
    public function getToken(): string
    {
        return $this->token;
    }

    /**
     * Criar Token do usuário
     *
     * @param array $data
     * @param integer $time_exp
     * @return string
     */
    public function createTokenUser(array $data = [], int $time_exp = 3600): string
    {
        $this->setPayload($data, $time_exp);
        $this->setEncodeJWT();
        return "Authorization: Bearer " . $this->getToken();
    }

    /**
     * Verificar token no servidor de conexão
     * @param Object $request
     * @return void
     */
    public function checkToken(object $request)
    {
        $headers = explode("\n", Message::toString($request));
        $is_authorization = preg_grep('/Authorization: Bearer /', $headers);

        if (count($is_authorization) === 1) {         
            $this->Result = $this->getDecodeJWT(explode(" ", trim(array_values($is_authorization)[0]))[2])['data'];
            $this->Error = "Conexão Autorizada!";
        } else {
            $this->Result = $is_authorization;
            $this->Error = "Conexão Recusada: o token de autorização não foi informado corretamente.";
        }     
    }

    /**
     * Verificar Ação
     * 
     * @return mixed
     */
    public function getResult()
    {
        return $this->Result;
    }

    /**
     * Obter Erro
     * 
     * @return string
     */
    public function getError(): string
    {
        return $this->Error;
    }
}
