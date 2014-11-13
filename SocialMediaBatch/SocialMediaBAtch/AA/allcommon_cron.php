<?php
ob_start();
error_reporting(0);
ini_set('default_charset', 'utf-8');
header("Content-Type: text/html; charset=UTF-8");
//error_reporting(E_ERROR);
require_once('./config.php');
require_once('./Rss.minified.php'); // include library

$username = DB_USERNAME;
$password = DB_PASSWORD;
$hostname = DB_SERVER;
$database = DB_DATABASE;

$con = mysql_connect($hostname, $username, $password) or die("Unable to connect to MySQL");
mysql_query("SET NAMES utf8");
$selected_db = mysql_select_db($database, $con) or die("Could not select Database");

# LINKEDIN API CRON [START]
define('COUNT_SIZE', 25);
require_once('./functions.php');

if(LINKEDINCRONSTART == 1){

        $error_log_str = '';
        echo "<BR><B>LINKEDIN CRON STARTED @</B> : ".date('Y-m-d H:i:s A');
        $user_ids = array();
        $auto_ids = array();

        #FETCH ALL USERS ID [START]
        $fields = array('DISTINCT user_id', 'id as user_auto_id', 'access_token');
        $result = getData('tbl_linkedin_user', $fields, NULL);
        while ($row = mysql_fetch_array($result)) {
            if (!empty($row['user_id']) && !empty($row['user_id'])) {
                $user_ids[] = $row['user_id'];
                $auto_ids[] = $row['user_auto_id'];
                $access_tokens[] = $row['access_token'];
            }
        }
        #FETCH ALL USERS ID [END]
        $total_user_ids = count($user_ids);

        if ($total_user_ids) {
            for ($i = 0; $i < $total_user_ids; $i++) {

                $user_auto_id = $auto_ids[$i];
                #FETCH ALL USER's KEYWORDS [START]
                $fields = array('id', 'name');
                $where = array('user_auto_id' => $user_auto_id);
                $keyword_result = getData('tbl_linkedin_keywords', $fields, $where);
                #FETCH ALL USER's KEYWORDS [END]

                $keywords = NULL;
                while ($row = mysql_fetch_array($keyword_result)) {

                    if (!empty($row['name'])) {
                        $keyword_id = $row['id'];
                        $keyword_name = $row['name'];

                        $where = array('keyword_id' => $keyword_id, 'user_auto_id' => $user_auto_id);
                        if (getRowCount('tbl_linkedin_search_user_data', $where) > 0) {
                            # DELETE KEYWORD SEARCH DATA FOR SAME USER
                            deleteData('tbl_linkedin_search_user_data', $where);
                        }

                        $search_keyword_str = "keywords=" . $keyword_name;
                        #GET LIVE USER DATA [CRON-START]
                        unset($_SESSION['access_token']);
                        $_SESSION['access_token'] = $access_tokens[$i];

                        $search_data = fetch('GET', "/v1/people-search:(people:(id,first-name,last-name,picture-url,headline),num-results)", $search_keyword_str);

                        # CREATE ERROR LOG [START]
                        if ($search_data->errorCode == '0' && !empty($search_data->message)) {
                            $err_user = array('keyword_id' => $keyword_id, 'user_auto_id' => $user_auto_id);
                            $error_log_str .= addErrorLog($search_data, $err_user);
                        } else {

                            if ($search_data->people->_total > 0 && !empty($search_data->people->values)) {
                                foreach ($search_data->people->values as $people_profile) {

                                    $people_profile_data = NULL;
                                    $photo = "http://s.c.lnkd.licdn.com/scds/common/u/img/icon/icon_no_photo_80x80.png";
                                    if (!empty($people_profile->pictureUrl)) {
                                        $photo = trim($people_profile->pictureUrl);
                                    }


                                    # GET SINGLE PEOPLE DATA [START]
                                    $people_profile_data['people_id'] = remove_spacial_1(remove_spacial(clean_insert($people_profile->id)));
                                    $people_profile_data['first_name'] = remove_spacial_1(remove_spacial(clean_insert($people_profile->firstName)));
                                    $people_profile_data['last_name'] = remove_spacial_1(remove_spacial(clean_insert($people_profile->lastName)));
                                    $people_profile_data['picture_url'] = remove_spacial_1(remove_spacial(clean_insert($people_profile->pictureUrl)));
                                    $people_profile_data['headline'] = remove_spacial_1(remove_spacial(clean_insert($people_profile->headline)));

                                    # GET SINGLE PEOPLE DATA [END]		
                                    $people_profile_datas[] = $people_profile_data; # COLLECT ALL PEOPLE DATA
                                }

                                # PREPARE SEACH DATA ARRAY TO STORE [START]
                                $el = array();
                                foreach ($people_profile_datas as &$el) {
                                    $el['keyword_id'] = $keyword_id;
                                    //$el['search_opt'] = $search_opt;			
                                    $el['user_auto_id'] = $user_auto_id;
                                    $el['updated_date'] = date('Y-m-d H:i:s');
                                }

                                if (!empty($people_profile_datas) && is_multidimention_array($people_profile_datas)) {
                                    foreach ($people_profile_datas as $row) {
                                        # ADD NEW KEYWORD SEARCH DATA [KEYWORD/PEOPLE/COMPANY]
                                        addData('tbl_linkedin_search_user_data', $row);
                                    }
                                }
                            }
                        }
                        # CREATE ERROR LOG [END]
                        #GET LIVE USER DATA [CRON-END]
                        #GET LIVE COMPANY PROFILE DATA [CRON-START]
                        $search_data = fetch('GET', "/v1/company-search:(companies:(id,name,logo_url,website-url,employee-count-range,specialties,locations,description,founded-year,end-year,num-followers))", $search_keyword_str);

                        # CREATE ERROR LOG [START]
                        if ($search_data->errorCode == '0' && !empty($search_data->message)) {
                            $error_log_str .= addErrorLog($search_data);
                        } else {

                            foreach ($search_data->companies->values as $company_profile) {
                                $photo = "http://s.c.lnkd.licdn.com/scds/common/u/img/icon/icon_no_photo_80x80.png";
                                if (!empty($company_profile->logoUrl)) {
                                    $photo = trim($company_profile->logoUrl);
                                }

                                # GET SINGLE COMPANY DATA [START]
                                $company_profile_data = NULL;
                                $company_profile_data['logo_url'] = $company_profile->logoUrl;
                                $company_profile_data['company_id'] = $company_profile->id;
                                $company_profile_data['name'] = remove_spacial_1(remove_spacial(clean_insert($company_profile->name)));
                                $company_profile_data['website'] = $company_profile->websiteUrl;

                                $address = doImplode($company_profile->locations->values);
                                $location = implode(",", $address);
                                $company_profile_data['address'] = remove_spacial_1(remove_spacial(clean_insert($location)));
                                $company_profile_data['followers'] = $company_profile->numFollowers;

                                # GET SINGLE COMPANY DATA [END]		
                                $company_profile_datas[] = $company_profile_data; # COLLECT ALL COMPANY DATA
                            }


                            $where = array('keyword_id' => $keyword_id, 'user_auto_id' => $user_auto_id);
                            if (getRowCount('tbl_linkedin_search_company_data', $where) > 0) {
                                # DELETE COMPANY DATA FOR SAME USER
                                deleteData('tbl_linkedin_search_company_data', $where);
                            }

                            $el = array();
                            foreach ($company_profile_datas as &$el) {
                                $el['keyword_id'] = $keyword_id;
                                $el['search_opt'] = '';			//$search_opt
                                $el['user_auto_id'] = $user_auto_id;
                                $el['updated_date'] = date('Y-m-d H:i:s');
                            }

                            if (is_multidimention_array($company_profile_datas)) {
                                foreach ($company_profile_datas as $row) {
                                    # ADD NEW KEYWORD SEARCH DATA [COMPANY PROFILE]
                                    addData('tbl_linkedin_search_company_data', $row);
                                }
                            }
                        }
                        # CREATE ERROR LOG [END]
                        #GET LIVE COMPANY PROFILE DATA [CRON-END]
                    }
                }
            }
        }

        if (!empty($error_log_str)) {

            echo "<BR>ERROR LOG: [START]<BR>";
            echo "<BR><FONT COLOR='RED'>";
            echo $error_log_str;
            echo "</FONT>";
            echo "<BR>ERROR LOG: [END]";
        } else {
            echo "<BR><FONT COLOR='GREEN'><B>CRON PERFORMED SUCCESSFULLY</B></FONT>";
        }
        echo "<BR><B>LINKEDIN CRON STARTED @</B> : ".date('Y-m-d H:i:s A');
        # LINKEDIN API CRON [END]
}
echo "<BR><BR>";

