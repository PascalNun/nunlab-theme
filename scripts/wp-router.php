<?php
/**
 * Local router for the PHP built-in server.
 */

$document_root = __DIR__ . '/../.wp-local';
$request_path  = parse_url( $_SERVER['REQUEST_URI'] ?? '/', PHP_URL_PATH );
$request_path  = $request_path ? urldecode( $request_path ) : '/';
$requested_file = $document_root . $request_path;

if (
	'/' !== $request_path &&
	! str_contains( $request_path, '..' ) &&
	is_file( $requested_file )
) {
	return false;
}

require $document_root . '/index.php';
