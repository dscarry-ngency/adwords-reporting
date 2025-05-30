<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v13/services/audience_insights_service.proto

namespace Google\Ads\GoogleAds\V13\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Request message for
 * [AudienceInsightsService.GenerateAudienceCompositionInsights][google.ads.googleads.v13.services.AudienceInsightsService.GenerateAudienceCompositionInsights].
 *
 * Generated from protobuf message <code>google.ads.googleads.v13.services.GenerateAudienceCompositionInsightsRequest</code>
 */
class GenerateAudienceCompositionInsightsRequest extends \Google\Protobuf\Internal\Message
{
    /**
     * Required. The ID of the customer.
     *
     * Generated from protobuf field <code>string customer_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    protected $customer_id = '';
    /**
     * Required. The audience of interest for which insights are being requested.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v13.services.InsightsAudience audience = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    protected $audience = null;
    /**
     * The one-month range of historical data to use for insights, in the format
     * "yyyy-mm". If unset, insights will be returned for the last thirty days of
     * data.
     *
     * Generated from protobuf field <code>string data_month = 3;</code>
     */
    protected $data_month = '';
    /**
     * Required. The audience dimensions for which composition insights should be
     * returned.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v13.enums.AudienceInsightsDimensionEnum.AudienceInsightsDimension dimensions = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     */
    private $dimensions;
    /**
     * The name of the customer being planned for.  This is a user-defined value.
     *
     * Generated from protobuf field <code>string customer_insights_group = 5;</code>
     */
    protected $customer_insights_group = '';

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type string $customer_id
     *           Required. The ID of the customer.
     *     @type \Google\Ads\GoogleAds\V13\Services\InsightsAudience $audience
     *           Required. The audience of interest for which insights are being requested.
     *     @type string $data_month
     *           The one-month range of historical data to use for insights, in the format
     *           "yyyy-mm". If unset, insights will be returned for the last thirty days of
     *           data.
     *     @type array<int>|\Google\Protobuf\Internal\RepeatedField $dimensions
     *           Required. The audience dimensions for which composition insights should be
     *           returned.
     *     @type string $customer_insights_group
     *           The name of the customer being planned for.  This is a user-defined value.
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V13\Services\AudienceInsightsService::initOnce();
        parent::__construct($data);
    }

    /**
     * Required. The ID of the customer.
     *
     * Generated from protobuf field <code>string customer_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return string
     */
    public function getCustomerId()
    {
        return $this->customer_id;
    }

    /**
     * Required. The ID of the customer.
     *
     * Generated from protobuf field <code>string customer_id = 1 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param string $var
     * @return $this
     */
    public function setCustomerId($var)
    {
        GPBUtil::checkString($var, True);
        $this->customer_id = $var;

        return $this;
    }

    /**
     * Required. The audience of interest for which insights are being requested.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v13.services.InsightsAudience audience = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Ads\GoogleAds\V13\Services\InsightsAudience|null
     */
    public function getAudience()
    {
        return $this->audience;
    }

    public function hasAudience()
    {
        return isset($this->audience);
    }

    public function clearAudience()
    {
        unset($this->audience);
    }

    /**
     * Required. The audience of interest for which insights are being requested.
     *
     * Generated from protobuf field <code>.google.ads.googleads.v13.services.InsightsAudience audience = 2 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param \Google\Ads\GoogleAds\V13\Services\InsightsAudience $var
     * @return $this
     */
    public function setAudience($var)
    {
        GPBUtil::checkMessage($var, \Google\Ads\GoogleAds\V13\Services\InsightsAudience::class);
        $this->audience = $var;

        return $this;
    }

    /**
     * The one-month range of historical data to use for insights, in the format
     * "yyyy-mm". If unset, insights will be returned for the last thirty days of
     * data.
     *
     * Generated from protobuf field <code>string data_month = 3;</code>
     * @return string
     */
    public function getDataMonth()
    {
        return $this->data_month;
    }

    /**
     * The one-month range of historical data to use for insights, in the format
     * "yyyy-mm". If unset, insights will be returned for the last thirty days of
     * data.
     *
     * Generated from protobuf field <code>string data_month = 3;</code>
     * @param string $var
     * @return $this
     */
    public function setDataMonth($var)
    {
        GPBUtil::checkString($var, True);
        $this->data_month = $var;

        return $this;
    }

    /**
     * Required. The audience dimensions for which composition insights should be
     * returned.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v13.enums.AudienceInsightsDimensionEnum.AudienceInsightsDimension dimensions = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDimensions()
    {
        return $this->dimensions;
    }

    /**
     * Required. The audience dimensions for which composition insights should be
     * returned.
     *
     * Generated from protobuf field <code>repeated .google.ads.googleads.v13.enums.AudienceInsightsDimensionEnum.AudienceInsightsDimension dimensions = 4 [(.google.api.field_behavior) = REQUIRED];</code>
     * @param array<int>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDimensions($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::ENUM, \Google\Ads\GoogleAds\V13\Enums\AudienceInsightsDimensionEnum\AudienceInsightsDimension::class);
        $this->dimensions = $arr;

        return $this;
    }

    /**
     * The name of the customer being planned for.  This is a user-defined value.
     *
     * Generated from protobuf field <code>string customer_insights_group = 5;</code>
     * @return string
     */
    public function getCustomerInsightsGroup()
    {
        return $this->customer_insights_group;
    }

    /**
     * The name of the customer being planned for.  This is a user-defined value.
     *
     * Generated from protobuf field <code>string customer_insights_group = 5;</code>
     * @param string $var
     * @return $this
     */
    public function setCustomerInsightsGroup($var)
    {
        GPBUtil::checkString($var, True);
        $this->customer_insights_group = $var;

        return $this;
    }

}

