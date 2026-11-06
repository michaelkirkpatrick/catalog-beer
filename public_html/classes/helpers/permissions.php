<?php
/*---
Permissions helpers

Wrappers around GET /brewer/{brewer_id}/permissions — the API's per-key answer
to "what may this user do with this brewer and its beers/locations?" The API
remains the enforcement point; everything here is cosmetic gating so pages only
draw Edit/Delete affordances that can actually succeed.

Roles: 'admin' and 'staff' may edit and delete anything in the brewer's
subtree. Any other role (including values this frontend doesn't know yet) is
treated as 'general': may edit only unverified entities, may never delete.
---*/

function brewerPermissions($api, $brewerID){
    // Only meaningful when a user is logged in — anonymous page views go out
    // on the master key, whose "user" isn't the visitor.
    if(session_status() !== PHP_SESSION_ACTIVE || !isset($_SESSION['userID'])){
        return null;
    }
    if(empty($brewerID)){
        return null;
    }

    $response = $api->request('GET', '/brewer/' . $brewerID . '/permissions', '');
    $data = json_decode($response);
    if($api->error || !isset($data->role) || isset($data->error)){
        // Fail closed: no affordances rather than doomed ones.
        return null;
    }
    return $data;
}

function permissionsCanManage($permissions){
    // Admin/staff: full edit + delete over the brewer's subtree
    if($permissions === null){
        return false;
    }
    return in_array($permissions->role, array('admin', 'staff'), true);
}

function permissionsCanEdit($permissions, $cbVerified, $brewerVerified){
    // $cbVerified/$brewerVerified are the flags of the specific entity being
    // edited (brewer, beer, or location). Only brewer_verified entities are
    // staff-only: cb_verified is an indicator, not a gate — the API lets any
    // account edit one and clears the badge on a non-admin write.
    if($permissions === null){
        return false;
    }
    if(permissionsCanManage($permissions)){
        return true;
    }
    return !$brewerVerified;
}

/*---
Brewery staff notice, for a change of email (account.php, confirm-email.php)

Staff rights come from the email's domain, so confirming an address on a new
domain ends them. GET /users/{id}/privileges names the brewers involved: more
than one when brewers share the domain (a sub-brand kept on a page of its
owner's site, such as Beachwood Brewing and Beachwood Blendery).
---*/

// The names of the brewers this account is staff of; empty when there are
// none or the API can't say (the notice is then simply not shown).
function staffBrewerNames($api, $userID){
    $response = $api->request('GET', '/users/' . $userID . '/privileges', '');
    $data = json_decode($response);
    if($api->httpcode != 200 || !isset($data->data) || !is_array($data->data)){
        return array();
    }
    $names = array();
    foreach($data->data as $brewer){
        if(isset($brewer->name) && $brewer->name !== ''){
            $names[] = $brewer->name;
        }
    }
    return $names;
}

// "A", "A and B", "A, B and C", then "A, B and 4 other breweries". Escaped
// and bolded, ready to echo.
function staffBrewerList(array $names){
    $bold = array_map(fn($name) => '<strong>' . h($name) . '</strong>', $names);
    $count = count($bold);
    if($count === 0){ return ''; }
    if($count === 1){ return $bold[0]; }
    if($count === 2){ return $bold[0] . ' and ' . $bold[1]; }
    if($count === 3){ return $bold[0] . ', ' . $bold[1] . ' and ' . $bold[2]; }
    return $bold[0] . ', ' . $bold[1] . ' and ' . ($count - 2) . ' other breweries';
}

// The notice itself, as HTML. $definite = false on the email form, where the
// new address isn't known yet ("If your new address is at a different
// domain..."); true once it is known to be on another domain. The edit-and-
// delete clause is not optional: brewer_verified is a gate, so a former staff
// member is locked out of the brewery's verified records, their own included.
function staffNoticeHtml(array $names, $domain, $definite){
    if(empty($names)){
        return '';
    }
    $list = staffBrewerList($names);
    $one = (count($names) === 1);
    $verifiedBy = $one ? h($names[0]) . ' has verified' : 'those breweries have verified';
    $addFor = $one ? h($names[0]) : 'them';
    if($definite){
        $html = 'Confirming this address ends your brewery staff privileges for ' . $list . '.';
    }else{
        $html = 'You have brewery staff privileges for ' . $list . ' because your email address is at ' . h($domain) . '. If your new address is at a different domain, you will lose those privileges when you confirm it.';
    }
    $html .= ' Everything you have contributed stays in the catalog. From then on you won&#8217;t be able to edit or delete records ' . $verifiedBy . ', including the ones you added, and anything new you add for ' . $addFor . ' won&#8217;t carry the Brewer Verified mark.';
    return $html;
}

// The part of an email address after the @, lowercased.
function emailDomain($email){
    return strtolower(substr(strrchr((string)$email, '@') ?: '', 1));
}
