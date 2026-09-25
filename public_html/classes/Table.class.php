<?php
class Table {
    
    public $tableStriped = true;
    
    public function startTable($headings){
        // Table Class
        if($this->tableStriped){
            $classAdd = ' table-striped';
        }else{
            $classAdd = '';
        }
        
        // Start Table
        $html = '<table class="table ' . $classAdd . '">' . "\n";
        $html .= '<thead>' . "\n";
        $html .= '<tr>' . "\n";
        
        // Loop through headings.
        //
        // This used to escape a heading only when it matched /^[A-Za-z0-9 ]*$/ --
        // that is, only when it contained nothing that needed escaping -- and
        // passed anything with markup in it through RAW via the else branch. The
        // test was exactly inverted. Safe only because every caller passed a
        // literal; admin/usage.php:93 already passes API-derived month labels.
        //
        // Also no longer iterates by reference: the old &$heading rewrote the
        // caller's array as a side effect of rendering it.
        foreach($headings as $heading){
            $html .= '<th>' . h($heading) . '</th>' . "\n";
        }
        $html .= '</tr>' . "\n";
        $html .= '</thead>' . "\n";
        $html .= '<tbody>' . "\n";
        
        // Return
        return $html;
    }
    
    public function closeTable(){
        $html = '</tbody>' . "\n";
        $html .= '</table>' . "\n";
        return $html;
    }
    
}