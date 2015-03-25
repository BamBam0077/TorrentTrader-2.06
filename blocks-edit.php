<?php 
/////////////////////////////////////////////////
// blocks-edit.php                             //
// ------------------------------------------- //
// (c)TorrentTrader (www.torrenttrader.org)    //
// ------------------------------------------- //
// coded by elegor                             //
// version 1.0.R2 (02/10/2007)                 //
// compatible with TTv2                        //
// ------------------------------------------- //
// under GPL-License                           //
/////////////////////////////////////////////////


require_once("backend/functions.php");

dbconn();
loggedinonly();

stdhead("Blocks Management");

if (!$CURUSER || $CURUSER["control_panel"]!="yes"){
 show_error_msg(_ERROR_, _ACCESS_DEN_,1);
}


if($preview){
	echo "<a name=\"".$name."\"></a>";
	begin_frame("Block preview");
	
		echo "<br /><center><b>"._BLC_USE_SITE_SET_."</b><hr>";
		echo "<table border=0 width=200 align=\"center\"><tr><td>";
		include("blocks/".$name."_block.php");
		echo "</td></tr></table><hr>";
		echo "<center><a href=\"javascript: self.close();\">"._CLS_WIN_."</a></center>";
		
	end_frame();
	stdfoot();
	die();
}


begin_frame(_BLC_MAN_);

// == addnew
if($addnew){
	foreach($addnew as $addthis){
		$i = $addthis;
		
		$addblock = $_POST["addblock_".$i];
		$wantedname = "'".$_POST["wantedname_".$i]."'";
		$name = "'".str_replace("_block.php","",$addblock)."'";
		$description = "'".$_POST["wanteddescription_".$i]."'";

		mysql_query("INSERT INTO blocks (named, name, description, position, enabled, sort) VALUES ($wantedname, $name, $description, 'left', 0, 0)")  or ((mysql_errno() == 1062) ? show_error_msg("Error","Sorry, this block is in database already!",1) : show_error_msg("Error","Database Query failed: " . mysql_error()));
		if(mysql_affected_rows() != 0){
			$success = "<center><font size=\"3\"><b>"._SUCCESS_ADD_."</b></font></center><br />";
		}else{
			$success = "<center><font size=\"3\"><b>"._FAIL_ADD_."</b></font></center><br />";
		}
	}
	echo $success;
}// end addnew

// == permanent delete
if($deletepermanent){
	foreach($deletepermanent as $delpthis){
		unlink("blocks/".$delpthis);
		if(file_exists("blocks/".$delpthis))
			$delmessage="<center><font size=\"3\"><b>"._FAIL_DEL_."</b></font></center><br />";
		else
			$delmessage="<center><font size=\"3\"><b>"._SUCCESS_DEL_."</b></font></center><br />";
	}
	echo $delmessage;
}// end addnew

$nextleft=(mysql_num_rows(mysql_query("SELECT position FROM blocks WHERE position='left' AND enabled=1"))+1);
$nextmiddle=(mysql_num_rows(mysql_query("SELECT position FROM blocks WHERE position='middle' AND enabled=1"))+1);
$nextright=(mysql_num_rows(mysql_query("SELECT position FROM blocks WHERE position='right' AND enabled=1"))+1);

