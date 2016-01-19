<?php

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


    $helper = $fb->getRedirectLoginHelper();
    try {
        $accessToken = $helper->getAccessToken();
    } catch(Facebook\Exceptions\FacebookResponseException $e) {
        // When Graph returns an error
        echo 'Graph returned an error1: ' . $e->getMessage();
        exit;
    } catch(Facebook\Exceptions\FacebookSDKException $e) {
        // When validation fails or other local issues
        echo 'Facebook SDK returned an error2: ' . $e->getMessage();
        exit;
    }

    if (isset($accessToken)) {
        // Logged in!
        $_SESSION['fb_access_token'] = (string) $accessToken;
        header('Location: http://localhost/IRTP/get_rawdata.php');
        // Now you can redirect to another page and use the
        // access token from $_SESSION['facebook_access_token']
    }else{
        echo "Not accessToken";
    }

?>
