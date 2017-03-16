<?php
namespace Chp\TextContent;

include_once(__DIR__ . '/../../app/config/text-content.php');

/**
 * Validate and valid help class for TextContent
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @Property  Object  $url  Anax-MVC url-handler class
 */
class ValidContent implements \Anax\DI\IInjectionAware
{
  use \Anax\DI\TInjectable;
  
  private $miniLength; // Minimum length on text-fields (ex. ingress, title etc)
  private $urlPrefix;
  private $filters;
  private $types;
  
  /**
   * Initialize the controller
   *
   * @Return    Void
   */
  public function initialize(){
    $this->miniLength = CHP_TC_MINILENGTH; // Minimum length on text-fields (ex. ingress, title etc)
    $this->urlPrefix  = CHP_TC_URLPREFIX;
    $this->filters = unserialize(CHP_TC_FILTERS);
    $this->types = unserialize(CHP_TC_TYPES);
  }
  
  /**
	 * Create a link to the content, based on its type.
	 *
	 * @Param  	Object  	$content	Content to link to
	 * @Return 	String    	   	 	  With url for content
	 */
	public function getUrlToContent($content) {
    if(isset($this->types[$content->type])){
      $type = $this->types[$content->type]; // Get type from type index
	  
      return $this->url->create($this->urlPrefix . "{$type['url']}{$type['prefix']}{$content->{$type['field']}}");
    }
    
    return null;
	}
	
  /**
	 * Return array with all content types title and keys (Use for content-type select) 
	 *
	 * @Return		Array		$types	Array with the types title and keys
	 */
	public function getTypes(){
    $types = array();
    
		// Loop through and save types key as key and title as value in a new array
		foreach($this->types AS $key => $value){
			$types[$key] = $value['title'];
		}
		
		return $types;
	}
  
  /**
	 * Return array with all filters 
	 *
	 * @Return		Array		$this->filters	Array with the filters
	 */
  public function getFilters(){
    return $this->filters; 
  }
  
	/**
	 * Return name of one specific type
	 *
	 * @Params  String $type  Type key
	 * @Return  String        Type title
	 */
	public function getTypeTitle($type){
		return $this->types[$type]['title'];
	}
  
  /**
	 * Create a slug of a string, to be used as url.
	 *
	 * @Param   String   $str  String to format as slug.
	 * @Return  String   $str  Formatted slug. 
	 */
	public function slugify($str) {
	  $str = mb_strtolower(trim($str));
		
		$str = str_replace(array("å","ä","ö"), array("a","a","o"), utf8_decode(utf8_encode($str)));
		
	  $str = preg_replace('/[^a-z0-9-_()]/', '_', $str);
	  $str = trim(preg_replace('/_+/', '_', $str), '_');
    
	  return $str;
	}
  
  /**
   * Check if content is published
   *
   * @Param   String      $datetime     When it will be published
   * @Return  Boolean     True/false    Validate result
   */
  public function checkIfAvailable($datetime){
    return ($datetime <= date('Y-m-d H:i:s')) ? true : false;
  }
  
	/**
	 * Check so the choosed type exist.
	 *
	 * @Param   	String		$type		Choosed type on content
	 * @Returns 	Boolean      			Validate result
	 */
	public function checkType($type){
		return isset($this->types[$type]); 
	}
  
  /**
   * Validate posted datetime so it is correct
   *
   * @Param   String    $datetime      Posted datetime to check  
   * @Return  Boolean   True/false     Validate status
   */
  public function checkDatetime($datetime){
    if(isset($datetime) && !empty($datetime)){
      $format = 'Y-m-d H:i:s';
      $d = \DateTime::createFromFormat($format, $datetime);
      return $d && $d->format($format) == $datetime;
    }
    return true;
  }
  
  /**
   * Minimum length
   *
   * @Param   String    $value        Value from form-element to validate
   * @Return  Boolean   True/false    Validate result
   */
  public function minimumLength($value){
    return (strlen($value) >= $this->miniLength);
  }
  
  /**
   * Validate slug url
   *
   * @Param   String    $url          Url to validate
   * @Return  Boolean   True/false    True if valid otherwish false
   */
  public function validateSlug($url){
    return ($this->slugify($url) == $url);
  }
  
  /**
	 * Check so the select filters exist.
	 *
	 * @Param     Array 	  $filters  Array with select filters
	 * @Return    Boolean   $result   Return the result of test
	 */
	public function checkFilter($filter = null){
	  if(!empty($filter)){
      // For each filter, check if the filter exist
      foreach($this->filters as $val){
        if($val == $filter)
          return true;
      }
      return false;
    }
	  return true;
	}
}