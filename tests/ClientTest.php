<?php

use Maidmaid\Zoho\Client;

class ClientTest extends PHPUnit_Framework_TestCase
{
    public function testConstructors()
    {
        $c = new Client('token');
    }
}
