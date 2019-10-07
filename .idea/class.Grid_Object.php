<?php
require_once 'class.Object.php';
require_once 'class.LayoutTemplate.php';
        
class Grid_Object extends Object
{
    public static $inst;
    public $columns = array(4,3,2,1);
    private $styles = '';
    public $classname = 'default';
    public $no_content_classname = 'default-no-content';

    // **************************************************
    // Constructor.
    // **************************************************
    public function __construct()
    {
        parent::__construct();
    }

    public function process()
    {       
        $output = array();
        $columns = $this->columns;
        $content = $this->content;
                
        if( !$content )
            return 'No content specified. Content must be an array';
                
        if( !is_array($columns) )
        {
            if( !is_numeric($columns) )
                return 'The separators should be either an array of numbers or a single number';
                
            $columns = array($columns);
        }
                
        if( !is_array($content) )
            $content = array($content);
                
        $output[] = $this->addGrid($content, $columns);

        $this->data = $output;

        return implode("\n", $output);
    }
        
    private function addGrid($content, $columns)
    {
        $count = 1;
        $output = array();

        foreach( $content as $_data )
        {
            // Create a new Grid template object
            $_tpl = new LayoutTemplate('grid.tpl');
            $_tpl->class = $this->classname;
            $_tpl->no_content_class = $this->no_content_classname;
            $_tpl->content = $_data;
            $_tpl->number = $count;
            $_tpl->columns = $this->addCOlumns($columns, $count);
            $output[] = $_tpl->getHtml(true);
            unset($_tpl);
            $count++;
        }
                
        return implode("\n", $output);
    }

    public function setColumns(Array $columns)
    {
        $this->columns = $columns;
    }

    public function setClassName($classname)
    {
        $this->classname = $classname;
    }
        
    function addColumns($columns, $count)
    {
        $output = array();

        // Add grid separators as needed
        foreach( $columns as $column_at )
        {
            if( !is_numeric($column_at) )
                return 'The separators should be numbers.';
                    
            if( $count % $column_at == 0 )
            {
                $column = new LayoutTemplate('grid-column.tpl');
                $column->class = 'grid-'. $column_at .'-clearer '. $this->classname. '-grid-'. $column_at .'-clearer';
                $output[] = $column->getHtml(true);
                unset($column);
            }
        }
                    
        return implode("\n", $output);
    }

    function addDividers($columns, $count)
    {
        if( $this->get('add_dividers') == 'no' )
            return '';
                
        $output = array();
        $divider = new LayoutTemplate('grid-divideline.tpl');
        $divider->class = '';

        // Add grid separators as needed
        foreach( $columns as $column_at )
        {
            if( !is_numeric($column_at) )
                return 'The separators should be numbers.';
                    
            if( $count % $column_at == 0 )
                $divider->class .= ' grid-'. $column_at .'-divideline';
        }
                
        $output[] = $divider->getHtml(true);
        unset($divider);
                        
        return implode("\n", $output);
    }
        
    public static function getInstance()
    {
        if( !isset(self::$inst) )
            self::$inst = new self;

        return self::$inst; 
    }
}