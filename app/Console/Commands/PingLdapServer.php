<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class PingLdapServer extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'ldap:ping';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Ping the LDAP server to check connectivity';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $host = env('LDAP_HOST');
        $output = [];
        $resultCode = 0;

        exec("ping -n 4 " . escapeshellarg($host), $output, $resultCode);

        if ($resultCode === 0) {
            $this->info("Ping successful:");
            foreach ($output as $line) {
                $this->line($line);
            }
        } else {
            $this->error("Ping failed. Please check the host and try again.");
        }

        return $resultCode;
    }
}
