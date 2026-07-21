<?php
/* ---
SRM — Standard Reference Method beer-color device (PHP port of assets/js srm.js).
Maps an SRM value (~1–40) to an approximate sRGB hex, and provides helpers for a
min→max gradient and a readable text color on top. SRM is the signature visual
device for the styles pages; keep this chart in step with the JS version.
--- */
class SRM {

    // Standard SRM 1..40 hex chart (Bryce/Druey approximation, widely used).
    private static $chart = array(
        1=>'#FFE699', 2=>'#FFD878', 3=>'#FFCA5A', 4=>'#FFBF42', 5=>'#FBB123', 6=>'#F8A600', 7=>'#F39C00',
        8=>'#EA8F00', 9=>'#E58500', 10=>'#DE7C00', 11=>'#D77200', 12=>'#CF6900', 13=>'#CB6200', 14=>'#C35900',
        15=>'#BB5100', 16=>'#B54C00', 17=>'#B04500', 18=>'#A63E00', 19=>'#A13700', 20=>'#9B3200', 21=>'#952D00',
        22=>'#8E2900', 23=>'#882300', 24=>'#821E00', 25=>'#7B1A00', 26=>'#771900', 27=>'#701400', 28=>'#6A0E00',
        29=>'#660D00', 30=>'#5E0B00', 31=>'#5A0A02', 32=>'#560903', 33=>'#520907', 34=>'#4C0505', 35=>'#470606',
        36=>'#440607', 37=>'#3F0708', 38=>'#3B0607', 39=>'#3A070B', 40=>'#360A0A'
    );

    public static function hex($v){
        if($v === null || !is_numeric($v)){
            return '#C8A86B';
        }
        $v = (int) round(floatval($v));
        if($v < 1){ $v = 1; }
        if($v > 40){ $v = 40; }
        return self::$chart[$v];
    }

    // Perceived luminance → choose ink or paper for text sitting on the color
    public static function onColor($hex){
        $c = ltrim($hex, '#');
        if(strlen($c) === 3){
            $c = $c[0].$c[0].$c[1].$c[1].$c[2].$c[2];
        }
        $r = hexdec(substr($c, 0, 2));
        $g = hexdec(substr($c, 2, 2));
        $b = hexdec(substr($c, 4, 2));
        $l = (0.2126 * $r + 0.7152 * $g + 0.0722 * $b) / 255;
        return ($l > 0.62) ? '#1B1A17' : '#FAF7F2';
    }

    // CSS linear-gradient string spanning a min→max SRM range (light→dark)
    public static function gradient($min, $max, $angle = '90deg'){
        $steps = array();
        $n = 5;
        for($i = 0; $i < $n; $i++){
            $v = $min + ($max - $min) * ($i / ($n - 1));
            $steps[] = self::hex($v) . ' ' . round(100 * $i / ($n - 1)) . '%';
        }
        return 'linear-gradient(' . $angle . ',' . implode(',', $steps) . ')';
    }
}
?>
