<?php
require_once __DIR__ . '/facebook/autoload.php';

class  FB_Functions_Series{

//=================================FaceBook Page Top posts with top comments============================================
public function save_page_top_posts_top_comments(  ){
    $page_top_posts =$this -> save_page_posts_data(  );
    $posts_top_count=10;
    $i=1;
    foreach( $page_top_posts as $key => $value){
        if( $posts_top_count > $i){
            $article_content=$this -> get_page_post_content($key);
            $article_comments=$this -> get_page_posts_top_comments($key);
            $this -> save_into_txt($key." ".$article_content['message']." ".$article_content['created_time']."\r\n",$file_folder="result_file",$key,"a");
            foreach($article_comments as $article_comment){
                $this -> save_into_txt($article_comment[0]." ". $article_comment[1]." ".$article_comment[2]."\r\n",$file_folder="result_file",$key,"a");
            }
            $i++;
        }else{
            break;
        }
    }
}
//=================================FaceBook Page Posts的统计数据===================================================
    public function get_page_posts_top_comments(  $fb_page_post_id, $max_count = 20, $top_count = 5){
        $init_config = $this -> init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init= $fb_page_post_id."/comments?limit=100";
            $fb_page_posts_temp=$fb_page_posts_init;
            $page_post_comments_array= array();
            for($i = 1;$i <= $max_count ;$i++){
                //var_dump($i);
                $response = $fb ->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all=$response ->getDecodedBody();

                $page_post_comments= $response_all['data'];
                foreach( $page_post_comments as $page_post_comment){
                    $page_post_likes_num= $this -> get_post_likes_num( $page_post_comment['id'] );
                    //var_dump( $page_post_comment);
                    $page_post_comments_array[$page_post_comment['id']][]=$page_post_comment["from"]["name"];//name
                    $page_post_comments_array[$page_post_comment['id']][]=$page_post_comment["message"];//message
                    $page_post_comments_array[$page_post_comment['id']][]=$page_post_likes_num;//likes_num
                    $page_post_comments_top[$page_post_comment['id']]=$page_post_likes_num;
                }

                //var_dump(count(  $page_post_comments_array));
                if(!empty($response_all["paging"]['next'])){
                    $fb_page_posts_temp=str_replace("https://graph.facebook.com/v2.5/","",$response_all["paging"]['next']);
                }else{
                    break;
                }
                //var_dump($response_all["paging"]);
            }
            arsort(  $page_post_comments_top);
            $i = 1;
            $page_post_comments_sorted = array();
            foreach($page_post_comments_top as $key => $value){
                $page_post_comments_sorted[] = $page_post_comments_array[$key];
                if( $i> $top_count ){
                    break;
                }
                $i += 1;
            }
            return $page_post_comments_sorted ;

        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================FaceBook Page Posts的统计数据===================================================
    public function save_page_posts_data(  ){
        $fb_page_id='118446058286811';
        $fb_page_id2='370391549696429';
        $max_count = 30;
        $page_post_comments_num_weight =10;
        $page_post_likes_num_weight=30;
        $page_post_shares_num_weight=50;
        $page_posts_influence_array= array();

        $page_posts_array = $this -> get_page_posts($fb_page_id,$max_count );
        foreach( $page_posts_array as  $page_posts){
            foreach($page_posts as $page_post){
               // var_dump($page_post);
                if(@!empty($page_post['message'])){
                    $this -> get_post_content($page_post['message'],$page_post['id'],100);
                    $page_post_likes_num= $this -> get_post_likes_num( $page_post['id'] );
                    $page_post_comments_num= $this -> get_post_comments_num( $page_post['id'] );
                    $page_post_shares_num= $this -> get_post_shares_num( $page_post['id'] );
                    $post_influence = log($page_post_comments_num_weight*$page_post_comments_num + $page_post_likes_num_weight* $page_post_likes_num+$page_post_shares_num_weight*$page_post_shares_num);
                    $page_posts_influence_array[$page_post['id']] = $post_influence;
                    $this -> save_into_txt($page_post['id']." ". $page_post_likes_num." ".$page_post_comments_num." ".$page_post_shares_num." ".$post_influence."\r\n",$file_folder="result_file","page_post_influence_detail","a");

                }
            }
        }
        $page_posts_array2 = $this -> get_page_posts($fb_page_id2,$max_count );
        foreach( $page_posts_array2 as  $page_posts2){
            foreach($page_posts2 as $page_post2){
                if(@!empty($page_post2['message'])){
                    $this -> get_post_content($page_post2['message'],$page_post2['id'],100);
                    $page_post_likes_num= $this -> get_post_likes_num( $page_post2['id'] );
                    $page_post_comments_num= $this -> get_post_comments_num( $page_post2['id'] );
                    $page_post_shares_num= $this -> get_post_shares_num( $page_post2['id'] );
                    $post_influence = log($page_post_comments_num_weight*$page_post_comments_num + $page_post_likes_num_weight* $page_post_likes_num+$page_post_shares_num_weight*$page_post_shares_num);
                    $page_posts_influence_array[$page_post2['id']] = $post_influence;
                    $this -> save_into_txt($page_post2['id']." ". $page_post_likes_num." ".$page_post_comments_num." ".$page_post_shares_num." ".$post_influence."\r\n",$file_folder="result_file","page_post_influence_detail","a");

                }
              }
        }
       arsort($page_posts_influence_array);
       return $page_posts_influence_array;
    }
//=================================FaceBook Page Posts的message=========================================================
    public function get_page_post_content( $fb_page_post_id ){
        $init_config = $this -> init_config();
        $fb = new Facebook\Facebook($init_config['config']);
        try {
            $response = $fb ->get( $fb_page_post_id, $init_config['accessToken']);
            $response_all=$response ->getDecodedBody();
            return  $response_all;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================FaceBook Page Posts的shares 数量===================================================
    public function get_post_shares_num( $fb_page_post_id ){
        $init_config = $this -> init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init= $fb_page_post_id."/?fields=shares";
            $page_post_shares_num = 1;
            $response = $fb ->get( $fb_page_posts_init, $init_config['accessToken']);
            $response_all=$response ->getDecodedBody();
            if(@empty($response_all['shares']['count'])){
                $page_post_shares_num +=0;
            }else{
                $page_post_shares_num += $response_all['shares']['count'];
            }
           // var_dump( $page_post_shares_num);
           return  $page_post_shares_num;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================FaceBook Page Posts的likes 数量===================================================
    public function get_post_likes_num( $fb_page_post_id ){
        $init_config = $this -> init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init= $fb_page_post_id."/likes?limit=100";
            $fb_page_posts_temp=$fb_page_posts_init;
            $page_post_likes_num = 1;
            do{
                $response = $fb ->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all=$response ->getDecodedBody();
                $page_post_likes_num += count($response_all['data']);
                @$fb_page_posts_temp=str_replace("https://graph.facebook.com/v2.5/","",$response_all["paging"]['next']);
            }while(!empty($response_all["paging"]['next']));

            return  $page_post_likes_num;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================FaceBook Page Posts的comments 数量===================================================
    public function get_post_comments_num( $fb_page_post_id ){
        $init_config = $this -> init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init= $fb_page_post_id."/comments?limit=100";
            $fb_page_posts_temp=$fb_page_posts_init;
            $page_post_comments_num = 1;
            do{
                $response = $fb ->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all=$response ->getDecodedBody();
                $page_post_comments_num += count($response_all['data']);
                @$fb_page_posts_temp=str_replace("https://graph.facebook.com/v2.5/","",$response_all["paging"]['next']);
            }while(!empty($response_all["paging"]['next']));

           return  $page_post_comments_num;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

//=================================FaceBook Page Posts的Message和 Top Number的comments==================================
    public function get_post_content($fb_page_post_content,$fb_page_post_id,$max_count){
        $init_config = $this -> init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init= $fb_page_post_id."/comments?limit=100";
            $fb_page_posts_temp=$fb_page_posts_init;

            for($i = 1;$i <= $max_count ;$i++){
               //var_dump($i);
                $response = $fb ->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all=$response ->getDecodedBody();
                $page_post_comments_array[]= $response_all['data'];
                //var_dump(count(  $page_post_comments_array));
                if(!empty($response_all["paging"]['next'])){
                    $fb_page_posts_temp=str_replace("https://graph.facebook.com/v2.5/","",$response_all["paging"]['next']);
                }else{
                    break;
                }
                //var_dump($response_all["paging"]);
            }
            foreach( $page_post_comments_array as $page_post_comments){
                foreach( $page_post_comments as  $page_post_comment){
                    $fb_page_post_content=$fb_page_post_content ." ".$page_post_comment['message'];
                }
            }
            $this -> save_into_txt($fb_page_post_content,$file_folder="original_file",$fb_page_post_id);
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================FaceBook 粉丝专页 Top Number 的Posts=================================================
    public function get_page_posts($fb_page_id,$max_count ){
        $init_config = $this -> init_config();
        $fb = new Facebook\Facebook($init_config['config']);
        $page_posts = array();
        try {
            $fb_page_posts_init= $fb_page_id."/posts?limit=100";
            $fb_page_posts_temp=$fb_page_posts_init;

            for($i = 1;$i <= $max_count ;$i++){
                $response = $fb ->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all=$response ->getDecodedBody();
                $page_posts[]= $response_all['data'];
                if(!empty($response_all["paging"]['next'])){
                    $fb_page_posts_temp=str_replace("https://graph.facebook.com/v2.5/","",$response_all["paging"]['next']);
                }else{
                    break;
                }
            }
            return  $page_posts;
        } catch(Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch(Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

//===========================================FaceBook 初始配置==========================================================
    public function init_config(){
        $accessToken=$_SESSION['fb_access_token'];
        $config = array(
            'app_id' => '1669303983347467',
            'app_secret' => 'f619732cca08aabeaf23ff8d12bf32b3',
            'default_graph_version' => 'v2.5'
        );
        $fb = new Facebook\Facebook($config);

        // OAuth 2.0 client handler
        $oAuth2Client = $fb->getOAuth2Client();

        // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken( $accessToken);

        return array(
            "config" => $config ,
            "accessToken" => $longLivedAccessToken
        );
    }
//===================================================字符串写入文本文件=================================================
    public function save_into_txt($content,$file_folder,$file_name,$write_type="w"){
        $this->createFolder(dirname(__FILE__) . "\\".$file_folder);
        $handle_CF=fopen(dirname(__FILE__) . "\\".$file_folder."\\".$file_name.".txt",$write_type);
        fwrite( $handle_CF,$content);
        fclose( $handle_CF);
    }
//==================================================创建文件夹==========================================================
// 创建文件夹
    public function createFolder($path)
    {
        if (!file_exists($path))
        {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }
}

