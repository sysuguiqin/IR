<?php

error_reporting( E_ALL & ~E_STRICT );
require_once './pscws4/pscws4.class.php';

class  IR_Functions{
//========================================统计各主题的热议程度==========================================================
 public function statics_process(){
     $original_files = $this ->  get_original_files( $file_path="original_file\\posts\\content");
     $original_files = $original_files[ "file_only_name"];
     $is_first=true;

     $hospitals = $this ->get_file_content("\\tool_file\\000.txt");
     $hospital_statics= array();
     $medical_field1_keywords = $this ->get_file_content("\\tool_file\\001.txt");
     $medical_field1_statics=array();
     $medical_field2_keywords = $this ->get_file_content("\\tool_file\\002.txt");
     $medical_field2_statics=array();
     $medical_field3_keywords = $this ->get_file_content("\\tool_file\\003.txt");
     $medical_field3_statics=array();
     $medical_field4_keywords = $this ->get_file_content("\\tool_file\\004.txt");
     $medical_field4_statics=array();
     $medical_field5_keywords = $this ->get_file_content("\\tool_file\\005.txt");
     $medical_field5_statics=array();
     $files_weight=array();
     for($i=1;$i<53;$i++){
         $files_weight = $this -> get_page_posts_influence("\\result_file\\page_post_influence_detail".$i.".txt", $files_weight);
     }


     foreach($original_files as $original_file){
         //var_dump($original_file);
         $file=dirname(__FILE__)."\\".$file_path."\\".$original_file.".txt";
         $file_content=file_get_contents($file);

         //var_dump( $file_content);
         //$this -> chinese_split($file_content);
         if($is_first == true){
             //医院 热议度统计
             $hospital_statics= $this -> hospital_statics_process($is_first,$file_content,$files_weight[$original_file],$hospitals,$hospital_statics);
             //医疗主题1 热议度
             $medical_field1_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field1_keywords,$medical_field1_statics);
             //医疗主题2 热议度
             $medical_field2_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field2_keywords,$medical_field2_statics);
             //医疗主题3 热议度
             $medical_field3_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field3_keywords,$medical_field3_statics);
             //医疗主题4 热议度
             $medical_field4_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field4_keywords,$medical_field4_statics);
             //医疗主题5 热议度
             $medical_field5_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field5_keywords,$medical_field5_statics);
             $is_first=false;
         }else{
             //医院 热议度统计
             $hospital_statics= $this -> hospital_statics_process($is_first,$file_content,$files_weight[$original_file],$hospitals,$hospital_statics);
             //医疗主题1 热议度
             $medical_field1_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field1_keywords,$medical_field1_statics);
             //医疗主题2 热议度
             $medical_field2_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field2_keywords,$medical_field2_statics);
             //医疗主题3 热议度
             $medical_field3_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field3_keywords,$medical_field3_statics);
             //医疗主题4 热议度
             $medical_field4_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field4_keywords,$medical_field4_statics);
             //医疗主题5 热议度
             $medical_field5_statics=$this ->medical_field_statics_process($is_first,$file_content,$files_weight[$original_file],$medical_field5_keywords,$medical_field5_statics);
         }
     }
     //
     arsort($hospital_statics);
     $this->save_array_into_txt($hospital_statics,"result_file","hospital");
     arsort( $medical_field1_statics);
     $this->save_array_into_txt( $medical_field1_statics,"result_file","medical_field1");
     arsort( $medical_field2_statics);
     $this->save_array_into_txt( $medical_field2_statics,"result_file","medical_field2");
     arsort( $medical_field3_statics);
     $this->save_array_into_txt( $medical_field3_statics,"result_file","medical_field3");
     arsort( $medical_field4_statics);
     $this->save_array_into_txt( $medical_field4_statics,"result_file","medical_field4");
     arsort( $medical_field5_statics);
     $this->save_array_into_txt( $medical_field5_statics,"result_file","medical_field5");

     $keywords =array();
     $keywords=$this ->get_medical_field_top_keywords(5,$keywords,$medical_field1_statics);
     $keywords=$this ->get_medical_field_top_keywords(5,$keywords,$medical_field2_statics);
     $keywords=$this ->get_medical_field_top_keywords(5,$keywords,$medical_field3_statics);
     $keywords=$this ->get_medical_field_top_keywords(5,$keywords,$medical_field4_statics);
     $keywords=$this ->get_medical_field_top_keywords(5,$keywords,$medical_field5_statics);
     $this->save_array_into_txt($keywords,"result_file","medical_field_keywords");


 }
