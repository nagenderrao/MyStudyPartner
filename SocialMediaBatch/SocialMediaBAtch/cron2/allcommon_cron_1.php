<?php

error_reporting(1);
ob_start();
ini_set('default_charset', 'utf-8');
ini_set('display_errors', 1);
session_start();
set_time_limit(0);
define('COUNT_SIZE', 25);
define('TWITTER_COUNT_SIZE', 50);

header("Content-Type: text/html; charset=UTF-8");

//error_reporting(E_ERROR);
require_once('config_second.php');
require_once('twitteroauth/twitteroauth.php');
$username = DB_USERNAME;
$password = DB_PASSWORD;
$hostname = DB_SERVER;
$database = DB_DATABASE;

$con = mysql_connect($hostname, $username, $password) or die("Unable to connect to MySQL");
mysql_query("SET NAMES utf8");
$selected_db = mysql_select_db($database, $con) or die("Could not select Database");

$code = $_REQUEST['code'];
$client_id = FACEBOOK_APP_ID; #Facebook Application Id.
$client_secret = FACEBOOK_APP_SECRET; #Facebook Application Secret Id.

require_once('functions.php');

if (INSTAGRAMCRONSTART == '1') {
    echo "<BR><B>INSTAGRAM CRON START @</B> : " . date('Y-m-d H:i:s A') . "<br><br>";

    $sqlintsa1 = "SELECT * FROM tbl_keywords";
    $resultintsa1 = mysql_query($sqlintsa1) or die(mysql_error());
    while ($row = mysql_fetch_array($resultintsa1)) {
        $project_id = $row['id'];
        $keywordNameintsa1 = trim($row['name']);

        $sql11intsa1 = "SELECT * FROM tbl_instagram_data where project_id = '" . $project_id . "'";
        $result11intsa1 = mysql_query($sql11intsa1) or die(mysql_error());
        $count11intsa1 = mysql_num_rows($result11intsa1);
        
        if ($count11intsa1 > 0) {
            $sql18intsa1 = "DELETE FROM tbl_instagram_data where project_id = '" . $project_id . "'";
            $result18intsa1 = mysql_query($sql18intsa1) or die(mysql_error());

            $searchName = str_replace(" ", "+", $keywordNameintsa1);
            $url = "https://api.instagram.com/v1/users/search?q=" . $searchName . "&access_token=787173102.f6a5d49.faf68ecf7f2e4b54bb86bb58ccd32731";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
            $data = curl_exec($ch);
            curl_close($ch);
            $finalData = json_decode($data);
            foreach ($finalData->data as $data) {
                $sql23insta1 = "INSERT INTO  `tbl_instagram_data` (   `username` ,  `bio`, `website` ,  `profile_picture` ,  `full_name` ,  `id` , `project_id`, `inserttime` ) 
                                VALUES (
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->username))) . "',
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->bio))) . "',
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->website))) . "',
                                        '" . $data->profile_picture . "',
                                        '" . $data->full_name . "',
                                        '" . $data->id . "',
                                        '" . $project_id . "',
                                        '" . date("Y-m-d H:i:s") . "'
                                )";

                mysql_query($sql23insta1) or die(mysql_error() . "==========>sql23insta1");
            }
        } else {
            $searchName = str_replace(" ", "+", $keywordNameintsa1);
            $url = "https://api.instagram.com/v1/users/search?q=" . $searchName . "&access_token=787173102.f6a5d49.faf68ecf7f2e4b54bb86bb58ccd32731";

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_URL, $url);
            curl_setopt($ch, CURLOPT_HEADER, 0);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
            curl_setopt ($ch,CURLOPT_SSL_VERIFYPEER,false);
            $data = curl_exec($ch);
            curl_close($ch);
            $finalData = json_decode($data);

            foreach ($finalData->data as $data) {
                $sql23insta11 = "INSERT INTO  `tbl_instagram_data` (   `username` ,  `bio`, `website` ,  `profile_picture` ,  `full_name` ,  `id` , `project_id`, `inserttime` ) 
                                VALUES (
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->username))) . "',
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->bio))) . "',
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->website))) . "',
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->profile_picture))) . "',
                                        '" . remove_spacial_1(remove_spacial(clean_insert($data->full_name))) . "',
                                        '" . $data->id . "',
                                        '" . $project_id . "',
                                        '" . date("Y-m-d H:i:s") . "'
                                )";
								
                mysql_query($sql23insta11) or die(mysql_error() . "==========>sql23insta11");
            }
        }
    }

    echo "<BR><B>INSTAGRAM CRON ENDED @</B> : " . date('Y-m-d H:i:s A') . "<br><br>";
}

