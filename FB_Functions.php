<?php
require_once __DIR__ . '/facebook/autoload.php';

class  FB_Functions
{
//=================================从文本获取粉丝专页的posts 数据=======================================================
    public function get_page_posts_data_from_txt()
    {//备注：由于Facebook 会有查询限制和查询时间过长问题，建议耗时太久的步骤，切分成小步骤完成
        $this ->get_page_posts_top_comments("118446058286811_759667444164666");
    }

//=================================FaceBook Page Top Posts的Top comments================================================
    public function get_page_posts_top_comments($fb_page_post_id, $max_count = 2, $top_count = 5)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init = $fb_page_post_id . "/comments?limit=100";
            $fb_page_posts_temp = $fb_page_posts_init;
            $page_post_comments_array = array();
            for ($i = 1; $i <= $max_count; $i++) {
                //var_dump($i);
                $response = $fb->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all = $response->getDecodedBody();

                $page_post_comments = $response_all['data'];
                foreach ($page_post_comments as $page_post_comment) {
                    $page_post_likes_num = $this->get_post_likes_num($page_post_comment['id']);
                    //var_dump( $page_post_comment);
                    $page_post_comments_array[$page_post_comment['id']][] = $page_post_comment["from"]["name"];//name
                    $page_post_comments_array[$page_post_comment['id']][] = $page_post_comment["message"];//message
                    $page_post_comments_array[$page_post_comment['id']][] = $page_post_likes_num;//likes_num
                    $page_post_comments_top[$page_post_comment['id']] = $page_post_likes_num;
                }

                //var_dump(count(  $page_post_comments_array));
                if (!empty($response_all["paging"]['next'])) {
                    $fb_page_posts_temp = str_replace("https://graph.facebook.com/v2.5/", "", $response_all["paging"]['next']);
                } else {
                    break;
                }
                //var_dump($response_all["paging"]);
            }
            if(!empty($page_post_comments_top)){
                arsort($page_post_comments_top);
                $i = 1;
                foreach ($page_post_comments_top as $key => $value) {
                    $this->save_into_txt($page_post_comments_array[$key][1], $file_folder="result_file", $file_name=$fb_page_post_id."=".$key."_comments", $write_type = "w");
                    $this->save_into_txt($page_post_comments_array[$key][0]." ".$page_post_comments_array[$key][2]."\r\n", $file_folder="result_file", $file_name=$fb_page_post_id."=".$key."_data", $write_type = "w");
                    if ($i > $top_count) {
                        break;
                    }
                    $i += 1;
                }
            }
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================对page posts 进行影响力排序，并保存结果至文本 ======================================
    public function save_page_posts_sorted_into_txt(){
        $array=array();
        for($i=1;$i<54;$i++){
		if($i<10){
			    $array= $this-> get_txt_into_array($file_folder="result_file", $file_name="page_post_influence_detail0".$i, $element_num=5, $array,$specific_num=5);
       	
		}else{
			    $array= $this-> get_txt_into_array($file_folder="result_file", $file_name="page_post_influence_detail".$i, $element_num=5, $array,$specific_num=5);
       	
		}
        var_dump(count($array));
	   }
        arsort($array);
	
        $this->save_array_into_txt($array, $file_folder="result_file", $file_name="page_post_influence_sorted");
    }

//=================================保存page posts的统计数据至文本==========================================================
    public function save_page_posts_statics_into_txt()
    {
        $fb_page_posts_data= array();
       for($fb_page_id=43;$fb_page_id<53;$fb_page_id++) {
           $fb_page_posts_data= $this->get_txt_into_array("original_file\\posts",$fb_page_id, 2, $fb_page_posts_data);
           $page_post_likes_num_weight = 10;
           $page_post_comments_num_weight =30;
           $page_post_shares_num_weight =50;

           foreach( $fb_page_posts_data as $key => $value){
               $page_post_likes_num = $this->get_post_likes_num( $key);
               $page_post_comments_num = $this->get_post_comments_num($key);
               $page_post_shares_num = $this->get_post_shares_num($key);
               $post_influence = log( $page_post_likes_num_weight * $page_post_likes_num+$page_post_comments_num_weight * $page_post_comments_num +  $page_post_shares_num_weight * $page_post_shares_num,10);
               $this->save_into_txt( $key . " " . $page_post_likes_num . " " . $page_post_comments_num . " " . $page_post_shares_num . " " . $post_influence . "\r\n", $file_folder = "result_file", "page_post_influence_detail".$fb_page_id, "a");
           }
        }


    }
//=================================从文本获取数据保存至数组=============================================================
    public function get_txt_into_array($file_folder, $file_name, $element_num, $array,$specific_num=0)
    {

        $file_path=dirname(__FILE__)."\\".$file_folder."\\".$file_name.".txt";
        if(file_exists($file_path)){
            $file_content=file($file_path);//读取文件内容
            foreach($file_content as $line){
                $line = str_replace(array("\r\n", "\r", "\n"),'',$line);
                $line = explode(" ",$line);
                if($specific_num==0){
                    for($i=1;$i<$element_num;$i++){
                        $array[trim($line[0])]=trim($line[$i]);
                    }
                }else{
                    @$array[trim($line[0])]=trim($line[$specific_num-1]);
                }
            }
            return  $array;
        }
    }
//=================================从Facebook获取粉丝专页的posts 数据===================================================
    public function get_page_posts_data($fb_page_ids = array('118446058286811', '370391549696429'), $page_posts_count = 50)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);
        try {
            foreach ($fb_page_ids as $fb_page_id) {
                $fb_page_posts_init = $fb_page_id . "/posts?limit=100";
                $fb_page_posts_temp = $fb_page_posts_init;

                for ($i = 1; $i <= $page_posts_count; $i++) {
                    $response = $fb->get($fb_page_posts_temp, $init_config['accessToken']);
                    $response_all = $response->getDecodedBody();
                    if (!@empty($response_all['data'])) {
                        $page_posts = $response_all['data'];
                        $this->save_posts_array_into_txt($page_posts, "original_file\\posts", $fb_page_id, $write_type = "a");
                    }
                    if (!@empty($response_all["paging"]['next'])) {
                        $fb_page_posts_temp = str_replace("https://graph.facebook.com/v2.5/", "", $response_all["paging"]['next']);
                     //   $this->save_into_txt($response_all["paging"]['next'] . "\r\n", "original_file\\posts", $fb_page_id . "_paging", $write_type = "a");
                    } else {
                        break;
                    }
                }
            }
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

//=================================FaceBook Page Posts的shares 数量===================================================
    public function get_post_shares_num($fb_page_post_id)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init = $fb_page_post_id . "/?fields=shares";
            $page_post_shares_num = 1;
            $response = $fb->get($fb_page_posts_init, $init_config['accessToken']);
            $response_all = $response->getDecodedBody();
            if (@empty($response_all['shares']['count'])) {
                $page_post_shares_num += 0;
            } else {
                $page_post_shares_num += $response_all['shares']['count'];
            }
            // var_dump( $page_post_shares_num);
            return $page_post_shares_num;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================FaceBook Page Posts的comments 数量===================================================
    public function get_post_comments_num($fb_page_post_id)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init = $fb_page_post_id . "/comments?limit=100";
            $fb_page_posts_temp = $fb_page_posts_init;
            $page_post_comments_num = 1;
            do {
                $response = $fb->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all = $response->getDecodedBody();
                $page_post_comments_num += count($response_all['data']);
                @$fb_page_posts_temp = str_replace("https://graph.facebook.com/v2.5/", "", $response_all["paging"]['next']);
            } while (!empty($response_all["paging"]['next']));

            return $page_post_comments_num;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================FaceBook Page Posts的likes 数量===================================================
    public function get_post_likes_num($fb_page_post_id)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init = $fb_page_post_id . "/likes?limit=100";
            $fb_page_posts_temp = $fb_page_posts_init;
            $page_post_likes_num = 1;
            do {
                $response = $fb->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all = $response->getDecodedBody();
                $page_post_likes_num += count($response_all['data']);
                @$fb_page_posts_temp = str_replace("https://graph.facebook.com/v2.5/", "", $response_all["paging"]['next']);
            } while (!empty($response_all["paging"]['next']));

            return $page_post_likes_num;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }
