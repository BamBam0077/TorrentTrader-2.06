<?
//
//  TorrentTrader v2.x
//	This file was last updated: 20/July/2007
//	
//	http://www.torrenttrader.org
//
//

function textbbcode($form,$name,$content="") {
	//$form = form name
	//$name = textarea name
	//$content = textarea content (only for edit pages etc)
?>
<script language=javascript>
function SmileIT(smile,form,text){
    document.forms[form].elements[text].value = document.forms[form].elements[text].value+" "+smile+" ";
    document.forms[form].elements[text].focus();
}

function PopMoreSmiles(form,name) {
         link='backend/smilies.php?action=display&form='+form+'&text='+name
         newWin=window.open(link,'moresmile','height=500,width=450,resizable=no,scrollbars=yes');
         if (window.focus) {newWin.focus()}
}

function PopMoreTags(form,name) {
         link='tags.php?form='+form+'&text='+name
         newWin=window.open(link,'moresmile','height=500,width=775,resizable=no,scrollbars=yes');
         if (window.focus) {newWin.focus()}
}


function BBTag(tag,s,text,form){
switch(tag)
    {
    case '[quote]':
    if (document.forms[form].elements[s].value=="QUOTE ")
       {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[quote]";
        document.forms[form].elements[s].value="QUOTE*";
        }
       else
           {
           document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[/quote]";
           document.forms[form].elements[s].value="QUOTE ";
           }
        break;
    case '[img]':
    if (document.forms[form].elements[s].value=="IMG ")
       {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[img]";
        document.forms[form].elements[s].value="IMG*";
        }
       else
           {
           document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[/img]";
           document.forms[form].elements[s].value="IMG ";
           }
        break;
    case '[url]':
    if (document.forms[form].elements[s].value=="URL ")
       {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[url]";
        document.forms[form].elements[s].value="URL*";
        }
       else
           {
           document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[/url]";
           document.forms[form].elements[s].value="URL ";
           }
        break;
    case '[*]':
    if (document.forms[form].elements[s].value=="List ")
       {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[*]";
        }
        break;
    case '[b]':
    if (document.forms[form].elements[s].value=="B ")
       {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[b]";
        document.forms[form].elements[s].value="B*";
        }
       else
           {
           document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[/b]";
           document.forms[form].elements[s].value="B ";
           }
        break;
    case '[i]':
    if (document.forms[form].elements[s].value=="I ")
       {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[i]";
        document.forms[form].elements[s].value="I*";
        }
       else
           {
           document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[/i]";
           document.forms[form].elements[s].value="I ";
           }
        break;
    case '[u]':
    if (document.forms[form].elements[s].value=="U ")
       {
        document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[u]";
        document.forms[form].elements[s].value="U*";
        }
       else
           {
           document.forms[form].elements[text].value = document.forms[form].elements[text].value+"[/u]";
           document.forms[form].elements[s].value="U ";
           }
        break;
    }
    document.forms[form].elements[text].focus();
}

</script>

  <CENTER>
  <table width="100%" cellpadding="0" cellspacing="0" border="0">
    <tr>
      <td colspan="2" align=center>
		  <table cellpadding="2" cellspacing="1">
		  <tr>
		  <td><input style="font-weight: bold;" type="button" name="bold" value="B " onclick="javascript: BBTag('[b]','bold','<? echo $name; ?>','<? echo $form; ?>')" /></td>
		  <td><input style="font-style: italic;" type="button" name="italic" value="I " onclick="javascript: BBTag('[i]','italic','<? echo $name; ?>','<? echo $form; ?>')" /></td>
		  <td><input style="text-decoration: underline;" type="button" name="underline" value="U " onclick="javascript: BBTag('[u]','underline','<? echo $name; ?>','<? echo $form; ?>')" /></td>
		  <td><input type="button" name="li" value="List " onclick="javascript: BBTag('[*]','li','<? echo $name; ?>','<? echo $form; ?>')" /></td>
		  <td><input type="button" name="quote" value="QUOTE " onclick="javascript: BBTag('[quote]','quote','<? echo $name; ?>','<? echo $form; ?>')" /></td>
		  <td><input type="button" name="url" value="URL " onclick="javascript: BBTag('[url]','url','<? echo $name; ?>','<? echo $form; ?>')" /></td>
		  <td><input type="button" name="img" value="IMG " onclick="javascript: BBTag('[img]','img','<? echo $name; ?>','<? echo $form; ?>')" /></td>
		  <td>&nbsp;<a href="javascript: PopMoreTags('<? echo $form; ?>','<? echo $name; ?>')"><? echo "[".MORE_TAGS."]";?></a></td>
		  </tr>
		  </table>
	</td>
	</tr>

	<tr>
    <td align=center>
	<textarea name="<? echo $name; ?>" rows="10" cols="50"><? echo $content; ?></textarea>
	</td>    
	<td>
		<center><table border="0" cellpadding=0 cellspacing=0>
		<tr>
		<td width=26><a href="javascript:SmileIT(':)','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/smile1.gif" border="0" alt=':)'></a></td>
		<td width=26><a href="javascript:SmileIT(';)','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/wink.gif" border="0" alt=';)'></a></td>
		<td width=26><a href="javascript:SmileIT(':D','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/grin.gif" border="0" alt=':D'></a></td>
		</tr>
		<tr>
		<td width=26><a href="javascript:SmileIT(':P','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/tongue.gif" border="0" alt=':P'></a></td>
		<td width=26><a href="javascript:SmileIT(':lol:','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/laugh.gif" border="0" alt=':lol:'></a></td>
		<td width=26><a href="javascript:SmileIT(':yes:','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/yes.gif" border="0" alt=':yes:'></a></td>
		</tr>
		<tr>
		<td width=26><a href="javascript:SmileIT(':no:','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/no.gif" border="0" alt=':no:'></a></td>
		<td width=26><a href="javascript:SmileIT(':wave:','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/wave.gif" border="0" alt=':wave:'></a></td>
		<td width=26><a href="javascript:SmileIT(':ras:','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/ras.gif" border="0" alt=':ras:'></a></td>
		</tr>
		<tr>
		<td width=26><a href="javascript:SmileIT(':sick:','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/sick.gif" border="0" alt=':sick:'></a></td>
		<td width=26><a href="javascript:SmileIT(':yucky:','<?echo $form?>','<?echo $name?>')"><img src="images/smilies/yucky.gif" border="0" alt=':yucky:'></a></td>
		<td width=26><a href="javascript:SmileIT(':rolleyes:','<?echo $form?>','<?echo $name?>')"><img src=images/smilies/rolleyes.gif border="0" alt=':rolleyes:'></a></td>
		</tr>
		</table>
	<br>
	<a href="javascript: PopMoreSmiles('<? echo $form; ?>','<? echo $name; ?>')"><? echo "[".MORE_SMILIES."]";?></a></a><br><br></center>
   </td>  
   </tr>
</table>
<?
}
?>