// upload block
if($upload){
	$uplfailmessage = "";
	$uplsuccessmessage = "";
	if ($_FILES['blockupl']) {

		$blockfile = $_FILES['blockupl'];

		if ($blockfile["name"] == ""){
			$uplfailmessage .= "<br />"._SEND_NOTHING_;
		}
		if (($blockfile["size"] == 0) && ($blockfile["name"] != "")){ 
			$uplfailmessage .= "<br />"._SEND_EMPTY_;
		}
		if ((!preg_match('/^(.+)\.php$/si', $blockfile['name'], $fmatches)) && ($blockfile["name"] != "")){
			$uplfailmessage .= "<br />"._SEND_INVALID_;
		}
		if ((!preg_match('/^(.+)\_block.php$/si', $blockfile['name'], $fmatches)) && ($blockfile["name"] != "")){
			$uplfailmessage .= "<br />"._SEND_NO_BLOCK_;
		}

		$blockfilename = $blockfile['tmp_name'];
		if (@!is_uploaded_file($blockfilename)){
			$uplfailmessage .= "<br />"._FAIL_UPL_;
		}
		
	}

	if(!$uplfailmessage){
		$blockfilename = $site_config['blocks_dir'] . "/" . $blockfile['name'];
		if($uploadonly){
			if(file_exists($blockfilename)){
				$uplfailmessage .= "<center><font size=\"3\">\"".$blockfile['name']."\"<b> "._BLC_EXIST_."</b></font></center><br />";
			}else{
				if(@!move_uploaded_file($blockfile["tmp_name"], $blockfilename)){
					$uplfailmessage .= "<center><font size=\"3\"><b>"._CANNOT_MOVE_." </b> \"".$blockfile['name']."\" <b>"._TO_DEST_DIR_."</b></font></center><br />"._CONFIG_DEST_DIR_.": <b>\"".$site_config['blocks_dir']. "\"</b><br />"._PLS_CHECK_." <b>config.php</b> "._SURE_FULL_PATH_.". "._YOUR_CASE_.": <b>\"".$_SERVER['DOCUMENT_ROOT']."\"</b> + <b>\"/"._SUB_DIR_."\"</b> ("._IF_ANY_.") "._AND_." + <b>\"/blocks\"</b>.";
				}else{
					$uplsuccessmessage .= "<center><font size=\"3\">\"".$blockfile['name']."\" <b>"._SUCCESS_UPL_."</b></font></center><br />";
				}
			}
		}else{
			if(file_exists($blockfilename)){
				$uplfailmessage .= "<center><font size=\"3\">\"".$blockfile['name']."\"<b> "._BLC_EXIST_."</b></font></center><br />";
			}else{
				if(@!move_uploaded_file($blockfile["tmp_name"], $blockfilename)){
					$uplfailmessage .= "<center><font size=\"3\"><b>"._CANNOT_MOVE_." </b> \"".$blockfile['name']."\" <b>"._TO_DEST_DIR_."</b></font></center><br />"._CONFIG_DEST_DIR_.": <b>\"".$site_config['blocks_dir']. "\"</b><br />"._PLS_CHECK_." <b>config.php</b> "._SURE_FULL_PATH_.". "._YOUR_CASE_.": <b>\"".$_SERVER['DOCUMENT_ROOT']."\"</b> + <b>\"/"._SUB_DIR_."\"</b> ("._IF_ANY_.") "._AND_." + <b>\"/blocks\"</b>.";
				}else{
					$uploadthis[] = "'".($wantedname ? $wantedname : str_replace("_block.php","",$blockfile['name'])) . "'";
					$uploadthis[] = "'".str_replace("_block.php","",$blockfile['name'])."'";
					$uploadthis[] = "'".$description."'";
					$uploadthis[] = "'".$position."'";
					$uploadthis[] = ($enabledyes ? $uplsort : 0);
					$uploadthis[] = ($enabledyes ? 1 : 0);
					
					mysql_query("INSERT INTO blocks (named, name, description, position, sort, enabled) VALUES (".implode(", ", $uploadthis).")")  or ((mysql_errno() == 1062) ? show_error_msg(_ERROR_,_BLC_IN_DB_ALREADY_,1) : show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error()));
					if(mysql_affected_rows() != 0){
						$uplsuccessmessage .= "<center><font size=\"3\"><b>"._SUCCESS_UPL_ADD_."</b></font></center><br />";
					}else{
						$uplfailmessage .= "<center><font size=\"3\"><b>"._FAIL_UPL_ADD_."</b></font></center><br />";
					}
					echo $uplsuccessmessage;
				}
			}
		}
	}
}// end upload block			

