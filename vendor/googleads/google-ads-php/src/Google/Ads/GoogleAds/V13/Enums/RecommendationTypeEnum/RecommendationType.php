<?php
# Generated by the protocol buffer compiler.  DO NOT EDIT!
# source: google/ads/googleads/v13/enums/recommendation_type.proto

namespace Google\Ads\GoogleAds\V13\Enums\RecommendationTypeEnum;

use UnexpectedValueException;

/**
 * Types of recommendations.
 *
 * Protobuf type <code>google.ads.googleads.v13.enums.RecommendationTypeEnum.RecommendationType</code>
 */
class RecommendationType
{
    /**
     * Not specified.
     *
     * Generated from protobuf enum <code>UNSPECIFIED = 0;</code>
     */
    const UNSPECIFIED = 0;
    /**
     * Used for return value only. Represents value unknown in this version.
     *
     * Generated from protobuf enum <code>UNKNOWN = 1;</code>
     */
    const UNKNOWN = 1;
    /**
     * Budget recommendation for campaigns that are currently budget-constrained
     * (as opposed to the FORECASTING_CAMPAIGN_BUDGET recommendation, which
     * applies to campaigns that are expected to become budget-constrained in
     * the future).
     *
     * Generated from protobuf enum <code>CAMPAIGN_BUDGET = 2;</code>
     */
    const CAMPAIGN_BUDGET = 2;
    /**
     * Keyword recommendation.
     *
     * Generated from protobuf enum <code>KEYWORD = 3;</code>
     */
    const KEYWORD = 3;
    /**
     * Recommendation to add a new text ad.
     *
     * Generated from protobuf enum <code>TEXT_AD = 4;</code>
     */
    const TEXT_AD = 4;
    /**
     * Recommendation to update a campaign to use a Target CPA bidding strategy.
     *
     * Generated from protobuf enum <code>TARGET_CPA_OPT_IN = 5;</code>
     */
    const TARGET_CPA_OPT_IN = 5;
    /**
     * Recommendation to update a campaign to use the Maximize Conversions
     * bidding strategy.
     *
     * Generated from protobuf enum <code>MAXIMIZE_CONVERSIONS_OPT_IN = 6;</code>
     */
    const MAXIMIZE_CONVERSIONS_OPT_IN = 6;
    /**
     * Recommendation to enable Enhanced Cost Per Click for a campaign.
     *
     * Generated from protobuf enum <code>ENHANCED_CPC_OPT_IN = 7;</code>
     */
    const ENHANCED_CPC_OPT_IN = 7;
    /**
     * Recommendation to start showing your campaign's ads on Google Search
     * Partners Websites.
     *
     * Generated from protobuf enum <code>SEARCH_PARTNERS_OPT_IN = 8;</code>
     */
    const SEARCH_PARTNERS_OPT_IN = 8;
    /**
     * Recommendation to update a campaign to use a Maximize Clicks bidding
     * strategy.
     *
     * Generated from protobuf enum <code>MAXIMIZE_CLICKS_OPT_IN = 9;</code>
     */
    const MAXIMIZE_CLICKS_OPT_IN = 9;
    /**
     * Recommendation to start using the "Optimize" ad rotation setting for the
     * given ad group.
     *
     * Generated from protobuf enum <code>OPTIMIZE_AD_ROTATION = 10;</code>
     */
    const OPTIMIZE_AD_ROTATION = 10;
    /**
     * Recommendation to change an existing keyword from one match type to a
     * broader match type.
     *
     * Generated from protobuf enum <code>KEYWORD_MATCH_TYPE = 14;</code>
     */
    const KEYWORD_MATCH_TYPE = 14;
    /**
     * Recommendation to move unused budget from one budget to a constrained
     * budget.
     *
     * Generated from protobuf enum <code>MOVE_UNUSED_BUDGET = 15;</code>
     */
    const MOVE_UNUSED_BUDGET = 15;
    /**
     * Budget recommendation for campaigns that are expected to become
     * budget-constrained in the future (as opposed to the CAMPAIGN_BUDGET
     * recommendation, which applies to campaigns that are currently
     * budget-constrained).
     *
     * Generated from protobuf enum <code>FORECASTING_CAMPAIGN_BUDGET = 16;</code>
     */
    const FORECASTING_CAMPAIGN_BUDGET = 16;
    /**
     * Recommendation to update a campaign to use a Target ROAS bidding
     * strategy.
     *
     * Generated from protobuf enum <code>TARGET_ROAS_OPT_IN = 17;</code>
     */
    const TARGET_ROAS_OPT_IN = 17;
    /**
     * Recommendation to add a new responsive search ad.
     *
     * Generated from protobuf enum <code>RESPONSIVE_SEARCH_AD = 18;</code>
     */
    const RESPONSIVE_SEARCH_AD = 18;
    /**
     * Budget recommendation for campaigns whose ROI is predicted to increase
     * with a budget adjustment.
     *
     * Generated from protobuf enum <code>MARGINAL_ROI_CAMPAIGN_BUDGET = 19;</code>
     */
    const MARGINAL_ROI_CAMPAIGN_BUDGET = 19;
    /**
     * Recommendation to expand keywords to broad match for fully automated
     * conversion-based bidding campaigns.
     *
     * Generated from protobuf enum <code>USE_BROAD_MATCH_KEYWORD = 20;</code>
     */
    const USE_BROAD_MATCH_KEYWORD = 20;
    /**
     * Recommendation to add new responsive search ad assets.
     *
     * Generated from protobuf enum <code>RESPONSIVE_SEARCH_AD_ASSET = 21;</code>
     */
    const RESPONSIVE_SEARCH_AD_ASSET = 21;
    /**
     * Recommendation to upgrade a Smart Shopping campaign to a Performance Max
     * campaign.
     *
     * Generated from protobuf enum <code>UPGRADE_SMART_SHOPPING_CAMPAIGN_TO_PERFORMANCE_MAX = 22;</code>
     */
    const UPGRADE_SMART_SHOPPING_CAMPAIGN_TO_PERFORMANCE_MAX = 22;
    /**
     * Recommendation to improve strength of responsive search ad.
     *
     * Generated from protobuf enum <code>RESPONSIVE_SEARCH_AD_IMPROVE_AD_STRENGTH = 23;</code>
     */
    const RESPONSIVE_SEARCH_AD_IMPROVE_AD_STRENGTH = 23;
    /**
     * Recommendation to update a campaign to use Display Expansion.
     *
     * Generated from protobuf enum <code>DISPLAY_EXPANSION_OPT_IN = 24;</code>
     */
    const DISPLAY_EXPANSION_OPT_IN = 24;
    /**
     * Recommendation to upgrade a Local campaign to a Performance Max
     * campaign.
     *
     * Generated from protobuf enum <code>UPGRADE_LOCAL_CAMPAIGN_TO_PERFORMANCE_MAX = 25;</code>
     */
    const UPGRADE_LOCAL_CAMPAIGN_TO_PERFORMANCE_MAX = 25;
    /**
     * Recommendation to raise target CPA when it is too low and there are very
     * few or no conversions.
     * It is applied asynchronously and can take minutes
     * depending on the number of ad groups there is in the related campaign.
     *
     * Generated from protobuf enum <code>RAISE_TARGET_CPA_BID_TOO_LOW = 26;</code>
     */
    const RAISE_TARGET_CPA_BID_TOO_LOW = 26;
    /**
     * Recommendation to raise the budget in advance of a seasonal event that is
     * forecasted to increase traffic, and change bidding strategy from maximize
     * conversion value to target ROAS.
     *
     * Generated from protobuf enum <code>FORECASTING_SET_TARGET_ROAS = 27;</code>
     */
    const FORECASTING_SET_TARGET_ROAS = 27;
    /**
     * Recommendation to add callout assets to campaign or customer level.
     *
     * Generated from protobuf enum <code>CALLOUT_ASSET = 28;</code>
     */
    const CALLOUT_ASSET = 28;
    /**
     * Recommendation to add sitelink assets to campaign or customer level.
     *
     * Generated from protobuf enum <code>SITELINK_ASSET = 29;</code>
     */
    const SITELINK_ASSET = 29;
    /**
     * Recommendation to add call assets to campaign or customer level.
     *
     * Generated from protobuf enum <code>CALL_ASSET = 30;</code>
     */
    const CALL_ASSET = 30;

