 <ol class='carousel-indicators'>
 	<?php
	    $slide = 0;
	    $active = "active";
	    while( $slide < $numSlides) :
    ?>
        <li data-target="#carousel-example-generic"k data-slide-to="<?php echo $slide++; ?>" class="<?php echo $active; ?>"></li>
    <?php
        	$active = "";
    	endwhile;
    ?>
</ol>