
<?php

 /*
 * basicamente os parametros da url são:
 * ponto = ID
 * linha = ID
 * validacao = CAPTCHA
 */
date_default_timezone_set("America/Sao_Paulo");
$pontoNum = 6166;
$url = "http://rast.vitoria.es.gov.br/previsao-web-service/previsao.jsp?ponto=".$pontoNum;

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10"); // Forjar ser navegador
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest', 'Referer: http://rast.vitoria.es.gov.br/pontovitoria/')); // Forjar vir de uma pagina interna
$result = curl_exec($ch);

curl_close($ch);
/*
print("<br/><br/>");

print("impressao print_r:    ");
print_r($result);

print("<br/><br/>");

print("impressao normal:   ");
print($result);

print("<br/><br/>");
print("testando XML:    ");
*/
$result = substr($result, strpos($result, "<"));
//print("<br/><br/>");
//print($result);

//printf("timestamp atual: %s", time());

$xml = simplexml_load_string($result);

if ($xml === false) {
    die('Error parsing XML');   
}

//now we can loop through the xml structure
print("<br/><br/>");

//var_dump($xml);
$i = 1;
foreach ($xml->ponto->estimativa as $est) {
    //printf("Busao %s: <strong>%s</strong> (%s): %s<br/>", $i, $est->linha->identificador, $est->linha->descricao, date("d/m/Y H:i:s:u", intval($est->horarioEstimado)));   
    $horario = intval(substr($est->horarioEstimado, 0, strlen($est->horarioEstimado)-3));
    $horario = round($horario/60)*60;
    printf("Busao %s: <strong>%s</strong> (%s): %s<br/>", $i, $est->linha->identificador, $est->linha->descricao, date("H:i:s", $horario));
    $i++;
}


?>