//========================================筛选出敏感词汇=============================================================
    public function get_medical_field_top_keywords($top_num,$keywords,$array){
        $i=1;
        foreach($array as $key => $value){
            if($i<=$top_num){
                if( $key!="TOTAL_AMOUNT"){
                     if(@empty($keywords[ $key])){
                         $keywords[ $key]=$value;
                     }else{
                         $keywords[ $key]+=$value;
                     }
                    $i+=1;
                }
            }else{
                break;
            }
        }
        return $keywords;
    }
//========================================醫療領域 分類统计=============================================================
    public function medical_field_statics_process($is_first,$article_content,$article_weight,$medical_field_keywords,$medical_field_statics){
        if($is_first == true){
            $total_amount = 0;
            foreach($medical_field_keywords as $medical_field_keyword){
                
                if(@strpos($article_content, trim($medical_field_keyword))==false){
                    $medical_field_statics[trim($medical_field_keyword)]=0;
                }else{
					if(@empty($article_weight["influence_total"])){
						@$medical_field_statics[trim($medical_field_keyword)]=1*1;
						@$total_amount += 1*1;
					}else{
						@$medical_field_statics[trim($medical_field_keyword)]=1*$article_weight["influence_total"];
						@$total_amount += 1*$article_weight["influence_total"];
					}
                    
                }
            }
            $medical_field_statics["TOTAL_AMOUNT"]=$total_amount;
        }else{
            foreach($medical_field_keywords as $medical_field_keyword){
                if(@strpos($article_content, trim($medical_field_keyword))==false){
                    $medical_field_statics[trim($medical_field_keyword)] +=0;
                }else{
					if(@empty($article_weight["influence_total"])){
						@$medical_field_statics[trim($medical_field_keyword)] +=1*1;
						@$medical_field_statics["TOTAL_AMOUNT"] += 1*1;
					}else{
						@$medical_field_statics[trim($medical_field_keyword)] +=1*$article_weight["influence_total"];
						@$medical_field_statics["TOTAL_AMOUNT"] +=1*$article_weight["influence_total"];
					}
                    
                }
            }
        }
        return $medical_field_statics;
    }
//========================================医院 统计=====================================================================
   public function hospital_statics_process($is_first,$article_content,$article_weight,$hospitals,$hospital_statics){
       if($is_first == true){
		   
           foreach($hospitals as $hospital){
			   
               if(@strpos($article_content, trim($hospital))==false){
				   
                   $hospital_statics[trim($hospital)]=0;
               }else{
				   
					if(@empty($article_weight["influence_total"])){
						$hospital_statics[trim($hospital)]= 1*1;
					}else{
						$hospital_statics[trim($hospital)]= 1*$article_weight["influence_total"];
					}
                   
               }
           }
       }else{
           foreach($hospitals as $hospital){
               if(@strpos($article_content, trim($hospital))==false){
                   $hospital_statics[trim($hospital)] +=0;
               }else{
				   if(@empty($article_weight["influence_total"])){
						$hospital_statics[trim($hospital)]+= 1*1;
					}else{
						$hospital_statics[trim($hospital)] += 1*$article_weight["influence_total"];
					}               
               }
           }
       }
       return $hospital_statics;
   }
//=======================================获取文本内容===================================================================
   public function get_file_content($file_path){
       $file_path=dirname(__FILE__).$file_path;
       $file_content = array();
       if(file_exists($file_path)){
           $file_content=file($file_path);//读取文件内容
           foreach($file_content as $line){
               $line = str_replace(array("\r\n", "\r", "\n"),'',$line);
               $line = explode(" ",$line);
               $file_content[]=trim($line[0]);
           }
            return  $file_content;
       }
   }
