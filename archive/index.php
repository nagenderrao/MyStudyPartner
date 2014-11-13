<!--
 Product Name: Google Plus & Youtube
 Product URI: http://s497438007.onlinehome.us/index.php
 Description: Get Keyword Search Data and Comments
 Developed By : http://genextwebs.com
 -->
 <?php
ob_start();
error_reporting( E_ALL );
error_reporting( E_ERROR );
require_once('config.php');

$keyword = strtolower($_POST['keyword']);
$keyword = addslashes($keyword);
?>
<html>
<header>
<meta http-equiv="Content-Type" content="text/html;charset=UTF-8">
<script type="text/javascript" src="./js/jquery.min.js"></script>
<script type="text/javascript" src="./js/custom.js"></script>
<link type="text/css" href="/css/style.css" rel="stylesheet" media="all" />
<link type="text/css" href="/css/custom.css" rel="stylesheet" media="all" />
<script>

</script>		
</header>
<body>
<div id="tfheader">
<form id="tfnewsearch" action="<?php echo $_SERVER['PHP_SELF']; ?>" method="POST">
<input type="text" placeholder="Enter Keyword" class="tftextinput" size="21" name="keyword" value="<?php echo $keyword; ?>">
<input type="submit" value="submit" name="submit" class="tfbutton">
<!-- Enter Max. Result Limit : <input type="text" name="max_result"><br>
Enter Start Index : <input type="text" name="start_index"><br>-->
	<div class="tfclear"></div>
</form>

</div>
<?php
#DEFAULT SET
set_time_limit(0);
//error_reporting(E_ALL);
$start_index=1;
$max_result=25;

if(!empty($keyword))
{
	$keyword = str_replace(' ','+',$keyword);
	$url = "http://gdata.youtube.com/feeds/api/videos?q=".$keyword."&v=2&alt=json";
	
	$output_json = _curl($url);	
	$youtube_data = json_decode($output_json);
	
	//echo "<PRE>";
	//print_r($youtube_data);
	//exit;
	//$video_id = array();
	//$video_id = getData($youtube_data); //exit;	
	
	$keyword = str_replace(" ","+",$keyword);
	$url1 = "https://www.googleapis.com/plus/v1/activities?query=".$keyword."&maxResults=20&key=AIzaSyDdYaI1mC_hacLk6OgqARCK9q7ie9jvCqA";

	$output_json1 = _curl($url1);	
	$googleplusData = json_decode($output_json1);
	
	$data = getYouTubeData($keyword,$youtube_data,$googleplusData);
	echo $data;
	//print_r($data);
	//echo "</PRE>";
	
	/*echo '<table>';
    foreach($data as $row){
        echo '<tr>';
        $row = explode('',$row);
        foreach($row as $cell){
            echo '<td>';
            echo $cell;
            echo '</td>';
        }
        echo '</tr>';
    }
    echo '</table>';*/
}

/*$query = 'something something_description, something2 something2_description, something3 something3_description...
';
echo "<table><tr>".implode("</tr><tr>",array_map(function($a) {return "<td>".implode("</td><td>",explode(" ",trim($a)))."</td>";},explode(",",$query)))."</tr></table>";*/

function _curl($url){	
	$ch = curl_init();
	curl_setopt($ch, CURLOPT_URL, $url);
	curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
	$output_json = curl_exec($ch);
	//echo $output;
	curl_close($ch);
	return $output_json;
}

