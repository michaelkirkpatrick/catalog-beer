<?php
/* ---
StyleList — fetches the canonical style vocabulary from the API and shapes it for
the guided-style confidence-ladder field, caching per session (fetched once, not
per page load).

Emits one global, window.CB_TAX, in the compact runtime shape the resolver uses:
  classes : [{slug,name,bev,al}]                    (GET /style/class)
  parents : [{slug,name,cls,bev,sort,al}]           (GET /style/parent)
  styles  : [{id,name,parent,cat,fam,bev,ca,al,srm}]  (GET /style; srm = {min,max}|null)

The database is the single source of truth; this is the browser-delivery path.
If the API call fails, the lists are empty and the field degrades to a plain text
input (the server still resolves/validates on submit).
--- */
class StyleList {

    public static function styles(){
        // v2: rows carry srm — versioned key so pre-upgrade session caches refetch
        if(isset($_SESSION['cb_styles_v2']) && is_array($_SESSION['cb_styles_v2'])){
            return $_SESSION['cb_styles_v2'];
        }
        $data = self::call('/style');
        $out = array();
        foreach($data as $s){
            $out[] = array(
                'id'     => $s['id'] ?? '',
                'name'   => $s['name'] ?? '',
                'parent' => $s['parent'] ?? '',
                'bev'    => $s['beverage_type'] ?? 'beer',
                'ca'     => !empty($s['catch_all']),
                'al'     => self::aliases($s),
                'srm'    => (isset($s['srm']) && is_array($s['srm'])) ? $s['srm'] : null,
            );
        }
        if($out){ $_SESSION['cb_styles_v2'] = $out; }
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
                'cls'  => $p['class'] ?? null,
                'bev'  => $p['beverage_type'] ?? 'beer',
                'sort' => isset($p['sort_order']) ? intval($p['sort_order']) : null,
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

    // Inline <script> assigning window.CB_TAX. JSON_HEX_TAG guards "</script>".
    public static function inlineScript(){
        $flags = JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $tax = array(
            'classes' => self::classes(),
            'parents' => self::parents(),
            'styles'  => self::styles(),
        );
        $json = json_encode($tax, $flags);
        if($json === false){ $json = '{"classes":[],"parents":[],"styles":[]}'; }
        return '<script>window.CB_TAX = ' . $json . ';</script>';
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
