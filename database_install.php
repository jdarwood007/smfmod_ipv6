<?php
error_reporting(E_ALL);

// Hopefully we have the goodies.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
{
	$using_ssi = true;
	require_once(dirname(__FILE__) . '/SSI.php');
}
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $db_prefix, $modSettings, $func, $smcFunc;

// Fields to add
$new_fields = array(
	'is_ipv6' => array('name'=> 'is_ipv6', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_low5' => array('name'=> 'ip_low5', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_high5' => array('name'=> 'ip_high5', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_low6' => array('name'=> 'ip_low6', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_high6' => array('name'=> 'ip_high6', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_low7' => array('name'=> 'ip_low7', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_high7' => array('name'=> 'ip_high7', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_low8' => array('name'=> 'ip_low8', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
	'ip_high8' => array('name'=> 'ip_high8', 'type'=>'SMALLINT(255)', 'unsigned' => true, 'default' => 0),
);

$changed_fields = array(
	'ip_low1' => array('name'=> 'ip_low1', 'type'=>'SMALLINT(255)'),
	'ip_high1' => array('name'=> 'ip_high1', 'type'=>'SMALLINT(255)'),
	'ip_low2' => array('name'=> 'ip_low2', 'type'=>'SMALLINT(255)'),
	'ip_high2' => array('name'=> 'ip_high2', 'type'=>'SMALLINT(255)'),
	'ip_low3' => array('name'=> 'ip_low3', 'type'=>'SMALLINT(255)'),
	'ip_high3' => array('name'=> 'ip_high3', 'type'=>'SMALLINT(255)'),
	'ip_low4' => array('name'=> 'ip_low4', 'type'=>'SMALLINT(255)'),
	'ip_high4' => array('name'=> 'ip_high4', 'type'=>'SMALLINT(255)'),
);

// Load up the board info, we will only add these once.
$table_columns = $smcFunc['db_list_columns']($db_prefix . 'ban_items');

// Do the loopy, loop, loe.
foreach ($new_fields as $column_name => $column_attributes)
	if (!in_array($column_name, $table_columns))
		$smcFunc['db_add_column']($db_prefix . 'ban_items', $column_attributes);

// Do the loopy, loop, loe.
foreach ($changed_fields as $column_name => $column_attributes)
	$smcFunc['db_change_column']($db_prefix . 'ban_items', $column_attributes);

// Find any IPv6 bans and reenable them again.
// !!! Note, We changed is_ipv6 to the time stamp of when it is supposed of expired, 1 if it was a perm ban.
$result = $smcFunc['db_query']('', '
	SELECT id_ban_group, is_ipv6
	FROM {db_prefix}ban_items
	WHERE is_ipv6 < {int:is_ipv6}',
	array(
		'is_ipv6' => '1'
));

$enabled_bans = array();
$disabled_bans = array();
$lost_bans = array();
while ($row = $smcFunc['db_fetch_assoc']($request))
{
	if ($row['is_ipv6'] == 1)
		$enabled_bans[] = $row['id_ban_group'];
	elseif ($row['is_ipv6'] > 1 && time() < $row['is_ipv6'])
		$enabled_bans[] = $row['id_ban_group'];
	elseif ($row['is_ipv6'] > 1 && time() > $row['is_ipv6'])
		$disabled_bans[] = $row['id_ban_group'];
	// Where did you come from?
	else
		$lost_bans[] = $row['id_ban_group'];

	// Not the best way, but will do the job.
	$smcFunc['db_query']('', '
		UPDATE {db_prefix}ban_groups
		SET expire_time = {raw:expire}
		WHERE id_ban_group = {int:ban_group}',
		array(
			'expire' => $row['is_ipv6'] == 1 ? 'NULL' : $row['is_ipv6'],
			'ban_group' => $row['id_ban_group'],
	));
}		

// Ok, We just re-enable these.
$smcFunc['db_query']('', '
	UPDATE {db_prefix}ban_items
	SET is_ipv6 = {int:enabled}
	WHERE is_ipv6 < {int:enabled}',
	array(
		'enabled' => '1',
));

// Handle our lost bans.
if (!empty($lost_bans) && !empty($using_ssi))
	echo 'We had some bans that we could not properly enable. Please check these ban ids:', implode(', ', $lost_bans), '<br />';

// For debugging/support purposes.
if (!empty($lost_bans))
log_error('Lost bans during IPV6 re-enabling:', implode(', ', $lost_bans), 'critical');

// Update our ban time, forcing rechecks to occur.
updateSettings(array('banLastUpdated' => time()));

if(!empty($using_ssi))
	echo 'If no errors, Success!';