function getYouTubeData($keyword,$youtube_data=array(),$googleplusData=array()) {

	$entries = array();

	$video_ids = array();
	$links = array();
	$comment_url = '';
	$comment_urls = array();
	$data = array();
	$table_str='';
	$table_data = array();

	$table_str .="<table class='bordered'>";
	$table_str .="<thead>";
	$table_str .= "<tr>";
	$table_str .= "<th>NO.</th>";
	$table_str .= "<th>TITLE</th>";
	$table_str .= "<th>CATEGORY</th>";
	$table_str .= "<th>PUBLISHED DATE</th>";
	$table_str .="</tr>";
	$table_str .="</thead>";
	
	$table_str .="<tbody>";
	
	//foreach($youtube_data->feed as $k=>$v) {
		#GET ALL FEED ENTRIES	
		$entries = $youtube_data->feed->entry;
		$counter=1;
		foreach($entries as $ek=>$ev) {
			/*echo "<PRE>";
			print_r($ev);
			exit;*/
	
			$table_data = array();
			$t = $ev->id->{'$t'};			
			$t = explode(',',$t);			
			$t1 = explode('video:',$t[1]);			
			$video_id = $t1[1];	#CURRENT VIDEO ID
			
			$table_data['video_url'] = $ev->link[0]->href;
			
			$table_data['entry_published'] =  date('Y-m-d H:i:s A',strtotime($ev->published->{'$t'}));
			$table_data['entry_category'] =  $ev->category[1]->label;
			$table_data['entry_title'] =  $ev->title->{'$t'};
			
			$table_data['favoriteCount'] = $ev->{'yt$statistics'}->favoriteCount;
			$table_data['viewCount'] = $ev->{'yt$statistics'}->viewCount;
			
			$table_data['numDislikes'] = $ev->{'yt$rating'}->numDislikes;
			$table_data['numLikes'] = $ev->{'yt$rating'}->numLikes;
			
			#CHECK VIDEO ID ALREADY EXIST?
			if (!in_array($video_id, $video_ids)) {

				$video_ids[] = $video_id; #COLLECT ALL VIDEO IDs IN ARRAY				
				$comment_url = $ev->{'gd$comments'}->{'gd$feedLink'}->href.'&alt=json';	
				
				$output_json = _curl($comment_url);
				$comment_data = json_decode($output_json);
				$comments_entry = $comment_data->feed->entry;
				
				$comments = array();
				$comment_user_ids = array();
				$comments_published_dates = array();
				
				# TO STORE VIDEO INFO DATA [START]
				$youtube_info_data['video_title'] = $table_data['entry_title'];
				$youtube_info_data['video_category'] = $table_data['entry_category'];
				$youtube_info_data['published_date'] = $table_data['entry_published'];
				$youtube_info_data['video_url'] = $table_data['video_url'];
				# TO STORE VIDEO INFO DATA [END]
				
				
				foreach($comments_entry as $ck=>$cv) 
				{
					//$cv->author[0]->{'yt$userId'}->{'$t'}
					if(!empty($cv->author[0]->{'uri'}->{'$t'})){
						$get_comment_user_id = end(explode('/', $cv->author[0]->{'uri'}->{'$t'}));
						$comment_user_ids[] = "http://www.youtube.com/user/".$get_comment_user_id; //TDR9111
						$comment_user_names[]=$cv->author[0]->{'name'}->{'$t'};
					}
					
					$comments_published_dates[] = date('Y-m-d H:i:s A',strtotime($cv->published->{'$t'}));					
					$comments[] = htmlentities($cv->content->{'$t'}, ENT_QUOTES | ENT_IGNORE, "UTF-8");
										
				}
				
				$table_data['comments'] = $comments;				
				$table_data['comments_published_dates'] = $comments_published_dates;
				$table_data['comment_user_ids'] = $comment_user_ids;
				$table_data['comment_user_names'] = $comment_user_names;
				
				# TO STORE VIDEO COMMENTS DATA [START]
				$youtube_comment_data['comment_text'] = $table_data['comments'];
				$youtube_comment_data['posted_user_url']= $table_data['comment_user_ids'];
				$youtube_comment_data['posted_user_name']= $table_data['comment_user_names'];
				$youtube_comment_data['posted_date'] = $table_data['comments_published_dates'];
				# TO STORE VIDEO COMMENTS DATA [END]
				
				/*echo "<PRE>";
				print_r($youtube_info_data);
				print_r($youtube_comment_data);
				exit;*/
				
				$heading = array_keys($table_data);
				
				$table_str .= "<tr class='record_head'>";
				$table_str .= "<td align=\"center\" valign=\"middle\">".$counter++."</td>";
				$table_str .= "<td>".wordwrap($table_data[$heading[3]], 150, "<br />");
				
				$table_str .= "<br/>";
				$video_url = $table_data['video_url'];
				$table_str .= "<br/>";				
				$table_str .= "<a href=\"$video_url\" target=\"_BLANK\">$video_url</a>";
				
				$table_str .= "<br/><br/>";					
				$table_str .= $table_data['viewCount']." Views";
				//by Jake Wright8 months ago27,982 views
				
				$table_str .= "</td>";
				$table_str .= "<td>".$table_data[$heading[2]]."</td>";
				$table_str .= "<td>".$table_data[$heading[1]]."</td>";
				$table_str .= "</tr>";
				
				$table_str .= "<tr class='record_data'>";			
				$table_str .= "<td colspan='4'>";
				$table_str .= "<ul>";
				
				if(!empty($table_data['comments'])){
				//$table_str .= "<div><li class=\"current\">View Comments</li><ul>";
				$table_str .= "<div><li class=\"current\">View Comments</li>&nbsp;(".count($table_data[comments]).")<ul>";
				$comments_count = count($table_data);
					for($c=0;$c<$comments_count;$c++){
						$user_id = $table_data['comment_user_ids'][$c];
						$posted_by = "<a href=\"$user_id\" target=\"_BLANK\">".$table_data['comment_user_names'][$c]."</a>";
						
						$table_str .= "<li type=\"decimal\">".wordwrap($table_data['comments'][$c], 150, "<br/>");
						
						$table_str .= "<br/><span class='comment_date'>Posted By&nbsp;:&nbsp;".$posted_by."</span>";
						
						$table_str .= "&nbsp;<span class='comment_date'>at&nbsp;:&nbsp;".$table_data['comments_published_dates'][$c]."</span>";
						$table_str .= "</li>";
					}
				} else {
					$table_str .= "<li>NO COMMENTS FOUND</li>";
				}
				$table_str .= "</ul></div>";
				$table_str .= "</ul>";
				$table_str .= "</td>";			
				$table_str .= "</tr>";
			}
		}
		$table_str .="</tbody>";
		$table_str .= "</table>";
                
                
                       $is_exist=array();
	$is_exist = isKeywordExist('tbl_keywords',$keyword); # CHECK EXIST?
	
	if($is_exist[0]>0 && !empty($is_exist[1])){ 
		# CHECK EXIST? "YES"
		
		# GET YOUTUBE INFO.
		$where = array('keyword_id'=>$is_exist[1]);
		$fields = array('id');
		$rows = getData('tbl_youtube_video_info',$fields,$where);
                
                                           /*print_r($rows);
		exit;*/
		
		# DELETE YOUTUBE INFO.
		$where = array('keyword_id'=>$is_exist[1]);
		deleteData('tbl_youtube_video_info',$where);
				
		# DELETE YOUTUBE COMMENTS.
		$where = array('video_info_id'=>$rows[0]);		
		deleteData('tbl_youtube_video_comments',$where);
                
                                            # DELETE GOOGLE PLUS INFO.
		$where = array('keyword_id'=>$is_exist[1]);
		deleteData('tbl_googleplus_info',$where);
				
		# DELETE GOOGLE PLUS COMMENTS.
		$where = array('keyword_id'=>$is_exist[1]);
		deleteData('tbl_googleplus_comments',$where);
		
		# ADD NEW YOUTUBE INFO.
		$youtube_info_data['keyword_id'] = $is_exist[1];
		$video_info_id = addData('tbl_youtube_video_info',$youtube_info_data);
		
		# ADD NEW YOUTUBE COMMENTS
		$youtube_comment_data['video_info_id'] = $video_info_id;
		addCommentData('tbl_youtube_video_comments',$youtube_comment_data);
		$keyword_id  = $is_exist[1];
	} else { 
		# CHECK EXIST? "NO"
		$keywords = array();
		$keywords['name'] = $keyword;
		# ADD NEW KEYWORD
		$keyword_id = addData('tbl_keywords',$keywords);
		$youtube_info_data['keyword_id'] = $keyword_id;	
		
		# ADD NEW YOUTUBE INFO.
		$video_info_id = addData('tbl_youtube_video_info',$youtube_info_data);
		
		# ADD NEW YOUTUBE COMMENTS
		$youtube_comment_data['video_info_id'] = $video_info_id;
		addCommentData('tbl_youtube_video_comments',$youtube_comment_data);
	}
                
                       $table_str1 = '';
                       //GOOGLE PLUS START
                       $table_str1 .="<table class='bordered'>";
	$table_str1 .="<thead>";
	$table_str1 .= "<tr>";
	$table_str1 .= "<th>DisplayName</th>";
                      $table_str1 .= "<th>Title</th>";
                      $table_str1 .= "<th>Content</th>";
                      $table_str1 .= "<th>+1 Count</th>";
                      $table_str1 .= "<th>Comments Count</th>";
                      $table_str1 .= "<th>Reshares Count</th>";
                      $table_str1 .= "<th>ObjectType</th>";
                      $table_str1 .= "<th>Url</th>";
                      $table_str1 .= "<th>Published</th>";
						$table_str1 .="</tr>";
						$table_str1 .="</thead>";
						
						$table_str1 .="<tbody>";
        
                            //DB STORE PROCESS START                           
                             foreach ($googleplusData->items as $data){
                                 //$keyword_id 
                               $sql = "INSERT INTO  `tbl_googleplus_info` (   `displayname` ,  `title` ,  `content` ,  `plus1count` ,  `commentcount` ,  `resharescount` ,  `objecttype` ,  `url` ,  `publish` ,  `keyword_id` ) 
                                        VALUES (
                                          '".$data->actor->displayName."',
                                          '".htmlentities($data->title, ENT_QUOTES | ENT_IGNORE | ENT_NOQUOTES, "UTF-8")."', 
                                          '".htmlentities($data->object->content, ENT_QUOTES | ENT_IGNORE | ENT_NOQUOTES, "UTF-8")."',  
                                          '".$data->object->plusoners->totalItems."',
                                          '".$data->object->replies->totalItems."',
                                          '".$data->object->resharers->totalItems."',
                                          '".$data->object->objectType."',
                                          '".$data->url."',
                                          '".$data->published."',
                                          '".$keyword_id."'
                                        )";
                               
                                mysql_query($sql) or die(mysql_error());
                                $lastInsertId = mysql_insert_id();

                                $table_str1 .= "<tr class='record_head'>";
                                $table_str1 .= "<td width='11%'>".$data->actor->displayName."</td>";
                                $table_str1 .= "<td width='11%'>".htmlspecialchars(strip_tags($data->title))."</td>";
                                $table_str1 .= "<td width='11%'>".htmlspecialchars(strip_tags($data->object->content))."</td>";
                                $table_str1 .= "<td width='11%'>".$data->object->plusoners->totalItems."</td>";
                                $table_str1 .= "<td>".$data->object->replies->totalItems;
                                if($data->object->replies->totalItems != "0"){
                                $table_str1 .= "<br><a href=\"javascript:void(0)\" onclick=\"javascript:fnShowCommentDiv('".$data->id."');\">View comment</a>";
                                }
                                $table_str1 .= "</td>";
                                $table_str1 .= "<td width='11%'>".$data->object->resharers->totalItems."</td>";
                                $table_str1 .= "<td width='11%'>".$data->object->objectType."</td>";
                                $table_str1 .= "<td width='11%'><a href='".$data->url."' target='_BLANK'>".$data->url."</a></td>";
                                $table_str1 .= "<td width='11%'>".$data->published."</td>";
                                $table_str1 .= "</tr>";
                                if($data->object->replies->totalItems != "0"){
                                    $url = "https://www.googleapis.com/plus/v1/activities/".$data->id."/comments?key=AIzaSyDdYaI1mC_hacLk6OgqARCK9q7ie9jvCqA";
                                    
                                    $output_json1 = _curl($url);	
                                    $finalDataComment = json_decode($output_json1);   
                                    
                                    $table_str1 .= "<tr id='".$data->id."' style='display:none;' class='record_data'><td colspan='9'><table width='100%'><thead><tr><th>DisplayName</th><th>Content</th><th>ObjectType</th><th>Published</th></tr></thead><tbody>";
                                    foreach ($finalDataComment->items as $data){
                                        
                                        $sql = "INSERT INTO `tbl_googleplus_comments` (`displayname`,`content`,`objecttype`,`publish`,`keyword_id`) 
                                         VALUES ('".$data->actor->displayName."',
                                                            '".htmlentities($data->object->content, ENT_QUOTES | ENT_IGNORE | ENT_NOQUOTES, "UTF-8")."',
                                                            '".$data->object->objectType."',
                                                            '".$data->published."',
                                                            '".$keyword_id."')";
                                    mysql_query($sql) or die(mysql_error());
                                    
                                        $table_str1 .= "<tr class='record_data'>";
                                        $table_str1 .= "<td width='11%'>".$data->actor->displayName."</td>";
                                        $table_str1 .= "<td width='11%'>".strip_tags($data->object->content)."</td>";
                                        $table_str1 .= "<td width='11%'>".$data->object->objectType."</td>";
                                        $table_str1 .= "<td width='11%'>".$data->published."</td>";
                                        $table_str1 .= "</tr>";
                                    }
                                    $table_str1 .= "</tbody></table></td></tr>";
                                }

                            }
	$table_str1 .="</tbody>";
                      $table_str1 .= "</table>";
                     //GOOGLE PLUS END
        
	
	$display_table ='';
	$display_table.="<h1>Youtube</h1>";
	$display_table.=$table_str;
	$display_table.="<BR/><BR/>";
	$display_table.="<h1>Google Plus</h1>";
	$display_table.=$table_str1;	
	return $display_table;
	//exit;
	//return $table_data;
}
?>
</div>
<!-- <br><br>
<style>
.developedBy{
position : absolute;
bottom:0px; 
right:4px; 
font-family: verdana,helvetica,arial,sans-serif;
font-size:10px;
color: #4a6b82;
}
.developedBy a {
text-decoration: none;
font-size:10px;
}
</style>
<div class="developedBy">Webmaster&nbsp;:&nbsp;<a href="http://www.genextwebs.com" target="_blank">GeNextWebs</a></div> -->
</body>
</html>
<?php
function addCommentData($tbl,$data = NULL){
$count = count($data['comment_text']);
	for($i=0; $i<$count; $i++){
		$row = array();
		$row['comment_text'] = $data['comment_text'][$i];
		$row['posted_user_url'] = $data['posted_user_url'][$i];
		$row['posted_user_name'] = $data['posted_user_name'][$i];
		$row['posted_date'] = $data['posted_date'][$i];
		$row['video_info_id'] = $data['video_info_id'];
		addData($tbl, $row);
	}
}
function getData($tbl,$fields = NULL,$where = NULL){
	$where_sql = ' WHERE ';
	foreach ($where as $k => $v) {
		if (!empty($k)) {
			$where_sql .= $k . "='" . $v . "' AND ";
		}
	}
	$where_sql = rtrim($where_sql, " AND ");
	$result = null;
	if (!empty($where_sql)) {
		$sql = "SELECT ".implode(',',$fields)." FROM ".$tbl." ".$where_sql;		
		$result = mysql_query($sql) or die(mysql_error());
	}
	
	$row = mysql_fetch_row($result);
	return $row;
}

