<?php

// Copyright 2019 The Bitum developers.
// - Mounir R'Quiba
// Licensed under the GNU Affero General Public License, version 3.

namespace Bitum\ES;

use Bitum\Transaction;
use Bitum\LoggerService;

use Bitum\BlockchainConfig;

class ESDomainService extends ESService
{
	protected $type = 'domain';

	protected $index = 'blockchain-domain';

	private $logger;

	public function __construct($prefix = '') {
		$this->logger = (LoggerService::getInstance())->getLogger();
		$this->index = $prefix ? $prefix . '_' . $this->index : $this->index;

		$this->transactionService = ESService::getInstance('transaction', $prefix);

		parent::__construct();
	}

	public function getByAddress($addressList = BlockchainConfig::NAME, $size = 100, $page = 0)
	{
		$from = 0;
		if ($page > 1) {
			$from = ((int) $page * $size - 1) - $size;
		}

		if (is_string($addressList)) {
			$addressList = [$addressList];
		}

		$addresses = implode(' OR ', $addressList);
		try {
			$result = $this->search([
				'ownerAddress' => $addresses
			]);
 		} catch (\Exception $e) {
			$result['error'] = $e->getMessage();
			// var_dump('ERROR --> ' . $response['error']);
		}

		if (isset($result['error']) || !isset($result['hits']['hits'][0])) {
			return [];
		}

		$output['domainList'] = [];
		foreach ($result['hits']['hits'] as $hit) {
			$output['domainList'][] = $hit['_source'];
		}

		$output['total'] = $result['hits']['total']['value'];

		return $output;
	}

	public function getByUrl($url)
	{
		$response = $this->get($url);

		if (isset($response['error'])) {
            return [];
        }

        $domain = $response['_source'];
        $domain['transactions'] = $this->transactionService->getByDomainUrl($url);
        return $domain;
	}


	public function getLastDomains() {
		try {
			$response = $this->client->search([
			    'index' => $this->index,
			    'type' => $this->type,
			    "from" => 0,
			    "size" => 100,
			    'body' => [
			        'query' => [
			            'match_all' => [
			            	"boost" => 1.2
			            ]
			        ],
			        "sort" => [
				    	"blockHeight" => "desc"
				  	],
			    ]
			]);
 		} catch (\Exception $e) {
 			$response['error'] = $e->getMessage();
			// var_dump('ERROR --> ' . $response['error']);
		}

		if (isset($response['error']) || !isset($response['hits']['hits'][0])) {
			return [];
		}

		$output['domainList'] = [];
		foreach ($response['hits']['hits'] as $hit) {
			$output['domainList'][] = $hit['_source'];
		}

		$output['total'] = $response['hits']['total']['value'];

		return $output;
	}

	public function getMapping() {
		return [
		    'domain' => [
		    	'properties' => [
		    		'hash' => [
			          'type' => 'text',
			        ],
			        'url' => [
			          'type' => 'text',
			        ],
			        'ownerAddress' => [
			          'type' => 'text',
			        ],
			        'ownerPublicKey' => [
			          'type' => 'text',
			        ],
			        'signature' => [
			          'type' => 'text',
			        ],
			        'blockHeight' => [
			          'type' => 'long'
			        ],
			        'blockHeightEnd' => [
			          'type' => 'long'
			        ],
			        'transsactionHash' => [
			          'type' => 'text'
			        ],
		    	],
		  	],
		];
	}
}