// == edit
if($edit){
	//resort left blocks
	function resortleft(){
		$sortleft = mysql_query("SELECT sort, id FROM blocks WHERE position='left' AND enabled=1 ORDER BY sort ASC");
		$i=1;
		while($sort = mysql_fetch_assoc($sortleft)){
			mysql_query("UPDATE blocks SET sort = $i WHERE id=".$sort["id"]) or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
			$i++;
		}
	}
	//resort middle blocks
	function resortmiddle(){
		$sortmiddle = mysql_query("SELECT sort, id FROM blocks WHERE position='middle' AND enabled=1 ORDER BY sort ASC");
		$i=1;
		while($sort = mysql_fetch_assoc($sortmiddle)){
			mysql_query("UPDATE blocks SET sort = $i WHERE id=".$sort["id"]) or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
			$i++;
		}
	}
	//resort right blocks
	function resortright(){
		$sortright = mysql_query("SELECT sort, id FROM blocks WHERE position='right' AND enabled=1 ORDER BY sort ASC");
		$i=1;
		while($sort = mysql_fetch_assoc($sortright)){
			mysql_query("UPDATE blocks SET sort = $i WHERE id=".$sort["id"]) or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
			$i++;
		}
	}

	// == delete
	if($delete){
		foreach($delete as $delthis){
			mysql_query("DELETE FROM blocks WHERE id=$delthis") or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
		}
			resortleft();
			resortmiddle();
			resortright();
	}// == end delete

	// == move to left
	if($left){
		mysql_query("UPDATE blocks SET position = 'left', sort = $nextleft WHERE id = ".$left) or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
		resortmiddle();
		resortright();
	}// end move to left
	
	// == move to center
	if($middle){
		mysql_query("UPDATE blocks SET position = 'middle', sort = $nextmiddle WHERE id = ".$middle) or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
		resortleft();
		resortright();
	}// end move to center
	
	// == move to right
	if($right){
		mysql_query("UPDATE blocks SET position = 'right', sort = $nextright WHERE enabled=1 AND id = ".$right) or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
		resortleft();
		resortmiddle();
	}// end move to right
	
	// == move upper
	if($up){
		$cur = mysql_query("SELECT position, sort, id FROM blocks WHERE id = $up");
		$curent = mysql_fetch_assoc($cur);

		mysql_query("UPDATE blocks SET sort = ".$sort." WHERE sort = ".($sort-1)." AND id != $up AND position = '".$position."'") or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
		mysql_query("UPDATE blocks SET sort = ".($sort-1)." WHERE id=$up") or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
	}// end move to upper
	
	// == move lower
	if($down){
		$cur = mysql_query("SELECT position, sort, id FROM blocks WHERE id = $down");
		$curent = mysql_fetch_assoc($cur);

		mysql_query("UPDATE blocks SET sort = ".($sort+1)." WHERE id=$down") or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
		mysql_query("UPDATE blocks SET sort = ".$sort." WHERE sort = ".($sort+1)." AND id != $down AND position = '".$position."'") or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
	}// end move lower
	
	// == update
	$res=mysql_query("SELECT * FROM blocks ORDER BY id");

	if(!$up && !$down && !$right && !$left && !$middle){
		while($upd = mysql_fetch_assoc($res)){
			$id = $upd["id"];
			$update[] = "enabled = ".$_POST["enable_".$upd["id"]];
			$update[] = "named = '".$_POST["named_".$upd["id"]]."'";
			$update[] = "description = '".$_POST["description_".$upd["id"]]."'";
			
			if(($upd["enabled"] == 0) && ($upd["position"] == "left") && ($_POST["enable_".$upd["id"]] == 1))
				$update[] = "sort = ".$nextleft;
			elseif(($upd["enabled"] == 0) && ($upd["position"] == "middle") && ($_POST["enable_".$upd["id"]] == 1))
				$update[] = "sort = ".$nextmiddle;
			elseif(($upd["enabled"] == 0) && ($upd["position"] == "right") && ($_POST["enable_".$upd["id"]] == 1))
				$update[] = "sort = ".$nextright;
			
			elseif(($upd["enabled"] == 1) && ($upd["position"] == "left") && ($_POST["enable_".$upd["id"]] == 0))
				$update[] = "sort = 0";
			elseif(($upd["enabled"] == 1) && ($upd["position"] == "middle") && ($_POST["enable_".$upd["id"]] == 0))
				$update[] = "sort = 0";
			elseif(($upd["enabled"] == 1) && ($upd["position"] == "right") && ($_POST["enable_".$upd["id"]] == 0))
				$update[] = "sort = 0";
			else
				$update[] = "sort = ".$upd["sort"];
				
			mysql_query("UPDATE blocks SET ". implode(", ", $update). " WHERE id=$id") or show_error_msg(_ERROR_,_FAIL_DB_QUERY_.": " . mysql_error());
		}
	}
	resortleft();
	resortmiddle();
	resortright();
}// == end edit

