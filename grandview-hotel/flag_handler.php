<?php
require_once 'config.php';

if (!isset($_SESSION['submitted_flags'])) {
	$_SESSION['submitted_flags'] = [];
}

$knownFlags = [
	'flag{weak_auth_bypass}' => 'SQLi auth bypass',
	'flag{sqli_union_success}' => 'SQLi UNION',
	'flag{user_enum_success}' => 'User enumeration via SQLi',
	'flag{config_exposure_found}' => 'Config disclosure',
	'flag{source_code_analysis}' => 'HTML comment discovery',
	'flag{xss_feedback_stored}' => 'Stored XSS',
	'flag{idor_booking_access}' => 'IDOR booking access'
];

$response = [
	'status' => 'error',
	'message' => 'Invalid request'
];

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
	$flag = trim($_POST['flag'] ?? '');
	if ($flag === '') {
		$response = ['status' => 'error', 'message' => 'Enter a flag.'];
	} else if (isset($knownFlags[$flag])) {
		if (!in_array($flag, $_SESSION['submitted_flags'], true)) {
			$_SESSION['submitted_flags'][] = $flag;
		}
		$_SESSION['last_flag_status'] = ['ok' => true, 'flag' => $flag, 'desc' => $knownFlags[$flag]];
		$response = ['status' => 'ok', 'message' => 'Flag accepted!'];
	} else {
		$_SESSION['last_flag_status'] = ['ok' => false, 'flag' => $flag];
		$response = ['status' => 'error', 'message' => 'Incorrect flag.'];
	}
}

// If called via AJAX, return JSON; otherwise redirect back
if (!empty($_SERVER['HTTP_X_REQUESTED_WITH'])) {
	header('Content-Type: application/json');
	echo json_encode($response);
	exit;
}

$redirect = $_SERVER['HTTP_REFERER'] ?? 'index.php';
header('Location: ' . $redirect);
exit;







