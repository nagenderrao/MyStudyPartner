<?php
ini_set('max_execution_time', 300);

class SemantriaApp extends BatchAdmin
{

	var $m_semantria_persistence;
	
    function SemantriaApp()
    {
    	
    	 $this->m_semantria_persistence = new SemantriaPagePersistence();
    }

    function Run()
    {
    	$this->BuildSentimentAnalysysData();
    }

    function InitValues()
    {
    	
    }

    function BuildSentimentAnalysysData()
    {
    	set_time_limit(50000);   	
        $sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysData();
        $analizer = new SemantriaAnalyser();
        print_r($sa_recs);		
        if(count($sa_recs) > 0){
	        $analyzedArray =  $analizer->analyzeData($sa_recs);
	        print_r($analyzedArray);
	        if(count($analyzedArray) >0 ){
	        	$this->m_semantria_persistence->UpdateAnalyzedData($analyzedArray);
	        }
        }

        //GooglePlus
        $google_sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysDataForGooglePlus();
        $googlePlusRecords = 12;
        $googlePlusLength = count($google_sa_recs);
        $googlePlusValueFrom = 0;
        $googlePlusValueTo = 12;
        $googlePlusCount = 0;
        
        if($googlePlusLength > $googlePlusRecords){
        	$googlePlusCount = ceil ( $googlePlusLength/$googlePlusRecords );
        }else{
        	$googlePlusCount = $googlePlusLength;
        }       	
   		for ($i=1; $i<=$googlePlusCount; $i++)
  		{ 			
	        $google_plus_current_array = array_slice($google_sa_recs,$googlePlusValueFrom,$googlePlusRecords);
	        $projectAnalizer = new SemantriaAnalyser();
	        print_r($google_plus_current_array);
	        echo "HHHHHHHHHHHHHHHHH";
	        $google_analyzedArray =  $projectAnalizer->analyzeProjectData($google_plus_current_array);
	        echo "JJJJJJJJJJJJJJJJJJJ";
	        print_r($google_analyzedArray);
	        $this->m_semantria_persistence->UpdateGooglePlusAnalyzedData($google_analyzedArray);
	        $googlePlusValueFrom = $googlePlusValueFrom + $googlePlusRecords;	        
 		} 
 		
        //Youtube        
        $youtube_sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysDataForYoutube();
        $youtubeRecords = 12;
        $youtubeLength = count($youtube_sa_recs);
        $youtubeValueFrom = 0;
        $youtubeValueTo = 12;
        $youtubeCount = 0;        	
        if($youtubeLength > $youtubeRecords){
        	$youtubeCount = ceil ( $youtubeLength/$youtubeRecords );
        }else{
        	$youtubeCount = $youtubeLength;
        }
        for ($i=1; $i<=$youtubeCount; $i++)
        {
        $you_tube_current_array = array_slice($youtube_sa_recs,$youtubeValueFrom,$youtubeRecords);
        $projectAnalizer = new SemantriaAnalyser();
        $youtube_analyzedArray =  $projectAnalizer->analyzeProjectData($you_tube_current_array);
        $this->m_semantria_persistence->UpdateYouTubeAnalyzedData($youtube_analyzedArray);
        $youtubeValueFrom = $youtubeValueFrom + $youtubeRecords;
        }        
               
        //Wordpress
        $wordpress_sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysDataForWordpress();
 		$wordPressRecords = 12;
 		$wordPressLength = count($wordpress_sa_recs);
 		$wordPressValueFrom = 0;
 		$wordPressValueTo = 12;
 		$wordPressCount = 0;
 		
 		if($wordPressLength > $wordPressRecords){
 			$wordPressCount = ceil ( $wordPressLength/$wordPressRecords );
 		}else{
 			$wordPressCount = $wordPressLength;
 		}
 		for ($i=1; $i<=$wordPressCount; $i++)
 		{
            $word_press_current_array = array_slice($wordpress_sa_recs,$wordPressValueFrom,$wordPressRecords);
            $projectAnalizer = new SemantriaAnalyser();
            $wordpress_analyzedArray =  $projectAnalizer->analyzeProjectData($word_press_current_array);
            $this->m_semantria_persistence->UpdateWordpressAnalyzedData($wordpress_analyzedArray);
            $wordPressValueFrom = $wordPressValueFrom + $wordPressRecords;
 		} 		
 		
 		
 		
 		//Facebook
 		$faceBook_sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysDataForFaceBook();
 		$faceBookRecords = 12;
 		$faceBookLength = count($faceBook_sa_recs);
 		$faceBookValueFrom = 0;
 		$faceBookValueTo = 12;
 		$faceBookCount = 0;
 			
 		if($faceBookLength > $faceBookRecords){
 			$faceBookCount = ceil ( $faceBookLength/$faceBookRecords );
 		}else{
 			$faceBookCount = $faceBookLength;
 		}
 		for ($i=1; $i<=$faceBookCount; $i++)
 		{
 		$face_book_current_array = array_slice($faceBook_sa_recs,$faceBookValueFrom,$faceBookRecords);
 		$projectAnalizer = new SemantriaAnalyser();
 		$faceBook_analyzedArray =  $projectAnalizer->analyzeProjectData($face_book_current_array);
 		$this->m_semantria_persistence->UpdateFaceBookAnalyzedData($faceBook_analyzedArray);
 		$faceBookValueFrom = $faceBookValueFrom + $faceBookRecords;
 		}
 		
 		
 		
 		//Twitter
 		$twitter_sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysDataForTwitter();
 		$twitterRecords = 12;
 		$twitterLength = count($twitter_sa_recs);
 		$twitterValueFrom = 0;
 		$twitterValueTo = 12;
 		$twitterCount = 0;
 			
 		if($twitterLength > $twitterRecords){
 			$twitterCount = ceil ( $twitterLength/$twitterRecords );
 		}else{
 			$twitterCount = $twitterLength;
 		}
 		for ($i=1; $i<=$twitterCount; $i++)
 		{
 		$twitter_current_array = array_slice($twitter_sa_recs,$twitterValueFrom,$twitterRecords);
 		$projectAnalizer = new SemantriaAnalyser();
 		$twitter_analyzedArray =  $projectAnalizer->analyzeProjectData($twitter_current_array);
 		$this->m_semantria_persistence->UpdateTwitterAnalyzedData($twitter_analyzedArray);
 		$twitterValueFrom = $twitterValueFrom + $twitterRecords;
 		}
 		
 		
 		//Reddit
 		$reddit_sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysDataForReddit();
 		$redditRecords = 12;
 		$redditLength = count($reddit_sa_recs);
 		$redditValueFrom = 0;
 		$redditValueTo = 12;
 		$redditCount = 0;
 			
 		if($redditLength > $redditRecords){
 			$redditCount = ceil ( $redditLength/$redditRecords );
 		}else{
 			$redditCount = $redditLength;
 		}
 		for ($i=1; $i<=$redditCount; $i++)
 		{
 		$reddit_current_array = array_slice($reddit_sa_recs,$redditValueFrom,$redditRecords);
 		$projectAnalizer = new SemantriaAnalyser();
 		$reddit_analyzedArray =  $projectAnalizer->analyzeProjectData($reddit_current_array);
 		$this->m_semantria_persistence->UpdateRedditAnalyzedData($reddit_analyzedArray);
 		$redditValueFrom = $redditValueFrom + $redditRecords;
 		}
 		
 		
 		
 		//Instagram
 		$instagram_sa_recs = $this->m_semantria_persistence->GetSentimentAnalysysDataForInstagram();
 		$instagramRecords = 12;
 		$instagramLength = count($instagram_sa_recs);
 		$instagramValueFrom = 0;
 		$instagramValueTo = 12;
 		$instagramCount = 0;
 			
 		if($instagramLength > $instagramRecords){
 			$instagramCount = ceil ( $instagramLength/$instagramRecords );
 		}else{
 			$instagramCount = $instagramLength;
 		}
 		for ($i=1; $i<=$instagramCount; $i++)
 		{
 		$instagram_current_array = array_slice($instagram_sa_recs,$instagramValueFrom,$instagramRecords);
 		$projectAnalizer = new SemantriaAnalyser();
 		$instagram_analyzedArray =  $projectAnalizer->analyzeProjectData($instagram_current_array);
 		$this->m_semantria_persistence->UpdateInstagramAnalyzedData($instagram_analyzedArray);
 		$instagramValueFrom = $instagramValueFrom + $instagramRecords;
 		}   
        
    }
}
?>