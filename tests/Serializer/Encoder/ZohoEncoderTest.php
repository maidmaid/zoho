<?php

namespace Maidmaid\Zoho\Serializer\Encoder;

class ZohoEncoderTest extends \PHPUnit_Framework_TestCase
{
    /** @var ZohoEncoder */
    private $encoder;

    protected function setUp()
    {
        $this->encoder = new ZohoEncoder();
    }

    public function testEncode()
    {
        $data = array('row' => array(array('@no' => '1', 'FL' => array(array('@val' => 'Id', '#' => 1)))));
        $actual = $this->encoder->encode($data, 'zoho', array('module' => 'Contacts'));
        $this->assertXmlStringEqualsXmlString('<Contacts><row no="1"><FL val="Id">1</FL></row></Contacts>', $actual);
    }

    public function testSupportsEncoding()
    {
        $this->assertTrue($this->encoder->supportsEncoding('zoho'));
        $this->assertFalse($this->encoder->supportsEncoding('foobar'));
    }

    public function testDecode()
    {
        $data = '{"response":{"result":true}}';
        $actual = $this->encoder->decode($data, 'zoho');
        $this->assertEquals(array('response' => array('result' => true)), $actual);
    }

    public function testSupportsDecoding()
    {
        $this->assertTrue($this->encoder->supportsDecoding('zoho'));
        $this->assertFalse($this->encoder->supportsDecoding('foobar'));
    }
}
