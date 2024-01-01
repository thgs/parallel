<?php declare(strict_types=1);

// The function returned by this script is run by context.php in a separate process or thread.
// $argc and $argv are available in this process as any other cli PHP script.

use Amp\Sync\Channel;

class Module
{
    public function getHandler()
    {
        return function (Channel $channel): int {
            do  {
                $received = $channel->receive();

                print "MODULE: Received!\n";
                print_r($received);

                print "MODULE: Processing...\n";

                // calls the method and computes the below result
                $result = 'Hello ' . $received[2];

                // sends it back
                $channel->send($result);
                $t[] = hrtime(true);
            } while ($received !== '__exit');

            // exit status
            print_r($t);        // avg diff : 282 microseconds / 0.282 ms / 282_232.6 nanoseconds
                             // 1 assignment: 23 microseconds / 0.02 ms / 22_659 nano
                                // single method call: 24_113 nano
            return 1;
        };
    }
}

$module = new Module();
return $module->getHandler();