if (TWITTERCRONSTART == '1') { 
    echo "<BR><B>TWITTER CRON START @</B> : " . date('Y-m-d H:i:s A') . "<br><br>";

    $connection = new TwitterOAuth(CONSUMER_KEY, CONSUMER_SECRET, TWITTER_OAUTH_TOKEN, TWITTER_OAUTH_TOKEN_SECRET);
    $searchUrl = $connection->host . "search/tweets." . $connection->format;

    $sqlinstw1 = "SELECT * FROM tbl_keywords";
    $resultinstw1 = mysql_query($sqlinstw1) or die(mysql_error());
    while ($row = mysql_fetch_array($resultinstw1)) {
        $idinstw1 = $row['id'];
        $keywordNameinstw1 = $row['name'];
        $searchkw = str_replace(" ", "+", $keywordNameinstw1);

        $sql11instw1 = "SELECT * FROM tbl_twitter_data where project_id = '" . $idinstw1 . "'";
        $result11instw1 = mysql_query($sql11instw1) or die(mysql_error());
        $count11instw1 = mysql_num_rows($result11instw1);
	$i = 0;
        if ($count11instw1 > 0) {
            $sql18instw1 = "DELETE FROM tbl_twitter_data where project_id = '" . $idinstw1 . "'";
            $result18instw1 = mysql_query($sql18instw1) or die(mysql_error());



            $searchResponse = $connection->oAuthRequest($searchUrl, "GET", array('q' => $searchkw, 'count' => TWITTER_COUNT_SIZE));
            $searchResponse = json_decode($searchResponse);
		
		
            foreach ($searchResponse as $searches) {
                foreach ($searches as $search) {
		
                    if (is_object($search)) {
			$i++;
                        $name = $search->user->name;
                        $screenName = $search->user->screen_name;
                        $tweet = $search->text;
                        $tweetDate = date('Y-m-d H:i:s', strtotime($search->created_at));
                        $insArrTw[] = "(							'" . $idinstw1 . "',
                                                                '" . $screenName . "',
                                                                '" . remove_spacial_1(remove_spacial(clean_insert($name))) . "',
                                                                '" . remove_spacial_1(remove_spacial(clean_insert($tweet))) . "',
                                                                '" . $tweetDate . "')";
                    }
                }
            }
            //$sql23insta1 = "insert into tbl_twitter_data(keywordId,screenName,name,tweet,tweetDate) values" . implode(',', $insArrTw);
            //mysql_query($sql23insta1) or die(mysql_error() . "==========>sql23insta1");
        } else {

            $searchResponse = $connection->oAuthRequest($searchUrl, "GET", array('q' => $searchkw, 'count' => TWITTER_COUNT_SIZE));
            $searchResponse = json_decode($searchResponse);


            foreach ($searchResponse as $searches) {
                foreach ($searches as $search) {
                    if (is_object($search)) {
		
                        $name = $search->user->name;
                        $screenName = $search->user->screen_name;
                        $tweet = $search->text;
                        $tweetDate = date('Y-m-d H:i:s', strtotime($search->created_at));
                        $insArrTw[] = "(							'" . $idinstw1 . "',
                                                '" . $screenName . "',
                                                '" . remove_spacial_1(remove_spacial(clean_insert($name))) . "',
                                                '" . remove_spacial_1(remove_spacial(clean_insert($tweet))) . "',
                                                '" . $tweetDate . "')";

			
                    }
                }
            }

		
        }
    }
	$sql23insta11 = "insert into tbl_twitter_data(project_id,screenName,name,tweet,tweetDate) values" . implode(',', $insArrTw);
        mysql_query($sql23insta11) or die(mysql_error() . "==========>sql23insta11");
    
	
    echo "<BR><B>TWITTER CRON ENDED @</B> : " . date('Y-m-d H:i:s A') . "<br><br>";
}

