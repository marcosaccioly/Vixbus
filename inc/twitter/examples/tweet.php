<?php

/**
 * Tweets a message from the user whose user token and secret you use.
 *
 * Although this example uses your user token/secret, you can use
 * the user token/secret of any user who has authorised your application.
 *
 * Instructions:
 * 1) If you don't have one already, create a Twitter application on
 *      https://dev.twitter.com/apps
 * 2) From the application details page copy the consumer key and consumer
 *      secret into the place in this code marked with (YOUR_CONSUMER_KEY
 *      and YOUR_CONSUMER_SECRET)
 * 3) From the application details page copy the access token and access token
 *      secret into the place in this code marked with (A_USER_TOKEN
 *      and A_USER_SECRET)
 * 4) Visit this page using your web browser.
 *
 * @author themattharris
 */

require '../tmhOAuth.php';
require '../tmhUtilities.php';
$tmhOAuth = new tmhOAuth(array(
  'consumer_key'    => '5A8tamVF9jljJimRvkczA',
  'consumer_secret' => 'ysIkfnwRlMulVLXfrAHzGzqGCe4SWJ1sMegpqTePE',
  'user_token'      => '398934223-kHaNvD88fL6XtcsUR4UUnpZgnRP03RxS112D3nZC',
  'user_secret'     => 'I6RIgjWjXtNNrbkH8v16wyV6bsdUSxmXi0qXs63acbQ',
));

$code = $tmhOAuth->request('POST', $tmhOAuth->url('1/statuses/update'), array(
  'status' => 'Olha ai @fernandogatti e @marquito o primeiro teste do nic_chaves pela API. (enviado por oAuth)'
));

if ($code == 200) {
  tmhUtilities::pr(json_decode($tmhOAuth->response['response']));
} else {
  tmhUtilities::pr($tmhOAuth->response['response']);
}

?>