echo "<center><a href=\"index.php\">".HOME."</a>&nbsp;&#8226;&nbsp;<a href=\"admincp.php\">".ADMIN_CP."</a>&nbsp;&#8226;&nbsp;<a href=\"admincp.php?action=blocks&do=view\">"._ADMIN_CP_BLC_."</a></center>";

// ---- <table> for blocks in database -----------------------------------------
print("<hr>");
$res = mysql_query("SELECT * FROM blocks ORDER BY enabled DESC, position, sort");

print("<table align=\"center\" width=\"1%\"><tr><td>".
	"<form name=\"blocks\" method=\"post\" action=\"blocks-edit.php\">".
	"<input type=\"hidden\" name=\"edit\" value=\"true\" />".
	"<table class=\"tablebg\" cellspacing=\"1\" width=\"100%\">".
		"<tr>".
			"<td class=\"rowTabHead\" align=\"center\"><font size=\"2\"><b>"._BLC_MAN_."</b></font></td>".
		"</tr>".
	"</table><br />".
	"<table cellspacing=\"1\" class=\"tablebg\" align=\"center\">".
		"<tr>".
			"<td rowspan=\"2\" class=\"rowTabHead\" align=\"center\">"._NAMED_."<br />("._FL_NM_IF_NO_SET_.")</td>".
			"<td rowspan=\"2\" class=\"rowTabHead\" align=\"center\">"._FILE_NAME_."</td>".
			"<td rowspan=\"2\" class=\"rowTabHead\" align=\"center\">".DESCRIPTION."<br />("._MAX_." 255 "._CHARS_.")</td>".
			"<td rowspan=\"2\" colspan=\"3\" class=\"rowTabHead\" align=\"center\">"._POSITION_."</td>".
			"<td rowspan=\"2\" colspan=\"2\" class=\"rowTabHead\" align=\"center\">"._SORT_ORDER_."</td>".
			"<td colspan=\"2\" class=\"rowTabHead\" align=\"center\">".ENABLED."</td>".
			"<td rowspan=\"2\" class=\"rowTabHead\" align=\"center\">"._DEL_."</td>".
		"</tr>".
		"<tr>".
			"<td class=\"rowTabHead\" align=\"center\">".YES."</td>".
			"<td class=\"rowTabHead\" align=\"center\">".NO."</td>".
		"</tr>");

