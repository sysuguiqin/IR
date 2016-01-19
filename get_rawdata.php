<?php
require_once './FB_Functions.php';
require_once './IR_Functions.php';
    require_once __DIR__ . '/facebook/autoload.php';
    if(!session_id()) {
        session_start();
    }

    $config = array(
        'app_id' => '1669303983347467',
        'app_secret' => 'f619732cca08aabeaf23ff8d12bf32b3',
        'default_graph_version' => 'v2.5'
    );
    $fb = new Facebook\Facebook($config);

    $accessToken=$_SESSION['fb_access_token'] ;

    // OAuth 2.0 client handler
    $oAuth2Client = $fb->getOAuth2Client();

    // Exchanges a short-lived access token for a long-lived one
    $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken( $accessToken);

try {
    echo "Get Facebook page posts data begin.";
    $FB_Functions= new FB_Functions();
   //此处需要分步骤完成，人工调试即可
    echo "Get Facebook page posts data end.";
    echo "Statics Facebook page posts data starts.";
    $IR_Functions = new  IR_Functions();
    $IR_Functions->statics_process();
    echo "Statics Facebook page posts data finishes.";

} catch(Facebook\Exceptions\FacebookResponseException $e) {
    echo 'Graph returned an error: ' . $e->getMessage();
    exit;
} catch(Facebook\Exceptions\FacebookSDKException $e) {
    echo 'Facebook SDK returned an error: ' . $e->getMessage();
    exit;
}
?>
