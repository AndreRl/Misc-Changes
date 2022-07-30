<?php

// Disallow direct access to this file for security reasons
if(!defined("IN_MYBB"))
{
	die("Direct initialization of this file is not allowed.");
}


function miscchanges_info()
{
	return array(
        "name"  => "Miscellaneous Changes",
        "description"=> "Small changes that do not require their own plugins.",
        "website"        => "https://github.com/AndreRl",
        "author"        => "Wires <i>(AndreRl)</i>",
        "authorsite"    => "https://github.com/AndreRl",
        "version"        => "1.0",
        "guid"             => "",
        "compatibility" => "18*"
    );
}

function miscchanges_install()
{
    global $db, $mybb;

    if(!isset($mybb->user['subscriptions']))
    {
        $db->add_column("users", "subscriptions", "INT NOT NULL DEFAULT 0");
    }

    $search = $db->simple_select("forumsubscriptions", "*", "");
    while($result = $db->fetch_array($search))
    {
        $user = get_user($result['uid']);
        $update = array(
            "subscriptions" => $user['subscriptions']++
        ); 

        $db->update_query("users", $update, "uid = ".$user['uid']."");
    }

    $search = $db->simple_select("threadsubscriptions", "*", "");
    while($result = $db->fetch_array($search))
    {
        $user = get_user($result['uid']);
        $update = array(
            "subscriptions" => $user['subscriptions']++
        ); 

        $db->update_query("users", $update, "uid = ".$user['uid']."");
    }


    $setting_group = array(
        'name' => 'miscchanges_mysettinggroup',
        'title' => 'Misc. Changes Settings',
        'description' => 'Enable individual changes',
        'disporder' => 5, // The order your setting group will display
        'isdefault' => 0
    );
    
    $gid = $db->insert_query("settinggroups", $setting_group);
    
    $setting_array = array(
        'miscchanges_subscription_enable' => array(
            'title' => 'Enable Subscriptions Shortcut',
            'description' => 'Adds Subscriptions shortcut to header',
            'optionscode' => 'yesno',
            'value' => 1, // Default
            'disporder' => 1
        )
    );
    
    foreach($setting_array as $name => $setting)
    {
        $setting['name'] = $name;
        $setting['gid'] = $gid;
    
        $db->insert_query('settings', $setting);
    }
    
    rebuild_settings();
    
}

function miscchanges_is_installed()
{

    global $mybb;
    if(isset($mybb->settings['miscchanges_subscription_enable']))
    {
        return true;
    }
    
    return false;
    
}

function miscchanges_uninstall()
{
    global $db;

    $db->delete_query('settings', "name LIKE 'enhancedconv_%'");
    $db->delete_query('settinggroups', "name = 'miscchanges_mysettinggroup'");
    $db->drop_column("users", "subscriptions");
    
    rebuild_settings();
    
}

function miscchanges_activate()
{

}

function miscchanges_deactivate()
{

}

$plugins->add_hook('usercp2_do_addsubscription', 'miscchanges_subscription');
$plugins->add_hook('usercp2_addsubscription_thread', 'miscchanges_subscription');
$plugins->add_hook('usercp2_removesubscription_forum', 'miscchanges_subscription');
$plugins->add_hook('usercp2_removesubscription_thread', 'miscchanges_subscription');
$plugins->add_hook('usercp2_removesubscriptions_forum', 'miscchanges_subscription');
$plugins->add_hook('usercp2_removesubscriptions_thread', 'miscchanges_subscription');
$plugins->add_hook('usercp2_addsubscription_forum', 'miscchanges_subscription');

function miscchanges_subscription()
{
    global $mybb, $db;
    if($mybb->input['action'] == "addsubscription" || $mybb->input['action'] == "do_addsubscription")
    {
    $update = array(
        "subscriptions" => ++$mybb->user['subscriptions']
    );

    $db->update_query("users", $update, "uid = ".$mybb->user['uid']."");
    }

    if($mybb->input['action'] == "removesubscription" && ($mybb->request_method == "post"))
    {
        $update = array(
            "subscriptions" => --$mybb->user['subscriptions']
        );
    
        $db->update_query("users", $update, "uid = ".$mybb->user['uid']."");
    } elseif($mybb->input['action'] == "removesubscriptions")
    {
        $update = array(
            "subscriptions" => --$mybb->user['subscriptions']
        );
    
        $db->update_query("users", $update, "uid = ".$mybb->user['uid']."");
    }

}

$plugins->add_hook('global_intermediate', 'miscchanges_subscriptionlink');

function miscchanges_subscriptionlink()
{
    global $mybb, $subscribe_link;

    $subscribe_link = '';

if($mybb->user['subscriptions'] >= 1)
{
    $subscribe_link = "<li><a href=\"#\">Subscriptions <i class=\"fa fa-angle-down\"></i></a>
    <ul>
    <li><a href=\"".$mybb->settings['bburl']."/usercp.php?action=subscriptions\">Thread Subscriptions</a></li>
    <li><a href=\"".$mybb->settings['bburl']."/usercp.php?action=forumsubscriptions\">Forum Subscriptions</a></li>
    </ul>
    </li>";
}
}