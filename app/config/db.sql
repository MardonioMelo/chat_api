--
-- Banco de dados: `chatbot`
--

-- --------------------------------------------------------

--
-- Estrutura da tabela `app_bot`
--

DROP TABLE IF EXISTS `app_bot`;
CREATE TABLE IF NOT EXISTS `app_bot`
(
    `bot_id`       int(11)      NOT NULL AUTO_INCREMENT,
    `bot_intent`   varchar(255) NOT NULL COMMENT 'Intenção - nome da intenção em uma palavra',
    `bot_entitie`  varchar(255) NOT NULL COMMENT 'Entidade - elementos de texto conhecidos',
    `bot_exemples` text         NOT NULL COMMENT 'Exemplos de textos do usuário',
    `bot_reply`    text         NOT NULL COMMENT 'Resposta ao user ou nome de uma função',
    PRIMARY KEY (`bot_id`)
) ENGINE = MyISAM
  AUTO_INCREMENT = 25
  DEFAULT CHARSET = utf8;

--
-- Extraindo dados da tabela `app_bot`
--

INSERT INTO `app_bot` (`bot_id`, `bot_intent`, `bot_entitie`, `bot_exemples`, `bot_reply`)
VALUES (1, 'createCliente', 'cadastrar cliente',
        '{\r\n  \"exemples\": [\r\n    \"Cadastre um cliente\",\r\n    \"Como cadastrar clientes\",\r\n    \"Cadastro de clientes\",\r\n    \"Como eu cadastro um cliente?\",\r\n    \"Quero cadastrar cliente\",\r\n    \"hey, você sabe cadastrar clientes?\"\r\n  ]\r\n}',
        'function'),
       (2, 'cumprimento', 'bom turno',
        '{\r\n  \"exemples\": [\r\n    \"Bom dia!\",   \r\n    \"Boa tarde!\",   \r\n    \"Boa noite!\"\r\n  ]\r\n}',
        'function'),
       (3, 'respNegativa', 'resposta negativa',
        '{\r\n  \"exemples\": [\r\n   \"vou mais ou menos\", \"vou ruim\", \"levando a vista\", \"com dificuldades\", \"não estou bem\"\r\n     ]\r\n}',
        'Faz parte da vida <small style=\"font-size:20px\">&#128540;</small>'),
       (4, 'fretePrecoPrazo', 'calcule frete',
        '{\r\n  \"exemples\": [\r\n    \"Bom dia! Preciso calcular o valor do envio e o prazo de entrega\",\r\n    \"verificar o frete e o prazo de entrega\",\r\n    \"calcular frete e o prazo de entrega\",\r\n    \"Cálcule o frete e veja o prazo de entrega\",\r\n    \"preciso do valor do frete e o prazo de entrega\",\r\n    \"como cálcular o frete?\",\r\n    \"quero verificar o frete\",\r\n    \"Você pode cálcular o meu frete?\",\r\n    \"como eu faço para cálcular o frente do pedido\",\r\n    \"tempo de entrega\",\r\n    \"prazo da entrega da encomenda\",\r\n    \"quanto tempo leva para entrega uma encomenda na cidade\"\r\n  ]\r\n}',
        'function'),
       (5, 'oi', 'oi',
        '{\r\n  \"exemples\": [\r\n    \"oi\",\r\n    \"ola\",\r\n    \"oie\",\r\n    \"opa\",\r\n    \"oiee\"\r\n     ]\r\n}',
        'Olá! Tudo bem? <small style=\"font-size:20px\">&#128515;</small>'),
       (6, 'nlp', 'Testar nlp', '{\r\n  \"exemples\": [\r\n    \"nlp\"  \r\n     ]\r\n}', 'function'),
       (7, 'stop', 'Parar conversar', '{\r\n  \"exemples\": [\r\n    \"stop\", \"cancelar\"  \r\n     ]\r\n}',
        'function'),
       (8, 'meme1', 'meme rindo',
        '{\r\n  \"exemples\": [\r\n   \"que legal\",\"a sim\",\"ta certo\",\"interessante\",\"beleza\",\"tbm\",\"de nada\"\r\n     ]\r\n}',
        '<small style=\"font-size:20px\">&#128578;</small>'),
       (9, 'tdbem', 'tudo bem',
        '{\r\n  \"exemples\": [\r\n   \"como você está?\",\"tudo bem?\",\"como vai?\",\"tudo joia?\",\"como esta?\"\r\n     ]\r\n}',
        'Ótimo e você? <small style=\"font-size:20px\">&#128526;</small>'),
       (10, 'evc ', 'sim e vc',
        '{\r\n  \"exemples\": [\r\n   \"bem e vc?\",\"e você?\", \"estou ótimo\", \"estou bem\", \"bem graças a Deus\", \"bem tbm\", \"bem tambem\", \"sim e você\", \"sim e vc\"\r\n     ]\r\n}',
        'Ótimo! <small style=\"font-size:20px\">&#128536;</small>'),
       (11, 'nameBot', 'nome do bot',
        '{\r\n  \"exemples\": [\r\n   \"qual o seu nome?\",\"como vc se chama?\",\"você tem nome?\",\"qual seu nome de batismo?\",\"vc tem nome?\"\r\n     ]\r\n}',
        'Meu nome é Gê! <small style=\"font-size:20px\">&#128579;</small>'),
       (12, 'obr', 'elogio',
        '{\r\n  \"exemples\": [\r\n  \"o seu também\", \"belo nome\",\"lindo nome\",\"bonito nome\",\"vc é\"\r\n     ]\r\n}',
        'Obrigado! <small style=\"font-size:20px\">&#128521;</small>'),
       (13, 'myName', 'nome do usuario',
        '{\r\n  \"exemples\": [\r\n \"meu nome é\", \"me chamo\", \"E o meu é\"\r\n     ]\r\n}', 'function'),
       (14, 'listCommands', 'listar comandos',
        '{\r\n  \"exemples\": [\r\n    \"O que vc faz?\", \"liste os comandos\", \"quais os seus comandos\", \"mostre os comandos\", \"o que você faz?\"\r\n     ]\r\n}',
        'function'),
       (15, 'conta', 'fazer um cálculo',
        '{\r\n  \"exemples\": [\r\n\"quanto é 1 + 1\",\"qunto é 1 - 1\",\"quanto é 1 / 2\",\"quanto é 1 * 1\",\"1 + 1\", \"1 - 1\", \"1 * 1\", \"1 / 1\"\r\n     ]\r\n}',
        'function'),
       (16, 'consultCEP', 'consulta de CEP',
        '{\r\n  \"exemples\": [\r\n    \"consulte o CEP\", \"colsulta de cep\", \"verifique o cep\", \"veja este cep\", \"procure este cep\", \"informações do cep\", \"endereço do cep\"\r\n     ]\r\n}',
        'function'),
       (17, 'rastreamento', 'rastreio de ecomenda',
        '{\r\n  \"exemples\": [\r\n    \"localizar encomenda\", \"localizar mercadoria\", \"rastreio de encomendas\", \"rastrei uma encomenda\", \"verifique um rastreio\", \"rastreio dos correios\", \"Fazendo o rastreio de encomendas online.\", \"Rastreamento de encomendas\" \r\n     ]\r\n}',
        'function');

-- --------------------------------------------------------
