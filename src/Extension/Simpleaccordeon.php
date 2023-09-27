<?php
/**
 * @version		2.1.0
 * @package		Simple Accordeon content plugin
 * @author		ConseilGouz
 * @copyright	Copyright (C) 2023 ConseilGouz. All rights reserved.
 * @license		GNU/GPL v2; see LICENSE.php
 **/
namespace ConseilGouz\Plugin\Content\Simpleaccordeon\Extension;
// no direct access
defined( '_JEXEC' ) or die;
use Joomla\CMS\Factory;
use Joomla\CMS\Plugin\CMSPlugin;
use Joomla\CMS\HTML\HTMLHelper;

class Simpleaccordeon extends CMSPlugin {
	public function onContentPrepare( $context, &$article, &$params, $page = 0 )
	{
		// first figure out whether to use article id or item_id:
		if (!((isset($article->item_id)) || (isset($article->id)))) {return;} 
		$id = isset($article->item_id) ? $article->item_id : $article->id;
		// simple performance check to determine whether bot should process further
		$shortcode = $this->params->get('shortcode','accordeon'); 
		if (strpos($article->text, '{'.$shortcode.'') === false ) {
			return true;
		}
		$regex = '/{'.$shortcode.'[\s\S]+?{\/'.$shortcode.'}*\s*/'; // get each accordeon chunk
	 	// find all instances of slides and put in $matches
		if (preg_match_all($regex, $article->text, $accordeon)) { // make sure there is a match
			$plg	= 'media/simpleaccordeon/';
			/** @var Joomla\CMS\WebAsset\WebAssetManager $wa */
			$wa = Factory::getApplication()->getDocument()->getWebAssetManager();
			$wa->registerAndUseStyle('simpleaccordeon', $plg.'/css/simpleaccordeon.css');
			$bgcolor = $this->params->get('backgroundcolor','#eeeeee'); 
			$chrcolor = $this->params->get('chrcolor','#000000'); 
			$selbgcolor = $this->params->get('selbgcolor','#c7c7bf'); 
			$fontsize = $this->params->get('fontsize','15px'); 
			$wa->addInLineStyle('button.accordeon {background-color: '.$bgcolor.';color:'.$chrcolor.';font-size:'.$fontsize.';line-height:'.$fontsize.'}');
			$wa->addInLineStyle('button.accordeon:not(.collapsed), button.accordeon:hover {background-color: '.$selbgcolor.' !important;}');
			// prep more specific regex:
			// add something to strip <br>s, like (?:<br ?\/?\>)
			$regex = '/(?:<(div|p)[^>]*>)?{'.$shortcode.'(?:=(.+))?}(?(1) *<\/\1>)?([\s\S]+?)(?:<(div|p)[^>]*>)?{\/'.$shortcode.'}(?(4) *<\/\4>)/i';
			foreach($accordeon[0] as $key=>$accordeon) {
				if (preg_match_all($regex, $accordeon, $matches, PREG_SET_ORDER)) { // ensure the more specific regex matches
					$identifier = 'acc' . $id . '_' . ($key+1);
					foreach ($matches as $match) {
						// $match[0] is full pattern match, $match[1] is an html tag wrapping '{accordeon ...}' (if found),
						// $match[2] is the title (toggler), $match[3] is the actual block,
						// $match[4] is an html tag wrapping '{/accordeon}' (if found)
						$output = '';
						// Make sure we've got what we need:
						if ($match[3]) {
							$tag = array();
							//  |open to open accordion as default behavior
							$strcollapsed = 'collapsed';
							$straria="false";
							$show = "";
							if (strpos($match[2],'|open') > 0 ) {
							    $match[2] = str_replace('|open','',$match[2]);
								$strcollapsed = '';
								$straria="true";
								$show = "show";
							}
							HTMLHelper::_('bootstrap.collapse', '#' . $identifier);
							$tag[1]= '<button id="'.$identifier.'" data-bs-toggle="collapse" data-bs-target="#panel'.$identifier.'" aria-expanded="'.$straria.'" aria-controls="panel'.$identifier.'" class="accordeon '.$strcollapsed.'">'.$match[2].'</button>';
							$tag[2] = '<div class="collapse '.$show.' " id="panel'.$identifier.'">'.$match[3].'</div>';
							$output .= $tag[1] . $tag[2];
						}
						$article->text = str_replace($match[0], $output, $article->text);
					}
				}
			}
			$article->text = $article->text;
		}
	}
}
?>
