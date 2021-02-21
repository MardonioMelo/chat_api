<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
//use App\Models\Breed;

/**
 * Classe controller principal da API
 */
class Api
{
    private $breed;
    private $linkImg;

    public function __construct()
    {
       // $this->breed = new Breed();
    }

    /**
     * Executa pagina index
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function home(Request $request, Response $response, array $args)
    {
        $payload = "API para consulta para chatbot de atendimento";
        $response->getBody()->write($payload);
        return $response;
    }


    /**
     * Lista todas as raças
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function listBreed(Request $request, Response $response, array $args)
    {
        $offset = (int) $request->getQueryParams()['offset'];
        $limit = (int) $request->getQueryParams()['limit'];
        $url = APP_CONFIG['home'] . $request->getUri()->getPath();
        $pathImgs = "img-raca";

        $read_breeds = $this->breed->find()->limit($limit)->offset($offset)->fetch(true);
        $count = $this->breed->find()->count();

        $next = ($limit + $offset) > $count ? null : $url . "?offset=" . ($limit + $offset) . "&limit=" . $limit;
        $offset_pre = str_replace("-", "", $limit - $offset);
        $previous = (int) $offset === 0 ? null : $url . "?offset=" . $offset_pre  . "&limit=" . $limit;

        $results = [];
        if ($read_breeds !== null) {
            foreach ($read_breeds as $info_breed) {

                $path = getcwd() . DIRECTORY_SEPARATOR . $pathImgs . $info_breed->breed_img;
                if (is_dir($path)) {
                    $dirImgs = scandir($path);
                    $img =  $dirImgs[2];
                } else {
                    $img = "";
                }

                $results[] = [
                    "id" => (int) $info_breed->breed_id,
                    "name" => $info_breed->breed_name,
                    "url" => $url . "/" . $info_breed->breed_id,
                    "img" => APP_CONFIG['home'] . "/api-racadog/public/" . $pathImgs . $info_breed->breed_img . "/" . $img
                ];
            };
        }

        $arr = [
            "count" => $count, //quantidade total de raças
            "next" => $next, //link para avançar
            "previous" => $previous, //link para voltar          
            "results" => $results
        ];

        $payload = json_encode($arr);

        $response->getBody()->write($payload);
        return  $response->withHeader('Content-Type', 'application/json; charset=utf-8');
    }


    /**
     * Consulta dados de uma raça
     *
     * @param Request $request
     * @param Response $response
     * @param array $args
     * @return void
     */
    public function readBreed(Request $request, Response $response, array $args)
    {
        $id = (int) $args['id'];
        $read_breed = $this->breed->findById($id);
        $arr = array();
        $pathImgs = "img-raca";

        if ($read_breed != null) {

            $arr['success'] = true;
            $arr['id'] = (int) $read_breed->breed_id;
            $arr["breed"] = [
                "id"            => (int) $read_breed->breed_id, # id
                "name"          =>  $read_breed->breed_name, # nome inglês
                "name_pt"       =>  $read_breed->breed_name_pt, # nome em português
                "history"       =>  $read_breed->breed_history, # história
                "about"         =>  $read_breed->breed_about, # sobre
                "group_akc"     =>  $read_breed->breed_group_akc, # grupo conforme AKC
                "group_akc_pt"  =>  $read_breed->breed_group_akc_pt, # grupo conforme AKC em português  
                "group_fci"     =>  $read_breed->breed_group_fci, # grupo conforme FCI
                "height"        =>  $read_breed->breed_height, # altura em cm
                "weight"        =>  $read_breed->breed_weight, # peso em kg
                "size"          =>  $read_breed->breed_size, # porte
                "lifetime"      =>  $read_breed->breed_lifetime, # tempo de vida
                "temperament"   =>  $read_breed->breed_temperament, # temperamento do animal
                "color"         =>  $read_breed->breed_color, # cor em geral predominante
                "brand_color"   =>  $read_breed->breed_brand_color, # cor das marcas
                "head"          =>  $read_breed->breed_head, # descrição da cabeça
                "body"          =>  $read_breed->breed_body,  # descrição do corpo           
            ];

            $this->linkImg = APP_CONFIG['home'] . "/api-racadog/public/" . $pathImgs . $read_breed->breed_img;
            $path = getcwd() . DIRECTORY_SEPARATOR . $pathImgs . $read_breed->breed_img;

            if (is_dir($path)) {
                $dirImgs = scandir($path);
                unset($dirImgs[0]);
                unset($dirImgs[1]);

                $arr["album"] = array_map(function ($img) {
                    return $this->linkImg . '/' . $img;
                }, array_values($dirImgs));
            } else {
                $arr["album"] = "";
            }
        } else {

            $arr['success'] = false;
            $arr['error'] = "O ID da raça não existe no banco de dados!";
        }

        $payload = json_encode($arr);
        $response->getBody()->write($payload);
        return  $response->withHeader('Content-Type', 'application/json');
    }
  
}
