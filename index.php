
<?php
date_default_timezone_set("America/Sao_Paulo");

// ler arquivo com ultimo ID   
$filename = 'lastTweetID.txt';
$lastID = file_get_contents($filename);

// oAuth do twitter
// você precisa obter esses dados ao criar uma app no twitter
require 'inc/twitter/tmhOAuth.php';
require 'inc/twitter/tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => 'Sua consumer key',
  'consumer_secret' => 'Sua consumer secret',
  'user_token'      => 'Seu user token',
  'user_secret'     => 'Seu user secret',
));

// ler timeline e ver se há alguma request: 
$code = $tmhOAuth->request('GET', $tmhOAuth->url('1/statuses/mentions'), array(
  'include_entities' => '1',
  'include_rts'      => '0',
  'count'            => 100,
  'since_id'         => $lastID
));

$meutweet = "";
$result_array = array();  

if ($code == 200) {
  $timeline = json_decode($tmhOAuth->response['response'], true);
  foreach ($timeline as $tweet) :
    $entified_tweet = tmhUtilities::entify($tweet);
    $is_retweet = isset($tweet['retweeted_status']);

    $diff = time() - strtotime($tweet['created_at']);
    
    // checar se há hashtag no formato #PNNNN onde NNNN são inteiros positivos
    $hashtags_array = $tweet['entities']['hashtags'];
    foreach($hashtags_array as $hashtag)
    {        
        if(preg_match("[P\d{4}]",$hashtag['text'],$results))
        {
            $meutweet .= sprintf("@%s #%s: ", $tweet['user']['screen_name'], $results[0]);
            $result_array = $results;
            //printf("<br/>ID: %s | Enviado por @%s em %s", $tweet['id_str'], $tweet['user']['screen_name'], $permalink );
        }
          
    }
    
    if(isset($result_array) && $result_array[0] != "")
    {    
        $pontoNum = trim(str_replace("P", "", $result_array[0]));
        
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
        
        print("<br/><br/>");
        
        $i = 1;
        $identificador_anterior = 0;
        $previsoes = array();
        if(is_object($xml->ponto->estimativa)){        
            foreach ($xml->ponto->estimativa as $est) {
                if(intval($identificador_anterior) !== intval($est->linha->identificador))
                {
                    $horario = intval(substr($est->horarioEstimado, 0, strlen($est->horarioEstimado)-3));
                    $diff = $horario-time();
                    $previsoes[strval($est->linha->identificador)] = $diff;
                                                        
                    //printf("<br/>num anterior: %s | novo_num: %s", $identificador_anterior, $est->linha->identificador);
                    $identificador_anterior = $est->linha->identificador;
                }
            }
        
            asort($previsoes);
                        
            // monta o tweet para envio
            foreach($previsoes as $linha=>$horario)
            {
                $diff = $horario;
                $horario = 0;
                if ($diff < 60*60)
                  $horario = round($diff/60) . 'min';
                elseif ($diff < 60*60*24)
                {
                  $minutos = round($diff/60);
                  $horas = floor($minutos/60);
                  $minutos %= 60;
                  $horario = sprintf("%sh%smin",$horas, $minutos);
                } 
                $meutweet .= sprintf("#%s %s | ", $linha, $horario);
            }        
            
            $meutweet = substr($meutweet, 0, 129);
            if(strlen($meutweet)==129) 
                $meutweet .= "...";
                $meutweet .= " #vixbus"; // jabá 
        }
        else // o ponto não foi encontrado, mas a estrutura #P<numero> está correta 
        {
            $meutweet .= "Este ponto existe mesmo? Que tal verificar em http://metzen.com.br/noponto ?";
        }            
            
        // zerar o result_array pra não termos repeteco
        $result_array[0] = "";         
    
    } 
    else //  não encontramos a hashtag #P<numero>. Pode ser um caso de mention aleatório ou erro na formação da hashtag
    {
        $meutweet = "@".$tweet['user']['screen_name'] . " Vixe! Nao entendi o numero do ponto. Lembre-se de usar #P e entao o numero. Um exemplo correto: #P6166 ";
    }
    
    
    
    // enviar tweet correspondente ao que foi recebido
    
    var_dump($meutweet); 
        
    if($meutweet != "")
    {    
        $code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
          'status' => $meutweet
        ));
        
        if ($code == 200) {
            echo "<br/>Horário enviado com sucesso";
           
            // Let's make sure the file exists and is writable first.
            if (is_writable($filename)) {
            
                if (!$handle = fopen($filename, 'w+')) {
                     echo "<br/>Não é possivel abrir o arquivo ($filename)";
                     exit;
                }                

                $lines = $tweet['id_str'];
            
                // Write $somecontent to our opened file.
                if (fwrite($handle, $lines) === FALSE) {
                    echo "Cannot write to file ($filename)";
                    exit;
                }
                
                echo "<br/>Ultimo ID gravado no arquivo.";
            
                fclose($handle);
            
            } else {
                echo "<br/>O arquivo $filename não pode ser escrito.";
            }

        }
        else
        {
            echo "Algum erro ocorreu e o tweet não foi enviado. " . var_dump($code);
        }
    }
    $meutweet = "";
  ?>

<?php
  endforeach;
} 

?>

