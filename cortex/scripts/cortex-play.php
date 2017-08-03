<?php

session_start();

if (isset($_GET['id'])) {
	$_SESSION['layer'] = $_GET['id'];
} elseif (isset($_SESSION['layer']) && ($_SESSION['layer'] < 100)) {
	$_SESSION['layer']++;
} else {
	$_SESSION['layer'] = 0;
}

$image = __DIR__ . '/dump-layer-' . str_pad($_SESSION['layer'], 4, '0', STR_PAD_LEFT) . '.png';

if (file_exists($image)) {
	header('Content-Type: image/png');
	header('Refresh: 0;url=?id=' . ($_SESSION['layer'] + 1));

	echo file_get_contents($image);
} else {
	header('Refresh: 0;url=?id=' . 0);
}