<?php

// use Exception;
use LdapRecord\Container;
use Illuminate\Http\Request;
use LdapRecord\Auth\BindException;
use LdapRecord\Models\OpenLDAP\User;
use Illuminate\Support\Facades\Route;
// use LdapRecord\Models\ActiveDirectory\Container;

Route::get('/user', function (Request $request) {
    return $request->user();
})->middleware('auth:sanctum');

Route::post('/login', function (Request $request) { // using LdapRecord-Laravel package
    // User credentials to authenticate
    $username = $request->input('username');
    $password = $request->input('password');

    // Connect to the LDAP server
    $ldap = Container::getDefaultConnection();
    $ldap->connect();
    
    try {
        // Search for the user
        $user = User::where('uid', '=', $username)->first();

        if (!$user) {
            return response()->json([
                'success' => false,
                'message' => 'User not found in LDAP'
            ]);
        }
   
        // Attempt to bind with the user's credentials
        $isAuthenticated = $ldap->auth()->attempt($user, $password);

        return $isAuthenticated ? 'User authenticated successfully ' : 'Invalid credentials';
        } catch (BindException $e) {
            return response()->json([
                'success' => false,
                'message' => 'Authentication failed: ' . $e->getMessage()
            ]);
        } catch (\Exception $e) {
            return response()->json([
                'success' => false,
                'message' => 'Error: ' . $e->getMessage()
            ]);
        }
});

Route::post('/alternative-login', function(Request $request){ // using PHP built-in LDAP methods
    // User credentials to authenticate
    $username = $request->input('username');
    $password = $request->input('password');

    // LDAP server details
    $ldapHost = env('LDAP_HOST'); 
    $ldapPort = env('LDAP_PORT'); // Default port for LDAP is 389
    $baseDn = env('LDAP_BASE_DN');

    // Service account credentials (for initial connection)
    $serviceUserDn = env('LDAP_USERNAME');
    $servicePassword = env('LDAP_PASSWORD');

    // Step 1: Connect to LDAP server
    $ldapConnection = ldap_connect("ldaps://{$ldapHost}", $ldapPort);
    if (!$ldapConnection) {
        return 'Failed to connect to LDAP server';
    }

    // Set LDAP options
    ldap_set_option($ldapConnection, LDAP_OPT_PROTOCOL_VERSION, 3);
    ldap_set_option($ldapConnection, LDAP_OPT_REFERRALS, 0);

    // Step 2: Bind with the service account
    $serviceBind = ldap_bind($ldapConnection, $serviceUserDn, $servicePassword);
    if (!$serviceBind) {
        return 'Failed to bind with service account';
    }

    // Step 3: Search for the user DN
    $searchFilter = "(uid={$username})"; // Adjust if using `uid` instead of `cn`
    $search = ldap_search($ldapConnection, $baseDn, $searchFilter);
    $entries = ldap_get_entries($ldapConnection, $search);

    if ($entries['count'] == 0) {
        return 'User not found in LDAP';
    }

    // Step 4: Get the user’s DN and attempt to bind with their credentials
    $userDn = $entries[0]['dn'];
    $userBind = @ldap_bind($ldapConnection, $userDn, $password);

    ldap_close($ldapConnection);

    return $userBind ? 'User authenticated successfully' : 'Invalid credentials';
});