//=======================================中文繁体字切词=================================================================
    public function chinese_split( $text){
    // 建立分词类对像, 参数为字符集, 默认为 gbk, 可在后面调用 set_charset 改变
        $pscws = new PSCWS4('utf8');

    // 接下来, 设定一些分词参数或选项, set_dict 是必须的, 若想智能识别人名等需要 set_rule
    // 包括: set_charset, set_dict, set_rule, set_ignore, set_multi, set_debug, set_duality ... 等方法
        $pscws->set_charset('utf8');
        $pscws->set_dict('./pscws4/etc/dict.ct.utf8.xdb');//繁體UTF8 Chinese Traditional
        $pscws->set_rule('./pscws4/etc/rules_cht.utf8.ini');//繁體UTF8
        //$pscws->set_dict('./etc/dict.sc.utf8.xdb');//简体UTF8 simplified Chinese
        //$pscws->set_rule('./etc/rules.utf8.ini');//简体UTF8 simplified Chinese
        //$pscws->set_dict('./etc/dict.sc.gbk.xdb');//简体GBK  simplified Chinese
        //$pscws->set_rule('./etc/rules.ini');//简体GBK  simplified Chinese

    // 分词调用 send_text() 将待分词的字符串传入, 紧接着循环调用 get_result() 方法取回一系列分好的词
    // 直到 get_result() 返回 false 为止
    // 返回的词是一个关联数组, 包含: word 词本身, idf 逆词率(重), off 在text中的偏移, len 长度, attr 词性
        $pscws->send_text($text);
        while ($some = $pscws->get_result())
        {
            foreach ($some as $word)
            {
                //文章词组处理
                var_dump($word);
            }
        }

    // 在 send_text 之后可以调用 get_tops() 返回分词结果的词语按权重统计的前 N 个词
    // 常用于提取关键词, 参数用法参见下面的详细介绍.
    // 返回的数组元素是一个词, 它又包含: word 词本身, weight 词重, times 次数, attr 词性
        //$tops = $pscws->get_tops(10, 'n,v');
        //var_dump($tops);
    }
//========================================获取page posts的影响力系数====================================================
    public function get_page_posts_influence( $file_path, $page_posts_influence){
        $file_path=dirname(__FILE__).$file_path;

        if(file_exists($file_path)){
            $file_content=file($file_path);//读取文件内容
            foreach($file_content as $line){
                $line = str_replace(array("\r\n", "\r", "\n"),'',$line);
                $line = explode(" ",$line);
                // var_dump($line);
                @$page_posts_influence[trim($line[0])]["likes_num"]=trim($line[1]);
                @$page_posts_influence[trim($line[0])]["comments_num"]=trim($line[2]);
                @$page_posts_influence[trim($line[0])]["shares_num"]=trim($line[3]);
                @$page_posts_influence[trim($line[0])]["influence_total"]=trim($line[4]);
            }
            //var_dump( $doc_terms_CF);
            return  $page_posts_influence;
        }
    }
//========================================获取指定文件夹下的所有文件====================================================
    public function get_original_files( $file_path){
        $dir=dirname(__FILE__)."\\".$file_path; //指定文件夹

        $origin_files_array=array();
        $handle=opendir($dir.".");//遍历文件夹下所有文件
        while (false !== ($file = readdir($handle)) )
        {
            if ($file != "." && $file != "..") {
                $origin_files_array[] = $file;
                $origin_only_file_name[] = str_replace('.txt','',$file);
            }
        }
        closedir($handle);
        sort($origin_only_file_name);
        return  array(
            "file_with_style" => $origin_files_array ,
            "file_only_name" => $origin_only_file_name
        );
    }
//====================================================key => value 数组写入文件，保存格式为 key value===================
    public function save_array_into_txt($array,$file_folder,$file_name){
        $this->createFolder(dirname(__FILE__) . "\\".$file_folder);
        $handle_CF=fopen(dirname(__FILE__) . "\\".$file_folder."\\".$file_name.".txt","a");
        foreach($array as $key => $value){
            $line = $key." ". $value."\r\n";
            fwrite( $handle_CF,$line);
        }
        fclose( $handle_CF);
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
//==================================================创建文件夹==========================================================
    public function deal_with_file(){
        $hospitals = $this ->get_file_content("\\tool_file\\05.txt");
        $handle_CF=fopen(dirname(__FILE__) . "\\tool_file\\005.txt","a");
        foreach($hospitals as $hospital){
            $hospital= str_replace(array("\r\n", "\r", "\n"),'',$hospital);
            $hospital=trim($hospital);
            $line = $hospital."\r\n";
            fwrite( $handle_CF,$line);
        }
        fclose( $handle_CF);

    }
}

