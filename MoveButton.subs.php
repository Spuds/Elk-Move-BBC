<?php

/**
 * @package Elk Move Button
 * @author Spuds
 * @copyright (c) 2011-2014 Spuds
 * @license WTFPL http://www.wtfpl.net/txt/copying/
 *
 * @version 1.1
 *
 */
if (!defined('ELK'))
	die('No access...');

/**
 * ibc_move_button
 *
 * - Subs hook for 1.0.x, integrate_bbc_codes hook, Called from Subs.php
 * - Used to add[move][/move] parsing values
 *
 * @param mixed[] $codes array of codes as defined for parse_bbc
 */
function ibc_move_button(&$codes)
{
	global $modSettings;

	// Only for when bbc is on
	if (empty($modSettings['enableBBC']))
		return;

	// Make sure the admin has not disabled the move tag
	if (!empty($modSettings['disabledBBC']))
	{
		foreach (explode(',', $modSettings['disabledBBC']) as $tag)
		{
			if ($tag === 'move')
				return;
		}
	}

	// All good, lets add our tag info to the parser, this controls how the tag will render
	// with parse_bbc when found in a post
	$codes[] = array(
		'tag' => 'move',
		'before' => '<marquee>',
		'after' => '</marquee>',
		'block_level' => true,
		'disallow_children' => array('move'),
	);
}

/**
 * iab_move_button
 *
 * - Codes hook for 1.1.x, integrate_additional_bbc, Called from ParseWrapper getCodes()
 *
 * @param mixed[] $codes array of codes as defined for parse_bbc
 */
function iab_move_button(&$codes)
{
	// Add our tag info to the parser, this controls how the tag will render
	$codes[] =
		array(
			\BBC\Codes::ATTR_TAG => 'move',
			\BBC\Codes::ATTR_TYPE => \BBC\Codes::TYPE_PARSED_CONTENT,
			\BBC\Codes::ATTR_BEFORE => '<marquee>',
			\BBC\Codes::ATTR_AFTER => '</marquee>',
			\BBC\Codes::ATTR_DISALLOW_CHILDREN => array('move'),
			\BBC\Codes::ATTR_BLOCK_LEVEL => true,
			\BBC\Codes::ATTR_AUTOLINK => false,
			\BBC\Codes::ATTR_LENGTH => 4,
		);
}

/**
 * ibb_move_button
 *
 * - Editor hook, integrate_bbc_buttons hook, Called from Editor.subs.php
 * - Used to add buttons to the editor menu bar
 *
 * @param mixed[] $buttons
 */
function ibb_move_button(&$bbc_tags)
{
	global $context;

	// Add the button in the 'bold', 'italic', 'underline', 'strike', 'superscript', 'subscript' group
	// This can be as simple as $bbc_tags['row1'][0][] = 'move'; to add it at the end
	// but for this exercise we will insert it after the strike tag

	// This is the group we intend to modify
	$where = $bbc_tags['row1'][0];

	// And here we insert the new value after strike
	$bbc_tags['row1'][0] = elk_array_insert($where, 'strike', array('move'), 'after', false);

	// Add the javascript, this tells the editor what to do with the new button
	// You don't need to do this if you don't want to add an editor button
	//
	// You can also add this to a file and use loadJavascript(yourfile) in place of using addInline
	// really depends on how complicated your function is
	//
	addInlineJavascript('
		// What to add in the editor window when the button is clicked, based on which mode the editor is in
		$.sceditor.command
			.set("move", {
				// Show the button on/off state
				state: function() {
					var currentNode = this.currentNode();

					return $(currentNode).is("marquee") || $(currentNode).parents("marquee").length > 0 ? 1 : 0;
				},
				exec: function () {
					this.insert("[move] ", "[/move]", false)
				},
				txtExec: ["[move]", "[/move]"],
				tooltip: "Move"
			}
		);

		// Called when toggling back and forth between wizzy and text, if you do not intend to render
		// the tag in WYSIWYG mode, you can omit this
		$.sceditor.plugins.bbcode.bbcode
			.set("move", {
				tags: {
					marquee: null,
				},
				isInline: false,
				format: function(element, content) {
					// Coming from wysiwyg (html) mode to bbc (text) mode
					return "[move]" + content + "[/move]";
				},
				html: function(element, attrs, content) {
					// Going to wysiwyg from bbc
					return "<marquee>" + content.replace("[", "&#91;") + "</marquee>";
				}
			}
		);'
	);

	// We need to supply the css for the button image, here we use a data-url to save an image call
	// But you can add this style to a css file as well
	$context['html_headers'] .= '<style>.sceditor-button-move div {background: url(data:image/gif;base64,R0lGODlhFwAWAJECAAAAAP///////////yH/C05FVFNDQVBFMi4wAwEAAAAh+QQJCgACACwAAAAAFwAWAAACL5SPqcvtD6OcVIJ7sNWCJ6BcmLiAIUl+4tqBbleW7xurLR2N46SZZgUMCofEIrEAACH5BAkKAAIALAAAAAAXABYAAAIwlI+py+0Po5w0gnsukDpjtSXaNoJLWSIjSgpkqJ7te4Ivbd0u/HzhVwkKh8SisVgAACH5BAkKAAIALAAAAAAXABYAAAIulI+py+0Po5z0gXsukDpjtSXaNnJdCY4kKaxmC39i2mKytbrRF97VDwwKh8RhAQAh+QQJCgACACwAAAAAFwAWAAACMJSPqcvtD6OctIF7LpA646htIdeNxoaMmCh8H7O2LSpb4ko/+E2eZ14JCofEovFYAAAh+QQJCgACACwAAAAAFwAWAAACMpSPqcvtD6OcdIF7LpA646htYbJZ3Zgp2CqIZfupYgsjrwy7JLP2XAm8VYbEovGITBoKACH5BAkKAAIALAAAAAAXABYAAAIxlI+py+0Po5w0gXsukDrjqG2hxYxj1mCi8LEnorIunIrqRi+3zeE+XgkKh8Si8agoAAAh+QQJCgACACwAAAAAFwAWAAACMpSPqcvtD6OcFAFwLpY3d95pW9Zogmg52HqOopmynzEua1ifZXtP25+rCIfEovGITBQAACH5BAkKAAIALAAAAAAXABYAAAIqlI+py+0Po5xUgVvPBVlsjoDPF0ocZnzkcopehKEaFLtmanf6zvf+nykAACH5BAkKAAIALAAAAAAXABYAAAIslI+py+0Po5w0AQDxuplxpIFeKJCHeYbXupajqKDGd8bvI5c5XPX+DwwKhwUAIfkECQoAAgAsAAAAABcAFgAAAiiUj6nL7Q+jnLQKgK3BIPbEfY2IhE5oZuSybl2rtKo2W5mG5/rO908BACH5BAUKAAIALAAAAAAXABYAAAItlI+py+0Po5yUglvPBZGnzXmLiIACWYLbGZ4NamDtq8iYFd7TDWf+DwwKh5ACADs=);}</style>';
}