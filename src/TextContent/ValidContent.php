<?php
namespace Chp\TextContent;

include_once(__DIR__ . '/../../app/config/text-content.php');

/**
 * Validate and valid help class for TextContent
 * Made by Rasmus Berg (c) 2014-2017
 *
 * @property  object  $url  Anax-MVC url-handler class
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
   * @return    void
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
	 * @param  	object  	$content	Content to link to
	 * @return 	string    	   	 	  With url for content
	 */
	public function getUrlToContent($content) {
    if(isset($this->types[$content->type])){
      $type = $this->types[$content->type]; // Get type from type index
	  
      return $this->url->create("{$this->urlPrefix}{$type['url']}{$type['prefix']}{$content->{$type['field']}}");
    }
    
    return null;
	}
	
  /**
	 * Return array with all content types title and keys (Use for content-type select) 
	 *
	 * @return		array		$types	Array with the types title and keys
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
	 * @return		array		$this->filters	Array with the filters
	 */
  public function getFilters(){
    return $this->filters; 
  }
  
	/**
	 * Return name of one specific type
	 *
	 * @params  string  $type                        Type key
	 * @return  string  $this->types[{key}]['title'] Type title
	 */
	public function getTypeTitle($type){
		return $this->types[$type]['title'];
	}
  
  /**
	 * Create a slug of a string, to be used as url.
	 *
	 * @param   string   $str  String to format as slug.
	 * @return  string   $str  Formatted slug. 
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
   * @param   string      $datetime     When it will be published
   * @return  boolean     True/false    Validate result
   */
  public function checkIfAvailable($datetime){
    return ($datetime <= date('Y-m-d H:i:s')) ? true : false;
  }
  
	/**
	 * Check so the choosed type exist.
	 *
	 * @param   	string		$type		    Choosed type on content
	 * @return 	  boolean   True/false  Validate result
	 */
	public function checkType($type = ''){
		return isset($this->types[$type]);
	}
  
  /**
   * Validate posted datetime so it is correct
   *
   * @param   string    $datetime      Posted datetime to check  
   * @return  boolean   True/false     Validate status
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
   * @param   string    $value        Value from form-element to validate
   * @return  boolean   True/false    Validate result
   */
  public function minimumLength($value){
    return (strlen($value) >= $this->miniLength);
  }
  
  /**
   * Validate slug url
   *
   * @param   string    $url          Url to validate
   * @return  boolean   True/false    True if valid otherwish false
   */
  public function validateSlug($url){
    return ($this->slugify($url) == $url);
  }
  
  /**
	 * Check so the selected filter exist.
	 *
	 * @param     string 	  $filter   Selected filter
	 * @return    boolean   $result   Return the result of test
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