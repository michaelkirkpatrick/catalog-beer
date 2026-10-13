<?php
/**
 * Provenance marks — the one place the two verification tiers are named,
 * ranked and drawn.
 *
 * Two tiers, one order everywhere:
 *
 *   brewer_verified  "Verified by the brewer"   — the brewery maintains the
 *                    record. The higher mark: filled amber dot, and on the
 *                    entity's own page an outlined pill.
 *   cb_verified      "Verified by Catalog.beer" — we checked the record
 *                    against the brewery. Cleared by the API the moment a
 *                    non-admin edits it. Hollow dot, muted text, no container.
 *
 * An entity carrying both shows the brewer mark only. Unmarked entities draw
 * nothing — except in a list that marks any row, where verifyDot() emits an
 * invisible placeholder so names stay aligned.
 *
 * Styles: .cb-vdot / .cb-verify in assets/css/catalog-components.css.
 */

/**
 * The tier of an API entity (beer, brewer, location, or an Algolia hit array):
 * 'first' (brewer), 'cbv' (Catalog.beer) or '' (unverified).
 */
function verifyTier($entity){
    $entity = (array) $entity;
    if(!empty($entity['brewer_verified'])){
        return 'first';
    }
    if(!empty($entity['cb_verified'])){
        return 'cbv';
    }
    return '';
}

function verifyLabel($tier){
    switch($tier){
        case 'first': return 'Verified by the brewer';
        case 'cbv':   return 'Verified by Catalog.beer';
        default:      return '';
    }
}

/**
 * The dot that leads a name in a list row, card or search hit. Pass
 * $placeholder = true inside a list that marks any of its rows, so an
 * unmarked row keeps the same indent.
 */
function verifyDot($tier, $placeholder = false){
    if($tier === ''){
        return $placeholder ? '<span class="cb-vdot cb-vdot--none" aria-hidden="true"></span>' : '';
    }
    $label = verifyLabel($tier);
    return '<span class="cb-vdot cb-vdot--' . $tier . '" title="' . $label . '" role="img" aria-label="' . $label . '"></span>';
}

/** The mark under an entity page's h1. Empty for an unverified entity. */
function verifyMark($tier){
    if($tier === ''){
        return '';
    }
    return '<div><span class="cb-verify cb-verify--' . $tier . '"><span class="cb-vdot cb-vdot--' . $tier . '"></span>' . verifyLabel($tier) . '</span></div>';
}

/** The key under a list that marks any of its rows. */
function verifyLegend(){
    return '<div class="cb-legend">'
        . '<span><span class="cb-vdot cb-vdot--first"></span>' . verifyLabel('first') . '</span>'
        . '<span><span class="cb-vdot cb-vdot--cbv"></span>' . verifyLabel('cbv') . '</span>'
        . '<span class="cb-legend__none">no mark &#8212; not yet verified</span>'
        . '</div>';
}
