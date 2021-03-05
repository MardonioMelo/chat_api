<?php

namespace App\Controllers;

use Psr\Http\Message\ResponseInterface as Response;
use Psr\Http\Message\ServerRequestInterface as Request;
use App\Models\BotModel;

/**
 * Classe controller principal da API
 */
class Home
{
    private $BotModel;
    private $linkImg;

    public function __construct()
    {
        $this->BotModel = new BotModel();
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
        $payload = $this->processData();

        //$payload = "API para consulta para chatbot de atendimento";
        $response->getBody()->write($payload);
        return $response;
    }

    //Processar dados
    public function processData()
    {
        $urlJson = '../app/config/refinar_dados/teste.json';

        if (is_file($urlJson)) {
            $payload = "Sucesso!";

            //Consultar todos os chamados em um arquivo json
            $getJson = json_decode(file_get_contents($urlJson));

            $id_anterior = 0;
            $cham_bot = [];

            //Consultar registros de um chamado
            foreach ($getJson->data as $key => $cham) {

                if ((int) $id_anterior === (int) $cham->cham_id) {

                    $key_anterior = $key - 1;

                    if ((int) $cham->inter_cli_id > 0) {

                        //Msg cliente       
                        if (empty($cham_bot[$key_anterior]["bot_exemples"]) && !empty($cham_bot[$key_anterior]["bot_reply"])) {

                            $cham_bot[$key_anterior]["bot_exemples"] = [trim(strip_tags($cham->inter_historico))];
                        } else {
                            $cham_bot[$key]["bot_intent"] = "teste";
                            $cham_bot[$key]["bot_entitie"] = "teste";
                            $cham_bot[$key]["bot_exemples"] = [trim(strip_tags($cham->inter_historico))];
                            $cham_bot[$key]["bot_reply"] = "";
                        }
                    } else {

                        //Msg funcionário
                        if (empty($cham_bot[$key_anterior]["bot_reply"]) && !empty($cham_bot[$key_anterior]["bot_exemples"])) {

                            $cham_bot[$key_anterior]["bot_reply"] = trim(strip_tags($cham->inter_historico));
                        } else {
                            $cham_bot[$key]["bot_intent"] = "teste";
                            $cham_bot[$key]["bot_entitie"] = "teste";
                            $cham_bot[$key]["bot_exemples"] = "";
                            $cham_bot[$key]["bot_reply"] = trim(strip_tags($cham->inter_historico));
                        }
                    }
                } else {

                    //Excluir conversa anterior que não casou o exemplo e a resposta
                    if (empty($cham_bot[$key - 1]["bot_exemples"]) || empty($cham_bot[$key - 1]["bot_reply"])) {
                        unset($cham_bot[$key - 1]);
                    }

                    //Abertura do chamado - cliente e funcionário
                    $cham_bot[$key]["bot_intent"] = "teste";
                    $cham_bot[$key]["bot_entitie"] = "teste";
                    $cham_bot[$key]["bot_exemples"] = [trim(strip_tags($cham->cham_historico))];
                    $cham_bot[$key]["bot_reply"] = trim(strip_tags($cham->inter_historico));
                }

                $id_anterior = $cham->cham_id;
            };

            //Excluir ultima conversa que não casou o exemplo e a resposta
            if (empty($cham_bot[$key]["bot_exemples"]) || empty($cham_bot[$key]["bot_reply"])) {
                unset($cham_bot[$key]);
            }
            
        } else {
            $payload = "O arquivo json não existe ou o caminho está errado!";
        }

        //Salvar os dados processados
        $this->saveProcessData($cham_bot);

        return $payload;
    }

    /**
     * Salvar dados no banco
     * Essa é a estrutura dentro de cada chave no array
     * array [
     *  ["bot_intent"] => "string unica"
     *  ["bot_entitie"] => "string"   
     *  ["bot_exemples"]=> array["ok1","ok2","ok3"]
     *  ["bot_reply"]=> "string"
     * ]
     * 
     * @param array $arr
     * @return void
     */
    public function saveProcessData($arr)
    {
        foreach ($arr as $item) {

            $this->BotModel->createExemple(
                $item["bot_intent"],
                $item["bot_entitie"],
                $item["bot_exemples"],
                $item["bot_reply"]
            );
        };
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
