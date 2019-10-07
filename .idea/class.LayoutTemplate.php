<?php
/**
 * Token Handler class
 * Templating Engine with token replacement and temporary data storage
 * 
 * @package      Layout
 * @author       Duane A. Comeaux <dacmeaux@gmail.com>
 * @license      http://www.php.net/license/3_0.txt  PHP License 3.0
 * @version      Release: @2.0@
 * @since        Class available since Release 1.0
 */
class LayoutTemplate{
    public static $inst;
    private $params = array();
    private $template = '';
    public $html = '';
    public $stored_html = array();
    private $processed = false;
    private $token_pattern = '/\{\{[\w]*\}\}/';
    private $rstry;

    /**
     * Constructor
     * 
     * @param string        template    the filename or the html layout string
     * @param object       skin_obj    The Skin Object
     * @param object       page_obj    The Page Object
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function __construct($template = 'default.tpl'){
        $this->rstry = getRegistry();
    
        if( $template != '' )
            $this->loadTemplate($template);
        else
            return false;
    }

    /**
     * Loads a template from passed in filename or sets the layout to passed in string
     * 
     * @param string        template    the filename or the html layout string
     * @param object       skin_obj    The Skin Object
     * @param object       page_obj    The Page Object
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function loadTemplate($template = 'default'){
        $file = $this->rstry->obj->page->getPath($template, 'templates', false);
    
        if( is_file($file) )
            $content = file_get_contents($template, true);
        else
            $content = $template;

        $this->html = $content;
    }

    /**
     * Determines if the html string contains any tokens
     * 
     * @access public
     * @return boolean
     *  @since      Method available since Release 1.0
     */
    public function hasTokens(){
        return preg_match($this->token_pattern, $this->html);
    }
    
    public function importParams(Array $params){
        $this->params = $params;
    }

    /**
     * Sets a token name and value and adds it to the params array
     * 
     * @param string         name                   name of the token
     * @param string         value                  value of the token
     * @param boolean        overwritre             does this need to replace or append values
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function setParam($name, $value = '', $overwrite = true){
        if( is_array($name) )
        {
            foreach( $name as $k=>$v )
                $this->params[$k] = $v;
        }
        else
        {
            if( !$overwrite )
                $value = $this->getParam($name) ."\n". $value;

            $this->params[$name] = $value;
        }
    }

    /**
     * Gets a token value from the params array
     * @param string        name    the name of the token to retrieve
     * @access public
     * @return string
     * @since      Method available since Release 1.0
     */
    public function getParam($name = ''){
        if( $name == '' )
            return $this->params;
        else
            return ($this->params[$name] != '' ? $this->params[$name] : '');
    }

    /**
     * Sets a token name and value and adds it to the params array
     * This is a PHP Overload Method that does exactly what LayoutToken::setParam() does,
     * but only takes two arguments. This is a quick way to add a single value that is
     * always overwritten when set.
     * Set like $object->name = value
     * @param string         name                     name of the token
     * @param string         value                    value of the token
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function  __set($name, $value){
        $this->params[$name] = $value;
    }

    /**
     * Gets a token value from the params array
     * This is a PHP Overload Method that does exactly what LayoutToken::getParam() does,
     *  but only takes one arguments.
     * Get like $object->name
     * 
     * @param string        name    the name of the token to retrieve
     * @access public
     * @return string
     * @since      Method available since Release 1.0
     */
    public function __get($name){
        return $this->params[$name];
    }

    
    public function preProcess($html){
        foreach( $params as $k=>$v )
            $html = str_replace('{{'. strtoupper($k) .'}}', $v, $html);

        $html = clean($html);

        return $html;
    }
    
    /**
     * Processes the tokens
     * 
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function process($clean = false){
        if( !$this->hasTokens() )
        {
            $this->processed = true;
            return;
        }

        // First handle all template type tokens
        foreach( $this->params as $k=>$v )
        {       
            if( preg_match('/_template/', $k) )
            {
                $this->replaceToken($k, $v);
                unset($this->params[$k]);
            }
        }
    
        // Then handle regular tokens
        foreach( $this->params as $k=>$v )
        {
            $this->replaceToken($k, $v);
        }
    
        if( $clean )
        {
            $this->clean();
            $this->processed = true;
        }
    }

    /**
     * Gets the processed html string
     * 
     * @access public
     * @return string
     * @since      Method available since Release 1.0
     */
    public function getHtml($clean = false){
        if( !$this->processed )
            $this->process($clean);

        return stripslashes($this->html);
    }
    
    /**
     * Add a snippet of html to the stored_html array for latter retrieval
     * @param string        html    the html snippet
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function addStoredHtml($html){
        $this->stored_html[] = $html;
    }
    
    /**
     * Gets the stored_html value
     * 
     * @access public
     * @return string
     * @since      Method available since Release 1.0
     */
    public function getStoredHtml(){
        return implode("\n", $this->stored_html);
    }

    /**
     * Replaces all occurances of a token in the html property
     * 
     * @param string        k    the name of the token in the pattern
     * @param string        v    the value to set the token to
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function replaceToken($k, $v, $declarative = false){
        $k = (!$declarative ? strtoupper($k) : $k);
        $this->html = preg_replace('/\{\{'. $k .'\}\}/', $v, $this->html);
    }

    /**
     * Cleans the html proprty of all orphanded tokens
     * @param string        html    the string to be cleaned
     * @access public
     * @return void
     * @since      Method available since Release 1.0
     */
    public function clean($html = ''){
        if( $html == '' )
            $html = $this->html;

        $this->html = preg_replace($this->token_pattern, '', $html);
    }

    /**
     * Returns an instance of this class
     */
    public static function getInstance(){
        return new self();
    }

    /****************
     * Can not be cloned
     */
    public function __clone(){
        trigger_error('Can not clone this object', E_USER_ERROR);
    }
}