//=================================保存粉丝专页的posts 数据至文本=======================================================
    public function save_posts_array_into_txt($array, $file_folder, $file_name, $write_type = "a")
    {
        $this->createFolder(dirname(__FILE__) . "\\" . $file_folder);
        $handle_CF = fopen(dirname(__FILE__) . "\\" . $file_folder . "\\" . $file_name . ".txt", $write_type);
        foreach ($array as $array_element) {
            if(!@empty($array_element['message'])){
                fwrite($handle_CF,$array_element['id'] ." ".$array_element['created_time']. "\r\n");
                $this->save_into_txt($array_element['message'], $file_folder."\\content", $array_element['id'], $write_type = "w");
            }
        }
        fclose($handle_CF);
    }
//=================================保存数组数据至文本=======================================================
    public function save_array_into_txt($array, $file_folder, $file_name, $write_type = "a",$has_key =true)
    {
        $this->createFolder(dirname(__FILE__) . "\\" . $file_folder);
        $handle_CF = fopen(dirname(__FILE__) . "\\" . $file_folder . "\\" . $file_name . ".txt", $write_type);
       if($has_key==true){
           foreach ($array as $key => $value) {
               fwrite($handle_CF,$key ." ".$value. "\r\n");
           }
       }
        fclose($handle_CF);
    }
