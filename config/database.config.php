<?php
/**
 * database.config.php
 * The database configuration file for shared DSPs
 *
 * Retrieves database credentials from the authentication system and caches them
 */
global $_dbName;

const AUTH_ENDPOINT = 'http://cerberus.fabric.dreamfactory.com/api/instance/credentials';

//	This file must be present to hit this block...
if ( file_exists( '/var/www/.fabric_hosted' ) )
{
	require_once dirname( __DIR__ ) . '/web/protected/components/HttpMethod.php';
	require_once dirname( __DIR__ ) . '/web/protected/components/Curl.php';

	$_host = isset( $_SERVER, $_SERVER['HTTP_HOST'] ) ? $_SERVER['HTTP_HOST'] : gethostname();

	if ( false === strpos( $_host, '.cloud.dreamfactory.com' ) )
	{
		throw new \CHttpException( 401, 'You are not authorized to access this system.' );
	}

	if ( isset( $_SESSION['_db_credentials_cache'] ) )
	{
		$_dbCredentialsCache = $_SESSION['_db_credentials_cache'];
	}
	else
	{
		$_parts = explode( '.', $_host );
		$_dspName = $_parts[0];

		//	Get the credentials from the auth server...
		$_response = \Curl::get( AUTH_ENDPOINT . '/' . $_dspName . '/database' );

		if ( !$_response || !is_object( $_response ) || false == $_response->success )
		{
			throw new RuntimeException( 'Cannot connect to authentication service:' . print_r( $_response, true ) );
		}

		$_SESSION['_db_credentials_cache'] = $_dbCredentialsCache = $_response->details;
	}

	$_dbName = $_dbCredentialsCache->db_name;
	$_dbUser = $_dbCredentialsCache->db_user;
	$_dbPassword = $_dbCredentialsCache->db_password;
}
else
{
	$_dbName = 'dreamfactory';
	$_dbUser = 'dsp_user';
	$_dbPassword = 'dsp_user';
}

return array(
	'connectionString' => 'mysql:host=localhost;port=3306;dbname=' . $_dbName,
	'username'         => $_dbUser,
	'password'         => $_dbPassword,
	'emulatePrepare'   => true,
	'charset'          => 'utf8',
);