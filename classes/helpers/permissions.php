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
    // edited (brewer, beer, or location) — verified entities are staff-only.
    if($permissions === null){
        return false;
    }
    if(permissionsCanManage($permissions)){
        return true;
    }
    return (!$cbVerified && !$brewerVerified);
}
