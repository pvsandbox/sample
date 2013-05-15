<?php
/**
 * @author websiteman@gmail.com
 * @since Feb 23 2012
 */

/**
 * Google Analytics wrapper for adding tracking to the site server-side
 */
class Analytics_GA
{
   /**
    * Instance of self
    * @var Analytics_GA self
    */
   protected static $instance = null;

   /**
    * GA profile id
    * @var String
    */
   protected $profileId = null;

   /**
    * Google Analytics include script
    * @var String
    */
   protected $jsInclude = null;

   /**
    * Array containing all the methods to track in GA
    * @var Array
    */
   protected $gaq = array();

   /**
    * tracker namespace
    */
   protected $namespace = 'siteTracker.';

   /**
    * Constructor...
    * @param string $profileId
    */
   public function __construct($profileId)
   {
      $gaJs = <<<GASCRIPT
(function() {
   if (typeof _gat != "undefined") return;
   var ga = document.createElement('script'); ga.type = 'text/javascript'; ga.async = true;
   ga.src = ('https:' == document.location.protocol ? 'https://ssl' : 'http://www') + '.google-analytics.com/ga.js';
   var s = document.getElementsByTagName('script')[0]; s.parentNode.insertBefore(ga, s);
})();
GASCRIPT;

      $this->profileId = $profileId;
      $this->jsInclude = $gaJs;
      self::$instance = $this;

   } // __construct()

   /**
    * Get the instance of this tracker, add a new instance if
    * one does not exist already
    * @param string $profileId [optional] used only wehen a new instance is created
    */
   public static function getInstance($profileId = null)
   {
      if(empty(self::$instance) && empty($profileId))
      {
         throw new InvalidArgumentException("No tracker instance, 'profileId' argument is required");
      }

      if (empty(self::$instance))
      {
         new self($profileId);
      }

      return self::$instance;

   } //getInstance()

   /**
    * Set tracker namespace
    * @param String $namespace
    */
   public function setNameSpace($namespace)
   {
      //end in a dot for easier concatanation
      $this->namespace = $namespace.'.';

      return $this;

   } // setNamespace()

   /**
    * Convinence method to get a funnel object for this tracker
    * @param String $funnelName
    * @return Analytics_GA_Funnel
    */
   public function getFunnel($funnelName = 'default')
   {
      $funnel = Analytics_GA_Funnel::getInstance($funnelName, $this);

      return $funnel;

   } // getFunnel()

   /**
    * Add a GA method to the tracker
    * @param String $method see Google Analytics docs for all avilable
    * @param mixed $args string or array of arguments to pass to GA
    * @throws InvalidArgumentException
    * @return Analytics_GA instance of self for chaining
    */
   public function addMethod($method, $args = null)
   {
      $params = '';
      $method = trim($method);

      if (empty($method))
      {
         throw new InvalidArgumentException("Method must be provided");
      }

      // loop over args and build a params string,
      // making sure to preserve the data types for JS
      if (is_array($args))
      {
         foreach ($args as $arg)
         {
            $params .= ', '.$this->getJsValue($arg);
         }
      }
      elseif(isset($args))
      {
         $params = ', '.$this->getJsValue($args);
      }

      // custom GA method is defined
      if (strpos($method, 'function', 0) === 0)
      {
         $this->gaq[] = $method;
      }
      else
      {
         // standard GA method
         $this->gaq[] = "['{$this->namespace}{$method}'{$params}]";
      }

      return $this;

   } // addMethod()

   /**
    * Convert PHP values to GA readable JS representations
    * @param mixed $var
    * @return mixed
    */
   private function getJsValue($var)
   {
      if (is_string($var))
      {
         $var = trim($var);
         return "'$var'";
      }
      elseif (is_bool($var))
      {
         return ($var == true) ? 'true' : 'false';
      }

      return $var;

   } // getJsValue)_

   /**
    * Init GA array; Should be called in the <head>
    * @param bool $wrapped in <script> tags
    * @return string generated JS code
    */
   public function getHeaderJs($wrapped = true)
   {
      $output = "var _gaq = _gaq || [];\n";
      $output .= "_gaq.push(['{$this->namespace}_setAccount', '{$this->profileId}']);";

      if ($wrapped)
      {
         return "<script type='text/javascript'>\n$output\n</script>";
      }

      return $output;

   } // getHeaderJs()


   /**
    * Returns the standard Google Anaylitcs _gaq
    * array filled with all the function calls.
    * Best if called in the HTML Footer before </body>
    * @param bool $wrapped in <script> tags
    * @return string generated JS code
    */
   public function getTrackerJs($wrapped = true)
   {
      $output = '';

      // these should be called last
      $this->addMethod('_trackPageview');
      $this->addMethod('_trackPageLoadTime');

      foreach ($this->gaq as $item)
      {
         $output .= "_gaq.push({$item});\n";
      }

      // append GA script
      $output .= "\n".$this->jsInclude;

      if ($wrapped)
      {
         return "<script type='text/javascript'>\n$output\n</script>";
      }

      return $output;

   } // getTrackerJs()

} // Analytics_GA
?>