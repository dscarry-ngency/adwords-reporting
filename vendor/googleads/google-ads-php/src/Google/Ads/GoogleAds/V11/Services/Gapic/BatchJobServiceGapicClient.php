<?php
/*
 * Copyright 2022 Google LLC
 *
 * Licensed under the Apache License, Version 2.0 (the "License");
 * you may not use this file except in compliance with the License.
 * You may obtain a copy of the License at
 *
 *     https://www.apache.org/licenses/LICENSE-2.0
 *
 * Unless required by applicable law or agreed to in writing, software
 * distributed under the License is distributed on an "AS IS" BASIS,
 * WITHOUT WARRANTIES OR CONDITIONS OF ANY KIND, either express or implied.
 * See the License for the specific language governing permissions and
 * limitations under the License.
 */

/*
 * GENERATED CODE WARNING
 * Generated by gapic-generator-php from the file
 * https://github.com/googleapis/googleapis/blob/master/google/ads/googleads/v11/services/batch_job_service.proto
 * Updates to the above are reflected here through a refresh process.
 */

namespace Google\Ads\GoogleAds\V11\Services\Gapic;

use Google\Ads\GoogleAds\V11\Resources\BatchJob\BatchJobMetadata;
use Google\Ads\GoogleAds\V11\Services\AddBatchJobOperationsRequest;
use Google\Ads\GoogleAds\V11\Services\AddBatchJobOperationsResponse;

use Google\Ads\GoogleAds\V11\Services\BatchJobOperation;

use Google\Ads\GoogleAds\V11\Services\ListBatchJobResultsRequest;
use Google\Ads\GoogleAds\V11\Services\ListBatchJobResultsResponse;
use Google\Ads\GoogleAds\V11\Services\MutateBatchJobRequest;
use Google\Ads\GoogleAds\V11\Services\MutateBatchJobResponse;
use Google\Ads\GoogleAds\V11\Services\MutateOperation;
use Google\Ads\GoogleAds\V11\Services\RunBatchJobRequest;
use Google\ApiCore\ApiException;
use Google\ApiCore\CredentialsWrapper;
use Google\ApiCore\GapicClientTrait;
use Google\ApiCore\LongRunning\OperationsClient;
use Google\ApiCore\OperationResponse;
use Google\ApiCore\PathTemplate;
use Google\ApiCore\RequestParamsHeaderDescriptor;
use Google\ApiCore\RetrySettings;
use Google\ApiCore\Transport\TransportInterface;
use Google\ApiCore\ValidationException;

use Google\Auth\FetchAuthTokenInterface;
use Google\LongRunning\Operation;

/**
 * Service Description: Service to manage batch jobs.
 *
 * This class provides the ability to make remote calls to the backing service through method
 * calls that map to API methods. Sample code to get started:
 *
 * ```
 * $batchJobServiceClient = new BatchJobServiceClient();
 * try {
 *     $formattedResourceName = $batchJobServiceClient->batchJobName('[CUSTOMER_ID]', '[BATCH_JOB_ID]');
 *     $mutateOperations = [];
 *     $response = $batchJobServiceClient->addBatchJobOperations($formattedResourceName, $mutateOperations);
 * } finally {
 *     $batchJobServiceClient->close();
 * }
 * ```
 *
 * Many parameters require resource names to be formatted in a particular way. To
 * assist with these names, this class includes a format method for each type of
 * name, and additionally a parseName method to extract the individual identifiers
 * contained within formatted names that are returned by the API.
 */
class BatchJobServiceGapicClient
{
    use GapicClientTrait;

    /**
     * The name of the service.
     */
    const SERVICE_NAME = 'google.ads.googleads.v11.services.BatchJobService';

    /**
     * The default address of the service.
     */
    const SERVICE_ADDRESS = 'googleads.googleapis.com';

    /**
     * The default port of the service.
     */
    const DEFAULT_SERVICE_PORT = 443;

    /**
     * The name of the code generator, to be included in the agent header.
     */
    const CODEGEN_NAME = 'gapic';

    /**
     * The default scopes required by the service.
     */
    public static $serviceScopes = [
        'https://www.googleapis.com/auth/adwords',
    ];

    private static $batchJobNameTemplate;

    private static $pathTemplateMap;

    private $operationsClient;

