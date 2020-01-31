<?php
function htmlspecial($string)
{
    $htmlEntities = array("&#202;", "&#199;", "&#213;", "&#193;", "&#195;", "&#201;", "&#194;", "&#205;", "&#211;", "&#176;", "&#170;", "&#186;", "&#218;", "&#212;", "&#225;", "&#231;", "&#227;", "&#237;", "&#243;", "&#234;", "&AACUTE;", "&ACIRC;", "&AGRAVE;", "&ATILDE;", "&CCEDIL;", "&EACUTE;", "&ECIRC;", "&IACUTE;", "&OACUTE;", "&OCIRC;", "&OTILDE;", "&UACUTE;", "&UUML;", "&QUOT;", "&LT;", "&GT;");
    $equalHtmlEnt = array("Ê", "Ç", "Õ", "Á", "Ã", "É", "Â", "Í", "Ó", "/", "ª", "º", "Ú", "Ô", "Á", "Ç", "Ã", "Í", "Ó", "Ê", "Á", "Â", "À", "Ã", "Ç", "É", "Ê", "Í", "Ó", "Ô", "Õ", "Ú", "Ü", '""', "<", ">");
    if ($string != '') {
        $string = str_replace($htmlEntities, $equalHtmlEnt, $string);
    }
    return $string;
};
function tirarAcentos($string)
{
    return preg_replace(array("/(á|à|ã|â|ä)/", "/(Á|À|Ã|Â|Ä)/", "/(é|è|ê|ë)/", "/(É|È|Ê|Ë)/", "/(í|ì|î|ï)/", "/(Í|Ì|Î|Ï)/", "/(ó|ò|õ|ô|ö)/", "/(Ó|Ò|Õ|Ô|Ö)/", "/(ú|ù|û|ü)/", "/(Ú|Ù|Û|Ü)/", "/(ñ)/", "/(Ñ)/"), explode(" ", "a A e E i I o O u U n N"), $string);
};
// Config
$domain = 'http://www.sicadi.com.br/';
$cod_r_estate = '0547';
$xml = simplexml_load_file($domain . 'i_mostra_imoveis_xml.php?codigo_imobiliaria=' . $cod_r_estate) or die("Error: Cannot create object");

// Send the headers
header('Content-type: text/xml');
header('Pragma: public');
header('Cache-control: private');
header('Expires: -1');
echo "<?xml version=\"1.0\" encoding=\"utf-8\"?>";
echo '<imoveis>';

foreach ($xml->imovel as $estate) {
    $detailsurl = $estate->link;
    $details = simplexml_load_file($domain . $detailsurl) or die("Error: Cannot create object");
    $id = $estate->attributes()->id;
    $codigo = $estate->attributes()->codigo;
    $tipo = htmlspecial($estate->tipo[0]);
    $bairro = htmlspecial($estate->bairro[0]);
    $garagens = $estate->garagens[0];
    $endereco = htmlspecial($estate->endereco[0]);
    $numfotos = $estate->fotos_disponiveis->attributes()->count;
    $cidade = htmlspecial($details->cidade);
    $estado = $details->estado;
    $cep = $details->cep;
    $condominio = $details->condominio;
    $caracteristicas = htmlspecial($details->caracteristicas);
    $coordenadas = $details->coordenadas;

    echo '<imovel id="' . $id . '" codigo="' . $codigo . '">';
    echo '<tipo>' . $tipo . '</tipo>';
    echo '<bairro>' . $bairro . '</bairro>';
    echo '<garagens>' . $garagens . '</garagens>';
    echo '<endereco>' . $endereco . '</endereco>';
    echo '<disponibilidade>';
    foreach ($estate->disponibilidade->disponivel as $availability) {
        $finalidade = $availability->attributes()->finalidade;
        $valor = $availability->attributes()->valor;
        $valorcond = $valor == '0,00' ? 'cond="Consulte!"' : '';
        echo '<disponivel finalidade="' . $finalidade . '" valor="' . $valor . '" ' . $valorcond . '/>';
    }
    echo '</disponibilidade>';
    if ($numfotos >= 1) {
        echo '<imagens>';
        $a = 0;
        foreach ($details->fotos->foto as $photo) {
            $a++;
            $foto = $domain . $photo->attributes()->src;
            echo '<img url="' . $foto . '">foto-' . $a . '-imovel-' . $codigo . '</img>';
        }
        unset($a);
        echo '</imagens>';
    } else {
        echo '<imagens>';
        $foto = 'https://www.portallider.com.br/img_nao_disponivel.jpg';
        echo '<img url="' . $foto . '">SEM IMAGEM</img>';
        echo '</imagens>';
    }
    echo '<cidade>' . $cidade . '</cidade>';
    echo '<estado>' . $estado . '</estado>';
    echo '<cep>' . $cep . '</cep>';
    echo '<condominio>' . $condominio . '</condominio>';
    echo '<caracteristicas><![CDATA[' . $caracteristicas . ']]></caracteristicas>';
    echo '<coordenadas>' . $coordenadas . '</coordenadas>';
    echo '<composicao>';
    foreach ($details->composicao_imovel->composicao as $composicao) {
        $qnt_comp = htmlspecial($composicao->attributes()->quantidade);
        $item_comp = str_replace(' ', '_', tirarAcentos(htmlspecial($composicao)));
        echo '<' . $item_comp . '>' . $qnt_comp . '</' . $item_comp . '>';
    }
    echo '</composicao>';

    echo '</imovel>';
}
echo '</imoveis>';