if (FACEBOOKCRONSTART == '1') {
    $sql = "SELECT user_access_token FROM tbl_facebook_user_info LIMIT 1";
    $result = mysql_query($sql) or die(mysql_error());
    $row = mysql_fetch_row($result);
    $user_access_token = $row[0];

    if (!empty($user_access_token)) {
        $url = "https://graph.facebook.com/oauth/access_token?client_id={$client_id}&client_secret={$client_secret}&grant_type=fb_exchange_token&fb_exchange_token={$user_access_token}";
        $contents = _curl($url);
        parse_str($contents);

        if (!empty($access_token)) {
            $_SESSION['fb_user_access_token'] = $access_token;

            $sql = "UPDATE tbl_facebook_user_info SET user_access_token='" . $access_token . "'";
            mysql_query($sql) or die(mysql_error());

            if (!empty($_SESSION['fb_user_access_token'])) {

                $fb_user_access_token = $_SESSION['fb_user_access_token'];
                $app_access_token = '422579731129315|x24Qa4SJhjKuwfRwEfvIWwOlQbI';

                echo "<BR><B>FACEBOOK CRON STARTED @</B> : " . date('Y-m-d H:i:s A');

                #TRUNCATE [START]
                //mysql_query("TRUNCATE TABLE tbl_keywords") or die(mysql_error());
                /* mysql_query("TRUNCATE TABLE tbl_facebook_user_page_info") or die(mysql_error());
                  mysql_query("TRUNCATE TABLE tbl_facebook_search_by_user_info") or die(mysql_error());
                  mysql_query("TRUNCATE TABLE tbl_facebook_search_by_page_info") or die(mysql_error());
                  mysql_query("TRUNCATE TABLE tbl_facebook_search_by_user_post") or die(mysql_error());
                  mysql_query("TRUNCATE TABLE tbl_facebook_search_by_page_post") or die(mysql_error());
                  mysql_query("TRUNCATE TABLE tbl_facebook_page_post_comments") or die(mysql_error());
                  mysql_query("TRUNCATE TABLE tbl_facebook_user_post_comments") or die(mysql_error()); */
                #TRUNCATE [END]
                //exit;
#FETCH ALL USER's KEYWORDS [START]
                $fields = array('id', 'name');
                $where = array();
                $keyword_result = getData('tbl_keywords', $fields, $where);
#FETCH ALL USER's KEYWORDS [END]

                $keywords = NULL;
                while ($row = mysql_fetch_array($keyword_result)) {

                    if (!empty($row['name'])) {
                        $project_id = $row['id'];
                        $keyword = mysql_real_escape_string($row['name']);
                        $keyword = str_replace(" ", "+", $keyword);

                        # IF keyword exist [START]
                        if (!empty($keyword)) {
# GET USER PAGES DETAIL [START]
                            //echo 'https://graph.facebook.com/me/accounts?access_token=' . $fb_user_access_token . '&limit=30';
                            //exit;
                            $output_json = _curl('https://graph.facebook.com/me/accounts?access_token=' . $fb_user_access_token);

                            $user_page_details = (array) json_decode($output_json);
                            /* print_r($user_page_details);
                              exit; */
                            $user_page_info = array();
                            $i = 0;
                            if (count($user_page_details['data'])>0) {
                                $sql = "REPLACE INTO tbl_facebook_user_page_info (page_id,page_name,page_category,page_access_token)";
                                $sql .=" VALUES ";
                                foreach ($user_page_details['data'] as $val) {
                                    $user_page_info['page_category'][$i] = clean_insert($val->category);
                                    $user_page_info['page_name'][$i] = clean_insert($val->name);
                                    $user_page_info['page_access_token'][$i] = clean_insert($val->access_token);
                                    $user_page_info['page_id'][$i] = clean_insert($val->id);

                                    $sql .="('" . $user_page_info['page_id'][$i] . "',";
                                    $sql .="'" . $user_page_info['page_name'][$i] . "',";
                                    $sql .="'" . $user_page_info['page_category'][$i] . "',";
                                    $sql .="'" . $user_page_info['page_access_token'][$i] . "'),";
                                    $i++;
                                }
                                $sql = rtrim($sql, ",");
                                mysql_query($sql) or die(mysql_error());
                            }
# GET USER PAGES DETAIL [END]
# GET USER SEARCH DETAILS [type=user] [START]
                            $output_json = _curl("https://graph.facebook.com/search?q={$keyword}&type=user&access_token={$fb_user_access_token}&limit=10");
                            $user_search_details = (array) json_decode($output_json);
                            $i = 0;
                            $sql = "REPLACE INTO tbl_facebook_search_by_user_info (profile_id,name,search_type,project_id)";
                            if (count($user_search_details['data'])) {
                                $sql .=" VALUES ";
                                foreach ($user_search_details['data'] as $val) {
                                    $id = $val->id;
                                    $name = clean_insert($val->name);

                                    $sql .="('" . $id . "',";
                                    $sql .="'" . $name . "',";
                                    $sql .="'user',";
                                    $sql .="'" . $project_id . "'),";
                                    $i++;
                                }
                                $sql = rtrim($sql, ",");
                                mysql_query($sql) or die(mysql_error());
                            }

# GET USER SEARCH DETAILS [type=user] [END]
# GET USER PUBLIC POST DETAILS WITH COMMENTS  [type=post] [START]
                            $url = "https://graph.facebook.com/search?q={$keyword}&type=post&access_token={$fb_user_access_token}&limit=10";
                            $output_json = _curl($url);
                            $user_post_search_details = (array) json_decode($output_json);

                            $i = 0;
                            if (count($user_post_search_details['data'])) {

                                $sql = "INSERT INTO tbl_facebook_search_by_user_post (post_id, post_from_category, post_from_name, post_from_id, post_message, post_picture, post_link, post_name, post_caption, post_type, post_description, post_created_time, post_updated_time, search_type, project_id) VALUES ";

                                foreach ($user_post_search_details['data'] as $val) {
                                    $post_id = $val->id;
                                    $post_from_category = clean_insert($val->from->category);
                                    $post_from_name = clean_insert($val->from->name);
                                    $post_from_id = $val->from->id;
                                    $post_message = clean_insert($val->message);
                                    $post_picture = clean_insert($val->picture);
                                    $post_link = clean_insert($val->link);
                                    $post_name = clean_insert($val->name);
                                    $post_caption = clean_insert($val->caption);
                                    $post_type = $val->type;
                                    $post_description = clean_insert($val->description);
                                    $post_created_time = $val->created_time;
                                    $post_updated_time = $val->updated_time;
                                    
                                    # USER POST COMMENTS [START]
                                    $url = "https://graph.facebook.com/{$post_id}/comments?access_token={$fb_user_access_token}&limit=10";
                                    $output_json = _curl($url);
                                    $user_post_comments_details = (array) json_decode($output_json);
                                    $comment_sql = '';
                                    $comments_count = count($user_post_comments_details['data']);

                                    if ($comments_count) {

                                        $comment_sql = "INSERT INTO tbl_facebook_user_post_comments (comment_id, from_name,from_id, message, created_time, like_count, user_likes, user_post_id) VALUES ";
                                        foreach ($user_post_comments_details['data'] as $cv) {
                                            $comment_id = $cv->id;
                                            $comment_from_name = clean_insert($cv->from->name);
                                            $comment_from_id = $cv->from->id;
                                            $comment_message = clean_insert($cv->message);
                                            $comment_like_count = clean_insert($cv->like_count);
                                            $comment_created_time = $cv->created_time;
                                            $comment_user_likes = $cv->user_likes;

                                            $comment_sql .="('" . $comment_id . "',";
                                            $comment_sql .="'" . $comment_from_name . "',";
                                            $comment_sql .="'" . $comment_from_id . "',";
                                            $comment_sql .="'" . $comment_message . "',";
                                            $comment_sql .="'" . $comment_created_time . "',";
                                            $comment_sql .="'" . $comment_like_count . "',";
                                            $comment_sql .="'" . $comment_user_likes . "',";
                                            $comment_sql .="'" . $post_id . "'),";
                                        }
                                        $comment_sql = rtrim($comment_sql, ",");
                                        mysql_query($comment_sql) or die(mysql_error());
                                    }

                                    $sql .="('" . $post_id . "',";
                                    $sql .="'" . $post_from_category . "',";
                                    $sql .="'" . $post_from_name . "',";
                                    $sql .="'" . $post_from_id . "',";
                                    $sql .="'" . $post_message . "',";
                                    $sql .="'" . $post_picture . "',";
                                    $sql .="'" . $post_link . "',";
                                    $sql .="'" . $post_name . "',";
                                    $sql .="'" . $post_caption . "',";
                                    $sql .="'" . $post_type . "',";
                                    $sql .="'" . $post_description . "',";
                                    $sql .="'" . $post_created_time . "',";
                                    $sql .="'" . $post_updated_time . "',";
                                    $sql .="'post',";
                                    $sql .="'" . $project_id . "'),";
                                    $i++;
                                }
                                $sql = rtrim($sql, ",");
                                mysql_query($sql) or die(mysql_error());
                            }

# GET USER PUBLIC POST DETAILS WITH COMMENTS  [type=post] [END]
# GET USER SEARCH DETAILS [type=page] [START]
                            if (count($user_page_info['page_access_token']) > 0) {
                                $page_access_token = $user_page_info['page_access_token'][0];

                                $url = "https://graph.facebook.com/search?q={$keyword}&type=page&access_token={$page_access_token}&limit=10";

                                $output_json = _curl($url);
                                $page_search_details = (array) json_decode($output_json);
                                $i = 0;
                                $sql = "REPLACE INTO tbl_facebook_search_by_page_info (page_id,page_name,page_category,search_type,project_id)";

                                if (count($page_search_details['data'])) {
                                    $sql .=" VALUES ";
                                    foreach ($page_search_details['data'] as $val) {

                                        $id = $val->id;
                                        $name = clean_insert($val->name);
                                        $category = clean_insert($val->category);

                                        $sql .="('" . $id . "',";
                                        $sql .="'" . $name . "',";
                                        $sql .="'" . $category . "',";
                                        $sql .="'page',";
                                        $sql .="'" . $project_id . "'),";
                                        $i++;
                                    }
                                    $sql = rtrim($sql, ",");
                                    mysql_query($sql) or die(mysql_error());
                                }
                            }

# GET PAGE POST DETAILS [type=post] [access_token=PAGE] [START]
                            $url = "https://graph.facebook.com/search?q={$keyword}&type=post&access_token={$user_page_info['page_access_token'][0]}&limit=10";
                            $output_json = _curl($url);
                            $page_post_search_details = (array) json_decode($output_json);

                            $i = 0;

                            if (count($page_post_search_details['data'])) {
                                $sql = "INSERT INTO tbl_facebook_search_by_page_post (post_id, post_from_category, post_from_name, post_from_id, post_message, post_picture, post_link, post_name, post_caption, post_type, post_description, post_created_time, post_updated_time, search_type, project_id) VALUES ";
                                foreach ($page_post_search_details['data'] as $val) {
                                    $post_id = $val->id;
                                    $post_from_category = clean_insert($val->from->category);
                                    $post_from_name = clean_insert($val->from->name);
                                    $post_from_id = $val->from->id;
                                    $post_message = clean_insert($val->message);
                                    $post_picture = clean_insert($val->picture);
                                    $post_link = clean_insert($val->link);
                                    $post_name = clean_insert($val->name);
                                    $post_caption = clean_insert($val->caption);
                                    $post_type = clean_insert($val->type);
                                    $post_description = clean_insert($val->description);
                                    $post_created_time = $val->created_time;
                                    $post_updated_time = $val->updated_time;

                                    # PAGE POST COMMENTS [START]
                                    $url = "https://graph.facebook.com/{$post_id}/comments?access_token={$user_page_info['page_access_token'][0]}&limit=10";
                                    $output_json = _curl($url);
                                    $page_post_comments_details = (array) json_decode($output_json);
                                    $comment_sql = '';
                                    $comments_count = count($page_post_comments_details['data']);

                                    if ($comments_count>0) {

                                        $comment_sql = "INSERT INTO tbl_facebook_page_post_comments (comment_id, from_name,from_id, message, created_time, like_count, user_likes, page_post_id) VALUES ";
                                        foreach ($page_post_comments_details['data'] as $cv) {
                                            $comment_id = $cv->id;
                                            $comment_from_name = clean_insert($cv->from->name);
                                            $comment_from_id = $cv->from->id;
                                            $comment_message = clean_insert($cv->message);
                                            $comment_like_count = $cv->like_count;
                                            $comment_created_time = clean_insert($cv->created_time);
                                            $comment_user_likes = $cv->user_likes;

                                            $comment_sql .="('" . $comment_id . "',";
                                            $comment_sql .="'" . $comment_from_name . "',";
                                            $comment_sql .="'" . $comment_from_id . "',";
                                            $comment_sql .="'" . $comment_message . "',";
                                            $comment_sql .="'" . $comment_created_time . "',";
                                            $comment_sql .="'" . $comment_like_count . "',";
                                            $comment_sql .="'" . $comment_user_likes . "',";
                                            $comment_sql .="'" . $post_id . "'),";
                                        }
                                        $comment_sql = rtrim($comment_sql, ",");
                                        mysql_query($comment_sql) or die(mysql_error());
                                    }
                                    # PAGE POST COMMENTS [END]

                                    //$table .= $comments_table; # APPEND COMMENTS IN POST ROW

                                    $sql .="('" . $post_id . "',";
                                    $sql .="'" . $post_from_category . "',";
                                    $sql .="'" . $post_from_name . "',";
                                    $sql .="'" . $post_from_id . "',";
                                    $sql .="'" . $post_message . "',";
                                    $sql .="'" . $post_picture . "',";
                                    $sql .="'" . $post_link . "',";
                                    $sql .="'" . $post_name . "',";
                                    $sql .="'" . $post_caption . "',";
                                    $sql .="'" . $post_type . "',";
                                    $sql .="'" . $post_description . "',";
                                    $sql .="'" . $post_created_time . "',";
                                    $sql .="'" . $post_updated_time . "',";
                                    $sql .="'post',";
                                    $sql .="'" . $project_id . "'),";
                                    $i++;
                                }
                                $sql = rtrim($sql, ",");
                                mysql_query($sql) or die(mysql_error());
                            }

# GET PAGE POST DETAILS [type=post] [access_token=PAGE] [END]
                        } # IF keyword exist [END]
                    }
                }
                echo "<BR><B>FACEBOOK CRON ENDED @</B> : " . date('Y-m-d H:i:s A');
            } else {
                echo "<BR>INVALID ACCESS FOUND";
            }
        }
    } else {
        echo "<h2>USER ACCESS TOKEN EMPTY FOUND FROM DATABASE</h2>";
    }
}

