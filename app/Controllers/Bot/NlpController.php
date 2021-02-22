<?php

/**
 * Copyright (c) 2020.  Mardônio M. Filho STARTMELO DESENVOLVIMENTO WEB.
 */

namespace App\Controllers\Bot;

use App\Models\BotModel;
use NlpTools\Stemmers\PorterStemmer;
use NlpTools\Tokenizers\WhitespaceAndPunctuationTokenizer;
use NlpTools\Tokenizers\WhitespaceTokenizer;
use NlpTools\Models\FeatureBasedNB;
use NlpTools\Documents\TrainingSet;
use NlpTools\Documents\TokensDocument;
use NlpTools\FeatureFactories\DataAsFeatures;
use NlpTools\Classifiers\MultinomialNBClassifier;
use NlpTools\Utils\Normalizers\Normalizer;
use NlpTools\Utils\StopWords;


class NlpController
{
    protected $tokP;
    protected $data;
    protected $tset;
    protected $tok;
    protected $ff;
    protected $modelNB;
    protected $content;
    protected $query;
    protected $response = [];
    protected $stemmer;
    protected $stopWords;
    protected $urlStopWords = '../app/config/stopwords.json';
    protected $normalizer;
    protected $urlTesting = '../app/config/testing.json';
    protected $botModel;


    /**
     * NplController constructor.
     */
    public function __construct()
    {

        $this->botModel = new BotModel();
        $this->tokP = new WhitespaceAndPunctuationTokenizer(); // será dividido em tokens com pontuação
        $this->tset = new TrainingSet(); // manterá os documentos de treinamento
        $this->tok = new WhitespaceTokenizer(); // será dividido em tokens
        $this->ff = new DataAsFeatures(); // veja os recursos na documentação
        $this->modelNB = new FeatureBasedNB(); // para treinar modelo Naive Bayes
        $this->stemmer = new PorterStemmer(); // reduzir as palavras flexionadas
        $this->stopWords = new StopWords($this->dataStopWords($this->urlStopWords)); // palavras que não agregam significado do texto
        $this->normalizer = Normalizer::factory("English"); // transformar as palavras em minúsculas
    }

    /**
     * Inicia o processamento. 
     * Informe true como parâmetro para modo de teste
     * @param null $test
     */
    public function start($test = null)
    {
        $test ? $this->testProcessing() : $this->processing();
    }

    /**
     * Usar classificação
     */
    protected function processing()
    {
        // ---------- Dados ----------------
        $training = $this->dataTraining(array_unique($this->stopW($this->tokP->tokenize($this->query))));

        if ($training) {

            // ---------- Treinamento ----------------
            $this->setTraining($training);

            // ---------- Classificação ----------------
            $cls = $this->classification($this->ff, $this->modelNB);

            // ---------- Previsão ----------------
            $intent = $cls->classify(
                $this->returnClasses($training), // todas as classes possíveis
                $this->tokDocument($this->query) // doc/msg do user
            );

            // ---------- Resposta ----------------
            $this->setResponse($this->data[$intent]['reply'], $intent, $this->data[$intent]['entitie']);
        } else {
            $this->setResponse("function", "notUnderstand", "null");
        }
    }

    /**
     * Testar classificação
     */
    protected function testProcessing()
    {
        // ---------- Dados ----------------
        $training = $this->dataTraining(array_unique($this->stopW($this->tokP->tokenize($this->query))));

        if ($training) {

            // e outro para avaliar
            $testing = $this->dataTesting($this->urlTesting);

            // ---------- Treinamento ----------------
            $this->setTraining($training);

            // ---------- Classificação ----------------
            $cls = $this->classification($this->ff, $this->modelNB);

            // ---------- Previsão ----------------
            $correct = 0;
            $intent = "";
            $pre = "<br>";
            $match = "";
            foreach ($testing as $d) {
                $intent = $cls->classify(
                    $this->returnClasses($training), // todas as classes possíveis
                    $this->tokDocument($d[1]) // doc/msg do user
                );
                if ((int)$intent === (int)$d[0]) :
                    $correct++;
                    $match .= $d[2] . ", ";
                endif;

                $pre .= "Teste " . $d[2] . " - Acurácia: " . (100 * $correct) / count($testing) . "%<br>";
            }

            // ---------- Resposta ----------------
            $this->setResponse($pre, $intent, $this->data[$intent]['entitie'], (100 * $correct) / count($testing));
        } else {
            $this->setResponse("function", "notUnderstand", "null");
        }
    }


