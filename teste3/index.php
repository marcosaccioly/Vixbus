
<?php

 /*
 * basicamente os parametros da url são:
 * ponto = ID
 * linha = ID
 * validacao = CAPTCHA
 */
date_default_timezone_set("America/Sao_Paulo");

// oAuth do twitter
// enviar tweet;
require '../inc/twitter/tmhOAuth.php';
require '../inc/twitter/tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => '',
  'consumer_secret' => '',
  'user_token'      => '',
  'user_secret'     => '',
));

// ler timeline e ver se há alguma request: 
$code = $tmhOAuth->request('GET', $tmhOAuth->url('1/statuses/mentions'), array(
  'include_entities' => '1',
  'include_rts'      => '0',
  'count'            => 100,
));

$meutweet = "";
$result_array = array();  

if ($code == 200) {
  $timeline = json_decode($tmhOAuth->response['response'], true);
  foreach ($timeline as $tweet) :
    $entified_tweet = tmhUtilities::entify($tweet);
    $is_retweet = isset($tweet['retweeted_status']);

    $diff = time() - strtotime($tweet['created_at']);
    if ($diff < 60*60)
      $created_at = floor($diff/60) . ' minutes ago';
    elseif ($diff < 60*60*24)
      $created_at = floor($diff/(60*60)) . ' hours ago';
    else
      $created_at = date('d M', strtotime($tweet['created_at']));

    $permalink  = str_replace(
      array(
        '%screen_name%',
        '%id%',
        '%created_at%'
      ),
      array(
        $tweet['user']['screen_name'],
        $tweet['id_str'],
        $created_at,
      ),
      '<a href="http://twitter.com/%screen_name%/%id%">%created_at%</a>'
    );
    
    // checar se há hashtag no formato #PNNNN onde NNNN são inteiros positivos
    $hashtags_array = $tweet['entities']['hashtags'];
    foreach($hashtags_array as $hashtag)
    {
        //echo $hashtag['text'];
        
        if(preg_match("[P\d{4}]",$hashtag['text'],$results))
        {
            $meutweet .= sprintf("@%s #%s: ", $tweet['user']['screen_name'], $results[0]);
            //var_dump($results);
            $result_array = $results;
        }
          
    }
    
    if(isset($result_array) && $result_array[0] != "")
    {    
        $pontoNum = trim(str_replace("P", "", $result_array[0]));
        //die(var_dump($pontoNum));
        
        $url = "http://rast.vitoria.es.gov.br/previsao-web-service/previsao.jsp?ponto=".$pontoNum;
        
        $ch = curl_init($url);
        
        curl_setopt($ch, CURLOPT_HEADER, true);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
        curl_setopt($ch, CURLOPT_USERAGENT, "Mozilla/5.0 (Windows; U; Windows NT 5.1; pt-BR; rv:1.9.2.10) Gecko/20100914 Firefox/3.6.10"); // Forjar ser navegador
        curl_setopt($ch, CURLOPT_HTTPHEADER, array('X-Requested-With: XMLHttpRequest', 'Referer: http://rast.vitoria.es.gov.br/pontovitoria/')); // Forjar vir de uma pagina interna
        $result = curl_exec($ch);
        
        curl_close($ch);
        $result = substr($result, strpos($result, "<"));
        
        $xml = simplexml_load_string($result);
        
        if ($xml === false) {
            die('Error parsing XML');   
        }
        
        //now we can loop through the xml structure
        print("<br/><br/>");
        
        //var_dump($xml);
        $i = 1;
        $identificador_anterior = 0;
        foreach ($xml->ponto->estimativa as $est) {
            if($identificador_anterior != $est->linha->identificador)
            {
                $horario = intval(substr($est->horarioEstimado, 0, strlen($est->horarioEstimado)-3));
                //$horario = round($horario/60)*60;
                $diff = $horario-time();
                    if ($diff < 60*60)
                      $horario = round($diff/60) . 'min';
                    elseif ($diff < 60*60*24)
                    {
                      $minutos = round($diff/60);
                      $horas = floor($minutos/60);
                      $minutos %= 60;
                      $horario = sprintf("%sh%smin",$horas, $minutos);
                    } 
                $meutweet .= sprintf("#%s %s | ", $est->linha->identificador, $horario);
                $identificador_anterior = $est->linha->identificador;
            }
        }
        
        var_dump($meutweet); 
        $result_array[0] = "";      
    
    }
    
    $meutweet .= " #vixbus"; // jabá
    
    // enviar tweet correspondente ao que foi recebido
    
    //var_dump($meutweet); 
        
    $meutweet = "";
    
  ?>
  <div id="<?php echo $tweet['id_str']; ?>" style="margin-bottom: 1em">
    <span>ID: <?php echo $tweet['id_str']; ?></span><br>
    <span>Orig: <?php echo $tweet['text']; ?></span><br>
    <span>Entified: <?php echo $entified_tweet ?></span><br>
    <small><?php echo $permalink ?><?php if ($is_retweet) : ?>is retweet<?php endif; ?>
    <span>via <?php echo $tweet['source']?></span></small>
  </div>
<?php
  endforeach;
} else {
  tmhUtilities::pr($tmhOAuth->response);
}



?>

