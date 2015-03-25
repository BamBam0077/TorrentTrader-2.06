<?php
//
//  TorrentTrader v2.x
//    This file was last updated: 4/Sept/2007
//    
//    [url="http://www.torrenttrader.org"]http://www.torrenttrader.org[/url]
//
//
require_once("backend/functions.php");
dbconn(false);
loggedinonly();

stdhead("Report");

begin_frame("Report");


$takeuser = $_POST["user"];
$taketorrent = $_POST["torrent"];
$takeforumid = $_POST["forumid"];
$takecomment = $_POST["comment"];
$takeforumpost = $_POST["forumpost"];
$takereason = mysql_real_escape_string($_POST["reason"]);

$user = (int)$_GET["user"];
$torrent = (int)$_GET["torrent"];
$comment = (int)$_GET["comment"];
$forumid = (int)$_GET["forumid"];
$forumpost = (int)$_GET["forumpost"];

//take report user
if (!empty($takeuser)){
    if (empty($takereason)){
        show_error_msg("Error", "You must enter a reason.",0);
        end_frame();
        stdfoot();
        die;
    }

    $res = mysql_query("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor = $takeuser AND type = 'user'");

    if (mysql_num_rows($res) == 0){
        mysql_query("INSERT into reports (addedby,votedfor,type,reason) VALUES ($CURUSER[id],$takeuser,'user', '$takereason')");
        print("User: $takeuser, Reason: $takereason<p></p>Successfully Reported");
        end_frame();
        stdfoot();
        die();
    }else{
        print("You have already reported user $takeuser");
        end_frame();
        stdfoot();
        die();
    }
}

//take report torrent
if (($taketorrent !="") && ($takereason !="")){
    if (!$takereason){
        show_error_msg("Error", "You must enter a reason.",0);
        stdfoot();
        end_frame();
        die;
    }

    $res = mysql_query("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor = $taketorrent AND type = 'torrent'");
    if (mysql_num_rows($res) == 0){
        mysql_query("INSERT into reports (addedby,votedfor,type,reason) VALUES ($CURUSER[id],$taketorrent,'torrent', '$takereason')");
        print("Torrent: $taketorrent, Reason: $takereason<p></p>Successfully Reported");
        end_frame();
        stdfoot();
        die();
    }else{
        print("You have already reported torrent $taketorrent");
        end_frame();
        stdfoot();
        die();
    }
}

//take report comment
if (($takecomment !="") && ($takereason !="")){
    if (!$takereason){
        show_error_msg("Error", "You must enter a reason.",0);
        stdfoot();
        end_frame();
        die;
    }

    $res = mysql_query("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor = $takecomment AND type = 'comment'");
    if (mysql_num_rows($res) == 0){
        mysql_query("INSERT into reports (addedby,votedfor,type,reason) VALUES ($CURUSER[id],$takecomment,'comment', '$takereason')");
        print("Comment: $takecomment, Reason: $takereason<p></p>Successfully Reported");
        end_frame();
        stdfoot();
        die();
    }else{
        print("You have already reported torrent $takecomment");
        end_frame();
        stdfoot();
        die();
    }
}

//take forum post report
if (($takeforumid !="") && ($takereason !="")){
    if (!$takereason){
        show_error_msg("Error", "You must enter a reason.",0);
        stdfoot();
        end_frame();
        die;
    }

    $res = mysql_query("SELECT id FROM reports WHERE addedby = $CURUSER[id] AND votedfor= $takeforumid AND votedfor_xtra= $takeforumpost AND type = 'forum'");

    if (mysql_num_rows($res) == 0){
        mysql_query("INSERT into reports (addedby,votedfor,votedfor_xtra,type,reason) VALUES ($CURUSER[id],$takeforumid,$takeforumpost ,'forum', '$takereason')");
        print("User: $takeuser, Reason: $takereason<p></p>Successfully Reported");
        end_frame();
        stdfoot();
        die();
    }else{
        print("You have already reported post $takeforumid");
        end_frame();
        stdfoot();
        die();
    }

}

//report user form
if ($user !=""){
    $res = mysql_query("SELECT username, class FROM users WHERE id=$user");
    if (mysql_num_rows($res) == 0){
        print("Invalid UserID");
        end_frame();
        stdfoot();
        die();
    }    

    $arr = mysql_fetch_assoc($res);
    
    print("<b>Are you sure you would like to report user:</b><BR><a href=account-details.php?id=$user><b>$arr[username]</b></a>?<BR>");
    print("<p>Please note, this is <b>not</b> to be used to report leechers, we have scripts in place to deal with them</p>");
    print("<b>Reason</b> (required): <form method=post action=report.php><input type=hidden name=user value=$user><input type=text size=100 name=reason><p></p><input type=submit class=btn value=Confirm></form>");
    end_frame();
    stdfoot();
    die();
}

//report torrent form
if ($torrent !=""){
    $res = mysql_query("SELECT name FROM torrents WHERE id=$torrent");

    if (mysql_num_rows($res) == 0){
        print("Invalid TorrentID");
        end_frame();
        stdfoot();
        die();
    }

    $arr = mysql_fetch_array($res);
    print("<b>Are you sure you would like to report torrent:</b><BR><a href=torrents-details.php?id=$torrent><b>$arr[name]</b></a>?<BR>");
    print("<b>Reason</b> (required): <form method=post action=report.php><input type=hidden name=torrent value=$torrent><input type=text size=100 name=reason><p></p><input type=submit class=btn value=Confirm></form>");
    end_frame();
    stdfoot();
    die();
}

//report forum post form
if (($forumid !="") && ($forumpost !="")){
    $res = mysql_query("SELECT subject FROM forum_topics WHERE id=$forumid");

    if (mysql_num_rows($res) == 0){
        print("Invalid Forum ID");
        end_frame();
        stdfoot();
        die();
    }

    $arr = mysql_fetch_array($res);
    print("<b>Are you sure you would like to report the following forum post:</b><BR><a href=forums.php?action=viewtopic&topicid=$forumid&page=p#$forumpost><b>$arr[subject]</b></a>?<BR>");
    print("<b>Reason</b> (required): <form method=post action=report.php><input type=hidden name=forumid value=$forumid><input type=hidden name=forumpost value=$forumpost><input type=text size=100 name=reason><p></p><input type=submit class=btn value=Confirm></form>");
    end_frame();
}

//report comment form
if ($comment !=""){
    $res = mysql_query("SELECT id, text FROM comments WHERE id=$comment");
    if (mysql_num_rows($res) == 0){
        print("Invalid Comment");
        end_frame();
        stdfoot();
        die();
    }    

    $arr = mysql_fetch_assoc($res);
    
    print("<b>Are you sure you would like to report Comment:</b><BR><BR><b>".format_comment($arr["text"])."</b>?<BR>");
    print("<p>Please note, this is <b>not</b> to be used to report leechers, we have scripts in place to deal with them</p>");
    print("<b>Reason</b> (required): <form method=post action=report.php><input type=hidden name=comment value=$comment><input type=text size=100 name=reason><p></p><input type=submit class=btn value=Confirm></form>");
    end_frame();
    stdfoot();
    die();
}

//error
if (($user !="") && ($torrent !="")){
    print("<h1>Missing Info</h1>");
    end_frame();
    stdfoot();
    die();
}

show_error_msg("Error", "Missing Info.", 0);
end_frame();
stdfoot();
?>