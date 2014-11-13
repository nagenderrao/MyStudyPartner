<?php
class BatchAdmin extends BatchRoot
{
    var $m_user;
    function BatchAdmin()
    {
        $this->Root();
        $this->m_user = new User();
    }

    function CLAdminSetup()
    {
        $this->SetupWSession();
        $this->m_user->UserSetup();
        $this->m_user->IsUserLoggedIn();
    }

    function GetSetSessionValues2($session_var, &$session_fld )
    {
        $session_fld=intval($this->DecodeUrlStr( $session_var ));
        if( !$session_fld )
            $session_fld = $this->m_session->GetSessionValue( $session_var );
        else
            $this->m_session->SetSessionValue( $session_var, $session_fld );
    }

    function GetSetSessionStrValues($session_var, &$session_fld, $default_val='' )
    {
        $session_fld=$this->DecodeUrlStr( $session_var );
        if( !$session_fld )
            $session_fld = $this->m_session->GetSessionValue( $session_var );
        else
            $this->m_session->SetSessionValue( $session_var, $session_fld );

        if( $session_fld == '' )
            $session_fld = $default_val;
    }

    function ClearSession($session_var)
    {
        $this->m_session->SetSessionValue( $session_var, '' );
    }

    function IsCLAdminUser()
    {
        return $this->m_user->m_is_CLAdmin;
    }

    function IsRootUser()
    {
        return $this->m_user->m_is_root;
    }
}
?>