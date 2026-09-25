<?php
/*
suppressAutofill — opt a form control out of browser autofill and
password-manager fill.

Most of this site's inputs describe the entity being catalogued — a brewer's
name, a taproom's address — not the person typing. A contributor's saved
name or home address is never the right value: at best noise, at worst
personal data submitted into a public database. So catalog fields suppress
autofill; only the forms where the user IS the subject (login,
create-account, contact) use real autocomplete tokens.

autocomplete="off" alone doesn't achieve it: Chrome has ignored it for
years, and where the attribute means nothing to it, it classifies the field
by name instead — and ours are named name, url, city, zip, telephone, which
is exactly what those heuristics look for. An unrecognised token leaves the
field unclassifiable, and a DIFFERENT token per field also stops Chrome
treating the group as one autofillable section. That the tokens aren't
spec-legal values is the point. data-1p-ignore and data-lpignore are the
documented opt-outs for 1Password and LastPass, which read the field names
too.

None of this is a guarantee — no method is, short of the browser vendors
agreeing on one — but it's the strongest signal a page can send.
*/

/**
 * Set the no-fill attributes on an InputField or DropDown. Call after
 * ->name is assigned; existing dataAttributes are kept.
 */
function suppressAutofill($field): void {
    $field->autocomplete = 'cb-no-autofill-' . $field->name;
    // DropDown has no dataAttributes property; the vendor opt-outs are
    // InputField-only, and selects aren't what password managers fill anyway.
    if(property_exists($field, 'dataAttributes')){
        $field->dataAttributes += array('1p-ignore' => '', 'lpignore' => 'true', 'form-type' => 'other');
    }
}
