<?php

/**
 * @package SimplePortal
 *
 * @author SimplePortal Team
 * @copyright 2014 SimplePortal Team
 * @license BSD 3-clause
 *
 * @version 2.4.2
 */

/**
 * Show the list of availalbe blocks in the system
 * If no specific area is provided will show all areas with available blocks in each
 * otherwise will just show the chosen area
 */
function template_block_list()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_manage_blocks">';

	// Show each portal area with the blocks in each one
	foreach($context['sides'] as $id => $side)
	{
		$sortables[] = '#side_' . $side['id'];

		echo '
		<h3 class="category_header">
			<a class="floatright" href="', $scripturl, '?action=admin;area=portalblocks;sa=add;col=', $side['id'], '">', sp_embed_image('add', sprintf($txt['sp-blocksCreate'], $side['label'])), '</a>
			<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=', $side['help'], '" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
			<a href="', $scripturl, '?action=admin;area=portalblocks;sa=', $id, '">', $side['label'], ' ', $txt['sp-blocksBlocks'], '</a>
		</h3>
		<table class="table_grid">
			<thead>
				<tr class="table_head">';

		foreach ($context['columns'] as $column)
			echo '
					<th scope="col"', isset($column['class']) ? ' class="' . $column['class'] . '"' : '', isset($column['width']) ? ' width="' . $column['width'] . '"' : '', '>', $column['label'], '</th>';

		echo '
				</tr>
			</thead>
			<tbody id="side_', $side['id'] ,'" class="sortme">';

		if (empty($context['blocks'][$side['name']]))
		{
			echo '
				<tr class="windowbg">
					<td class="centertext noticebox" colspan="4"></td>
				</tr>';
		}

		foreach($context['blocks'][$side['name']] as $block)
		{
			echo '
				<tr id="block_',$block['id'],'" class="windowbg">
					<td>', $block['label'], '</td>
					<td>', $block['type_text'], '</td>
					<td class="centertext">', implode(' ', $block['actions']), '</td>
				</tr>';
		}

		echo '
			</tbody>
		</table>';
	}

	// Engage sortable to allow drag/drop arrangement of the blocks
	echo '
	</div>
	<script>
		// Set up our sortable call
		$().elkSortable({
			sa: "blockorder",
			error: "' . $txt['admin_order_error'] . '",
			title: "' . $txt['admin_order_title'] . '",
			token: {token_var: "' . $context['admin-sort_token_var'] . '", token_id: "' . $context['admin-sort_token'] . '"},
			tag: "' . implode(',', $sortables) . '",
			connect: ".sortme",
			containment: "#sp_manage_blocks",
			href: "?action=admin;area=portalblocks",
			placeholder: "ui-state-highlight",
			axis: "y",
		});
	</script>';
}

/**
 * Used to edit a blocks details when using the block on the portal
 */
