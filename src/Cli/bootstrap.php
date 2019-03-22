<?php

namespace JDecool\Whois\Cli;

use JDecool\Whois\WhoisClient;

function bootstrap(): Application
{
    return new Application(
        WhoisClient::create('whois.nic.fr'),
//        WhoisClient::create('whois.nic.me'),
    );
}
