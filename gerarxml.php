<?php
$domain = 'http://www.sicadi.com.br/';
error_reporting(E_ALL);
//Corrige as entidades especiais HTML
function htmlspecial($string)
{
    $htmlEntities = array("&#202;", "&#199;", "&#213;", "&#193;", "&#195;", "&#201;", "&#194;", "&#205;", "&#211;", "&#176;", "&#170;", "&#186;", "&#218;", "&#212;", "&#225;", "&#231;", "&#227;", "&#237;", "&#243;", "&#234;", "&AACUTE;", "&ACIRC;", "&AGRAVE;", "&ATILDE;", "&CCEDIL;", "&EACUTE;", "&ECIRC;", "&IACUTE;", "&OACUTE;", "&OCIRC;", "&OTILDE;", "&UACUTE;", "&UUML;", "&QUOT;", "&LT;", "&GT;");
    $equalHtmlEnt = array("Ê", "Ç", "Õ", "Á", "Ã", "É", "Â", "Í", "Ó", "/", "ª", "º", "Ú", "Ô", "Á", "Ç", "Ã", "Í", "Ó", "Ê", "Á", "Â", "À", "Ã", "Ç", "É", "Ê", "Í", "Ó", "Ô", "Õ", "Ú", "Ü", '""', "<", ">");
    if ($string != '') {
        $string = str_replace($htmlEntities, $equalHtmlEnt, $string);
    }
    ;
    return $string;
};
//CABECALHO DO XML QUE SERA EXIBIDO NO NAVEGADOR
$DOMDocument = new DOMDocument('1.0', 'utf-8'); //CRIA DOCUMENTO DOM PARA EXIBICAO
$DOMDocument->preserveWhiteSpace = false;
$DOMDocument->formatOutput = true;
$root = $DOMDocument->createElement("imoveis"); //CRIA O NODE RAIZ
//CARREGA O XML INDICE
$index = simplexml_load_file('http://www.sicadi.com.br/i_mostra_imoveis_xml.php?codigo_imobiliaria=0547') or die("Error: Cannot create object");

foreach ($index->imovel as $estate) { //LACO QUE TRATA CADA IMOVEL COMO UNICO
    $id = $estate->attributes()->id; //ARMAZENA ID
    $codigo = $estate->attributes()->codigo; //ARMAZENA CODIGO
    $tipo = htmlspecial($estate->tipo[0]); //ARMAZENA TIPO
    $bairro = $estate->bairro[0]; //ARMAZENA BAIRRO
    $quartos = $estate->quartos[0]; //ARMAZENA QUANTIDADE DE QUARTOS
    $suites = $estate->suites[0]; //ARMAZENA QUANTIDADE DE SUITES
    $banheiros = $estate->banheiros[0]; //ARMAZENA QNT DE BANHEIROS
    $garagens = $estate->garagens[0]; //ARMAZENA QNT GARAGENS
    $endereco = $estate->endereco[0]; //ARMAZENA ENDERECO
    $numfotos = $estate->fotos_disponiveis->attributes()->count; //QNT FOTOS NO XML INDEX
    //CARREGA O DETALHE DO IMOVEL
    $details = simplexml_load_file('http://www.sicadi.com.br/i_detalhe_xml.php?oq=&imovel_id=' . $id . '&imobiliaria_id=1347') or die("Error: Cannot create object");

    //INICIO DA CONSTRUCAO DO CORPO DO XML PRINCIPAL
    $imovel = $DOMDocument->createElement("imovel"); //CRIA NODE 'IMOVEL'
    $idmov = $DOMDocument->createElement("id", $id); //CRIA NODE 'ID' E ADD ID
    $imovel->appendChild($idmov); //ANEXA O NODE 'ID' AO NODE 'IMOVEL'
    $codigomov = $DOMDocument->createElement("codigo", $codigo); //CRIA O NODE 'CODIGO' E ADD O CODIGO
    $imovel->appendChild($codigomov); //ANEXA O NODE 'CODIGO' AO NODE 'IMOVEL'
    $tipomov = $DOMDocument->createElement("tipo", $tipo); //CRIA O NODE 'TIPO' E ADD TIPO
    $imovel->appendChild($tipomov); //ANEXA O NODE 'TIPO' AO NODE 'IMOVEL'
    $fotosmov = $DOMDocument->createElement("fotos"); //CRIA O NODE 'FOTOS'
    $imovel->appendChild($fotosmov); //ANEXA O NODE 'FOTOS' AO NODE 'IMOVEL'
    if ($numfotos >= 1) { //SE A QNT DE FOTOS FOR MAIOR QUE '1', EXECUTA O LACO ABAIXO
        foreach ($details->fotos->foto as $photo) { //CAPTURA AS FOTOS NO XML DE DETALHE ENQUANTO HOUVER NODE
            $fotos = $domain . $photo->attributes()->src; //ADICIONA O DOMINIO DE ORIGEM DAS FOTOS CAPTURA A URL DE CADA UMA
            $fotomov = $DOMDocument->createElement("foto", $fotos); //CRIA O NODE 'FOTO' E ADICIONA A FOTO
            $fotosmov->appendChild($fotomov); //ANEXA O NODE 'FOTO' AO NODE 'FOTOS'
        }
        ;
    } else { //SE QNT DE FOTOS FOR MENOR QUE '1', EXECUTA:
        $fotos = 'https://www.portallider.com.br/img_nao_disponivel.jpg'; //IMAGEM PARA IMOVEIS SEM FOTO
        $fotomov = $DOMDocument->createElement("foto", $fotos); //CRIA O NODE 'FOTO' E ADICIONA A FOTO PADRAO
        $fotosmov->appendChild($fotomov); //ANEXA O NODE 'FOTO' AO NODE 'FOTOS'
    }

    $endmov = $DOMDocument->createElement("endereco", $endereco);
    $imovel->appendChild($endmov);
    $bairromov = $DOMDocument->createElement("bairro", $bairro);
    $imovel->appendChild($bairromov);
    $quartosmov = $DOMDocument->createElement("quartos", $quartos);
    $imovel->appendChild($quartosmov);
    $suitesmov = $DOMDocument->createElement("suites", $suites);
    $imovel->appendChild($suitesmov);
    $banheirosmov = $DOMDocument->createElement("banheiros", $banheiros);
    $imovel->appendChild($banheirosmov);
    $garagensmov = $DOMDocument->createElement("garagens", $garagens);
    $imovel->appendChild($garagensmov);

    $root->appendChild($imovel);
    $DOMDocument->appendChild($root);
}
header('Content-Type: text/xml');
print $DOMDocument->saveXML();
//file_put_contents("imoveis.xml", $DOMDocument->asXML()); //GERA ARQUIVO XML COM O RESULTADO