function template_block_edit()
{
	global $context, $settings, $options, $scripturl, $txt, $helptxt, $modSettings;

	// Want to take a look before you save?
	if (!empty($context['SPortal']['preview']))
	{
		if (!empty($context['SPortal']['error']))
			echo '
	<div class="errorbox">' , $context['SPortal']['error'], '</div>';

		echo '
	<div class="sp_auto_align" style="width: ', $context['widths'][$context['SPortal']['block']['column']], ';">';

		template_block($context['SPortal']['block']);

		echo '
	</div>';
	}

	echo '
	<div id="sp_edit_block">
		<form id="admin_form_wrapper" name="sp_edit_block_form" id="sp_edit_block_form" action="', $scripturl, '?action=admin;area=portalblocks;sa=edit" method="post" accept-charset="UTF-8" onsubmit="submitonce(this);">
			<h3 class="category_header">
				<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=sp-blocks', $context['SPortal']['is_new'] ? 'Add' : 'Edit', '" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
				', $context['SPortal']['is_new'] ? $txt['sp-blocksAdd'] : $txt['sp-blocksEdit'], '
			</h3>
			<div class="windowbg">
				<div class="sp_content_padding">
					<dl class="sp_form">
						<dt>
							', $txt['sp-adminColumnType'], ':
						</dt>
						<dd>
							', $context['SPortal']['block']['type_text'], '
						</dd>
						<dt>
							<label for="block_name">', $txt['sp-adminColumnName'], ':</label>
						</dt>
						<dd>
							<input type="text" name="block_name" id="block_name" value="', $context['SPortal']['block']['label'], '" size="30" class="input_text" />
						</dd>
						<dt>
							<label for="block_permissions">', $txt['sp_admin_blocks_col_permissions'], ':</label>
						</dt>
						<dd>
							<select name="permissions" id="block_permissions">';

	foreach ($context['SPortal']['block']['permission_profiles'] as $profile)
		echo '
									<option value="', $profile['id'], '"', $profile['id'] == $context['SPortal']['block']['permissions'] ? ' selected="selected"' : '', '>', $profile['label'], '</option>';

	echo '
							</select>
						</dd>';

	// Display any options that are available for this block
	foreach ($context['SPortal']['block']['options'] as $name => $type)
	{
		if (empty($context['SPortal']['block']['parameters'][$name]))
			$context['SPortal']['block']['parameters'][$name] = '';

		echo '
						<dt>';

		if (!empty($helptxt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name]))
			echo '
							<a class="help" href="', $scripturl, '?action=quickhelp;help=sp_param_', $context['SPortal']['block']['type'] , '_' , $name, '" onclick="return reqOverlayDiv(this.href);">
								<img class="icon" src="', $settings['images_url'], '/helptopics.png" alt="', $txt['help'], '" />
							</a>';

		echo '
							<label for="', $type == 'bbc' ? 'bbc_content' : $name, '">', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name], ':</label>
						</dt>
						<dd>';

		if ($type == 'bbc')
		{
			echo '
						</dd>
					</dl>
					<div id="sp_rich_editor">
						<div id="sp_rich_bbc"></div>
						<div id="sp_rich_smileys"></div>
						', template_control_richedit($context['SPortal']['bbc'], 'sp_rich_smileys', 'sp_rich_bbc'), '
						<input type="hidden" name="bbc_name" value="', $name, '" />
						<input type="hidden" name="bbc_parameter" value="', $context['SPortal']['bbc'], '" />
					</div>
					<dl class="sp_form">';
		}
		elseif ($type == 'boards' || $type == 'board_select')
		{
					echo '
							<input type="hidden" name="parameters[', $name, ']" value="" />';

				if ($type == 'boards')
					echo '
							<select name="parameters[', $name, '][]" id="', $name, '" size="7" multiple="multiple">';
				else
					echo '
							<select name="parameters[', $name, '][]" id="', $name, '">';

				foreach ($context['SPortal']['block']['board_options'][$name] as $option)
					echo '
								<option value="', $option['value'], '"', ($option['selected'] ? ' selected="selected"' : ''), ' >', $option['text'], '</option>';

				echo '
							</select>';
		}
		elseif ($type == 'int')
			echo '
							<input type="text" name="parameters[', $name, ']" id="', $name, '" value="', $context['SPortal']['block']['parameters'][$name],'" size="7" class="input_text" />';
		elseif ($type == 'text')
			echo '
							<input type="text" name="parameters[', $name, ']" id="', $name, '" value="', $context['SPortal']['block']['parameters'][$name],'" size="25" class="input_text" />';
		elseif ($type == 'check')
				echo '
							<input type="checkbox" name="parameters[', $name, ']" id="', $name, '"', !empty($context['SPortal']['block']['parameters'][$name]) ? ' checked="checked"' : '', ' class="input_check" />';
		elseif ($type == 'select')
		{
				$options = explode('|', $txt['sp_param_' . $context['SPortal']['block']['type'] . '_' . $name . '_options']);

				echo '
							<select name="parameters[', $name, ']" id="', $name, '">';

				foreach ($options as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif (is_array($type))
		{
				echo '
							<select name="parameters[', $name, ']" id="', $name, '">';

				foreach ($type as $key => $option)
					echo '
								<option value="', $key, '"', $context['SPortal']['block']['parameters'][$name] == $key ? ' selected="selected"' : '', '>', $option, '</option>';

				echo '
							</select>';
		}
		elseif ($type == 'textarea')
		{
			echo '
						</dd>
					</dl>
					<div id="sp_text_editor">
						<textarea name="parameters[', $name, ']" id="', $name, '" cols="45" rows="10">', $context['SPortal']['block']['parameters'][$name], '</textarea>
						<input type="button" class="button_submit" value="-" onclick="document.getElementById(\'', $name, '\').rows -= 10" />
						<input type="button" class="button_submit" value="+" onclick="document.getElementById(\'', $name, '\').rows += 10" />
					</div>
					<dl class="sp_form">';
		}

		if ($type != 'bbc')
			echo '
						</dd>';
	}

	if (empty($context['SPortal']['block']['column']))
	{
		echo '
						<dt>
							<label for="block_column">', $txt['sp-blocksColumn'], ':</label>
						</dt>
						<dd>
							<select id="block_column" name="block_column">';

		$block_sides = array(5 => 'Header', 1 => 'Left', 2 => 'Top', 3 => 'Bottom', 4 => 'Right', 6 => 'Footer');
		foreach ($block_sides as $id => $side)
			echo '
								<option value="', $id, '">', $txt['sp-position' . $side], '</option>';

		echo '
							</select>
						</dd>';
	}

	if (count($context['SPortal']['block']['list_blocks']) > 1)
	{
		echo '
						<dt>
							', $txt['sp-blocksRow'], ':
						</dt>
						<dd>
							<select id="order" name="placement"', !$context['SPortal']['is_new'] ? ' onchange="this.form.block_row.disabled = this.options[this.selectedIndex].value == \'\';"' : '', '>
								', !$context['SPortal']['is_new'] ? '<option value="nochange">' . $txt['sp-placementUnchanged'] . '</option>' : '', '
								<option value="before"', (!empty($context['SPortal']['block']['placement']) && $context['SPortal']['block']['placement'] == 'before' ? ' selected="selected"' : ''), '>', $txt['sp-placementBefore'], '...</option>
								<option value="after"', (!empty($context['SPortal']['block']['placement']) && $context['SPortal']['block']['placement'] == 'after' ? ' selected="selected"' : ''), '>', $txt['sp-placementAfter'], '...</option>
							</select>
							<select id="block_row" name="block_row"', !$context['SPortal']['is_new'] ? ' disabled="disabled"' : '', '>';

		foreach ($context['SPortal']['block']['list_blocks'] as $block)
		{
			if ($block['id'] != $context['SPortal']['block']['id'])
				echo '
								<option value="', $block['row'], '"', (!empty($context['SPortal']['block']['row']) && $context['SPortal']['block']['row'] == $block['row'] ? ' selected="selected"' : ''), '>', $block['label'], '</option>';		}

		echo '
							</select>
						</dd>';
	}

	if ($context['SPortal']['block']['type'] != 'sp_boardNews')
	{
		echo '
						<dt>
							<label for="block_force">', $txt['sp-blocksForce'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="block_force" id="block_force" value="1"', $context['SPortal']['block']['force_view'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>';
	}

	echo '
						<dt>
							<label for="block_active">', $txt['sp-blocksActive'], ':</label>
						</dt>
						<dd>
							<input type="checkbox" name="block_active" id="block_active" value="1"', $context['SPortal']['block']['state'] ? ' checked="checked"' : '', ' class="input_check" />
						</dd>
					</dl>
					<div class="sp_button_container">
						<input type="submit" name="preview_block" value="', $txt['sp-blocksPreview'], '" class="right_submit" />
						<input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" class="right_submit" />
					</div>
				</div>
			</div>';

	if (!empty($context['SPortal']['block']['column']))
		echo '
			<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	echo '
			<input type="hidden" name="block_type" value="', $context['SPortal']['block']['type'], '" />
			<input type="hidden" name="block_id" value="', $context['SPortal']['block']['id'], '" />
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />';

	// Display Options is integrated
	if (!empty($modSettings['sp_enableIntegration']))
	{
		echo '
			<br />
			<h3 class="category_header">
				<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=sp-blocksDisplayOptions" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
				', $txt['sp-blocksDisplayOptions'], '
			</h3>
			<div class="windowbg2">
				<div class="sp_content_padding">
					<span class="floatright">', $txt['sp-blocksAdvancedOptions'], '<input type="checkbox" name="display_advanced" id="display_advanced" onclick="$(\'#sp_display_advanced\').slideToggle(300); document.getElementById(\'display_simple\').disabled = this.checked;" ', empty($context['SPortal']['block']['display_type']) ? '' : ' checked="checked"', ' class="input_check" /></span>
					', $txt['sp-blocksShowBlock'], '
					<select name="display_simple" id="display_simple"', empty($context['SPortal']['block']['display_type']) ? '' : ' disabled="disabled"', '>';

		foreach ($context['simple_actions'] as $action => $label)
			echo '
						<option value="', $action, '"', in_array($action, $context['SPortal']['block']['display']) ? ' selected="selected"' : '', '>', $label, '</option>';

		echo '
					</select>
					<div id="sp_display_advanced"', empty($context['SPortal']['block']['display_type']) ? ' style="display: none;"' : '', '>';

		$display_types = array('actions', 'boards', 'pages');
		foreach ($display_types as $type)
		{
			if (empty($context['display_' . $type]))
				continue;

			echo '
						<a href="javascript:void(0);" onclick="sp_collapseObject(\'', $type, '\')">
							<img id="sp_collapse_', $type, '" src="', $settings['images_url'], '/selected_open.png" alt="*" />
						</a> ', $txt['sp-blocksSelect' . ucfirst($type)], '
						<ul id="sp_object_', $type, '" class="reset sp_display_list" style="display: none;">';

			foreach ($context['display_' . $type] as $index => $action)
			{
				echo '
							<li>
								<input type="checkbox" name="display_', $type, '[]" id="', $type, $index, '" value="', $index, '"', in_array($index, $context['SPortal']['block']['display']) ? ' checked="checked"' : '', ' class="input_check" />
								<label for="', $type, $index, '">', $action, '</label>
							</li>';
		}

			echo '
							<li>
								<input type="checkbox" onclick="invertAll(this, this.form, \'display_', $type, '[]\');" class="input_check" /> <em>', $txt['check_all'], '</em>
							</li>
						</ul>
						<br />';
		}

		echo '
						<a class="help" href="', $scripturl, '?action=quickhelp;help=sp-blocksCustomDisplayOptions" onclick="return reqOverlayDiv(this.href);">
							<img class="icon" src="', $settings['images_url'], '/helptopics.png" alt="', $txt['help'], '" />
						</a>
						<label for="display_custom">', $txt['sp_display_custom'], ': </label>
						<input class="input_text" type="text" name="display_custom" id="display_custom" value="', $context['SPortal']['block']['display_custom'], '" />
					</div>
					<div class="sp_button_container">
						<input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" class="right_submit" />
					</div>
				</div>
			</div>';
	}

	$style_sections = array('title' => 'left', 'body' => 'right');
	$style_types = array('default' => 'DefaultClass', 'class' => 'CustomClass', 'style' => 'CustomStyle');
	$style_parameters = array(
		'title' => array('category_header', 'secondary_header'),
		'body' => array('portalbg', 'portalbg2', 'information', 'roundframe'),
	);

	// Style options for the block, but not boardNews
	if ($context['SPortal']['block']['type'] != 'sp_boardNews')
	{
		echo '
			<br />
			<h3 class="category_header">
				<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=sp-blocksStyleOptions" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
				', $txt['sp-blocksStyleOptions'], '
			</h3>
			<div class="windowbg2">
				<div class="sp_content_padding">';

		foreach ($style_sections as $section => $float)
		{
			echo '
					<dl id="sp_edit_style_', $section, '" class="sp_form sp_float_', $float, '">';

			foreach ($style_types as $type => $label)
			{
				echo '
						<dt>
							', $txt['sp-blocks' . ucfirst($section) . $label], ':
						</dt>
						<dd>';

				if ($type == 'default')
				{
					echo '
							<select name="', $section, '_default_class" id="', $section, '_default_class">';

					foreach ($style_parameters[$section] as $class)
						echo '
								<option value="', $class, '"', $context['SPortal']['block']['style'][$section . '_default_class'] == $class ? ' selected="selected"' : '', '>', $class, '</option>';

					echo '
							</select>';
				}
				else
					echo '
							<input type="text" name="', $section, '_custom_', $type, '" id="', $section, '_custom_', $type, '" value="', $context['SPortal']['block']['style'][$section . '_custom_' . $type], '" class="input_text" />';

				echo '
						</dd>';
			}

			echo '
						<dt>
							', $txt['sp-blocksNo' . ucfirst($section)], ':
						</dt>
						<dd>
							<input type="checkbox" name="no_', $section, '" id="no_', $section, '" value="1"', !empty($context['SPortal']['block']['style']['no_' . $section]) ? ' checked="checked"' : '', 'onclick="check_style_options();" class="input_check" />
						</dd>
					</dl>';
		}

		echo '
					<script><!-- // --><![CDATA[
						check_style_options();
					// ]]></script>
					<div class="sp_button_container">
						<input type="submit" name="add_block" value="', !$context['SPortal']['is_new'] ? $txt['sp-blocksEdit'] : $txt['sp-blocksAdd'], '" class="right_submit" />
					</div>
				</div>
			</div>';
	}

	echo '
		</form>
	</div>';
}

/**
 * Used to select one of our predefined blocks for use in the portal
 */
function template_block_select_type()
{
	global $context, $scripturl, $txt;

	echo '
	<div id="sp_select_block_type">
		<h3 class="category_header">
			<a class="hdicon cat_img_helptopics help" href="', $scripturl, '?action=quickhelp;help=sp-blocksSelectType" onclick="return reqOverlayDiv(this.href);" title="', $txt['help'], '"></a>
			', $txt['sp-blocksSelectType'], '
		</h3>
		<form id="admin_form_wrapper" action="', $scripturl, '?action=admin;area=portalblocks;sa=add" method="post" accept-charset="UTF-8">
			<ul class="reset">';

	// For every block type defined in the system
	foreach($context['SPortal']['block_types'] as $index => $type)
	{
		$this_block = isset($context['SPortal']['block_inuse'][$type['function']]) ? $context['SPortal']['block_inuse'][$type['function']] : false;
		$this_title = !empty($this_block) ? sprintf($txt['sp-adminBlockInuse'], $context['location'][$this_block['column']]) . ': ' . (!empty($this_block['state']) ? '(' . $txt['sp-blocksActive'] . ')' : '') : '';

		echo '
						<li class="windowbg">
								<input type="radio" name="selected_type[]" id="block_', $type['function'], '" value="', $type['function'], '" class="input_radio" />
								<strong><label ', (!empty($this_block) ? 'class="sp_block_active" ' : ''), 'for="block_', $type['function'], '" title="', $this_title, '">', $txt['sp_function_' . $type['function'] . '_label'], '</label></strong>
								<p class="smalltext">', $txt['sp_function_' . $type['function'] . '_desc'], '</p>';

		echo '
						</li>
						';
	}

	echo '
<li>
					<input type="submit" name="select_type" value="', $txt['sp-blocksSelectType'], '" class="right_submit" />
			</li></ul>';

	if (!empty($context['SPortal']['block']['column']))
		echo '
			<input type="hidden" name="block_column" value="', $context['SPortal']['block']['column'], '" />';

	echo '
			<input type="hidden" name="', $context['session_var'], '" value="', $context['session_id'], '" />
		</form>
	</div>';
}