if (REDDITCRONSTART == '1') {
    $fields = array('id', 'name');
    $where = array();
    $keyword_result = getData('tbl_keywords', $fields, $where);
    #FETCH ALL USER's KEYWORDS [END]
    if (count($keyword_result)) {
        echo "<BR><B>REDDIT CRON STARTED @</B> : " . date('Y-m-d H:i:s A');
        $subreddit_data = array();
        while ($row = mysql_fetch_array($keyword_result)) {
            if (!empty($row['name'])) {
                $project_id = $row['id'];
                $keyword = stripslashes($row['name']);

                # IF keyword exist [START]
                if (!empty($keyword)) {
                    # GET REDDIT DETAIL [START]

                    $keyword = str_replace(" ", "+", $keyword);
                    $url = "http://www.reddit.com/r/" . $keyword . "/.json?limit=50";

                    // echo "<PRE>";
                    $subreddit_data = _curl_reddit($url);
                    $subreddit_data = $subreddit_data->data->children;
                    //print_r($subreddit_data);exit;

                    if (count($subreddit_data)) {
                        $sql = "INSERT INTO tbl_subreddit_data (project_id,domain,subreddit,selftext,likes,author,score,subreddit_id,permalink,name,created,url,title,num_comments,create_time)";
                        $sql .=" VALUES ";
                        foreach ($subreddit_data as $k => $v) {
                            $data = $v->data;

                            $domain = clean_insert($data->domain);
                            $subreddit = clean_insert($data->subreddit);
                            $selftext = clean_insert($data->selftext);
                            $likes = $data->likes;
                            $author = clean_insert($data->author);
                            $score = $data->score;
                            $subreddit_id = clean_insert($data->subreddit_id);
                            $permalink = clean_insert($data->permalink);
                            $name = clean_insert($data->name);
                            $created = date('Y-m-d H:i:s', $data->created);
                            $url = clean_insert($data->url);
                            $title = clean_insert($data->title);
                            $num_comments = clean_insert($data->num_comments);

                            $create_time = date('Y-m-d H:i:s');

                            $sql .="('" . $project_id . "',";
                            $sql .="'" . $domain . "',";
                            $sql .="'" . $subreddit . "',";
                            $sql .="'" . $selftext . "',";
                            $sql .="'" . $likes . "',";
                            $sql .="'" . $author . "',";
                            $sql .="'" . $score . "',";
                            $sql .="'" . $subreddit_id . "',";
                            $sql .="'" . $permalink . "',";
                            $sql .="'" . $name . "',";
                            $sql .="'" . $created . "',";
                            $sql .="'" . $url . "',";
                            $sql .="'" . $title . "',";
                            $sql .="'" . $num_comments . "',";
                            $sql .="'" . $create_time . "'),";
                        }
                        $sql = rtrim($sql, ",");
                        mysql_query($sql) or die(mysql_error());
                    }
                    # GET REDDIT DETAIL [END]
                }
            }
        }
        echo "<BR><B>REDDIT CRON ENDED @</B> : " . date('Y-m-d H:i:s A');
    }
}


