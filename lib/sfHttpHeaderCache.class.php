<?php
/**
 * Cache class that sets HTTP cache headers with an alternative cache for
 * partials/components.
 *
 * @package     symfony-plugins
 * @subpackage  cache
 * @author      Ben Lancaster <benlanc@ster.me.uk>
 * @author      Christian Schaefer <christian.schaefer@gmail.com>
 * @see         http://snippets.symfony-project.org/snippet/365
 */
class sfHttpHeaderCache extends sfCache
{
  /**
   * @var headers HTTP headers to be set
   */
  private $headers = array();

 /**
  * Initializes this sfCache instance.
  *
  * Available options:
  *
  * * headers:  HTTP headers to be set (array)
  *
  * * see sfCache for options available for all drivers
  *
  * @see sfCache
  */
  public function initialize($options = array())
  {
    parent::initialize($options);

    if (!$this->getOption('headers'))
    {
      throw new sfInitializationException('You must pass a "headers" option to initialize a csHttpCacheHeaderCache object.');
    }

    $this->headers = $this->getOption('headers');
    if($alt = $this->getOption('alt'))
    {
      $this->alt = new $alt['cache']['class']($alt['cache']['param']);
    }
    else
    {
      $this->alt = new sfNoCache;
    }
  }

  /**
   * @see sfCache
   */
  public function get($key, $default = null)
  {
    if($this->isPartial($key))
    {
      return $this->alt->get($key,$default);
    }
    else
    {
      return $default;
    }
  }

  /**
   * @see sfCache
   */
  public function has($key)
  {
    if($this->isPartial($key))
    {
      return $this->alt->has($key);
    }
    return false;
  }

  /**
   * @see sfCache
   */
  public function set($key, $data, $lifetime = null)
  {
    if(false === $this->isPartial($key))  // don't set cache headers for partials
    {
      if(is_object(unserialize($data)))   // don't set cache headers for pages without layout
      {
        $response = sfContext::getInstance()->getResponse();

        foreach($this->headers as $key => $value)
        {
          $value = str_replace('%EXPIRE_TIME%', gmdate("D, d M Y H:i:s", time() + $lifetime), $value);
          $value = str_replace('%LAST_MODIFIED%', gmdate("D, d M Y H:i:s", time()), $value);
          $value = str_replace('%LIFETIME%', $lifetime, $value);
          $value = str_replace('%ETAG%',md5($key), $value);
          $response->setHttpHeader($key, $value, true);
        }
        return true;
      }
    }
    else
    {
      return $this->alt->set($key,$data.time(),$lifetime);
    }
    return true;
  }

  /**
   * @see sfCache
   */
  public function remove($key)
  {
    if($this->isPartial($key))
    {
      return $this->alt->remove($key);
    }
    return true;
  }

  /**
   * @see sfCache
   */
  public function removePattern($pattern)
  {
    return true;
  }

  /**
   * @see sfCache
   */
  public function clean($mode = sfCache::ALL)
  {
    $this->alt->clean($mode);
    return true;
  }

  /**
   * @see sfCache
   */
  public function getLastModified($key)
  {
    if($this->isPartial($key))
    {
      return $this->alt->getLastModified($key);
    }
    return 0;
  }

  /**
   * @see sfCache
   */
  public function getTimeout($key)
  {
    if($this->isPartial($key))
    {
      return $this->alt->getTimeout($key);
    }
    return 0;
  }
  
  /**
   * Check that the given key is a partial or not
   *
   * @param string $key Cache key
   * @return boolean True if partial/component, false if not
   **/
  protected function isPartial($key)
  {
    return false !== strpos($key, '/sf_cache_partial/');
  }
}