# YOUTUBE API CRON [START]
set_time_limit(0);

$start_index = 1;
$max_result = 25; //25
$current_date = date('Y-m-d h:i:s');
if(YOUTUBECRONSTART == '1'){
        echo "<BR><B>YOUTUBE CRON STARTED @</B> : ".date('Y-m-d H:i:s A');
        #FETCH ALL KEYWORDS [START]
        $fields = array('DISTINCT name', 'id as keyword_auto_id');
        $result = getData('tbl_keywords', $fields, NULL);
        while ($row = mysql_fetch_array($result)) {
            if (!empty($row['name'])) {
                $keywords_arr[] = $row['name'];
                $keyword_ids_arr[] = $row['keyword_auto_id'];
            }
        }
        #FETCH ALL KEYWORDS [END]
        $total_keyword_ids = count($keyword_ids_arr);

        if ($total_keyword_ids) {
            for ($i = 0; $i < $total_keyword_ids; $i++) {

                $keyword_id = $keyword_ids_arr[$i];
                                        $keyword_name = $keywords_arr[$i];

                #CHECK CRON ALREADY EXECUTED TODAY? [START]

                $where = array('keyword_id' => $keyword_id, 'updated_date' => date('Y-m-d'));
                if (getRowCount('tbl_youtube_cron_log', $where) > 0) {
                    //echo "<BR><FONT COLOR='RED'>CRON ALREADY EXECUTED TODAY FOR <b>'" . $keyword_name . "'</b> KEYWORD</FONT><BR>";
                } else {
                    $youtube_cron_log_data['keyword_id'] = $keyword_id;
                    $youtube_cron_log_data['updated_date'] = date('Y-m-d');
                    $cron_log_id = addData('tbl_youtube_cron_log', $youtube_cron_log_data);
                }
                #CHECK CRON ALREADY EXECUTED TODAY? [END]
                #FETCH ALL USER's KEYWORDS [START]
                $fields = array('id as video_auto_id', 'updated_date');
                $where = array('keyword_id' => $keyword_id);
                $video_info_result = getData('tbl_youtube_video_info', $fields, $where);
                #FETCH ALL USER's KEYWORDS [END]
                $video_info_result_count = mysql_num_rows($video_info_result);

                if ($updated_date < date('Y-m-d')) {

                    # CRON [START]
                    $keyword_name = str_replace(' ', '+', $keyword_name);
                    $url = "http://gdata.youtube.com/feeds/api/videos?q=" . $keyword_name . "&v=2&max-results=" . $max_result . "&alt=json";
                    $output_json = _curl($url);
                    $youtube_data = json_decode($output_json);
                    # CRON [END]
                                                        $entries = $youtube_data->feed->entry;
                    if (!empty($entries) && $video_info_result_count == 0) {
                        #GET ALL FEED ENTRIES [START]

                                                                        $counter = 1;
                                                                        $youtube_info_data = array();
                                                                        $i=1;

                        foreach ($entries as $ek => $ev) {

                            $t = $ev->id->{'$t'};
                            $t = explode(',', $t);
                            $t1 = explode('video:', $t[1]);
                            $video_id = $t1[1]; #CURRENT VIDEO ID

                            $where = array('keyword_id' => $keyword_id, 'video_id' => $video_id);
                            if (!getRowCount('tbl_youtube_video_info', $where)) {

                                //echo "<BR>($i) => NEW VIDEO ADDED FOR <b>'" . $keyword_name . "'</b> KEYWORD<BR>";

                                $published_date = date('Y-m-d H:i:s', strtotime($ev->published->{'$t'}));

                                # TO STORE VIDEO INFO DATA [START]
                                $youtube_info_data['video_id'] = $video_id;
                                $youtube_info_data['video_title'] = remove_spacial_1(remove_spacial(clean_insert($ev->title->{'$t'})));
                                $youtube_info_data['video_category'] = remove_spacial_1(remove_spacial(clean_insert($ev->category[1]->label)));
                                $youtube_info_data['published_date'] = $published_date;
                                $youtube_info_data['video_url'] = mysql_real_escape_string($ev->link[0]->href);
                                $youtube_info_data['keyword_id'] = $keyword_id;
                                $youtube_info_data['created_date'] = $current_date;
                                $youtube_info_data['updated_date'] = $current_date;
                                # TO STORE VIDEO INFO DATA [END]
                                # ADD NEW VIDEO DATA
                                $video_info_id = addData('tbl_youtube_video_info', $youtube_info_data);
                            } else {
                                $video_info_id = $video_info_result['video_info_id'];
                            }

                            # COMMENT CRON [START]
                            if(!empty($ev->{'gd$comments'}->{'gd$feedLink'}->href)){
                             $comment_url = $ev->{'gd$comments'}->{'gd$feedLink'}->href . '&max-results=' . $max_result . '&alt=json';
                            $output_json = _curl($comment_url);
                            $comment_data = json_decode($output_json);

                            # COMMENT CRON [END]

                            $comments_entry = $comment_data->feed->entry;
                            $j=1;
                            $comment_user_name='';
                            foreach ($comments_entry as $ck => $cv) {

                                if (!empty($cv->author[0]->{'uri'}->{'$t'})) {
                                    $get_comment_user_id = end(explode('/', $cv->author[0]->{'uri'}->{'$t'}));
                                    $comment_user_id = "http://www.youtube.com/user/" . $get_comment_user_id;
                                    $comment_user_name = remove_spacial_1(remove_spacial(clean_insert($cv->author[0]->{'name'}->{'$t'})));
                                }

                                $comments_published_date = date('Y-m-d H:i:s', strtotime($cv->published->{'$t'}));
                                $comment = remove_spacial_1(remove_spacial(clean_insert($cv->content->{'$t'})));

                                # TO STORE VIDEO COMMENTS DATA [START]
                                $youtube_comment_data = array();
                                $youtube_comment_data['comment_text'] = $comment;
                                $youtube_comment_data['posted_user_url'] = $comment_user_id;
                                $youtube_comment_data['posted_user_name'] = $comment_user_name;
                                $youtube_comment_data['posted_date'] = $comments_published_date;
                                # TO STORE VIDEO COMMENTS DATA [END]

                                unset($where);
                                $where = array('video_info_id' => $video_info_id, 'posted_date' => $comments_published_date);
                                if (!getRowCount('tbl_youtube_video_comments', $where)) {
                                    $youtube_comment_data['video_info_id'] = $video_info_id;
                                    addData('tbl_youtube_video_comments', $youtube_comment_data);
                                    //echo "<BR>&nbsp;&nbsp;&nbsp;($j) => NEW VIDEO COMMENT ADDED FOR <b>'" . $keyword_name . "'</b> KEYWORD<BR>";
                                }

                                $j++;
                            }}
                            $i++;
                        }
                        #GET ALL FEED ENTRIES [END] 
                    }
                } else {
                    //echo "<BR>CRON ALREADY EXECUTED FOR '" . $keyword_name . "' KEYWORD<BR>";
                }
            }
        }
        echo "<BR><B>YOUTUBE CRON ENDED @</B> : ".date('Y-m-d H:i:s A')."<br><br>";
        # YOUTUBE API CRON [END]
}

