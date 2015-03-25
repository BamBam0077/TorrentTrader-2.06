<?php
require "backend/functions.php";

dbconn();
loggedinonly();

stdhead("Staff");

    // Display Staff List to all users
    begin_frame("" . STAFF.  "");

    // Get current datetime
    $dt = get_date_time(gmtime() - 180);
    // Search User Database for Moderators and above and display in alphabetical order
    $res = mysql_query("SELECT id, username, class,last_access FROM users WHERE class >=3 AND status='confirmed' ORDER BY username") or sqlerr();
    $num = mysql_num_rows($res);
    while ($arr = mysql_fetch_assoc($res))
    {
        $staff_table[$arr['class']]=$staff_table[$arr['class']].
            "<td><img src=images/button_o".($arr[last_access]>$dt?"n":"ff")."line.gif></td>".
            "<td><a href=account-details.php?id=$arr[id]>$arr[username]</a></td>".
               "<td><a href=mailbox.php?compose&id=$arr[id]>".
            "<img src=images/button_pm.gif border=0></a></td><td> </td>";
        // Show 3 staff per row, separated by an empty column
        ++ $col[$arr['class']];
        if ($col[$arr['class']]<=4)
            $staff_table[$arr['class']]=$staff_table[$arr['class']]."<td> </td>";
        else
        {
            $staff_table[$arr['class']]=$staff_table[$arr['class']]."</tr><tr height=15>";
            $col[$arr['class']]=2;
        }
    }
?>
<BR><BR>
<br>
<table width=100% cellspacing=0 align=center>
<? if (get_user_class() >= 5) {
?>
<tr>
    <td colspan=14><b>Administrators</b><font color="#FF0000"> [HIDDEN FROM PUBLIC]</font></td>
</tr>
<tr>
    <td colspan=14><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
    <?=$staff_table[7]?>
</tr>
<tr>
    <td colspan=14> </td>
</tr>
<?
}
?>

<tr>
    <td colspan=14><b>Super Moderators</b></td>
</tr>
<tr>
    <td colspan=14><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
    <?=$staff_table[6]?>
</tr>
<tr>
    <td colspan=14> </td>
</tr>
<tr>
    <td colspan=14><b>Moderators</b></td>
</tr>
<tr>
    <td colspan=14><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
    <?=$staff_table[5]?>
</tr>
<? if (get_user_class() >= 5) {
?>
<tr>
    <td colspan=14> </td>
</tr>
<tr>
    <td colspan=14><b>VIP Members</b><font color="#FF0000"> [HIDDEN FROM PUBLIC]</font></td>
</tr>
<tr>
    <td colspan=14><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
    <?=$staff_table[3]?>
</tr>
<tr>
    <td colspan=14> </td>
</tr>
<tr>
    <td colspan=14><b>Uploaders</b><font color="#FF0000"> [HIDDEN FROM PUBLIC]</font></td>
</tr>
<tr>
    <td colspan=14><hr color="#4040c0" size=1></td>
</tr>
<tr height=15>
    <?=$staff_table[4]?>
</tr>
<tr>
    <!-- Define table column widths -->
    <td width="20"></td>
    <td width="100"></td>
    <td width="25"></td>
    <td width="35"></td>
    <td width="90"></td>
    <td width="20"></td>
    <td width="100"></td>
    <td width="25"></td>
    <td width="35"></td>
    <td width="90"></td>
    <td width="20"></td>
    <td width="100"></td>
    <td width="25"></td>
    <td width="35"></td>
</tr>
<?
}
?>

</table>
<?
end_frame();

stdfoot();
?>