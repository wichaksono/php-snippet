<?php

function compressResize( $imagePath, $maxWidth = 800 )
{
    $CI =& get_instance();

    if ( is_readable( $imagePath ) ) {

        // get image property
        $getImageSize = getimagesize( $imagePath );

        if ( $getImageSize[0] > $maxWidth ) {
            $config['image_library'] = 'gd2';
            $config['source_image']  = $imagePath;
            $config['width']         = $maxWidth;

            $CI->load->library( 'image_lib', $config );
            $CI->image_lib->resize();

            return true;
        }
    }

    return false;
}
