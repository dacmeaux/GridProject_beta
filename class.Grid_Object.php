<?php /** @noinspection PhpMissingFieldTypeInspection */
require_once 'class.GenericObject.php';
require_once 'class.LayoutTemplate.php';
        
class Grid_Object extends GenericObject
{
    public static $inst;
    private $columns = array(4,3,2,1);
    private $styles = '';
    private $classname = 'default';
    private $no_content_classname = 'default-no-content';
    private $content = '';
    private $generate_css = false;
    private $debug = false;
    private $colors = array();

    // **************************************************
    // Constructor.
    // **************************************************

    /**
     * Grid_Object constructor.
     *
     * @access public
     * @return void
     * @since method available since Release 1.0
     */
    public function __construct()
    {
        parent::__construct();
        $this->colors = range(0, 15);
    }


    /**
     * Create the Grid
     *
     * @return string
     * @access public
     * @since method available since release 1.0
     */
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
        $this->addCss($content, $columns);

        $this->data = $output;

        return implode("\n", $output);
    }


    /**
     * @param array $content
     * @param array $columns
     * @return string
     * @access public
     * @since method available since Release 1.0
     */
    private function addGrid(Array $content, Array $columns)
    {
        $count = 1;
        $output = array();

        foreach( $content as $_data )
        {
            // Create a new Grid template object
            $_tpl = new LayoutTemplate('grid.tpl');
            $_tpl->class = $this->classname;

            if( !$content )
                $_tpl->no_content_class = $this->no_content_classname;

            $_tpl->content = $_data;
            $_tpl->number = $count;
            $_tpl->clearers = $this->addClearer(array_keys($columns), $count);
            $output[] = $_tpl->getHtml(true);
            unset($_tpl);
            $count++;
        }
                
        return implode("\n", $output);
    }


    /**
     * Add Content to the grid
     *
     * @param array $content
     * @access public
     * @return void;
     * @since metod available since Release 1.0
     */
    public function setContent(Array $content)
    {
        $this->content = $content;
    }


    /**
     * Add column data to the grid
     *
     * @param array $columns
     * @access public
     * @return void
     * @since method available since Release 2.0
     */
    public function setColumns(Array $columns)
    {
        $this->columns = $columns;
    }


    /**
     * Set base classname for the grid
     *
     * @param $classname
     * @access = public
     * @return void;
     * @since method available since Release 1.0
     */
    public function setClassName($classname)
    {
        $this->classname = $classname;
    }


    /**
     * Adds a appropriate clearers to grid cells
     *
     * @param array $columns
     * @param int $count
     * @return string
     * @access private
     * @since method available since Release 2.0
     */
    private function addClearer(Array $columns, Int $count)
    {
        $output = array();

        // Add grid separators as needed
        foreach( $columns as $column_at )
        {
            if( !is_numeric($column_at) )
                return 'The columns should be numbers.';
                    
            if( $count % $column_at == 0 )
            {
                $clearer = new LayoutTemplate('grid-clearer.tpl');
                $clearer->classname = 'grid-'. $column_at .'-clearer '. $this->classname. '-grid-'. $column_at .'-clearer';
                $output[] = $clearer->getHtml(true);
                unset($clearer);
            }
        }
                    
        return implode("\n", $output);
    }


    /**
     * Generates CSS for this Grid
     *
     * @param bool generate
     * @access public
     * @return void
     * @since method available since version 2.0
     */
    public function generateCss($generate = true)
    {
        $this->generate_css = $generate;
    }

    /**
     * Creates the CSS for this grid
     *
     * @param array content
     * @param array columns
     * @access private
     * @return void|string
     * @since method available since Release 2.0
     */
    private function addCss($content, $columns)
    {
        $css = array();
        $count = 0;
        $class_rules = array();
        $color_hex = '000000';
        $text_color_hex = '000000';

        for( $i = 0; $i < sizeof($content); $i++ )
        {
            $x = 0;

            if( $this->debug )
            {
                $color_hex = dechex($this->colors[rand(0, 15)]) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15));

                $text_color_hex = dechex($this->colors[rand(0, 15)]) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15)) .
                    dechex(rand(0, 15));
            }

            foreach( $columns as $column_at=>$viewport_width)
            {
                $other_columns = $columns;
                unset($other_columns[$column_at]);

                if( !is_numeric($column_at) )
                    return 'The separators should be numbers.';

                $media_tpl = new LayoutTemplate('media.txt');
                $media_tpl->width = ($x == 0 ? 'min-width: ' : 'max-width: ') . $viewport_width;
                $_rules = '.'. $this->classname .'-grid{width:'. round(100 / $column_at, 2) .'%;}' ."\n";
                $_rules .= '.'. $this->classname .'-grid-'. $column_at .'-clearer{display:block;}';

                foreach( $other_columns as $other_column_at=>$other_column_viewport_width )
                {
                    $_rules .= '.grid-'. $other_column_at .'-clearer{display: none;}';
                }

                $media_tpl->class_rules = $_rules;

                if( in_array($_rules, $class_rules) )
                    continue;
                else
                    $css[] = $media_tpl->getHtml(true);

                $class_rules[] = $_rules;

                $x++;
            }

            $count++;

            if( $this->debug )
                $this->styles .= '.'. $this->classname .'-grid-'. $count .'{background-color:#'. $color_hex .'; color:#'. $text_color_hex .';}';
        }

        $this->styles .= "\n". implode("\n", $css) ."\n";

        return '';
    }


    /**
     * Retrieve the CSS generated for this grid
     *
     * @return string
     * @access public
     * @return void
     * @since method available since Release 2.0
     */
    public function getCss()
    {
        return $this->styles;
    }


    /**
     * Set debug flag
     *
     * @param $debug
     * @access public
     * @return void
     * @since method available since Release 2.0
     */
    public function setDebug($debug)
    {
        $this->debug = $debug;
    }


    /**
     * Return an instance of the Grid Object (singleton)
     *
     * @return GenericObject|Grid_Object
     * @access public|static
     * @since method available since Release 1.0
     */
    public static function getInstance()
    {
        if( !isset(self::$inst) )
            self::$inst = new self;

        return self::$inst; 
    }
}