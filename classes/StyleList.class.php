<?php
/* ---
StyleList — fetches the canonical style vocabulary from the API and shapes it for
the guided-style confidence-ladder field, caching per session (fetched once, not
per page load).

Emits one global, window.CB_TAX, in the compact runtime shape the resolver uses:
  classes : [{slug,name,bev,al}]                    (GET /style/class)
  parents : [{slug,name,cls,bev,sort,al}]           (GET /style/parent)
  styles  : [{id,name,parent,cat,fam,bev,ca,al}]    (GET /style)
  approx  : { "<normalized alias>": "<style_id>" }  (GET /style/approx)

The database is the single source of truth; this is the browser-delivery path.
If the API call fails, the lists are empty and the field degrades to a plain text
input (the server still resolves/validates on submit).
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
                'id'     => $s['id'] ?? '',
                'name'   => $s['name'] ?? '',
                'parent' => $s['parent'] ?? '',
                'cat'    => $s['category'] ?? '',
                'fam'    => $s['family'] ?? '',
                'bev'    => $s['beverage_type'] ?? 'beer',
                'ca'     => !empty($s['catch_all']),
                'al'     => self::aliases($s),
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

    // manual-approx best-fits, as a { normalized-alias => style_id } map for the
    // resolver's "Closest match" (Approx) tier. These never auto-resolve a beer.
    public static function approx(){
        if(isset($_SESSION['cb_approx']) && is_array($_SESSION['cb_approx'])){
            return $_SESSION['cb_approx'];
        }
        $data = self::call('/style/approx');
        $out = array();
        foreach($data as $a){
            $alias = isset($a['alias']) ? strtolower(trim($a['alias'])) : '';
            if($alias !== '' && !empty($a['style_id'])){
                $out[$alias] = $a['style_id'];
            }
        }
        if($out){ $_SESSION['cb_approx'] = $out; }
        return $out;
    }

    // Inline <script> assigning window.CB_TAX. JSON_HEX_TAG guards "</script>".
    public static function inlineScript(){
        $flags = JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES;
        $tax = array(
            'classes' => self::classes(),
            'parents' => self::parents(),
            'styles'  => self::styles(),
            'approx'  => (object) self::approx(),  // cast so an empty map encodes as {} not []
        );
        $json = json_encode($tax, $flags);
        if($json === false){ $json = '{"classes":[],"parents":[],"styles":[],"approx":{}}'; }
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
