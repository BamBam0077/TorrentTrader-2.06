<?

if ($site_config['NEWSON']){ //check news is turned on first

	begin_block("Latest News");

	?>
	<style type="text/css">

	#marqueecontainer{
	position: relative;
	/*width: 200px; marquee width */
	height: 200px; /*marquee height */
	background-color: white;
	overflow: hidden;
	/*border: 3px solid orange;*/
	padding: 2px;
	padding-left: 4px;
	}

	</style>

	<script type="text/javascript">

	/***********************************************
	* Cross browser Marquee II- © Dynamic Drive (www.dynamicdrive.com)
	* This notice MUST stay intact for legal use
	* Visit http://www.dynamicdrive.com/ for this script and 100s more.
	***********************************************/

	var delayb4scroll=2000 //Specify initial delay before marquee starts to scroll on page (2000=2 seconds)
	var marqueespeed=1 //Specify marquee scroll speed (larger is faster 1-10)
	var pauseit=1 //Pause marquee onMousever (0=no. 1=yes)?

	////NO NEED TO EDIT BELOW THIS LINE////////////

	var copyspeed=marqueespeed
	var pausespeed=(pauseit==0)? copyspeed: 0
	var actualheight=''

	function scrollmarquee(){
	if (parseInt(cross_marquee.style.top)>(actualheight*(-1)+8))
	cross_marquee.style.top=parseInt(cross_marquee.style.top)-copyspeed+"px"
	else
	cross_marquee.style.top=parseInt(marqueeheight)+8+"px"
	}

	function initializemarquee(){
	cross_marquee=document.getElementById("vmarquee")
	cross_marquee.style.top=0
	marqueeheight=document.getElementById("marqueecontainer").offsetHeight
	actualheight=cross_marquee.offsetHeight
	if (window.opera || navigator.userAgent.indexOf("Netscape/7")!=-1){ //if Opera or Netscape 7x, add scrollbars to scroll and exit
	cross_marquee.style.height=marqueeheight+"px"
	cross_marquee.style.overflow="scroll"
	return
	}
	setTimeout('lefttime=setInterval("scrollmarquee()",30)', delayb4scroll)
	}

	if (window.addEventListener)
	window.addEventListener("load", initializemarquee, false)
	else if (window.attachEvent)
	window.attachEvent("onload", initializemarquee)
	else if (document.getElementById)
	window.onload=initializemarquee


	</script>

	<div id="marqueecontainer" onMouseover="copyspeed=pausespeed" onMouseout="copyspeed=marqueespeed">
	<div id="vmarquee" style="position: absolute; width: 100%;">

	<!--YOUR SCROLL CONTENT HERE-->
	<?
	$res = mysql_query("SELECT * FROM news WHERE ADDDATE(added, INTERVAL 45 DAY) > '".get_date_time()."' ORDER BY added DESC LIMIT 10") or die(mysql_error());
	if (mysql_num_rows($res) > 0){
		while($array = mysql_fetch_array($res)){
			print("<a href=comments.php?type=news&id=". $array['id'] . "><b>". $array['title'] . "</b></a><BR><B>Posted:</B> " . gmdate("d-M-y",strtotime($array['added'])) . "<BR><BR><BR>");
		}
	}else{
		echo "No News Yet..";
	}
	?>
	</div>
	</div>
	<?

	end_block();
}//end newson check
?>