if(GOOGLEPLUSCRONSTART == '1'){
            echo "<BR><B>GOOGLEPLUS CRON STARTED @</B> : ".date('Y-m-d H:i:s A');
            $sql = "SELECT * FROM tbl_keywords"; // where id='92'
            //$sql = "SELECT * FROM tbl_keywords";
            $result = mysql_query($sql) or die(mysql_error());
            while ($row = mysql_fetch_array($result)) {
                 $id = $row['id'];
                $keywordName = $row['name'];

                $sql1 = "SELECT * FROM tbl_googleplus_info where keyword_id = '".$id."'";
                $result1 = mysql_query($sql1) or die(mysql_error());
                $count1 = mysql_num_rows($result1);

               if($count1 > "0"){
                    //update code
                    $sql18 = "DELETE FROM tbl_googleplus_info where keyword_id = '".$id."'";
                    $result18 = mysql_query($sql18) or die(mysql_error());
                    $sql19 = "DELETE FROM tbl_googleplus_comments where keyword_id = '".$id."'";
                    $result19 = mysql_query($sql19) or die(mysql_error());

                    $searchName = str_replace(" ","+",$keywordName);
                    $url = "https://www.googleapis.com/plus/v1/activities?query=".$searchName."&maxResults=20&key=".GOOGLEPLUSKEY;
                    //$output_json = $this->curl($url);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt ($ch, CURLOPT_HEADER, 0);
                    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
                    $output_json = curl_exec($ch);
                    curl_close($ch);
                    $finalData = json_decode($output_json);   
                    //echo "<PRE>";print_R($output_json);exit;
                    if(!empty($finalData->items)){
                        foreach ($finalData->items as $data){

                            $sql23 = "INSERT INTO  `tbl_googleplus_info` (   `displayname` ,  `userid`, `title` ,  `content` ,  `plus1count` ,  `commentcount` ,  `resharescount` ,  `objecttype` ,  `url` ,  `publish` ,  `keyword_id` ) 
                                        VALUES (
                                          '".remove_spacial_1(remove_spacial(clean_insert($data->actor->displayName)))."',
                                                                                                                                                                    '0',
                                                                                                                                                                    '".remove_spacial_1(remove_spacial(clean_insert($data->title)))."',
                                                                                                                                                                    '".remove_spacial_1(remove_spacial(clean_insert($data->object->content)))."',
                                                                                                                                                                    '".$data->object->plusoners->totalItems."',
                                                                                                                                                                    '".$data->object->replies->totalItems."',
                                                                                                                                                                    '".$data->object->resharers->totalItems."',
                                                                                                                                                                    '".$data->object->objectType."',
                                                                                                                                                                    '".$data->url."',
                                                                                                                                                                    '".date("Y-m-d H:i:s", strtotime($data->published))."',
                                                                                                                                                                    '".$id."'
                                        )";

                            mysql_query($sql23) or die(mysql_error()."==========>17");
                            $lastInsertId = mysql_insert_id();
                            if($data->object->replies->totalItems != "0"){
                                $url = "https://www.googleapis.com/plus/v1/activities/".$data->id."/comments?key=".GOOGLEPLUSKEY;
                                //$output_json1 = _curl($url);
                                $ch = curl_init();
                                curl_setopt($ch, CURLOPT_URL, $url);
                                curl_setopt ($ch, CURLOPT_HEADER, 0);
                                curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                                                                            curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
                                $output_json1 = curl_exec($ch);
                                $finalDataComment = json_decode($output_json1);   
                                if(!empty($finalDataComment->items)){
                                    foreach ($finalDataComment->items as $data){
                                        $sql33 = "INSERT INTO `tbl_googleplus_comments` (`displayname`,`userid`, `content`,`objecttype`,`publish`,`keyword_id`) 
                                                      VALUES ('".remove_spacial_1(remove_spacial(clean_insert($data->actor->displayName)))."',
                                                                                                                                                                                                                    '0',
                                                                                                                                                                                                                    '".remove_spacial_1(remove_spacial(clean_insert($data->object->content)))."',
                                                                                                                                                                                                                    '".$data->object->objectType."',
                                                                                                                                                                                                                    '".date("Y-m-d H:i:s", strtotime($data->published))."',
                                                                                                                                                                                                                    '".$id."')";
                                        mysql_query($sql33) or die(mysql_error()."==========>18");
                                    }
                                }
                            }
                    }
                    }
                    if(!empty($finalData->error)){
                        $sql25 = "INSERT INTO  `tbl_error_log` (reason,message,type,keywordid) 
                                            values ('".$finalData->error->errors->reason."','".$finalData->error->errors->message."','googleplus','".$id."')";
                        mysql_query($sql25) or die(mysql_error());
                    }
                }else{
                    //insert code
                    $searchName = str_replace(" ","+",$keywordName);
                    $url = "https://www.googleapis.com/plus/v1/activities?query=".$searchName."&maxResults=20&key=".GOOGLEPLUSKEY;
                    //$output_json = $this->curl($url);
                    $ch = curl_init();
                    curl_setopt($ch, CURLOPT_URL, $url);
                    curl_setopt ($ch, CURLOPT_HEADER, 0);
                    curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                    curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
                    $output_json = curl_exec($ch);
                    curl_close($ch);
                    $finalData = json_decode($output_json);   
                    //echo "<PRE>";print_R($output_json);exit;
                   if(!empty($finalData->items)){

                   //'".clean_insert($data->title)))))."', 
                        foreach ($finalData->items as $data){
                                    $sql23 = "INSERT INTO  `tbl_googleplus_info` 
                                                                    (   `displayname` ,  `userid`, `title` ,  `content` ,  `plus1count` ,  `commentcount` ,  `resharescount` ,  `objecttype` ,  `url` ,  `publish` ,  `keyword_id` ) 
                                                                                                                                                            VALUES (
                                                                                                                                                                    '".remove_spacial_1(remove_spacial(clean_insert($data->actor->displayName)))."',
                                                                                                                                                                    '0',
                                                                                                                                                                    '".remove_spacial_1(remove_spacial(clean_insert($data->title)))."',
                                                                                                                                                                    '".remove_spacial_1(remove_spacial(clean_insert($data->object->content)))."',
                                                                                                                                                                    '".$data->object->plusoners->totalItems."',
                                                                                                                                                                    '".$data->object->replies->totalItems."',
                                                                                                                                                                    '".$data->object->resharers->totalItems."',
                                                                                                                                                                    '".$data->object->objectType."',
                                                                                                                                                                    '".$data->url."',
                                                                                                                                                                    '".date("Y-m-d H:i:s", strtotime($data->published))."',
                                                                                                                                                                    '".$id."'
                                        )";
                                                //echo $sql23."<br/>";
                                    mysql_query($sql23) or die(mysql_error()."==========>7=====>".$keywordName."====".$id);
                                    $lastInsertId = mysql_insert_id();
                                    if($data->object->replies->totalItems != "0"){
                                        $url = "https://www.googleapis.com/plus/v1/activities/".$data->id."/comments?key=".GOOGLEPLUSKEY;
                                        //$output_json1 = _curl($url);
                                        $ch = curl_init();
                                        curl_setopt($ch, CURLOPT_URL, $url);
                                        curl_setopt ($ch, CURLOPT_HEADER, 0);
                                        curl_setopt ($ch, CURLOPT_RETURNTRANSFER, 1);
                                                                                                            curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
                                        $output_json1 = curl_exec($ch);
                                        $finalDataComment = json_decode($output_json1);   
                                        if(!empty($finalDataComment->items)){
                                            foreach ($finalDataComment->items as $data){
                                                $sql33 = "INSERT INTO `tbl_googleplus_comments` (`displayname`,`userid`, `content`,`objecttype`,`publish`,`keyword_id`) 
                                                              VALUES (
                                                                                            '".remove_spacial_1(remove_spacial(clean_insert($data->actor->displayName)))."',
                                                                                            '0',
                                                                                                                                                                                                                                                    '".remove_spacial_1(remove_spacial(clean_insert($data->object->content)))."',
                                                                                                                                                                                                                                                    '".$data->object->objectType."',
                                                                                                                                                                                                                                                    '".date("Y-m-d H:i:s", strtotime($data->published))."',
                                                                                                                                                                                                                                                    '".$id."')";
                                                mysql_query($sql33) or die(mysql_error()."==========>8");
                                            }
                                        }
                                    }
                            }
                    }
                    if(!empty($finalData->error)){
                        $sql24 = "INSERT INTO  `tbl_error_log` (reason,message,type,keywordid) 
                                            values ('".$finalData->error->errors->reason."','".$finalData->error->errors->message."','googleplus','".$id."')";
                        mysql_query($sql24) or die(mysql_error());
                    }
                }
            }
            echo "<BR><B>GOOGLEPLUS CRON ENDED @</B> : ".date('Y-m-d H:i:s A')."<br><br>";
}

