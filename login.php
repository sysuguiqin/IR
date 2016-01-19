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
	
	//权限设置
    $permissions = [
        'email',
        'user_location',
        'user_birthday',
        'user_managed_groups',
        'publish_actions',
        'publish_pages',
        'manage_pages',
        'public_profile'
    ]; 
    
	$loginUrl = $helper->getLoginUrl('http://localhost/IRTP/login-callback.php', $permissions);
    echo '<a href="' . htmlspecialchars($loginUrl) . '">Log in with Facebook!</a>';


?>