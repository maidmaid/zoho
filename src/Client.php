<?php

namespace Maidmaid\Zoho;

use Maidmaid\Zoho\Serializer\Encoder\ZohoEncoder;
use Maidmaid\Zoho\Serializer\Normalizer\ZohoNormalizer;
use GuzzleHttp\Client as GuzzleClient;
use GuzzleHttp\ClientInterface;
use Symfony\Component\Serializer\Serializer;
use Symfony\Component\Serializer\SerializerInterface;

class Client
{
    protected $client;
    protected $authtoken;
    protected $uri;
    protected $serializer;
    protected $lastErrors;

    public function __construct($authtoken, ClientInterface $client = null, SerializerInterface $serializer = null, $uri = 'https://crm.zoho.com')
    {
        $this->authtoken = $authtoken;
        $this->serializer = $serializer ?: new Serializer(array(new ZohoNormalizer()), array(new ZohoEncoder()));
        $this->client = $client ?: new GuzzleClient();
        $this->uri = $uri;
    }

    public function call($module, $method, $params = array(), $data = array())
    {
        $this->lastErrors = array();

        // Set basic options
        $options = array(
            'base_uri' => $this->uri.'/crm/private/json/', //https://crm.zoho.com or https://crm.zoho.eu
            'query' => array_merge(array(
                'authtoken' => $this->authtoken,
                'scope' => 'crmapi',
            ), $params),
        );

        // Set form params options (xmlData)
        if ($data) {
            $options['form_params'] = array(
                'xmlData' => $this->serializer->serialize($data, 'zoho', array('module' => $module)),
            );
        }

        $response = $this->client->post($uri = sprintf('%s/%s', $module, $method), $options);
        $body = (string) $response->getBody();

        // TODO debug mode dumps all info (time, module, method, params, data, data_serialized, response, response_deserialize)

        return $body;
    }

    public function searchRecords($module, $criteria)
    {
        $response = $this->call($module, 'searchRecords', array('criteria' => $criteria, 'newFormat' => 2));

        return $this->serializer->deserialize($response, null, 'zoho', array('errors' => &$this->lastErrors, 'module' => $module));
    }

    public function insertRecords($module, $data)
    {
        $response = $this->call($module, 'insertRecords', array('newFormat' => 1, 'version' => 4), $data);

        return $this->serializer->deserialize($response, null, 'zoho', array('errors' => &$this->lastErrors));
    }

    public function updateRecords($module, $data)
    {
        $response = $this->call($module, 'updateRecords', array('newFormat' => 1, 'version' => 4), $data);

        return $this->serializer->deserialize($response, null, 'zoho', array('errors' => &$this->lastErrors));
    }

    public function getFields($module)
    {
        $response = $this->call($module, 'getFields');

        return $this->serializer->decode($response, 'zoho', array('module' => $module));
    }

    public function deleteRecords($module, $id)
    {
        $response = $this->call($module, 'deleteRecords', array('id' => $id));

        $this->serializer->deserialize($response, null, 'zoho', array('errors' => &$this->lastErrors));
    }

    public function getLastErrors()
    {
        return $this->lastErrors;
    }

    public function getRecordById($module, $ids)
    {
        $response = $this->call($module, 'getRecordById', array('newFormat' => 2, 'version' => 2, 'idlist' => implode(';', $ids)));

        return $this->serializer->deserialize($response, null, 'zoho', array('errors' => &$this->lastErrors, 'module' => $module));
    }

    public function getRecords($module, $page = 1)
    {
        $maxResults = 200;

        $response = $this->call($module, 'getRecords', array(
            'selectColumns' => 'All',
            'fromIndex' => $maxResults * ($page - 1) + 1,
            'toIndex' => $maxResults * $page,
            'version' => 2,
            'newFormat' => 2,
        ));

        return $this->serializer->deserialize($response, null, 'zoho', array('errors' => &$this->lastErrors, 'module' => $module));
    }
}