    /**
     * @param $dataTokens
     * @return bool
     */
    protected function dataTraining($dataTokens)
    {
        $this->botModel->exeReadArray($dataTokens);
        return $this->botModel->getResult();
    }

    /**
     * @param $data
     */
    protected function setTraining($data)
    {
        foreach ($data as $d) {
            $exemples = json_decode($d->bot_exemples, true)['exemples'];

            foreach ($exemples as $ex) {
                $this->tset->addDocument($d->bot_intent, $this->tokDocument($ex)); // classe e documento
            }

            $this->data[$d->bot_intent] = [
                "id" => $d->bot_id,
                "entitie" => $d->bot_entitie,
                "reply" => $d->bot_reply,
            ];
        }
        $this->modelNB->train($this->ff, $this->tset);   // treinar um modelo Naive Bayes
    }

    /**
     * @param $data
     * @return array
     */
    protected function returnClasses($data)
    {
        $result = [];
        foreach ($data as $d) {
            $result[] = $d->bot_intent;
        }
        return $result;
    }

    /**
     * @param $url
     * @return mixed|string
     */
    protected function dataStopWords($url)
    {
        return file_exists($url) ?
            json_decode(file_get_contents($url), true)['stopwords'] : "";
    }

    /**
     * @param $url
     * @return mixed|string
     */
    protected function dataTesting($url)
    {
        return file_exists($url) ?
            json_decode(file_get_contents($url), true)['testing'] : "Arquivo de teste não localizado!";
    }

    /**
     * @param $arr
     * @return array
     */
    protected function stem($arr = [])
    {
        return $this->stemmer->stemAll($arr);
    }

    /**
     * @param $arr
     * @return array
     */
    protected function stopW($arr = [])
    {
        $d = new TokensDocument($arr);
        $d->applyTransformation($this->stopWords);
        return $d->getDocumentData();
    }

    /**
     * @param $arr
     * @return array
     */
    protected function norm($arr = [])
    {
        return $this->normalizer->normalizeAll($arr);
    }

    /**
     * @param  $tokstr
     * @return array|TokensDocument
     */
    protected function tokDocument($tokstr)
    {
        return new TokensDocument(
            $this->stem(
                $this->stopW(
                    $this->norm(
                        $this->tokP->tokenize($tokstr)
                    )
                )
            )
        );
    }

    /**
     * @param $ff
     * @param $model
     * @return MultinomialNBClassifier
     */
    protected function classification($ff, $model)
    {
        return new MultinomialNBClassifier($ff, $model);
    }

    /**
     * @return mixed
     */
    public function getContent()
    {
        return $this->content;
    }

    /**
     * @param mixed $query
     */
    public function setQuery($query)
    {
        $this->query = empty(trim(strip_tags($query))) ? null : $query;
    }

    /**
     * @return mixed
     */
    public function getQuery()
    {
        return $this->query;
    }

    /**
     * @param $reply
     * @param $intent
     * @param $entitie
     * @param int $acuracy
     */
    public function setResponse($reply, $intent, $entitie, $acuracy = 0)
    {
        $this->response['reply'] = $reply;
        $this->response['intent'] = $intent;
        $this->response['entitie'] = $entitie;
        $this->response['acuracy'] = $acuracy;
    }

    /**
     * @return mixed
     */
    public function getResponse()
    {
        return json_encode($this->response);
    }
}
