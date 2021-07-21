<?php

namespace Src\Models;

use PhpParser\Node\Expr\StaticCall;

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

    /**
     * Gerar links de paginação Next e Previous
     *
     * @param string $url
     * @param integer $limit
     * @param integer $offset
     * @param integer $count
     * @param string $other
     * @return array
     */
    public static function paginationLink(string $url, int $limit, int $offset, int $count, $other = ""): array
    {
        $link = array();
        $link['next'] = ($limit + $offset) > $count ? null : $url . "?offset=" . ($limit + $offset) . "&limit=" . $limit . $other;
        $offset_pre = str_replace("-", "", $limit - $offset);
        $link['previous'] = (int) $offset === 0 ? null : $url . "?offset=" . $offset_pre  . "&limit=" . $limit . $other;

        return $link;
    }

    /**
     * Passar data do formato BR (01/30/2020) para o formato (30/01/2020), verificar se é valida e retorna em formato (2020-01-30).
     * 
     * @param string $date
     * @return boll|string
     */
    public static function validDateBrForUSA(string $date)
    {
        $usa = explode("-", str_replace("/", "-", $date));

        if ((int)$usa[2] > 2020) {
            return checkdate((int) $usa[1], (int)$usa[0], (int)$usa[2]) ? $usa[2] . "-" . $usa[1] . "-" . $usa[0] : false;
        } else {
            return false;
        }
    }
}
