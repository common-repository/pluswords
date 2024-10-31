<?php

/*
Plugin Name: PlusWords
Plugin URI: http://pluswords.thehumblyproud.com/
Description: Show the most used words on your posts and pages and counts them.
Author: Luis Guerreiro
Version: 0.2
Author URI: http://pluswords.thehumblyproud.com/
*/

// ################################################################################################
function get_post_data($postId) {
	global $wpdb;
	$resultados = $wpdb->get_results("SELECT * FROM $wpdb->posts WHERE ID=$postId AND post_status = 'publish'");
	if ($resultados) return $resultados;
}

function get_post_count() {
	global $wpdb;
	$numposts = $wpdb->get_var("SELECT COUNT(*) FROM $wpdb->posts");
	if (0 < $numposts) $numposts = number_format($numposts); 
	return $numposts;
}

function pluswords_widget() {
	$options = get_option("plugin_pluswords_options"); 
	if ($options['widget_ocurrencias'] != "") {
		$occurrences=$options['widget_ocurrencias'];	
	} else {
		$occurrences=5;
	}
	if ($options['widget_tamanho'] != "") {
		$tamanho=$options['widget_tamanho'];	
	} else {
		$tamanho=5;
	}

	$exclude = file_get_contents(dirname(__FILE__).'/exclude.txt', true);
	$exclude = mb_convert_encoding($exclude, 'UTF-8', mb_detect_encoding($exclude, 'UTF-8, ISO-8859-1', true));
	$contarMuda=0;
	for ($i=1;$i<=get_post_count();$i++) {
		$data = get_post_data($i);
		$datas = $datas . " " . $data[0]->post_content; 
		$datas = $datas . " " . $data[0]->post_post_title; 
	}
	$newstring = preg_replace("/[^A-Za-z0-9[:space:]!-_áàéèóòõãẽÓñÑÒÁÀêÊíìçÍÌúÚùÙ]/", "", strtolower(strip_tags($datas)));
	$pieces = explode(" ", $newstring);
	foreach($pieces as $word) {
		if ($word) {
			$word = " " . $word . " ";  
			if (substr_count($newstring,$word) >= $occurrences && substr_count($exclude,strtolower($word)) == 0) {
				if ((@substr_count($array,$word) < 1) && (strlen(trim($word)) >= $tamanho)) {
					$array = $array . $word . "(" . substr_count($newstring,$word) . ")" . " ";
					if (isset($palavrasAdd[substr_count($newstring,$word)])) {
						$palavrasAdd[substr_count($newstring,$word)] .= trim($word) . " ";
					} else {
						$palavrasAdd[substr_count($newstring,$word)] = trim($word) . " ";
					}
					if (substr_count($newstring,$word) >$max) {
						$max = substr_count($newstring,$word);
					}
				}
					$contarMuda=1;
			}
			if ($contarMuda == 0 && count($palavrasAdd)<1) {
				$occurrences-=1;
			}
		}
	}
	$c = 0;
	echo "<h3>PlusWords:</h3>";
	for ($x=$max;$x>0;$x--) { 
		if ($palavrasAdd[$x] != "") {
			$explodir=explode(" ", $palavrasAdd[$x]);
			$fontsize = 17-($c*2);
			if ($x == 0) {
				$blogurl = "--" . get_bloginfo('url');
			} else {
				$blogurl = "";
			}
			if (count($explodir)>1) {
				for ($i=0;$i<=count($explodir);$i++) {
					if ($explodir[$i] != "") {
						$randomizar[] = $fontsize . "--" . $explodir[$i] . $blogurl;
					}
				}
			} else {
			$randomizar[] = $fontsize . "--" . $palavrasAdd[$x] . $blogurl;
			}
			if ($c==8) {
				$c=8;
			} else {
				$c++;
			}
		} 
	}
	shuffle($randomizar);
	for ($i=0;$i<count($randomizar);$i++) {
		if ($i==(count($randomizar))) {
		$flashVars .= $i . "=" . $randomizar[$i];
		} else {
			$flashVars .= $i . "=" . $randomizar[$i] . "&";
		}
	}
	echo "<div><div>
<OBJECT classid=\"clsid:D27CDB6E-AE6D-11cf-96B8-444553540000\" codebase=\"http://macromedia.com/cabs/swflash.cab#version=6,0,0,0\" ID=plusWords WIDTH=190 HEIGHT=210>
  <PARAM NAME=movie VALUE=\"/pluswords.swf\">
  <PARAM NAME=quality VALUE=medium>
  <PARAM NAME=\"wmode\" VALUE=\"transparent\"> 
  <PARAM NAME=FlashVars VALUE=\"" . $flashVars . "\">
  <PARAM NAME=bgcolor VALUE=#ffffff>
  <EMBED src=\"/pluswords.swf\" FlashVars=\"" . $flashVars . "\" bgcolor=#ffffff WIDTH=190 HEIGHT=210 TYPE=\"application/x-shockwave-flash\"  wmode=\"transparent\" >
  </EMBED>
</OBJECT></div></div>";
	echo "<p align=\"right\"><span style=\"font-size: 10px;\"><a href=\"http://pluswords.thehumblyproud.com/\">pluswords</a></span></p>";
}


function pluswords_widget_control() {

    $options = get_option("plugin_pluswords_options"); 

    if ($_POST['pluswords-Submit']) {
        $options['widget_ocurrencias'] = htmlspecialchars($_POST['pluswords-widget-ocurrencias']);
        $options['widget_tamanho'] = htmlspecialchars($_POST['pluswords-widget-tamanho']);
        update_option("plugin_pluswords_options", $options);
    }

?>

<p>
<label for="pluswords-widget-ocurrencias">No of Ocurrences: </label>
<input type="text" id="pluswords-widget-ocurrencias" name="pluswords-widget-ocurrencias" value="<?php echo $options['widget_ocurrencias']; ?>" />
<br /><br />
<label for="pluswords-widget-tamanho">Word Length: </label>
<input type="text" id="pluswords-widget-tamanho" name="pluswords-widget-tamanho" value="<?php echo $options['widget_tamanho']; ?>" />
<input type="hidden" id="pluswords-Submit"  name="pluswords-Submit" value="1" />
</p>

<?php

}

 
function init_pluswords(){
	register_sidebar_widget("PlusWords", "pluswords_widget");
	register_widget_control('Pluswords', 'pluswords_widget_control');
}
add_action("plugins_loaded", "init_pluswords");

?>
