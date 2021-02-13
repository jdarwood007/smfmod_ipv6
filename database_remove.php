<?php
error_reporting(E_ALL);

// Hopefully we have the goodies.
if (file_exists(dirname(__FILE__) . '/SSI.php') && !defined('SMF'))
	require_once(dirname(__FILE__) . '/SSI.php');
elseif (!defined('SMF'))
	exit('<b>Error:</b> Cannot install - please verify you put this in the same place as SMF\'s index.php.');

global $smcFunc;

// SSI may not present the database extension, while being ran via package manager does.
db_extend('packages');

// Find any IPv6 bans.
$request = $smcFunc['db_query']('', '
	SELECT bi.id_ban_group, bi.is_ipv6, bg.expire_time
	FROM {db_prefix}ban_items AS bi
		INNER JOIN {db_prefix}ban_groups AS bg ON (bi.id_ban_group = bg.id_ban_group)
	WHERE bi.is_ipv6 = {int:is_ipv6}',
	array(
		'is_ipv6' => '1'
));

$ipv6_bans = array();
while ($row = $smcFunc['db_fetch_assoc']($request))
{
	if ($row['expire_time'] != 'NULL')
		$smcFunc['db_query']('', '
			UPDATE {db_prefix}ban_items
			SET is_ipv6 = {raw:expire_time}
			WHERE id_ban_group = {int:ban_group}',
			array(
				'expire_time' => $row['expire_time'],
				'ban_group' => $row['id_ban_group'],
		));

	$ipv6_bans[] = $row['id_ban_group'];
}

// Do a mass update to disable these bans.
$smcFunc['db_query']('', '
	UPDATE {db_prefix}ban_groups
	SET expire_time = {int:expired_time}
	WHERE id_ban_group IN ({array_int:bans})',
	array(
		'expired_time' => time() - 60, // 1 minute ago should do.
		'bans' => $ipv6_bans
));

// Update our ban time, forcing rechecks to occur.
updateSettings(array('banLastUpdated' => time()));

if (SMF === 'SSI')
	echo 'If no errors, Success!';