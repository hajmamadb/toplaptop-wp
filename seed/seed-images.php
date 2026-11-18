<?php
/**
 * Generate placeholder product images with GD and assign them as featured images.
 * Run with: wp eval-file seed/seed-images.php  (with -d extension=php_gd.dll)
 */

$uploads = wp_upload_dir();
$dir = $uploads['basedir'] . '/tl-placeholder/';
if (!is_dir($dir)) wp_mkdir_p($dir);

// label => [background rgb, text rgb, filename]
$defs = array(
    'gaming'    => array(array(45,52,64),   array(120,220,255), 'gaming.png'),
    'ultrabook' => array(array(208,214,222),array(60,70,90),    'ultrabook.png'),
    'business'  => array(array(120,128,140),array(240,244,248), 'business.png'),
);

function make_placeholder($file, $bg, $fg, $label) {
    $w = 800; $h = 600;
    $im = imagecreatetruecolor($w, $h);
    $bgc = imagecolorallocate($im, $bg[0], $bg[1], $bg[2]);
    $fgc = imagecolorallocate($im, $fg[0], $fg[1], $fg[2]);
    imagefill($im, 0, 0, $bgc);
    // laptop-ish rectangle
    $rc = imagecolorallocate($im, (int)($fg[0]*0.25), (int)($fg[1]*0.25), (int)($fg[2]*0.25));
    imagefilledrectangle($im, 200, 150, 600, 430, $rc);
    imagefilledrectangle($im, 170, 430, 630, 470, $rc);
    imagestring($im, 5, (int)((250 - imagefontwidth(5)*4)/2), 300, $label, $fgc);
    imagestring($im, 3, (int)((250 - imagefontwidth(3)*8)/2), 480, 'Top Laptop', $fgc);
    imagepng($im, $file);
    imagedestroy($im);
}

// product id => def key
$map = array(11 => 'gaming', 12 => 'ultrabook', 13 => 'ultrabook', 14 => 'gaming', 15 => 'business');

// generate files
$files = array();
foreach ($defs as $key => $d) {
    $path = $dir . $d[2];
    make_placeholder($path, $d[0], $d[1], strtoupper($key));
    $files[$key] = $path;
}

foreach ($map as $prod_id => $key) {
    $prod = wc_get_product($prod_id);
    if (!$prod) { echo "no product $prod_id\n"; continue; }
    $path = $files[$key];
    $attach_id = wp_insert_attachment(array(
        'post_title'   => 'placeholder-' . $key,
        'post_status'  => 'inherit',
        'post_mime_type' => 'image/png',
    ), $path, 0);
    $img = wp_get_image_editor($path);
    $meta = $img ? $img->get_size() : array('width' => 800, 'height' => 600);
    $meta = array('width' => $meta['width'] ?? 800, 'height' => $meta['height'] ?? 600, 'file' => 'tl-placeholder/' . $files[$key]);
    wp_update_attachment_metadata($attach_id, wp_generate_attachment_metadata($attach_id, $path));
    update_post_meta($attach_id, '_wp_attached_file', 'tl-placeholder/' . basename($path));
    $prod->set_image_id($attach_id);
    $prod->save();
    echo "Product $prod_id -> image $attach_id ($key)\n";
}
echo "Images done.\n";