//===================================================字符串写入文本文件=================================================
    public function save_into_txt($content, $file_folder, $file_name, $write_type = "w")
    {
        $this->createFolder(dirname(__FILE__) . "\\" . $file_folder);
        $handle_CF = fopen(dirname(__FILE__) . "\\" . $file_folder . "\\" . $file_name . ".txt", $write_type);
        fwrite($handle_CF, $content);
        fclose($handle_CF);
    }
//==================================================创建文件夹==========================================================
// 创建文件夹
    public function createFolder($path)
    {
        if (!file_exists($path)) {
            $this->createFolder(dirname($path));
            mkdir($path, 0777);
        }
    }
 //===========================================FaceBook 初始配置==========================================================
    public function init_config()
    {
        $accessToken = $_SESSION['fb_access_token'];
        $config = array(
            'app_id' => '1669303983347467',
            'app_secret' => 'f619732cca08aabeaf23ff8d12bf32b3',
            'default_graph_version' => 'v2.5'
        );
        $fb = new Facebook\Facebook($config);

        // OAuth 2.0 client handler
        $oAuth2Client = $fb->getOAuth2Client();

        // Exchanges a short-lived access token for a long-lived one
        $longLivedAccessToken = $oAuth2Client->getLongLivedAccessToken($accessToken);

        return array(
            "config" => $config,
            "accessToken" => $longLivedAccessToken
        );
    }
//======================================================================================================================


//=================================FaceBook Page Top posts with top comments============================================
    public function save_page_top_posts_top_comments()
    {
        $page_top_posts = $this->save_page_posts_data();
        $posts_top_count = 10;
        $i = 1;
        foreach ($page_top_posts as $key => $value) {
            if ($posts_top_count > $i) {
                $article_content = $this->get_page_post_content($key);
                $article_comments = $this->get_page_posts_top_comments($key);
                $this->save_into_txt($key . " " . $article_content['message'] . " " . $article_content['created_time'] . "\r\n", $file_folder = "result_file", $key, "a");
                foreach ($article_comments as $article_comment) {
                    $this->save_into_txt($article_comment[0] . " " . $article_comment[1] . " " . $article_comment[2] . "\r\n", $file_folder = "result_file", $key, "a");
                }
                $i++;
            } else {
                break;
            }
        }
    }

//======================================================================================================================
    public function get_page_post_content($fb_page_post_id)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);
        try {
            $response = $fb->get($fb_page_post_id, $init_config['accessToken']);
            $response_all = $response->getDecodedBody();
            return $response_all;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }




//=================================FaceBook Page Posts的Message和 Top Number的comments==================================
    public function get_post_content($fb_page_post_content, $fb_page_post_id, $max_count)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);

        try {
            $fb_page_posts_init = $fb_page_post_id . "/comments?limit=100";
            $fb_page_posts_temp = $fb_page_posts_init;

            for ($i = 1; $i <= $max_count; $i++) {
                //var_dump($i);
                $response = $fb->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all = $response->getDecodedBody();
                $page_post_comments_array[] = $response_all['data'];
                //var_dump(count(  $page_post_comments_array));
                if (!empty($response_all["paging"]['next'])) {
                    $fb_page_posts_temp = str_replace("https://graph.facebook.com/v2.5/", "", $response_all["paging"]['next']);
                } else {
                    break;
                }
                //var_dump($response_all["paging"]);
            }
            foreach ($page_post_comments_array as $page_post_comments) {
                foreach ($page_post_comments as $page_post_comment) {
                    $fb_page_post_content = $fb_page_post_content . " " . $page_post_comment['message'];
                }
            }
            $this->save_into_txt($fb_page_post_content, $file_folder = "original_file", $fb_page_post_id);
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

//=================================FaceBook 粉丝专页 Top Number 的Posts=================================================
    public function get_page_posts($fb_page_id, $max_count)
    {
        $init_config = $this->init_config();
        $fb = new Facebook\Facebook($init_config['config']);
        $page_posts = array();
        try {
            $fb_page_posts_init = $fb_page_id . "/posts?limit=100";
            $fb_page_posts_temp = $fb_page_posts_init;

            for ($i = 1; $i <= $max_count; $i++) {
                $response = $fb->get($fb_page_posts_temp, $init_config['accessToken']);
                $response_all = $response->getDecodedBody();
                $page_posts[] = $response_all['data'];
                if (!empty($response_all["paging"]['next'])) {
                    $fb_page_posts_temp = str_replace("https://graph.facebook.com/v2.5/", "", $response_all["paging"]['next']);
                } else {
                    break;
                }
            }
            return $page_posts;
        } catch (Facebook\Exceptions\FacebookResponseException $e) {
            echo 'Graph returned an error: ' . $e->getMessage();
            exit;
        } catch (Facebook\Exceptions\FacebookSDKException $e) {
            echo 'Facebook SDK returned an error: ' . $e->getMessage();
            exit;
        }
    }

}