while($blocks2 = mysql_fetch_assoc($res)){
	$down=$blocks["id"];
	if(!$setclass){
		$class="row2";$setclass=true;}
	else{
		$class="row1";$setclass=false;}
	switch($blocks2["position"]){
		case "left":
			$pos = _LEFT_;
			break;
		case "middle":
			$pos = _MIDDLE_;
			break;
		case "right":
			$pos = _RIGHT_;
			break;
		}

	print("<tr>".
			"<td id=\"qq\" rowspan=\"2\" class=\"$class\"><input type=\"text\" name=\"named_".$blocks2["id"]."\" value=\"".($blocks2["named"] ? $blocks2["named"] : $blocks2["name"])."\" /></td>".
			"<td rowspan=\"2\" class=\"$class\">".$blocks2["name"]."</td>".
			"<td rowspan=\"2\" class=\"$class\"><textarea name=\"description_".$blocks2["id"]."\" rows=2 cols=20>".$blocks2["description"]."</textarea></td>".
			"<td colspan=\"3\" class=\"$class\" align=center>".$pos."</td>".
			"<td colspan=\"2\" class=\"$class\" align=center>".$blocks2["sort"]."</td>".
			"<td rowspan=\"2\" class=\"$class\" align=center><input type=\"radio\" name=\"enable_".$blocks2["id"]."\"".($blocks2["enabled"] ? "checked=\"checked\"" : "")." value=\"1\" /></td>".
			"<td rowspan=\"2\" class=\"$class\" align=center><input type=\"radio\" name=\"enable_".$blocks2["id"]."\"".(!$blocks2["enabled"] ? "checked=\"checked\"" : "")." value=\"0\" /></td>".
			"<td rowspan=\"2\" class=\"$class\" align=\"center\"><input type=\"checkbox\" name=\"delete[]\" value=\"".$blocks2["id"]."\"/></td>".
		"</tr>".
		"<tr>".
			"<td class=\"$class\" height=\"1%\">".((($blocks2["position"] != "left") && ($blocks2["enabled"] == 1)) ? "<a href=\"blocks-edit.php?edit=true&amp;position=left&amp;left=".$blocks2["id"]."\"><img border=0 src=\"images/leftenable.gif\" width=\"18\" height=\"15\" alt=\""._MOVE_LEFT_."\" /></a>" : "<img border=0 src=\"images/leftdisable.gif\" width=\"18\" height=\"15\" ".($blocks2["enabled"] ? "alt=\""._AT_LEFT_."\"" : "alt=\""._MUST_ENB_MOVE_."\"")." ".($blocks2["enabled"] ? "onclick=\"javascript: alert('"._AT_LEFT_."');\"" : "onclick=\"javascript: alert('"._MUST_ENB_FIRST."');\"")."  />")."</td>".
			"<td class=\"$class\" height=\"1%\">".((($blocks2["position"] != "middle") && ($blocks2["enabled"] == 1)) ? "<a href=\"blocks-edit.php?edit=true&amp;position=middle&amp;middle=".$blocks2["id"]."\"><img border=0 src=\"images/middleenable.gif\" width=\"18\" height=\"15\" alt=\""._MOVE_CENTER_."\" /></a>" : "<img border=0 src=\"images/middledisable.gif\" width=\"18\" height=\"15\" ".($blocks2["enabled"] ? "alt=\""._AT_CENTER_."\"" : "alt=\""._MUST_ENB_MOVE_."\"")." ".($blocks2["enabled"] ? "onclick=\"javascript: alert('"._AT_CENTER_."');\"" : "onclick=\"javascript: alert('"._MUST_ENB_FIRST."');\"")."  />")."</td>".
			"<td class=\"$class\" height=\"1%\">".((($blocks2["position"] != "right") && ($blocks2["enabled"] == 1)) ? "<a href=\"blocks-edit.php?edit=true&amp;position=right&amp;right=".$blocks2["id"]."\"><img border=0 src=\"images/rightenable.gif\" width=\"18\" height=\"15\" alt=\""._MOVE_RIGHT_."\" /></a>" : "<img border=0 src=\"images/rightdisable.gif\" width=\"18\" height=\"15\" ".($blocks2["enabled"] ? "alt=\""._AT_RIGHT_."\"" : "alt=\""._MUST_ENB_MOVE_."\"")." ".($blocks2["enabled"] ? "onclick=\"javascript: alert('"._AT_RIGHT_."');\"" : "onclick=\"javascript: alert('"._MUST_ENB_FIRST."');\"")."  />")."</td>".
			"<td class=\"$class\" height=\"1%\">".((($blocks2["sort"]!= 1) && ($blocks2["enabled"] != 0)) ? "<a href=\"blocks-edit.php?edit=true&amp;position=".$blocks2["position"]."&amp;sort=".$blocks2["sort"]."&up=".$blocks2["id"]."\"><img border=0 src=\"images/upenable.gif\" width=\"18\" height=\"15\" alt=\""._MOVE_UP_."\" /></a>" : "<img border=0 src=\"images/updisable.gif\" width=\"18\" height=\"15\" alt=\"".($blocks2["enabled"] ? ""._AT_TOP_."" : ""._MUST_ENB_SORT_."")."\" ".($blocks2["enabled"] ? "onclick=\"javascript: alert('"._AT_TOP_."');\"" : "onclick=\"javascript: alert('"._MUST_ENB_FIRST."');\"")." />")."</td>".
			"<td class=\"$class\" height=\"1%\">".(((($blocks2["sort"] != ($nextleft-1)) && ($blocks2["position"] == "left") || ($blocks2["sort"] != ($nextright-1)) && ($blocks2["position"] == "right") || ($blocks2["sort"] != ($nextmiddle-1)) && ($blocks2["position"] == "middle")) && ($blocks2["enabled"] != 0)) ? "<a href=\"blocks-edit.php?edit=true&amp;position=".$blocks2["position"]."&amp;sort=".$blocks2["sort"]."&down=".$blocks2["id"]."\"><img border=0 src=\"images/downenable.gif\" width=\"18\" height=\"15\" alt=\""._MOVE_DOWN_."\" /></a>" : "<img border=0 src=\"images/downdisable.gif\" width=\"18\" height=\"15\" alt=\"".($blocks2["enabled"] ? ""._AT_BOTTOM_."" : ""._MUST_ENB_SORT_."")."\" ".($blocks2["enabled"] ? "onclick=\"javascript: alert('"._AT_BOTTOM_."');\"" : "onclick=\"javascript: alert('"._MUST_ENB_FIRST."');\"")." />")."</td>".
		"</tr>");
}	
print("<tr>".
		"<td colspan=\"11\" align=\"center\" class=\"rowTabHead\"><input type=\"submit\" class=\"btn\" value=\""._BTN_UPDT_."\" /></td>".
	"</tr>".
	"</table>".
	"</form></td></tr></table>");
