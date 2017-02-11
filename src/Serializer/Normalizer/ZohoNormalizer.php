<?php

namespace Maidmaid\Zoho\Serializer\Normalizer;

use Maidmaid\Zoho\ZohoCRMException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

class ZohoNormalizer implements NormalizerInterface, DenormalizerInterface
{
    /**
     * {@inheritdoc}
     */
    public function normalize($object, $format = null, array $context = array())
    {
        $rows = array('row' => array());
        foreach ($object as $key => $module) {
            $row = array('@no' => $key, 'FL' => array());
            foreach ($module as $field => $value) {
                $row['FL'][] = $field == 'Product Details'
                    ? array('@val' => $field, 'product' => $this->normalizeDetails($value))
                    : array('@val' => $field, '#' => $this->normalizeValue($value));
            }
            $rows['row'][] = $row;
        }

        return $rows;
    }

    /**
     * {@inheritdoc}
     */
    public function supportsNormalization($data, $format = null)
    {
        return 'zoho' === $format;
    }

    private function normalizeDetails($details)
    {
        $products = array();
        foreach ($details as $key => $detail) {
            $product = array('@no' => $key, 'FL' => array());
            foreach ($detail as $field => $value) {
                $product['FL'][] = array('@val' => $field, '#' => $this->normalizeValue($value));
            }
            $products[] = $product;
        }

        return $products;
    }

    private function normalizeValue($value)
    {
        return $value instanceof \DateTime ? $value->format('Y-m-d') : $value;
    }

    /**
     * {@inheritdoc}
     */
    public function denormalize($data, $class, $format = null, array $context = array())
    {
        if (isset($data['response']['error'])) {
            throw new ZohoCRMException($data['response']['error']['message'], $data['response']['error']['code'], $data['response']['uri']);
        }

        if (isset($context['module'])) {
            return $this->denormalizeRowsWithModule($data, $context['module']);
        } else {
            return $this->denormalizeRows($data, $context['errors']);
        }
    }

    /**
     * {@inheritdoc}
     */
    public function supportsDenormalization($data, $type, $format = null)
    {
        return 'zoho' === $format;
    }

    private function denormalizeRows($records, &$errors)
    {
        $rows = isset($records['response']['result']['row']['no'])
            ? array($records['response']['result']['row']) // 1 data
            : $records['response']['result']['row']; // n data

        $_rows = array();
        foreach ($rows as $key => $row) {
            if (isset($row['success'])) {
                $_rows[$row['no']] = $this->denormalizeFL($row['success']['details']['FL']);
            } else {
                $errors[$row['no']] = $row['error'];
            }
        }

        return $_rows;
    }

    private function denormalizeRowsWithModule($records, $module)
    {
        $rows = isset($records['response']['nodata'])
            ? array() // no data
            : (isset($records['response']['result'][$module]['row']['no'])
                ? array($records['response']['result'][$module]['row']) // 1 data
                : $records['response']['result'][$module]['row']); // n data

        $_rows = array();
        foreach ($rows as $row) {
            $_rows[$row['no']] = $this->denormalizeFL($row['FL']);

            // Denormalize product details
            $details = array_column($row['FL'], 'product', 'val');
            if ($details) {
                $_rows[$row['no']]['Product Details'] = array();

                // Arrayize
                if (isset($details['Product Details']['no'])) {
                    $details['Product Details'] = array($details['Product Details']);
                }

                foreach ($details['Product Details'] as $detail) {
                    $_rows[$row['no']]['Product Details'][$detail['no']] = $this->denormalizeFL($detail['FL']);
                }
            }
        }

        return $_rows;
    }

    private function denormalizeFL($fl)
    {
        $fields = array_column($fl, 'content', 'val');

        // Convert null, false and true field
        $fields = array_map(function ($field) {
            return $field === 'null' ? null : $field;
        }, $fields);
        $fields = array_map(function ($field) {
            return $field === 'false' ? false : $field;
        }, $fields);
        $fields = array_map(function ($field) {
            return $field === 'true' ? true : $field;
        }, $fields);

        return $fields;
    }
}
