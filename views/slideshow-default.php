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

        <div class="container">
            <div class="carousel-caption">
                <h1><a style="text-decoration:none;" href='<?php echo $link; ?>'><?php echo $title; ?></a></h1>
                <?php if(isset($subtitle)): ?>
                    <p><a href='<?php echo $link; ?>'><?php echo $subtitle;  ?> </a></p> 
                <?php endif; ?>
                
                <?php if($link != ""): ?>
                    <p><a class="slide-bt" style="text-decoration:none;" href='<?php echo $link; ?>'>Learn More</a></p>
                <?php endif; ?>
            </div> 
        </div>
</div>