#FUNCTIONS [START]

function clean_insert($text) {
    return mysql_real_escape_string(htmlspecialchars(addslashes(trim($text))));
}

function remove_spacial($str) {
    $str = iconv('windows-1250', 'utf-8', $str);
    return $str;
}

function remove_spacial_1($str) {
    $str = preg_replace("/[^\x9\xA\xD\x20-\x7F]/", "", $str);
    return $str;
}

function selfURL() {
    $pageURL = (@$_SERVER["HTTPS"] == "on") ? "https://" : "http://";
    if ($_SERVER["SERVER_PORT"] != "80") {
        $pageURL .= $_SERVER["SERVER_NAME"] . ":" . $_SERVER["SERVER_PORT"] . $_SERVER["REQUEST_URI"];
    } else {
        $pageURL .= $_SERVER["SERVER_NAME"] . $_SERVER["REQUEST_URI"];
    }
    return $pageURL;
}

function _curl($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output_json = curl_exec($ch);
    curl_close($ch);
    //$finalData = json_decode($output_json);   
    return $output_json;
}

function _curl_reddit($url) {
    $ch = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_HEADER, 0);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    curl_setopt($ch, CURLOPT_SSL_VERIFYPEER, false);
    $output_json = curl_exec($ch);
    curl_close($ch);
    return json_decode($output_json);
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

function addData($tbl, $data = NULL) {
    $keys = array_keys($data);
    $keys_string = implode(",", $keys);
    $values = array_values($data);
    $values_string = "'" . implode("','", $values) . "'";
    $sql = "INSERT INTO " . $tbl . " ({$keys_string}) VALUES ({$values_string})";
    mysql_query($sql) or die(mysql_error());
    return mysql_insert_id();
}

function br2nl($string) {
    return preg_replace('/\<br(\s*)?\/?\>/i', "\n", $string);
}

function clean_view($text) {
    return html_entity_decode(stripslashes(br2nl($text)));
}

#FUNCTIONS [END]

ob_end_flush();
?>