    private static function getClientDefaults()
    {
        return [
            'serviceName' => self::SERVICE_NAME,
            'serviceAddress' => self::SERVICE_ADDRESS . ':' . self::DEFAULT_SERVICE_PORT,
            'clientConfig' => __DIR__ . '/../resources/batch_job_service_client_config.json',
            'descriptorsConfigPath' => __DIR__ . '/../resources/batch_job_service_descriptor_config.php',
            'gcpApiConfigPath' => __DIR__ . '/../resources/batch_job_service_grpc_config.json',
            'credentialsConfig' => [
                'defaultScopes' => self::$serviceScopes,
            ],
            'transportConfig' => [
                'rest' => [
                    'restClientConfigPath' => __DIR__ . '/../resources/batch_job_service_rest_client_config.php',
                ],
            ],
        ];
    }

    private static function getBatchJobNameTemplate()
    {
        if (self::$batchJobNameTemplate == null) {
            self::$batchJobNameTemplate = new PathTemplate('customers/{customer_id}/batchJobs/{batch_job_id}');
        }

        return self::$batchJobNameTemplate;
    }

    private static function getPathTemplateMap()
    {
        if (self::$pathTemplateMap == null) {
            self::$pathTemplateMap = [
                'batchJob' => self::getBatchJobNameTemplate(),
            ];
        }

        return self::$pathTemplateMap;
    }

    /**
     * Formats a string containing the fully-qualified path to represent a batch_job
     * resource.
     *
     * @param string $customerId
     * @param string $batchJobId
     *
     * @return string The formatted batch_job resource.
     */
    public static function batchJobName($customerId, $batchJobId)
    {
        return self::getBatchJobNameTemplate()->render([
            'customer_id' => $customerId,
            'batch_job_id' => $batchJobId,
        ]);
    }

    /**
     * Parses a formatted name string and returns an associative array of the components in the name.
     * The following name formats are supported:
     * Template: Pattern
     * - batchJob: customers/{customer_id}/batchJobs/{batch_job_id}
     *
     * The optional $template argument can be supplied to specify a particular pattern,
     * and must match one of the templates listed above. If no $template argument is
     * provided, or if the $template argument does not match one of the templates
     * listed, then parseName will check each of the supported templates, and return
     * the first match.
     *
     * @param string $formattedName The formatted name string
     * @param string $template      Optional name of template to match
     *
     * @return array An associative array from name component IDs to component values.
     *
     * @throws ValidationException If $formattedName could not be matched.
     */
    public static function parseName($formattedName, $template = null)
    {
        $templateMap = self::getPathTemplateMap();
        if ($template) {
            if (!isset($templateMap[$template])) {
                throw new ValidationException("Template name $template does not exist");
            }

            return $templateMap[$template]->match($formattedName);
        }

        foreach ($templateMap as $templateName => $pathTemplate) {
            try {
                return $pathTemplate->match($formattedName);
            } catch (ValidationException $ex) {
                // Swallow the exception to continue trying other path templates
            }
        }

        throw new ValidationException("Input did not match any known format. Input: $formattedName");
    }

    /**
     * Return an OperationsClient object with the same endpoint as $this.
     *
     * @return OperationsClient
     */
    public function getOperationsClient()
    {
        return $this->operationsClient;
    }

    /**
     * Resume an existing long running operation that was previously started by a long
     * running API method. If $methodName is not provided, or does not match a long
     * running API method, then the operation can still be resumed, but the
     * OperationResponse object will not deserialize the final response.
     *
     * @param string $operationName The name of the long running operation
     * @param string $methodName    The name of the method used to start the operation
     *
     * @return OperationResponse
     */
    public function resumeOperation($operationName, $methodName = null)
    {
        $options = isset($this->descriptors[$methodName]['longRunning']) ? $this->descriptors[$methodName]['longRunning'] : [];
        $operation = new OperationResponse($operationName, $this->getOperationsClient(), $options);
        $operation->reload();
        return $operation;
    }

