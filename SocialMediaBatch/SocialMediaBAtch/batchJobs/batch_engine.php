<?php
/**
 * Created by Nunc Devolopers.
 * User: venkat
 * Date: 19/8/13
 * Time: 6:09 PM
 */

class BatchRoot
{
    var $m_php_self;
    var $m_session;
    var $m_current_log_level;
    var $m_start_exec_time;

    function BatchRoot()
    {
        $this->m_current_log_level = "INFO1";
        $this->m_php_self = @$_SERVER['SCRIPT_URL'];
        $this->m_start_exec_time = $this->Microtime();
    }

    function Setup()
    {
    }

    function SetupWSession()
    {
        $this->m_session = new SessionClass( );
    }

    function DecodeUrlStrRaw($key, $val="")
    {
        if(@$_GET["$key"])
            $val= $_GET["$key"];
        else
        {
            if(@$_POST["$key"])
                $val= $_POST["$key"];
        }

        return trim($val);
    }

    function DecodeClean($key)
    {
        $val="";
        $val =  $this->DecodeUrlStrRaw($key, $val);
        $val =  Helper::StripSlashes($val);
        return trim($val, ' ');
    }

    function GetIntValue($key)
    {
        $val = intval($this->DecodeUrlStr($key));
        return $val;
    }

    function DecodeDBVal($key, $val="")
    {
        $val =  $this->DecodeUrlStrRaw($key, $val);
        $val = str_replace('"','',$val);
        return trim($val);
    }

    function DecodeUrlStr($key, $val="")
    {
        $val =  $this->DecodeUrlStrRaw($key, $val);
        $val = str_replace('"','\"',$val);
        $val = str_replace("\\\\\"","\\\"",$val);
        return trim($val);
    }

    function DecodeUrlStrAsArray($key, $val=array())
    {
        if(@$_GET["$key"])
            $val= $_GET["$key"];
        else
        {
            if(@$_POST["$key"])
                $val= $_POST["$key"];
        }

        return  ($val);
    }

    function GetSetSessionValues($session_var, &$session_fld,$int=0 )
    {
        if($int)
            $session_fld=intval($this->DecodeUrlStr( $session_var ));
        else
            $session_fld=$this->DecodeUrlStr( $session_var );

        if( !$session_fld )
            $session_fld = $this->m_session->GetSessionValue( $session_var );
        else
            $this->m_session->SetSessionValue( $session_var, $session_fld );
    }

    function GetSessionValue($session_var)
    {
         return $this->m_session->GetSessionValue( $session_var );
    }

    function SetSessionValue($key,$value)
    {
        $this->m_session->SetSessionValue($key,$value);
    }

    function GetServerSettings($fldname="")
    {
        if($fldname)
            $retval=@$_SERVER[$fldname];
        else
            $retval=$_SERVER;

        return $retval;
    }

    function CleanUp()
    {

    }


    function CreateGUID()
    {

        // The field names refer to RFC 4122 section 4.1.2

        return sprintf('%04x%04x-%04x-%03x4-%04x-%04x%04x%04x',
            mt_rand(0, 65535), mt_rand(0, 65535), // 32 bits for "time_low"
            mt_rand(0, 65535), // 16 bits for "time_mid"
            mt_rand(0, 4095),  // 12 bits before the 0100 of (version) 4 for "time_hi_and_version"
            bindec(substr_replace(sprintf('%016b', mt_rand(0, 65535)), '01', 6, 2)),
            // 8 bits, the last two of which (positions 6 and 7) are 01, for "clk_seq_hi_res"
            // (hence, the 2nd hex digit after the 3rd hyphen can only be 1, 5, 9 or d)
            // 8 bits for "clk_seq_low"
            mt_rand(0, 65535), mt_rand(0, 65535), mt_rand(0, 65535) // 48 bits for "node"
        );
    }


    function ReplaceVals($row_data,$html_contet)
    {
        if($row_data)
        {
            foreach($row_data as $key_val => $col_val )
                $html_contet = str_replace("<!--$key_val-->",$col_val,$html_contet);
        }
        return $html_contet;
    }

    function ReplaceHTMLVals($row_data,$html_contet)
    {
        if($row_data)
        {
            foreach($row_data as $key_val => $col_val )
                $html_contet = str_replace("<!--$key_val-->",htmlentities($col_val),$html_contet);
        }
        return $html_contet;
    }

    function DisplayLog($str,$prefix="")
    {
        if( is_array($str) )
        {
            echo(date("m/d/y h:i:s")." $prefix - <br/><pre><br/>");
            print_r($str);
            echo(date("m/d/y h:i:s")."</pre><br/>");
        }
        else
            echo(date("m/d/y h:i:s")." {$prefix} - {$str} <br/>\n");
    }

    function CommentLog($str,$prefix="")
    {
        echo("<!--\n");
        if( is_array($str) )
        {

            print_r($str);
        }
        else
            echo(" {$prefix} - {$str} \n");
        echo("-->\n");
    }

    function InfoMessages($methodname,$str)
    {
        if( $this->m_current_log_level == "INFO" )
        {
            $this->DisplayLog( $str,$methodname);
        }
    }

    function ErrorMessages($methodname,$str)
    {
        if( $this->m_current_log_level == "ERROR" ||  $this->m_current_log_level == "WARN" || $this->m_current_log_level == "INFO" )
        {
            $this->DisplayLog( $str,$methodname);
        }
    }

    function WarningMessages($methodname,$str)
    {
        if( $this->m_current_log_level == "WARN" || $this->m_current_log_level == "INFO"  )
        {
            $this->DisplayLog( $str,$methodname);
        }
    }

    function RedirectURL($url)
    {
        global $SCF_URL;
        header("Location: {$SCF_URL}{$url}");
        exit;
    }

    function Microtime()
    {
        list($usec, $sec) = explode(" ", microtime());
        return ((float)$usec + (float)$sec);
    }

    function ExecTime()
    {
        $time_end = $this->Microtime();
        $time_exec = $time_end - $this->m_start_exec_time;
        return round($time_exec,3);
    }

    function GetCurrentTime()
    {
        return date("Y-m-d H:i:s");
    }


}

/*$m_application = new Root();
$m_application->Setup();
$m_application->Run();
$m_application->Cleanup();*/
?>