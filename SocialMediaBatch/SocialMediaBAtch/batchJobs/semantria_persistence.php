<?php

class SemantriaPagePersistence
{
    var $m_persistence;

    function SemantriaPagePersistence()
    {
    	$this->m_persistence = new BatchPersistence();
    	$this->m_persistence->BatchPersistenceSetup();
    }


    function GetSentimentAnalysysData()
    {
        $sa_data = array();
        //$selQuery = "select * from scf_user_pov_comments where scfupov_bplnmps_id = $bplnmps_id";
        $selQuery = "select * from scf_user_pov_comments where DATE(scfupov_create_date) = CURDATE() and scfupov_comment_value !='' and scfupov_pov_value != ''";
        $this->m_persistence->SelectQuery($selQuery);
        if(count($this->m_persistence->m_data_rows ) >0)
        {
        	echo "inside";
            foreach ($this->m_persistence->m_data_rows as $row_data)
            {
                $sa_data[] = array("id"=>$row_data["scfupov_id"],"text"=>$row_data["scfupov_pov_value"]);
            }
        }
        return $sa_data;
    }
    
    function GetSentimentAnalysysDataForGooglePlus()
    {
    	$sa_data = array();
    	$selQuery = "select * from tbl_googleplus_comments where DATE(publish) = CURDATE() and content != ''";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["project_id"],"comments"=>$row_data["content"]);
    		}
    	}
    	return $sa_data;
    }
    
    function GetSentimentAnalysysDataForLinkedIn()
    {
    	$sa_data = array();
    	$selQuery = "select * from tbl_googleplus_comments where DATE(publish) = CURDATE()";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["project_id"],"comments"=>$row_data["content"]);
    		}
    	}
    	return $sa_data;
    }
    
    function GetSentimentAnalysysDataForYoutube()
    {
    	$sa_data = array();
    	$selQuery = "select * from tbl_youtube_video_comments where DATE(posted_date) = CURDATE()  and comment_text != ''";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["project_id"],"comments"=>$row_data["comment_text"]);
    		}
    	}
    	return $sa_data;
    }
    
    function GetSentimentAnalysysDataForWordpress()
    {
    	$sa_data = array();
    	$selQuery = "select * from wp_blog_feed_comment where DATE(publishdate) = CURDATE() and title != '' and description != ''";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["project_id"],"comments"=>$row_data["description"]);
    		}
    	}
    	return $sa_data;
    }
    
    function GetSentimentAnalysysDataForFaceBook()
    {
    	$sa_data = array();
    	$selQuery = "select * from tbl_facebook_user_post_comments where DATE(created_time) = CURDATE() and message != ''";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["page_post_id"],"comments"=>$row_data["message"]);
    		}
    	}
    	return $sa_data;
    }
    
    function GetSentimentAnalysysDataForTwitter()
    {
    	$sa_data = array();
    	$selQuery = "select * from tbl_twitter_data where DATE(tweetdate) = CURDATE() and tweet != ''";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["project_id"],"comments"=>$row_data["tweet"]);
    		}
    	}
    	return $sa_data;
    }
    
    function GetSentimentAnalysysDataForReddit()
    {
    	$sa_data = array();
    	$selQuery = "select * from tbl_subreddit_data where DATE(created) = CURDATE() and selftext != ''";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["project_id"],"comments"=>$row_data["selftext"]);
    		}
    	}
    	return $sa_data;
    }
    
    function GetSentimentAnalysysDataForInstagram()
    {
    	$sa_data = array();
    	$selQuery = "select * from tbl_instagram_data where DATE(inserttime) = CURDATE() and bio != ''";
    	$this->m_persistence->SelectQuery($selQuery);
    	if(count($this->m_persistence->m_data_rows ) >0)
    	{
    		foreach ($this->m_persistence->m_data_rows as $row_data)
    		{
    			$sa_data[] = array("id"=>$row_data["id"],"project_id"=>$row_data["project_id"],"comments"=>$row_data["bio"]);
    		}
    	}
    	return $sa_data;
    }
    
    function UpdateAnalyzedData($analyzedArray)
    {
    	$sql_query = "UPDATE scf_user_pov_comments SET scfupov_score = CASE scfupov_id";    	
    	$inQuery = "";
    	$length = count($analyzedArray);
    	$i = 1;
    	foreach ($analyzedArray as $text) {   		
    		$sql_query .= " WHEN ".$text["id"]." THEN ".$text["score"];
    		if($i<$length){
    			$inQuery .= $text["id"].", ";
    		}else{
    			$inQuery .= $text["id"];
    		}
    		$i++;
    	}
    	$sql_query .= " END WHERE scfupov_id IN ( ".$inQuery." ) ";    	
    	$id = $this->m_persistence->ExecuteQuery($sql_query);
    	return $id;
    }
    
    function UpdateGooglePlusAnalyzedData($analyzedArray)
    {
    	$social_media_id = 1;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }

    function UpdateWordpressAnalyzedData($analyzedArray)
    {
    	$social_media_id = 2;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }

    function UpdateYouTubeAnalyzedData($analyzedArray)
    {
    	$social_media_id = 3;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }
    
    function UpdateLinkedInAnalyzedData($analyzedArray)
    {
        $social_media_id = 4;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }
    
    function UpdateFaceBookAnalyzedData($analyzedArray)
    {
    	$social_media_id = 5;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }
    
    function UpdateTwitterAnalyzedData($analyzedArray)
    {
    	$social_media_id = 6;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }
    
    function UpdateRedditAnalyzedData($analyzedArray)
    {
    	$social_media_id = 7;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }
    
    function UpdateInstagramAnalyzedData($analyzedArray)
    {
    	$social_media_id = 8;
    	foreach ($analyzedArray as $text) {
    		$myArray = explode('____', $text["id"]);
    		$sql_query = "INSERT INTO tbl_sementria_score (prj_id,social_media_id, Date, Semtria_score) VALUES ( " . $myArray[1] . ", " . $social_media_id . ", NOW(),". $text["score"] . " )";
    		$this->m_persistence->ExecuteQuery($sql_query);
    	}
    }
}
?>