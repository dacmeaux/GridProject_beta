# GridProject
Class for creating an automatically responsive grid

This class is used to create a grid of like items such as thumbnails for a catalog or a photo gallery. It creates a grid of an arbitrary number of columns and breakpoints (CSS media query breakpoints) specified by the viewport width of any device. This class also creates the necessary CSS to acompany the generated grid. 

__Here's How it Works__
1. pass an array of data to the grid object (any HTML, text, etc).
2. set the breakpoints for the grid (associative array with the column count as the key and the target viewport width as its value).
3. specify a css classname base for the grid.
4. instruct the grid to generate the appropriate CSS.
5. render the grid.
6. *That's it.* 

__Example Implementation__

        require_once 'class.Grid_Object.php';
        $grid = Grid_Object::getInstance();
        
        // Set Breakpoints for this grid in descending order as number_of_columns=>viewport_width
        (notice that colum five viewport width is only one pixel greater than column four viewport width. 
        This is because the first colum uses min-width and all others use max-width for breakpoints. 
        This ensures that the largest column count will be active from the next breakpoint up to the max viewport width).
        $grid->setColumns(array(5=>1025, 4=>1024, 3=>960, 2=>768, 1=>480));
        
        // Set base classname for the grid
        $grid->setClassName('thumbs');
        
        // Add an array of content to the grid
        $grid->setContent(array('Cell One', 'Cell Two', 'Cell Three', 'Cell Four', 'Cell FIve', 
        'Cell Six', 'Cell Seven', 'Cell Eight', 'Cell   Nine', 'Cell Ten'));
        
        // Instruct the grid to generate CSS
        // You can create your own CSS but this is much more convenient
        $grid->generateCss(true);
        
        // Set debug to see grid cells with color backgrounds
        $grid->setDebug(true);

        // Render the grid
        $html = $grid->process();
        
        // Retrive the generated CSS
        $css = $grid->getCss();
  
