<?php

namespace Src\Models;

use Firebase\JWT\JWT;
use GuzzleHttp\Psr7\Message;

/**
 * Class para geração e autentificação de token JWT
 */
class JWTModel
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
        $now = time();
        $this->payload = array(
            "iss" => HOME, # Emitente        
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
     * Criar token
     *
     * @param array $data
     * @param integer $time_exp
     * @return void
     */
    public function createToken(array $data = [], int $time_exp = 3600): void
    {
        $this->setPayload($data, $time_exp);
        $this->setEncodeJWT();

        $this->Result = true;
        $this->Error['header'] = "Authorization";
        $this->Error['token'] = "Bearer " . $this->getToken();
        $this->Error['msg'] = "Token gerado com sucesso!";
    }

    /**
     * Verificar token iformado via 
     * @param Object $request
     * @return void
     */
    public function checkToken(object $request)
    {
        $headers = explode("\n", Message::toString($request));
        $is_authorization = preg_grep('/Authorization: Bearer /', $headers);

        if (count($is_authorization) === 1) {
            $this->Result = true;
            $this->Error['data'] = $this->getDecodeJWT(explode(" ", trim(array_values($is_authorization)[0]))[2])['data'];
            $this->Error['msg'] = "Conexão Autorizada!";
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Conexão Recusada: o token de autorização não foi informado corretamente.";
        }
    }

    /**
     * Verificar token informado via GET
     * 
     * @param Object $request
     * @return void
     */
    public function checkTokenMethodGET(object $request)
    {
        $get = parse_url($request->httpRequest->getRequestTarget());
        parse_str($get['query'], $token);
        $now = time();

        if (!empty($token['t'])) {
            $decoded = JWT::decode($token['t'], $this->key, $this->algorithms);
            $decoded_array = (array) $decoded;

            if ((int)$now < (int)$decoded_array['exp']) {
                $this->Result = true;
                $this->Error['data'] = $this->getDecodeJWT($token['t'])['data'];
                $this->Error['msg'] = "Conexão Autorizada!";
            } else {
                $this->Result = false;
                $this->Error['msg'] = "Conexão Recusada: o token de autorização está vencido: " . date("d/m/Y H:i", $decoded_array['iat']) . "hs";
            }
        } else {
            $this->Result = false;
            $this->Error['msg'] = "Conexão Recusada: o token de autorização não foi informado corretamente.";
        }
    }

    /**
     * Verificar Ação
     * 
     * @return bool
     */
    public function getResult(): bool
    {
        return $this->Result;
    }

    /**
     * Obter Erro
     * 
     * @return array
     */
    public function getError(): array
    {
        return $this->Error;
    }
}
