<?php

namespace Src\Models;

/**
 * Class de utilidades com métodos em comum no sistema
 */
class UtilitiesModel
{

    /**
     * Validar CPF
     *
     * @param string $cpf
     * @return bool
     */
    public static function validateCPF(string $cpf): bool
    {
        // Extrai somente os números
        $cpf = preg_replace('/[^0-9]/is', '', $cpf);

        // Verifica se foi informado todos os dígitos corretamente
        if (strlen($cpf) != 11) {
            return false;
        }

        // Verifica se foi informada uma sequência de dígitos repetidos. Ex: 111.111.111-11
        if (preg_match('/(\d)\1{10}/', $cpf)) {
            return false;
        }

        // Faz o calculo para validar o CPF
        for ($t = 9; $t < 11; $t++) {
            for ($d = 0, $c = 0; $c < $t; $c++) {
                $d += $cpf[$c] * (($t + 1) - $c);
            }
            $d = ((10 * $d) % 11) % 10;
            if ($cpf[$c] != $d) {
                return false;
            }
        }
        return true;
    }

    /**
     * Extrair somente os números do CPF
     *
     * @param string $cpf
     * @return int
     */
    public static function numCPF(string $cpf): int
    {
        return (int) preg_replace('/[^0-9]/is', '', $cpf);
    }

    /**
     * Limpar parâmetros de tags e espaços
     *
     * @param array $params
     * @return void
     */
    public static function filterParams($params = []): array
    {
        return array_filter($params, function ($str) {
            return preg_replace("/\r|\n/", "",  trim(strip_tags($str)));
        });
    }

    /**
     * Obter os dados de requisições PUT
     *
     * @return array
     */
    public static function getPUT(): array
    {
        $put = array();

        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'PUT')) {
            parse_str(file_get_contents('php://input'), $put);
        }

        return $put;
    }

    /**
     * Obter os dados de requisições DELETE
     *
     * @return array
     */
    public static function getDELETE(): array
    {
        $delete = array();

        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'DELETE')) {
            parse_str(file_get_contents('php://input'), $delete);
        }

        return $delete;
    }

    /**
     * Obter os dados de requisições PATCH
     *
     * @return array
     */
    public static function getPATCH(): array
    {
        $patch = array();

        if (!strcasecmp($_SERVER['REQUEST_METHOD'], 'PATCH')) {
            parse_str(file_get_contents('php://input'), $patch);
        }

        return $patch;
    }
}
