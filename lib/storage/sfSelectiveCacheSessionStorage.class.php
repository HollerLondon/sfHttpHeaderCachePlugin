<?php
/**
 * Selectively disable the session for individual routes
 *
 * @package default
 * @author Ben Lancaster
 **/
class sfSelectiveCacheSessionStorage extends sfCacheSessionStorage
{
  public function initialize($options = array())
  {
    if(!$this->isSessionlessRoute())
    {
      // initialize parent
      return parent::initialize(array_merge(
        array(
          'session_name'              => 'sfproject',
          'session_cookie_lifetime'   => '+30 days',
          'session_cookie_path'       => '/',
          'session_cookie_domain'     => null,
          'session_cookie_secure'     => false,
          'session_cookie_http_only'  => true,
          'session_cookie_secret'     => 'sf$ecret'
        ), $options
      ));
    }
    else
    {
      $this->options = array_merge(array(
        'auto_shutdown' => true,
      ), $options);
    }
    return true;
  }
  
  public function regenerate($destroy = false)
  {
    if($this->isSessionlessRoute())
    {
      return;
    }
    else
    {
      return parent::regenerate($destroy);
    }
  }
  
  public function shutdown()
  {
    if($this->isSessionlessRoute())
    {
      return;
    }
    else
    {
      return parent::shutdown();
    }
  }
  
  
  protected function isSessionlessRoute()
  {
    return (bool) sfContext::getInstance()->getRequest()->getParameter('no_session',false);
  }
} // END class 