function deleteData($tbl,$where = NULL){
	$where_sql = ' WHERE ';
	foreach ($where as $k => $v) {
		if (!empty($k)) {
			$where_sql .= $k . "='" . $v . "' AND ";
		}
	}
	$where_sql = rtrim($where_sql, " AND ");
	
	if (!empty($tbl)) {
		$sql = "DELETE FROM ".$tbl." ".$where_sql;	
		mysql_query($sql) or die(mysql_error());
	}
}

function addData($tbl,$data = NULL) {
	$keys = array_keys($data);
	$keys_string = implode(",", $keys);
	$values = array_values($data);
	$values_string = "'" . implode("','", $values) . "'";
	$sql = "INSERT INTO ".$tbl." ({$keys_string}) VALUES ({$values_string})";
	mysql_query($sql) or die(mysql_error());
	return mysql_insert_id();
}

function editData($data = array(), $whereParam = array()) {
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
			$sql = "UPDATE ".$tbl." SET " . $set_sql . $where_sql;
			mysql_query($sql) or die(mysql_error());
		}
	}
}

function isKeywordExist($tbl,$keyword) {
	$sql = "SELECT count(*),id FROM ".$tbl." WHERE name='" . $keyword . "'";
	$result = mysql_query($sql) or die(mysql_error());
	$row = mysql_fetch_row($result);
	return $row;
}
ob_end_flush();
?>