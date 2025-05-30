<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v13/services/audience_insights_service.proto

namespace Google\Ads\GoogleAds\V13\Services;

use Google\Protobuf\Internal\GPBType;
use Google\Protobuf\Internal\RepeatedField;
use Google\Protobuf\Internal\GPBUtil;

/**
 * Response message for [AudienceInsightsService.ListAudienceInsightsDates][].
 *
 * Generated from protobuf message <code>google.ads.googleads.v13.services.ListInsightsEligibleDatesResponse</code>
 */
class ListInsightsEligibleDatesResponse extends \Google\Protobuf\Internal\Message
{
    /**
     * The months for which AudienceInsights data is currently
     * available, each represented as a string in the form "YYYY-MM".
     *
     * Generated from protobuf field <code>repeated string data_months = 1;</code>
     */
    private $data_months;

    /**
     * Constructor.
     *
     * @param array $data {
     *     Optional. Data for populating the Message object.
     *
     *     @type array<string>|\Google\Protobuf\Internal\RepeatedField $data_months
     *           The months for which AudienceInsights data is currently
     *           available, each represented as a string in the form "YYYY-MM".
     * }
     */
    public function __construct($data = NULL) {
        \GPBMetadata\Google\Ads\GoogleAds\V13\Services\AudienceInsightsService::initOnce();
        parent::__construct($data);
    }

    /**
     * The months for which AudienceInsights data is currently
     * available, each represented as a string in the form "YYYY-MM".
     *
     * Generated from protobuf field <code>repeated string data_months = 1;</code>
     * @return \Google\Protobuf\Internal\RepeatedField
     */
    public function getDataMonths()
    {
        return $this->data_months;
    }

    /**
     * The months for which AudienceInsights data is currently
     * available, each represented as a string in the form "YYYY-MM".
     *
     * Generated from protobuf field <code>repeated string data_months = 1;</code>
     * @param array<string>|\Google\Protobuf\Internal\RepeatedField $var
     * @return $this
     */
    public function setDataMonths($var)
    {
        $arr = GPBUtil::checkRepeatedField($var, \Google\Protobuf\Internal\GPBType::STRING);
        $this->data_months = $arr;

        return $this;
    }

}