    private static $valueToName = [
        self::UNSPECIFIED => 'UNSPECIFIED',
        self::UNKNOWN => 'UNKNOWN',
        self::CAMPAIGN_BUDGET => 'CAMPAIGN_BUDGET',
        self::KEYWORD => 'KEYWORD',
        self::TEXT_AD => 'TEXT_AD',
        self::TARGET_CPA_OPT_IN => 'TARGET_CPA_OPT_IN',
        self::MAXIMIZE_CONVERSIONS_OPT_IN => 'MAXIMIZE_CONVERSIONS_OPT_IN',
        self::ENHANCED_CPC_OPT_IN => 'ENHANCED_CPC_OPT_IN',
        self::SEARCH_PARTNERS_OPT_IN => 'SEARCH_PARTNERS_OPT_IN',
        self::MAXIMIZE_CLICKS_OPT_IN => 'MAXIMIZE_CLICKS_OPT_IN',
        self::OPTIMIZE_AD_ROTATION => 'OPTIMIZE_AD_ROTATION',
        self::KEYWORD_MATCH_TYPE => 'KEYWORD_MATCH_TYPE',
        self::MOVE_UNUSED_BUDGET => 'MOVE_UNUSED_BUDGET',
        self::FORECASTING_CAMPAIGN_BUDGET => 'FORECASTING_CAMPAIGN_BUDGET',
        self::TARGET_ROAS_OPT_IN => 'TARGET_ROAS_OPT_IN',
        self::RESPONSIVE_SEARCH_AD => 'RESPONSIVE_SEARCH_AD',
        self::MARGINAL_ROI_CAMPAIGN_BUDGET => 'MARGINAL_ROI_CAMPAIGN_BUDGET',
        self::USE_BROAD_MATCH_KEYWORD => 'USE_BROAD_MATCH_KEYWORD',
        self::RESPONSIVE_SEARCH_AD_ASSET => 'RESPONSIVE_SEARCH_AD_ASSET',
        self::UPGRADE_SMART_SHOPPING_CAMPAIGN_TO_PERFORMANCE_MAX => 'UPGRADE_SMART_SHOPPING_CAMPAIGN_TO_PERFORMANCE_MAX',
        self::RESPONSIVE_SEARCH_AD_IMPROVE_AD_STRENGTH => 'RESPONSIVE_SEARCH_AD_IMPROVE_AD_STRENGTH',
        self::DISPLAY_EXPANSION_OPT_IN => 'DISPLAY_EXPANSION_OPT_IN',
        self::UPGRADE_LOCAL_CAMPAIGN_TO_PERFORMANCE_MAX => 'UPGRADE_LOCAL_CAMPAIGN_TO_PERFORMANCE_MAX',
        self::RAISE_TARGET_CPA_BID_TOO_LOW => 'RAISE_TARGET_CPA_BID_TOO_LOW',
        self::FORECASTING_SET_TARGET_ROAS => 'FORECASTING_SET_TARGET_ROAS',
        self::CALLOUT_ASSET => 'CALLOUT_ASSET',
        self::SITELINK_ASSET => 'SITELINK_ASSET',
        self::CALL_ASSET => 'CALL_ASSET',
    ];

    public static function name($value)
    {
        if (!isset(self::$valueToName[$value])) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no name defined for value %s', __CLASS__, $value));
        }
        return self::$valueToName[$value];
    }


    public static function value($name)
    {
        $const = __CLASS__ . '::' . strtoupper($name);
        if (!defined($const)) {
            throw new UnexpectedValueException(sprintf(
                    'Enum %s has no value defined for name %s', __CLASS__, $name));
        }
        return constant($const);
    }
}

// Adding a class alias for backwards compatibility with the previous class name.
class_alias(RecommendationType::class, \Google\Ads\GoogleAds\V13\Enums\RecommendationTypeEnum_RecommendationType::class);

