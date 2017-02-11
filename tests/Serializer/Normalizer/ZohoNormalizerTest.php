<?php

namespace Maidmaid\Zoho\Serializer\Normalizer;

class ZohoNormalizerTest extends \PHPUnit_Framework_TestCase
{
    /** @var ZohoNormalizer */
    private $normalizer;

    protected function setUp()
    {
        $this->normalizer = new ZohoNormalizer();
    }

    public function getDataForNormalization()
    {
        return array(
            array( // 1 module with 1 field
                array(1 => array('Id' => 1)),
                array('row' => array(array('@no' => 1, 'FL' => array(array('@val' => 'Id', '#' => 1))))),
            ),
            array( // 1 module with 2 fields
                array(1 => array('Id' => 1, 'Email' => 'a@b.com')),
                array('row' => array(array('@no' => 1, 'FL' => array(array('@val' => 'Id', '#' => 1), array('@val' => 'Email', '#' => 'a@b.com'))))),
            ),
            array( // 2 modules with 1 field
                array(1 => array('Id' => 1), 9 => array('Id' => 9)),
                array('row' => array(array('@no' => 1, 'FL' => array(array('@val' => 'Id', '#' => 1))), array('@no' => 9, 'FL' => array(array('@val' => 'Id', '#' => 9))))),
            ),
            array( // 1 module with product details
                array(1 => array('Id' => 1, 'Product Details' => array(1 => array('Product Id' => 9)))),
                array('row' => array(array('@no' => 1, 'FL' => array(array('@val' => 'Id', '#' => 1), array('@val' => 'Product Details', 'product' => array(array('@no' => 1, 'FL' => array(array('@val' => 'Product Id', '#' => 9))))))))),
            ),
        );
    }

    /**
     * @dataProvider getDataForNormalization
     */
    public function testNormalize($object, $expected)
    {
        $normalized = $this->normalizer->normalize($object, 'zoho', array('module' => 'Contacts'));
        $this->assertEquals($expected, $normalized);
    }

    public function testSupportsNormalization()
    {
        $this->assertTrue($this->normalizer->supportsNormalization(null, 'zoho'));
        $this->assertFalse($this->normalizer->supportsNormalization(null, 'foobar'));
    }

    public function getDataForDenormalization()
    {
        $fl1 = array(array('content' => 1, 'val' => 'Id'));
        $fl9 = array(array('content' => 9, 'val' => 'Id'));
        $success1 = array('code' => 2001, 'details' => array('FL' => $fl1));
        $success9 = array('code' => 2001, 'details' => array('FL' => $fl9));

        return array(
            array( // 1 success
                array('response' => array('result' => array('row' => array('no' => 1, 'success' => $success1)))),
                array(1 => array('Id' => 1)),
                $withModule = false,
            ),
            array( // 2 successes
                array('response' => array('result' => array('row' => array(array('no' => 1, 'success' => $success1), array('no' => 9, 'success' => $success9))))),
                array(1 => array('Id' => 1), 9 => array('Id' => 9)),
                $withModule = false,
            ),
            array( // 1 module
                array('response' => array('result' => array('Contacts' => array('row' => array('no' => 1, 'FL' => $fl1))))),
                array(1 => array('Id' => 1)),
                $withModule = true,
            ),
            array( // 2 modules
                array('response' => array('result' => array('Contacts' => array('row' => array(array('no' => 1, 'FL' => $fl1), array('no' => 9, 'FL' => $fl9)))))),
                array(1 => array('Id' => 1), 9 => array('Id' => 9)),
                $withModule = true,
            ),
        );
    }

    /**
     * @dataProvider getDataForDenormalization
     */
    public function testDenormalize($data, $expected, $withModule)
    {
        $context = $withModule ? array('module' => 'Contacts') : array();
        $denormalized = $this->normalizer->denormalize($data, '', 'zoho', $context);
        $this->assertEquals($expected, $denormalized);
    }

    /**
     * @expectedException Maidmaid\Zoho\ZohoCRMException
     * @expectedExceptionMessage message error
     * @expectedExceptionCode 666
     */
    public function testDenormalizeWithErrorsInResponse()
    {
        $data = array('response' => array('error' => array('message' => 'message error', 'code' => 666), 'uri' => 'http://test.test'));
        $this->normalizer->denormalize($data, '', 'zoho');
    }

    public function getDataForDenormalizationWithErrorsInRow()
    {
        $error666 = array('details' => 'error 666', 'code' => 666);
        $error999 = array('details' => 'error 999', 'code' => 999);

        return array(
            array( // 1 error
                array('response' => array('result' => array('row' => array('no' => 1, 'error' => $error666)))),
                array(1 => array('details' => 'error 666', 'code' => 666)),
            ),
            array( // 2 errors
                array('response' => array('result' => array('row' => array(array('no' => 1, 'error' => $error666), array('no' => 9, 'error' => $error999))))),
                array(1 => array('details' => 'error 666', 'code' => 666), 9 => array('details' => 'error 999', 'code' => 999)),
            ),
        );
    }

    /**
     * @dataProvider getDataForDenormalizationWithErrorsInRow
     */
    public function testDenormalizeWithErrorsInRow($data, $expected)
    {
        $errors = array();
        $denormalized = $this->normalizer->denormalize($data, '', 'zoho', array('errors' => &$errors));
        $this->assertEmpty($denormalized);
        $this->assertEquals($expected, $errors);
    }

    public function testSupportsDenormalization()
    {
        $this->assertTrue($this->normalizer->supportsDenormalization(null, null, 'zoho'));
        $this->assertFalse($this->normalizer->supportsDenormalization(null, null, 'foobar'));
    }
}
