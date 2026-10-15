<?php
/**
 * Draws the blog cover images (content/covers/*.png, 1600x900) and the social share image
 * (theme assets/images/og-image.png, 1200x630) in the brand colours. Needs PHP GD with FreeType and the
 * Segoe UI fonts that come with Windows.
 *
 * Usage: php tools/make-images.php
 */

$root  = dirname( __DIR__ );
$bold  = 'C:/Windows/Fonts/segoeuib.ttf';
$semi  = 'C:/Windows/Fonts/seguisb.ttf';
$plain = 'C:/Windows/Fonts/segoeui.ttf';

function hex( $im, $hex, $alpha = 0 ) {
	return imagecolorallocatealpha( $im, hexdec( substr( $hex, 1, 2 ) ), hexdec( substr( $hex, 3, 2 ) ), hexdec( substr( $hex, 5, 2 ) ), $alpha );
}

// Soft glow: many transparent circles, largest first.
function glow( $im, $cx, $cy, $r, $hex, $strength ) {
	for ( $i = 0; $i < 40; $i++ ) {
		$rr = (int) ( $r * ( 1 - $i / 40 ) );
		imagefilledellipse( $im, $cx, $cy, $rr * 2, $rr * 2, hex( $im, $hex, 127 - $strength ) );
	}
}

// Logo mark (rounded square, arrow arc, amber box) at x,y with size s.
function mark( $im, $x, $y, $s ) {
	$k = $s / 32;
	imagefilledrectangle( $im, (int) ( $x + 8 * $k ), (int) $y, (int) ( $x + 24 * $k ), (int) ( $y + $s ), hex( $im, '#0B6E55' ) );
	imagefilledrectangle( $im, (int) $x, (int) ( $y + 8 * $k ), (int) ( $x + $s ), (int) ( $y + 24 * $k ), hex( $im, '#0B6E55' ) );
	foreach ( array( array( 8, 8 ), array( 24, 8 ), array( 8, 24 ), array( 24, 24 ) ) as $c ) {
		imagefilledellipse( $im, (int) ( $x + $c[0] * $k ), (int) ( $y + $c[1] * $k ), (int) ( 16 * $k ), (int) ( 16 * $k ), hex( $im, '#0B6E55' ) );
	}
	imagesetthickness( $im, max( 2, (int) ( 2.4 * $k ) ) );
	imagearc( $im, (int) ( $x + 16 * $k ), (int) ( $y + 16 * $k ), (int) ( 17.2 * $k ), (int) ( 17.2 * $k ), 0, 330, hex( $im, '#FFFFFF' ) );
	imagesetthickness( $im, 1 );
	$b = 7.2 * $k;
	imagefilledrectangle( $im, (int) ( $x + 12.4 * $k ), (int) ( $y + 12.4 * $k ), (int) ( $x + 12.4 * $k + $b ), (int) ( $y + 12.4 * $k + $b ), hex( $im, '#F5A524' ) );
}

// Wraps text to a width; returns lines.
function wrap( $text, $font, $size, $width ) {
	$lines = array();
	$line  = '';
	foreach ( explode( ' ', $text ) as $word ) {
		$try = '' === $line ? $word : "$line $word";
		$box = imagettfbbox( $size, 0, $font, $try );
		if ( $box[2] - $box[0] > $width && '' !== $line ) {
			$lines[] = $line;
			$line    = $word;
		} else {
			$line = $try;
		}
	}
	$lines[] = $line;
	return $lines;
}

function canvas( $w, $h ) {
	$im = imagecreatetruecolor( $w, $h );
	imagealphablending( $im, true );
	imagefilledrectangle( $im, 0, 0, $w, $h, hex( $im, '#08533F' ) );
	glow( $im, (int) ( $w * 1.0 ), 0, (int) ( $w * 0.55 ), '#F5A524', 5 );
	glow( $im, 0, $h, (int) ( $w * 0.45 ), '#7DD3B0', 4 );
	// Faint grid, like a stock sheet
	$line = hex( $im, '#FFFFFF', 120 );
	for ( $x = 0; $x < $w; $x += 80 ) {
		imageline( $im, $x, 0, $x, $h, $line );
	}
	for ( $y = 0; $y < $h; $y += 80 ) {
		imageline( $im, 0, $y, $w, $y, $line );
	}
	return $im;
}

$covers = array(
	'cover-stocky.png'        => array( 'STOCKY', 'Life after Stocky: how to keep reordering' ),
	'cover-reorder-point.png' => array( 'GUIDE', 'How to calculate a reorder point' ),
	'cover-case-packs.png'    => array( 'GUIDE', 'Case packs and minimum orders' ),
	'cover-dead-stock.png'    => array( 'GUIDE', 'Dead stock: find it, and stop buying more' ),
);
@mkdir( "$root/content/covers", 0777, true );
foreach ( $covers as $file => list( $label, $title ) ) {
	$w  = 1600;
	$h  = 900;
	$im = canvas( $w, $h );
	imagettftext( $im, 26, 0, 120, 210, hex( $im, '#FFD48A' ), $semi, $label );
	$y = 310;
	foreach ( wrap( $title, $bold, 72, 1250 ) as $line ) {
		imagettftext( $im, 72, 0, 116, $y, hex( $im, '#FFFFFF' ), $bold, $line );
		$y += 108;
	}
	mark( $im, 120, 730, 56 );
	imagettftext( $im, 30, 0, 196, 772, hex( $im, '#FFFFFF' ), $bold, 'Reorder Desk' );
	imagepng( $im, "$root/content/covers/$file", 9 );
	echo "content/covers/$file\n";
}

// Social share image
$im = canvas( 1200, 630 );
mark( $im, 90, 90, 72 );
imagettftext( $im, 34, 0, 186, 140, hex( $im, '#FFFFFF' ), $bold, 'Reorder Desk' );
$y = 300;
foreach ( wrap( 'Know what to reorder before it runs out', $bold, 60, 960 ) as $line ) {
	imagettftext( $im, 60, 0, 86, $y, hex( $im, '#FFFFFF' ), $bold, $line );
	$y += 88;
}
imagettftext( $im, 26, 0, 90, $y + 30, hex( $im, '#BFE3D4' ), $plain, 'Stock forecasts and purchase orders for Shopify' );
imagepng( $im, "$root/theme/reorder-desk/assets/images/og-image.png", 9 );
echo "theme/reorder-desk/assets/images/og-image.png\n";
