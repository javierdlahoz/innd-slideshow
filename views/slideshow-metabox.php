<?php
//nonce field for security
wp_nonce_field( 'meta-box-save', 'in-slideshow-plugin' );
?>

<table>
    <tr>
        <td> <?php echo __('Subtitle', 'in-slideshow-plugin') ;?> :</td>
        <td><input type="text" name="slide_subtitle" value="<?php echo esc_attr( $subtitle ); ?>" size="50"></td>
    </tr>
    <tr>
        <td> <?php echo __('Link', 'in-slideshow-plugin'); ?> :</td>
        <td><input type="text" name="slide_link" value="<?php echo esc_attr( $link ); ?>" size="50"></td>
    </tr>
    <tr>
        <td><?php echo __('Video', 'in-slideshow-plugin'); ?> :</td>
        <td><input type="text" name="slide_video" value="<?php echo esc_attr( $video ); ?>" size="50"></td>
    </tr>
</table>
	