<?php
/* ---
StyleList — fetches the canonical style vocabulary from the API and shapes it for
the guided-style typeahead, caching per session (fetched once, not per page load).

Three tiers, all fileable:
  - window.CB_STYLES  = [{id,name,category,bev,ca,al}]        (GET /style)
  - window.CB_PARENTS = [{slug,name,bev,class,al}]            (GET /style/parent)
  - window.CB_CLASSES = [{slug,name,bev,al}]                  (GET /style/class)

The database is the single source of truth; this is the browser-delivery path.
If the API call fails, the lists are empty and the field degrades to plain text
(the server still resolves/validates on submit).
--- */
class StyleList {

    public static function styles(){
        if(isset($_SESSION['cb_styles']) && is_array($_SESSION['cb_styles'])){
            return $_SESSION['cb_styles'];
        }
        $data = self::call('/style');
        $out = array();
        foreach($data as $s){
            $out[] = array(
                'id'   => $s['id'] ?? '',
                'name' => $s['name'] ?? '',
                'category' => $s['category'] ?? '',
                'bev'  => $s['beverage_type'] ?? 'beer',
                'ca'   => !empty($s['catch_all']),
                'al'   => self::aliases($s),
            );
        }
        if($out){ $_SESSION['cb_styles'] = $out; }
        return $out;
    }

    public static function parents(){
        if(isset($_SESSION['cb_parents']) && is_array($_SESSION['cb_parents'])){
            return $_SESSION['cb_parents'];
        }
        $data = self::call('/style/parent');
        $out = array();
        foreach($data as $p){
            $out[] = array(
                'slug' => $p['slug'] ?? '',
                'name' => $p['name'] ?? '',
                'bev'  => $p['beverage_type'] ?? 'beer',
                'class' => $p['class'] ?? null,
                'al'   => self::aliases($p),
            );
        }
        if($out){ $_SESSION['cb_parents'] = $out; }
        return $out;
    }

    public static function classes(){
        if(isset($_SESSION['cb_classes']) && is_array($_SESSION['cb_classes'])){
            return $_SESSION['cb_classes'];
        }
        $data = self::call('/style/class');
        $out = array();
        foreach($data as $c){
            $out[] = array(
                'slug' => $c['slug'] ?? '',
                'name' => $c['name'] ?? '',
                'bev'  => $c['beverage_type'] ?? 'beer',
                'al'   => self::aliases($c),
            );
        }
        if($out){ $_SESSION['cb_classes'] = $out; }
        return $out;
    }

    // Inline <script> assigning the three globals. JSON_HEX_TAG guards "</script>".
    public static function inlineScript(){
        $flags = JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $styles  = json_encode(self::styles(), $flags);  if($styles === false){ $styles = '[]'; }
        $parents = json_encode(self::parents(), $flags); if($parents === false){ $parents = '[]'; }
        $classes = json_encode(self::classes(), $flags); if($classes === false){ $classes = '[]'; }
        return '<script>window.CB_STYLES = ' . $styles . ';'
             . 'window.CB_PARENTS = ' . $parents . ';'
             . 'window.CB_CLASSES = ' . $classes . ';</script>';
    }

    // --- helpers -----------------------------------------------------------
    private static function call($endpoint){
        $api = new API();
        $data = json_decode($api->request('GET', $endpoint, ''), true);
        return (is_array($data) && isset($data['data']) && is_array($data['data'])) ? $data['data'] : array();
    }
    private static function aliases($row){
        return (isset($row['aliases']) && is_array($row['aliases'])) ? $row['aliases'] : array();
    }
}
?>
