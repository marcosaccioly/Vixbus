<?php

 /*
 * basicamente os parametros da url são:
 * ponto = ID
 * linha = ID
 * validacao = CAPTCHA
 */

$pontoNum = 6666;
$url = "http://rast.vitoria.es.gov.br/previsao-web-service/previsao.jsp?ponto=".$pontoNum;

$ch = curl_init($url);

curl_setopt($ch, CURLOPT_HEADER, true);
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10"); // Forjar ser navegador
curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest', 'Referer: http://rast.vitoria.es.gov.br/pontovitoria/')); // Forjar vir de uma pagina interna
echo curl_exec($ch);

curl_close($ch);

?>