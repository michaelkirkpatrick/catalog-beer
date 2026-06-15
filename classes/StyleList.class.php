<?php
/* ---
StyleList — fetches the canonical style vocabulary from the API (GET /style),
shapes it for the guided-style typeahead (window.CB_STYLES), and caches it in
the session so it's fetched once per session rather than on every form load.

The database is the single source of truth; this is just the browser-delivery
path. If the API call fails, fetch() returns [] and the style field degrades to
plain free text (the server still resolves/validates on submit).

Usage:
    echo json_encode(StyleList::fetch(), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
--- */
class StyleList {

    // Returns the shaped style array: [{id, name, category, bev, ca, al:[...]}, ...]
    public static function fetch(){
        // Serve from the per-session cache when available
        if(isset($_SESSION['cb_styles']) && is_array($_SESSION['cb_styles'])){
            return $_SESSION['cb_styles'];
        }

        $api = new API();
        $response = $api->request('GET', '/style', '');
        $data = json_decode($response, true);

        $out = array();
        if(is_array($data) && isset($data['data']) && is_array($data['data'])){
            foreach($data['data'] as $s){
                $out[] = array(
                    'id'   => $s['id'] ?? '',
                    'name' => $s['name'] ?? '',
                    'category' => $s['category'] ?? '',
                    'bev'  => $s['beverage_type'] ?? 'beer',
                    'ca'   => !empty($s['catch_all']),
                    'al'   => isset($s['aliases']) && is_array($s['aliases']) ? $s['aliases'] : array(),
                );
            }
            // Cache only a successful fetch (keyed implicitly to this session)
            $_SESSION['cb_styles'] = $out;
            if(isset($data['version'])){
                $_SESSION['cb_styles_version'] = $data['version'];
            }
        }

        return $out;
    }

    // Inline <script> assigning window.CB_STYLES. JSON_HEX_TAG guards against
    // a stray "</script>" in any style name/alias.
    public static function inlineScript(){
        $json = json_encode(self::fetch(), JSON_HEX_TAG | JSON_UNESCAPED_UNICODE | JSON_UNESCAPED_SLASHES);
        if($json === false){ $json = '[]'; }
        return '<script>window.CB_STYLES = ' . $json . ';</script>';
    }
}
?>