// ---- </table> for blocks in database -----------------------------------------
	
// ---- <table> for blocks exist but not in database ----------------------------
$exist=mysql_query("SELECT name FROM blocks");
while($fileexist = mysql_fetch_assoc($exist)){
	$indb[] = $fileexist["name"]."_block.php";
}

if ($folder = opendir('blocks')) {
    while (false !== ($file = readdir($folder))) {
        if ($file != "." && $file != ".." && !in_array($file, $indb)) {
            if (preg_match("/_block.php/i", $file))
                $infolder[] = $file;
        }
    }
    closedir($folder);
}

if($infolder){
	print("<a name=\"anb\"></a>");
	print("<hr>");
	echo $success.$delmessage;
	
	print("<table align=\"center\" width=\"1%\"><tr><td>");
	print("<form name=\"addnewblock\" method=\"post\" action=\"blocks-edit.php#anb\">".
		"<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
			"<tr>".
				"<td class=\"rowTabHead\" align=\"center\"><font size=\"2\"><b>"._BLC_AVAIL_."</b></font><br />("._IN_FOLDER_.")</td>".
			"</tr>".
		"</table><br />".
		"<table cellspacing=\"1\" class=\"tablebg\" align=\"center\">".
			"<tr>".
				"<td class=\"rowTabHead\" align=\"center\">"._NAMED_."<br />("._FL_NM_IF_NO_SET_.")</td>".
				"<td class=\"rowTabHead\" align=\"center\">".FILE."</td>".
				"<td class=\"rowTabHead\" align=\"center\">".DESCRIPTION."<br />("._MAX_." 255 "._CHARS_.")</td>".
				"<td class=\"rowTabHead\" align=\"center\">"._ADD_."</td>".
				"<td class=\"rowTabHead\" align=\"center\">"._DEL_."</td>".
			"</tr>");
	
			/* loop over the blocks directory and take file names witch are not in database. */
			if ($folder = opendir('blocks')) {
				$i=0;
				while (false !== ($file = readdir($folder))) {
					if ($file != "." && $file != ".." && !in_array($file, $indb)) {
						if (preg_match("/_block.php/i", $file)){
							if(!$setclass){
								$class="row2";$setclass=true;}
							else{
								$class="row1";$setclass=false;}
							print("<tr>".
										"<input type=\"hidden\" name=\"addblock_".$i."\" value=\"".$file."\" />".
										"<td class=$class><input type=\"text\" name=\"wantedname_".$i."\" value=\"".str_replace("_block.php","",$file)."\"/></td>".
										"<td class=$class>$file</td>".
										"<td class=$class align=\"center\"><textarea name=\"wanteddescription_".$i."\" rows=2 cols=20></textarea>".
										"<td class=$class align=\"center\"><div id=\"addn_".$i."\" ><input type=checkbox name=addnew[] value=\"".$i."\" onclick=\"javascript: if(dltp_".$i.".style.display=='none'){dltp_".$i.".style.display='block'}else{dltp_".$i.".style.display='none'}; \" /></div></td>".
										"<td class=$class align=\"center\"><div id=\"dltp_".$i."\" ><input type=checkbox name=deletepermanent[] value=\"".$file."\" onclick=\"javascript: if(addn_".$i.".style.display=='none'){addn_".$i.".style.display='block'}else{addn_".$i.".style.display='none'}\" /></div></td>".
									"</tr>");
							$i++;
						}
					}
				}
			closedir($folder);
			}
			/* end loop over the blocks directory and take names. */
	
	print("<tr>".
				"<td colspan=\"5\" class=\"rowTabHead\" align=\"center\"><input type=\"submit\" name=\"submit\" class=\"btn\" value=\""._BTN_DOIT_."\" />&nbsp;<input type=\"reset\" class=\"btn\" value=\""._BTN_RESET_."\" /></td>".
			"</tr>".
			"</table>".
		"</form></td></tr></table>");
	print("<center>("._DLT_WIL_PER_." <font color=red>"._NO_ADD_WAR_."</font>)</center><br />");
}
// ---- </table> for blocks exist but not in database ----------------------------

