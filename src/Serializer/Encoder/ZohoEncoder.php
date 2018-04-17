<?php

namespace Maidmaid\Zoho\Serializer\Encoder;

use Symfony\Component\Serializer\Encoder\DecoderInterface;
use Symfony\Component\Serializer\Encoder\EncoderInterface;
use Symfony\Component\Serializer\Encoder\JsonDecode;
use Symfony\Component\Serializer\Encoder\XmlEncoder;

class ZohoEncoder implements DecoderInterface, EncoderInterface
{
    private $xmlEncoder;
    private $jsonDecode;

    /**
     * {@inheritdoc}
     */
    public function __construct()
    {
        $this->jsonDecode = new JsonDecode($associative = true);
        $this->xmlEncoder = new XmlEncoder();
    }

    /**
     * {@inheritdoc}
     */
    public function encode($data, $format, array $context = array())
    {
        $encoded = $this->xmlEncoder->encode($data, $format, array('xml_root_node_name' => $context['module']));
        $encoded = preg_replace('/<\?xml.*\?>\n+/', '', $encoded);

        return $encoded;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsEncoding($format)
    {
        return 'zoho' === $format;
    }

    /**
     * {@inheritdoc}
     */
    public function decode($data, $format, array $context = array())
    {
        return $this->jsonDecode->decode($data, $format, $context);
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDecoding($format)
    {
        return 'zoho' === $format;
    }
}
