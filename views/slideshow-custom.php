
    <div class="item <?php echo $active; ?>">
            
            <?php 
                if($slideshowType == "video" && $video != ""){
                    self::showIframe($video);
                }
                else if($image != ""){
            ?>
                <img class='image' src="<?php echo $image; ?>" alt="<?php echo $title; ?>">
            <?php 
              }  
            ?>

        <div class="carousel-caption">
            <h1><a style="text-decoration:none;" href='<?php echo $link; ?>'><?php echo $title; ?></a></h1>
            <?php  
                foreach( $data as $key => $value ):
            ?>

                <?php if( isset( $value )): ?>
                    <p><a href='<?php echo $link; ?>'><?php echo $value;  ?> </a></p> 
                <?php endif; ?>

            <?php
                endforeach;
            ?>
            <div class="underline"></div>  
            <?php if($link != ""): ?>
                <p><a class="slide-bt" style="text-decoration:none;" href='<?php echo $link; ?>'>Learn More</a></p>
            <?php endif; ?>
         
        </div>   
    </div>