if(WORDPRESSCRONSTART == '1'){
        echo "<BR><B>WORDPRESS CRON START @</B> : ".date('Y-m-d H:i:s A')."<br><br>";

        $sql = "SELECT * FROM wp_blog_url";
        $result = mysql_query($sql) or die(mysql_error());
        $date = date("Y-m-d H:i:s", time());
        while ($row = mysql_fetch_array($result)) {
            $id = $row['id'];
            $blogurl = $row['blogurl'];
            $sql1 = "SELECT * FROM wp_blog_feed where wp_blog_url_id = '".$id."'";
            $result1 = mysql_query($sql1) or die(mysql_error());
            $count1 = mysql_num_rows($result1);

            $Rss = new Rss; // create object
            $blogurl1 = addhttp($blogurl);
            $siteArr = parse_url($blogurl1);
            $host = $siteArr['host'];
            $siteexploArr = explode('.',$host);

            if($count1 >= "10"){
                //update code
                if(!empty($host)){
                    $sFeedURL = $host."/feed";
                    $sValidator = 'http://feedvalidator.org/check.cgi?url=';
                    if( $sValidationResponse = @file_get_contents($sValidator . urlencode($sFeedURL)) ){
                        if( stristr( $sValidationResponse , 'This is a valid RSS feed' ) !== false ){
                           $feed = $Rss->getFeed('http://'.$host.'/feed/', Rss::TXT);
                            if(empty($feed)){
                                $feed = $Rss->getFeed('http://feeds2.feedburner.com/'.$siteexploArr['1'].'/', Rss::TXT);
                                if(!empty($feed)){
                                    $sql44 = "delete from wp_blog_feed where wp_blog_url_id='".$id."'";
                                    mysql_query($sql44);
                                    $sql441 = "delete from wp_blog_feed_comment where wp_blog_url_id='".$id."'";
                                    mysql_query($sql441);
                                    foreach($feed as $item)	{
                                         $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                         $sql = "INSERT INTO `wp_blog_feed` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['description'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                    $feedComment = $Rss->getFeed('http://'.$host.'/comments/feed/', Rss::TXT);
                                    foreach($feedComment as $item)	{
                                        $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                        $sql = "INSERT INTO `wp_blog_feed_comment` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['content'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                }
                            }else{
                                if(!empty($feed)){
                                    foreach($feed as $item)	{
                                         $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                         $sql = "INSERT INTO `wp_blog_feed` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['description'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                    $feedComment = $Rss->getFeed('http://'.$host.'/comments/feed/', Rss::TXT);
                                    foreach($feedComment as $item)	{
                                        $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                        $sql = "INSERT INTO `wp_blog_feed_comment` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['content'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                }
                            }
                        }else{
                            //error entry code here
                            $sql25 = "INSERT INTO  `tbl_error_log` (reason,message,type,keywordid) 
                                        values ('','Url Not valid','wordpress','".$id."')";
                            mysql_query($sql25) or die(mysql_error());
                        }
                    }
                }
            }else{
                //insert code
                if(!empty($host)){
                    $sFeedURL = $host."/feed";
                    $sValidator = 'http://feedvalidator.org/check.cgi?url=';
                    if( $sValidationResponse = @file_get_contents($sValidator . urlencode($sFeedURL)) ){
                        if( stristr( $sValidationResponse , 'This is a valid RSS feed' ) !== false ){
                           $feed = $Rss->getFeed('http://'.$host.'/feed/', Rss::TXT);
                           if(empty($feed)){
                                $feed = $Rss->getFeed('http://feeds2.feedburner.com/'.$siteexploArr['1'].'/', Rss::TXT);
                                if(!empty($feed)){
                                    foreach($feed as $item)	{
                                        $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                        $sql = "INSERT INTO `wp_blog_feed` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['description'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                    $feedComment = $Rss->getFeed('http://'.$host.'/comments/feed/', Rss::TXT);
                                    foreach($feedComment as $item)	{
                                        $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                        $sql = "INSERT INTO `wp_blog_feed_comment` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['content'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                }
                            }else{
                                if(!empty($feed)){
                                    foreach($feed as $item)	{
                                         $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                         $sql = "INSERT INTO `wp_blog_feed` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['description'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                    $feedComment = $Rss->getFeed('http://'.$host.'/comments/feed/', Rss::TXT);
                                    foreach($feedComment as $item)	{
                                        $publishdate = date("Y-m-d H:i:s", strtotime($item['date']));
                                        $sql = "INSERT INTO `wp_blog_feed_comment` (`title`,`description`,`publishdate`,`wp_blog_url_id`,`inserttime`) 
                                                            VALUES ('".remove_spacial_1(remove_spacial(clean_insert($item['title'])))."','".remove_spacial_1(remove_spacial(clean_insert($item['content'])))."','".$publishdate."','".$id."','".$date."');";
                                        mysql_query($sql);
                                    }
                                }
                            }
                        }else{
                            //error entry code here
                            $sql25 = "INSERT INTO  `tbl_error_log` (reason,message,type,keywordid) 
                                        values ('','Url Not valid','wordpress','".$id."')";
                            mysql_query($sql25) or die(mysql_error());
                        }
                    }
                }
            }
        }
        echo "<BR><B>WORDPRESS CRON ENDED @</B> : ".date('Y-m-d H:i:s A')."<br><br>";
}

#FUNCTIONS [START]
function _curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
    $output_json = curl_exec($ch);
	  if($output_json==false){echo 'error>>'.curl_error($ch);exit;}
    curl_close($ch);
    return $output_json;
}

function getRowCount($tbl, $whereParam = array()) {
    $where_sql = ' WHERE ';
    foreach ($whereParam as $k => $v) {
        if (!empty($k)) {
            $where_sql .= $k . "='" . $v . "' AND ";
        }
    }
    $where_sql = rtrim($where_sql, " AND ");

    $result = mysql_query("SELECT * FROM $tbl $where_sql") or die(mysql_error());
    $num_rows = (int) mysql_num_rows($result);
    return $num_rows;
}

function addCommentData($tbl, $data = NULL) {
    $count = count($data['comment_text']);
    for ($i = 0; $i < $count; $i++) {
        $row = array();
        $row['comment_text'] = $data['comment_text'][$i];
        $row['posted_user_url'] = $data['posted_user_url'][$i];
        $row['posted_user_name'] = $data['posted_user_name'][$i];
        $row['posted_date'] = $data['posted_date'][$i];
        $row['video_info_id'] = $data['video_info_id'];
        addData($tbl, $row);
    }
}

function getData($tbl = NULL, $fields = NULL, $where = NULL) {
    $where_sql = '';
    if (!empty($where)) {
        $where_sql = ' WHERE ';
        foreach ($where as $k => $v) {
            if (!empty($k)) {
                $where_sql .= $k . "='" . $v . "' AND ";
            }
        }
        $where_sql = rtrim($where_sql, " AND ");
    }

    $result = null;
    if (!empty($tbl)) {
        $sql = "SELECT " . implode(',', $fields) . " FROM " . $tbl . $where_sql;
        $result = mysql_query($sql) or die(mysql_error());
    }

    return $result;
}

function deleteData($tbl, $where = NULL) {
    $where_sql = ' WHERE ';
    foreach ($where as $k => $v) {
        if (!empty($k)) {
            $where_sql .= $k . "='" . $v . "' AND ";
        }
    }
    $where_sql = rtrim($where_sql, " AND ");

    if (!empty($tbl)) {
        $sql = "DELETE FROM " . $tbl . " " . $where_sql;
        mysql_query($sql) or die(mysql_error());
    }
}

function addData($tbl, $data = NULL) {

    $keys = array_keys($data);
    $keys_string = implode(",", $keys);
    $values = array_values($data);
    $values_string = "'" . implode("','", $values) . "'";
    $sql = "INSERT INTO " . $tbl . " ({$keys_string}) VALUES ({$values_string})";
    mysql_query($sql) or die(mysql_error());
    return mysql_insert_id();
}

function editData($tbl = NULL, $data = array(), $whereParam = array()) {
    if (!empty($data) && !empty($whereParam)) {
        $set_sql = '';
        foreach ($data as $k => $v) {
            if (!empty($k)) {
                $set_sql .= $k . "='" . $v . "',";
            }
        }
        $set_sql = rtrim($set_sql, ",");

        $where_sql = ' WHERE ';
        foreach ($whereParam as $k => $v) {
            if (!empty($k)) {
                $where_sql .= $k . "='" . $v . "' AND ";
            }
        }
        $where_sql = rtrim($where_sql, " AND ");
        if (!empty($set_sql) && !empty($where_sql)) {
            $sql = "UPDATE " . $tbl . " SET " . $set_sql . $where_sql;
            mysql_query($sql) or die(mysql_error());
        }
    }
}

function isKeywordExist($tbl, $keyword) {
    $sql = "SELECT count(*),id FROM " . $tbl . " WHERE name='" . $keyword . "'";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_row($result);
    return $row;
}

function addhttp($url) {
    if (!preg_match("~^(?:f|ht)tps?://~i", $url)) {
        $url = "https://" . $url;
    }
    return $url;
}

function clean_insert($text){
        return mysql_real_escape_string(htmlspecialchars(addslashes(trim($text))));
}

function remove_spacial($str){ $str= iconv('windows-1250', 'utf-8', $str); return $str; }

function remove_spacial_1($str){ $str = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $str); return $str; }
#FUNCTIONS [END]

ob_end_flush();
?>