    /**
     * Constructor.
     *
     * @param array $options {
     *     Optional. Options for configuring the service API wrapper.
     *
     *     @type string $serviceAddress
     *           The address of the API remote host. May optionally include the port, formatted
     *           as "<uri>:<port>". Default 'googleads.googleapis.com:443'.
     *     @type string|array|FetchAuthTokenInterface|CredentialsWrapper $credentials
     *           The credentials to be used by the client to authorize API calls. This option
     *           accepts either a path to a credentials file, or a decoded credentials file as a
     *           PHP array.
     *           *Advanced usage*: In addition, this option can also accept a pre-constructed
     *           {@see \Google\Auth\FetchAuthTokenInterface} object or
     *           {@see \Google\ApiCore\CredentialsWrapper} object. Note that when one of these
     *           objects are provided, any settings in $credentialsConfig will be ignored.
     *     @type array $credentialsConfig
     *           Options used to configure credentials, including auth token caching, for the
     *           client. For a full list of supporting configuration options, see
     *           {@see \Google\ApiCore\CredentialsWrapper::build()} .
     *     @type bool $disableRetries
     *           Determines whether or not retries defined by the client configuration should be
     *           disabled. Defaults to `false`.
     *     @type string|array $clientConfig
     *           Client method configuration, including retry settings. This option can be either
     *           a path to a JSON file, or a PHP array containing the decoded JSON data. By
     *           default this settings points to the default client config file, which is
     *           provided in the resources folder.
     *     @type string|TransportInterface $transport
     *           The transport used for executing network requests. May be either the string
     *           `rest` or `grpc`. Defaults to `grpc` if gRPC support is detected on the system.
     *           *Advanced usage*: Additionally, it is possible to pass in an already
     *           instantiated {@see \Google\ApiCore\Transport\TransportInterface} object. Note
     *           that when this object is provided, any settings in $transportConfig, and any
     *           $serviceAddress setting, will be ignored.
     *     @type array $transportConfig
     *           Configuration options that will be used to construct the transport. Options for
     *           each supported transport type should be passed in a key for that transport. For
     *           example:
     *           $transportConfig = [
     *               'grpc' => [...],
     *               'rest' => [...],
     *           ];
     *           See the {@see \Google\ApiCore\Transport\GrpcTransport::build()} and
     *           {@see \Google\ApiCore\Transport\RestTransport::build()} methods for the
     *           supported options.
     *     @type callable $clientCertSource
     *           A callable which returns the client cert as a string. This can be used to
     *           provide a certificate and private key to the transport layer for mTLS.
     * }
     *
     * @throws ValidationException
     */
    public function __construct(array $options = [])
    {
        $clientOptions = $this->buildClientOptions($options);
        $this->setClientOptions($clientOptions);
        $this->operationsClient = $this->createOperationsClient($clientOptions);
    }

    /**
     * Add operations to the batch job.
     *
     * List of thrown errors:
     * [AuthenticationError]()
     * [AuthorizationError]()
     * [BatchJobError]()
     * [HeaderError]()
     * [InternalError]()
     * [QuotaError]()
     * [RequestError]()
     * [ResourceCountLimitExceededError]()
     *
     * Sample code:
     * ```
     * $batchJobServiceClient = new BatchJobServiceClient();
     * try {
     *     $formattedResourceName = $batchJobServiceClient->batchJobName('[CUSTOMER_ID]', '[BATCH_JOB_ID]');
     *     $mutateOperations = [];
     *     $response = $batchJobServiceClient->addBatchJobOperations($formattedResourceName, $mutateOperations);
     * } finally {
     *     $batchJobServiceClient->close();
     * }
     * ```
     *
     * @param string            $resourceName     Required. The resource name of the batch job.
     * @param MutateOperation[] $mutateOperations Required. The list of mutates being added.
     *
     *                                            Operations can use negative integers as temp ids to signify dependencies
     *                                            between entities created in this batch job. For example, a customer with
     *                                            id = 1234 can create a campaign and an ad group in that same campaign by
     *                                            creating a campaign in the first operation with the resource name
     *                                            explicitly set to "customers/1234/campaigns/-1", and creating an ad group
     *                                            in the second operation with the campaign field also set to
     *                                            "customers/1234/campaigns/-1".
     * @param array             $optionalArgs     {
     *     Optional.
     *
     *     @type string $sequenceToken
     *           A token used to enforce sequencing.
     *
     *           The first AddBatchJobOperations request for a batch job should not set
     *           sequence_token. Subsequent requests must set sequence_token to the value of
     *           next_sequence_token received in the previous AddBatchJobOperations
     *           response.
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a {@see RetrySettings} object, or an
     *           associative array of retry settings parameters. See the documentation on
     *           {@see RetrySettings} for example usage.
     * }
     *
     * @return \Google\Ads\GoogleAds\V11\Services\AddBatchJobOperationsResponse
     *
     * @throws ApiException if the remote call fails
     */
    public function addBatchJobOperations($resourceName, $mutateOperations, array $optionalArgs = [])
    {
        $request = new AddBatchJobOperationsRequest();
        $requestParamHeaders = [];
        $request->setResourceName($resourceName);
        $request->setMutateOperations($mutateOperations);
        $requestParamHeaders['resource_name'] = $resourceName;
        if (isset($optionalArgs['sequenceToken'])) {
            $request->setSequenceToken($optionalArgs['sequenceToken']);
        }

        $requestParams = new RequestParamsHeaderDescriptor($requestParamHeaders);
        $optionalArgs['headers'] = isset($optionalArgs['headers']) ? array_merge($requestParams->getHeader(), $optionalArgs['headers']) : $requestParams->getHeader();
        return $this->startCall('AddBatchJobOperations', AddBatchJobOperationsResponse::class, $optionalArgs, $request)->wait();
    }

