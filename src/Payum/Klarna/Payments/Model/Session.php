<?php
/**
 * Copyright 2014 Klarna AB
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 * http://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 *
 * File containing the Order class.
 */

namespace Payum\Klarna\Payments\Model;

use GuzzleHttp\Exception\RequestException;
use Klarna\Rest\Resource;
use Klarna\Rest\Transport\Connector;
use Klarna\Rest\Transport\Exception\ConnectorException;

/**
 * Payments session resource.
 *
 * @example https://developers.klarna.com/api/#payments-api-create-a-new-credit-session Create the credit session
 */
class Session extends Resource
{
    /**
     * {@inheritDoc}
     */
    const ID_FIELD = 'session_id';

    /**
     * {@inheritDoc}
     */
    public static $path = '/payments/v1/sessions';

    /**
     * Constructs an session instance.
     *
     * @param Connector $connector          HTTP transport connector
     * @param string    $authorizationToken Session ID
     */
    public function __construct(Connector $connector, $authorizationToken = null)
    {
        parent::__construct($connector);

        if ($authorizationToken !== null) {
            $this->setLocation(self::$path . "/{$authorizationToken}");
            $this[static::ID_FIELD] = $authorizationToken;
        }
    }

    /**
     * Creates the resource.
     *
     * @param array $data Creation data
     *
     * @throws ConnectorException When the API replies with an error response
     * @throws RequestException   When an error is encountered
     * @throws \RuntimeException  If the location header is missing
     * @throws \RuntimeException  If the API replies with an unexpected response
     * @throws \LogicException    When Guzzle cannot populate the response
     *
     * @return self
     */
    public function create(array $data)
    {
        $url = $this->post(self::$path, $data)
            ->status('201')
            ->getLocation();

        $this->setLocation($url);

        return $this;
    }

    /**
     * Updates the resource.
     *
     * @param array $data Update data
     *
     * @throws ConnectorException        When the API replies with an error response
     * @throws RequestException          When an error is encountered
     * @throws \RuntimeException         On an unexpected API response
     * @throws \RuntimeException         If the response content type is not JSON
     * @throws \InvalidArgumentException If the JSON cannot be parsed
     * @throws \LogicException           When Guzzle cannot populate the response
     *
     * @return self
     */
    public function update(array $data)
    {
        $data = $this->post($this->getLocation(), $data)
            ->status('200')
            ->contentType('application/json')
            ->getJson();

        $this->exchangeArray($data);

        return $this;
    }
}
