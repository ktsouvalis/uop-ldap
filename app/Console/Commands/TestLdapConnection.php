<?php

namespace App\Console\Commands;

use LdapRecord\Container;
use LdapRecord\Laravel\Ldap;
use Illuminate\Console\Command;
use LdapRecord\Models\ActiveDirectory\User as LdapUser;

class TestLdapConnection extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Tests the connection to the LDAP server';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        try {
            $ldap = Container::getDefaultConnection();
            $ldap->connect();

            if ($ldap->isConnected()) {
                $this->info("Successfully connected to the LDAP server.");

                // Attempt to bind with the credentials.
                if ($ldap->auth()->attempt(env('LDAP_USERNAME'), env('LDAP_PASSWORD'))) {
                    $this->info("Successfully bound to the LDAP server with the provided credentials.");
                } else {
                    $this->error("Failed to bind to the LDAP server. Please check your credentials.");
                }
            } 
            else {
                $this->error("Failed to connect to the LDAP server.");
            }
        } catch (\Exception $e) {
            $this->error("Error: " . $e->getMessage());
        }

        return 0;
    }
}