    /**
     * Returns the results of the batch job. The job must be done.
     * Supports standard list paging.
     *
     * List of thrown errors:
     * [AuthenticationError]()
     * [AuthorizationError]()
     * [BatchJobError]()
     * [HeaderError]()
     * [InternalError]()
     * [QuotaError]()
     * [RequestError]()
     *
     * Sample code:
     * ```
     * $batchJobServiceClient = new BatchJobServiceClient();
     * try {
     *     $formattedResourceName = $batchJobServiceClient->batchJobName('[CUSTOMER_ID]', '[BATCH_JOB_ID]');
     *     // Iterate over pages of elements
     *     $pagedResponse = $batchJobServiceClient->listBatchJobResults($formattedResourceName);
     *     foreach ($pagedResponse->iteratePages() as $page) {
     *         foreach ($page as $element) {
     *             // doSomethingWith($element);
     *         }
     *     }
     *     // Alternatively:
     *     // Iterate through all elements
     *     $pagedResponse = $batchJobServiceClient->listBatchJobResults($formattedResourceName);
     *     foreach ($pagedResponse->iterateAllElements() as $element) {
     *         // doSomethingWith($element);
     *     }
     * } finally {
     *     $batchJobServiceClient->close();
     * }
     * ```
     *
     * @param string $resourceName Required. The resource name of the batch job whose results are being listed.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type string $pageToken
     *           A page token is used to specify a page of values to be returned.
     *           If no page token is specified (the default), the first page
     *           of values will be returned. Any page token used here must have
     *           been generated by a previous call to the API.
     *     @type int $pageSize
     *           The maximum number of resources contained in the underlying API
     *           response. The API may return fewer values in a page, even if
     *           there are additional values to be retrieved.
     *     @type int $responseContentType
     *           The response content type setting. Determines whether the mutable resource
     *           or just the resource name should be returned.
     *           For allowed values, use constants defined on {@see \Google\Ads\GoogleAds\V11\Enums\ResponseContentTypeEnum\ResponseContentType}
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a {@see RetrySettings} object, or an
     *           associative array of retry settings parameters. See the documentation on
     *           {@see RetrySettings} for example usage.
     * }
     *
     * @return \Google\ApiCore\PagedListResponse
     *
     * @throws ApiException if the remote call fails
     */
    public function listBatchJobResults($resourceName, array $optionalArgs = [])
    {
        $request = new ListBatchJobResultsRequest();
        $requestParamHeaders = [];
        $request->setResourceName($resourceName);
        $requestParamHeaders['resource_name'] = $resourceName;
        if (isset($optionalArgs['pageToken'])) {
            $request->setPageToken($optionalArgs['pageToken']);
        }

        if (isset($optionalArgs['pageSize'])) {
            $request->setPageSize($optionalArgs['pageSize']);
        }

        if (isset($optionalArgs['responseContentType'])) {
            $request->setResponseContentType($optionalArgs['responseContentType']);
        }

        $requestParams = new RequestParamsHeaderDescriptor($requestParamHeaders);
        $optionalArgs['headers'] = isset($optionalArgs['headers']) ? array_merge($requestParams->getHeader(), $optionalArgs['headers']) : $requestParams->getHeader();
        return $this->getPagedListResponse('ListBatchJobResults', $optionalArgs, ListBatchJobResultsResponse::class, $request);
    }