// ---- <table> for upload block -------------------------------------------------
print("<a name=\"upload\"></a>");
print("<hr>");

if($upload){
	if($uplfailmessage){
		echo $uplfailmessage;
	}else{
	echo $uplsuccessmessage;
	}
}

print("<table align=\"center\" width=\"1%\"><tr><td>");
print("<form enctype=\"multipart/form-data\"  action=\"blocks-edit.php#upload\" method=\"post\" >".
			"<input type=\"hidden\" name=\"upload\" value=\"true\" />".
		"<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
			"<tr>".
					"<td class=\"rowTabHead\" align=\"center\"><font size=\"2\"><b>"._BLC_UPL_."</b></font><br></td>".
			"</tr>".
		"</table><br />".
		"<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
			"<tr>".
				"<td class=\"rowTabHead\" valign=\"top\">"._NAMED_."</td>".
				"<td class=\"row2\" valign=\"top\"><input type=\"text\" size=\"33\" name=\"wantedname\" /><br />("._FL_NM_IF_NO_SET_.")</td>".
			"</tr>".
			"<tr>".
				"<td class=\"rowTabHead\" valign=\"top\">".DESCRIPTION."</td>".
				"<td class=\"row2\" valign=\"top\"><textarea name=\"description\" rows=2 cols=25></textarea><br />("._MAX_." 255 "._CHARS_.")</td>".
			"</tr>".
			"<tr>".
				"<td class=\"rowTabHead\" valign=\"top\">".FILE."</td>".
				"<td class=\"row2\" valign=\"top\"><input type=\"file\" name=\"blockupl\" style=\"border: 1px solid #d0d0d0; background-color: white;\" /></td>".
			"</tr>".
		"</table><br />".
		"<table class=\"tablebg\" cellspacing=\"1\" align=\"center\" width=\"100%\">".
			"<tr>".
				"<td class=\"rowTabHead\" align=\"center\">"._POSITION_."</td>".
				"<td class=\"rowTabHead\" align=\"center\">"._SORT_."</td>".
				"<td class=\"rowTabHead\" align=\"center\">".ENABLED."</td>".
				"<td class=\"rowTabHead\" align=\"center\">"._JUST_UPL_."</td>".
			"</tr>".
			"<tr>".
				"<td class=\"row2\">".
					"<div id=\"pos\">".
					"<table align=\"center\" width=\"100%\">".
						"<tr>".
							"<td align=\"center\" ><input type=\"radio\" name=\"position\" checked=\"checked\" value=\"left\" onclick=\"javascript: if(enabledyes.checked){uplsort.value = '$nextleft';}else{uplsort.value = '0';} \" /></td>".
							"<td align=\"center\" ><input type=\"radio\" name=\"position\" value=\"middle\" onclick=\"javascript: if(enabledyes.checked){uplsort.value = '$nextmiddle';}else{uplsort.value = '0';} \" /></td>".
							"<td align=\"center\" ><input type=\"radio\" name=\"position\" value=\"right\" onclick=\"javascript: if(enabledyes.checked){uplsort.value = '$nextright';}else{uplsort.value = '0';} \" /></td>".
						"</tr>".
						"<tr>".
							"<td align=\"center\" >["._L_."]</td>".
							"<td align=\"center\" >["._M_."]</td>".
							"<td align=\"center\" >["._R_."]</td>".
						"</tr>".
					"</table>".
					"</div>".
				"</td>".
				"<td class=\"row2\" align=\"center\"><input type=\"text\" name=\"uplsort\" size=\"1\" readonly=\"readonly\" value=\"0\" style=\"text-align: center;\" onclick=\"javascript: alert('"._CLICK_POS_."');\" /></td>".
				"<td class=\"row2\" align=\"center\"><input type=\"checkbox\" name=\"enabledyes\" onclick=\"javascript: uploadonly.disabled = enabledyes.checked; if(enabledyesnotice.style.display == 'block'){enabledyesnotice.style.display = 'none'}else{enabledyesnotice.style.display = 'block'}; if(!checked){uplsort.value = '0'}\"   /></td>".
				"<td class=\"row2\" align=\"center\"><input type=\"checkbox\" name=\"uploadonly\" onclick=\"javascript: wantedname.disabled = enabledyes.disabled = description.disabled = pos.disabled = uploadonly.checked; if(uploadonlynotice.style.display == 'block'){uploadonlynotice.style.display = 'none'}else{uploadonlynotice.style.display = 'block'};\"   /></td>".
			"</tr>".
			"<tr>".
				"<td colspan=\"4\" class=\"rowTabHead\" align=\"center\"><input type=\"submit\" class=\"btn\" value=\""._BTN_UPL_."\" /><div id=\"uploadonlynotice\" style=\"display: none;\">("._UPL_ONLY_.")</div><div id=\"enabledyesnotice\" style=\"display: none;\">("._UPL_ADD_.")</div></td>".
			"</tr>".
		"</table>".
	"</form>");
print("</td></tr></table>");	
// ---- </table> for upload block -------------------------------------------------
	
end_frame();
stdfoot();

?>