    /**
     * Mutates a batch job.
     *
     * List of thrown errors:
     * [AuthenticationError]()
     * [AuthorizationError]()
     * [HeaderError]()
     * [InternalError]()
     * [QuotaError]()
     * [RequestError]()
     * [ResourceCountLimitExceededError]()
     *
     * Sample code:
     * ```
     * $batchJobServiceClient = new BatchJobServiceClient();
     * try {
     *     $customerId = 'customer_id';
     *     $operation = new BatchJobOperation();
     *     $response = $batchJobServiceClient->mutateBatchJob($customerId, $operation);
     * } finally {
     *     $batchJobServiceClient->close();
     * }
     * ```
     *
     * @param string            $customerId   Required. The ID of the customer for which to create a batch job.
     * @param BatchJobOperation $operation    Required. The operation to perform on an individual batch job.
     * @param array             $optionalArgs {
     *     Optional.
     *
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a {@see RetrySettings} object, or an
     *           associative array of retry settings parameters. See the documentation on
     *           {@see RetrySettings} for example usage.
     * }
     *
     * @return \Google\Ads\GoogleAds\V11\Services\MutateBatchJobResponse
     *
     * @throws ApiException if the remote call fails
     */
    public function mutateBatchJob($customerId, $operation, array $optionalArgs = [])
    {
        $request = new MutateBatchJobRequest();
        $requestParamHeaders = [];
        $request->setCustomerId($customerId);
        $request->setOperation($operation);
        $requestParamHeaders['customer_id'] = $customerId;
        $requestParams = new RequestParamsHeaderDescriptor($requestParamHeaders);
        $optionalArgs['headers'] = isset($optionalArgs['headers']) ? array_merge($requestParams->getHeader(), $optionalArgs['headers']) : $requestParams->getHeader();
        return $this->startCall('MutateBatchJob', MutateBatchJobResponse::class, $optionalArgs, $request)->wait();
    }

    /**
     * Runs the batch job.
     *
     * The Operation.metadata field type is BatchJobMetadata. When finished, the
     * long running operation will not contain errors or a response. Instead, use
     * ListBatchJobResults to get the results of the job.
     *
     * List of thrown errors:
     * [AuthenticationError]()
     * [AuthorizationError]()
     * [BatchJobError]()
     * [HeaderError]()
     * [InternalError]()
     * [QuotaError]()
     * [RequestError]()
     *
     * Sample code:
     * ```
     * $batchJobServiceClient = new BatchJobServiceClient();
     * try {
     *     $formattedResourceName = $batchJobServiceClient->batchJobName('[CUSTOMER_ID]', '[BATCH_JOB_ID]');
     *     $operationResponse = $batchJobServiceClient->runBatchJob($formattedResourceName);
     *     $operationResponse->pollUntilComplete();
     *     if ($operationResponse->operationSucceeded()) {
     *         // operation succeeded and returns no value
     *     } else {
     *         $error = $operationResponse->getError();
     *         // handleError($error)
     *     }
     *     // Alternatively:
     *     // start the operation, keep the operation name, and resume later
     *     $operationResponse = $batchJobServiceClient->runBatchJob($formattedResourceName);
     *     $operationName = $operationResponse->getName();
     *     // ... do other work
     *     $newOperationResponse = $batchJobServiceClient->resumeOperation($operationName, 'runBatchJob');
     *     while (!$newOperationResponse->isDone()) {
     *         // ... do other work
     *         $newOperationResponse->reload();
     *     }
     *     if ($newOperationResponse->operationSucceeded()) {
     *         // operation succeeded and returns no value
     *     } else {
     *         $error = $newOperationResponse->getError();
     *         // handleError($error)
     *     }
     * } finally {
     *     $batchJobServiceClient->close();
     * }
     * ```
     *
     * @param string $resourceName Required. The resource name of the BatchJob to run.
     * @param array  $optionalArgs {
     *     Optional.
     *
     *     @type RetrySettings|array $retrySettings
     *           Retry settings to use for this call. Can be a {@see RetrySettings} object, or an
     *           associative array of retry settings parameters. See the documentation on
     *           {@see RetrySettings} for example usage.
     * }
     *
     * @return \Google\ApiCore\OperationResponse
     *
     * @throws ApiException if the remote call fails
     */
    public function runBatchJob($resourceName, array $optionalArgs = [])
    {
        $request = new RunBatchJobRequest();
        $requestParamHeaders = [];
        $request->setResourceName($resourceName);
        $requestParamHeaders['resource_name'] = $resourceName;
        $requestParams = new RequestParamsHeaderDescriptor($requestParamHeaders);
        $optionalArgs['headers'] = isset($optionalArgs['headers']) ? array_merge($requestParams->getHeader(), $optionalArgs['headers']) : $requestParams->getHeader();
        return $this->startOperationsCall('RunBatchJob', $optionalArgs, $request, $this->getOperationsClient())->wait();
    }
}
