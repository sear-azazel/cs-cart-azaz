<?php
/***************************************************************************
 *                                                                          *
 *   (c) 2004 Vladimir V. Kalynyak, Alexey V. Vinokurov, Ilya M. Shalnev    *
 *                                                                          *
 * This  is  commercial  software,  only  users  who have purchased a valid *
 * license  and  accept  to the terms of the  License Agreement can install *
 * and use this program.                                                    *
 *                                                                          *
 ****************************************************************************
 * PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
 * "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
 ****************************************************************************/

namespace Ebay\responses;
use Ebay\ProductOption;
use Ebay\ProductOptionVariant;
use Ebay\ProductVariation;
use Ebay\XmlHelper;

/**
 * Class GetItemResponse
 * @package Ebay\responses
 * @see http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/GetItem.html
 */
class GetItemResponse extends Response
{
    /**
     * @var \SimpleXMLElement
     * @see http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/GetItem.html#Response.Item
     */
    protected $item;
    /** @var  string */
    protected $listing_status;

    /**
     * @inheritdoc
     */
    public function __construct(\SimpleXMLElement $response)
    {
        parent::__construct($response);

        if (!empty($response->Item)) {
            $this->item = $response->Item;
            $this->listing_status = (string) $this->item->SellingStatus->ListingStatus;
        }
    }

    /**
     * Return listing status code
     *
     * @return null|string
     * @see http://developer.ebay.com/Devzone/XML/docs/Reference/ebay/types/ListingStatusCodeType.html
     */
    public function getStatusCode()
    {
        return $this->listing_status;
    }

    /**
     * Return flag product is active
     * @return bool
     */
    public function statusIsActive()
    {
        return $this->listing_status === 'Active';
    }

    /**
     * @return ProductVariation[]
     */
    public function getProductVariations()
    {
        $result = array();
        $options = array();

        if (!empty($this->item->Variations->Variation)) {
            foreach ($this->item->Variations->Variation as $item) {
                $variants = array();
                $price = XmlHelper::getAsDouble($item, 'StartPrice');
                $quantity = XmlHelper::getAsDouble($item, 'Quantity');

                foreach ($item->VariationSpecifics->NameValueList as $option_item) {
                    $option_name = XmlHelper::getAsString($option_item, 'Name');
                    $variant_name = XmlHelper::getAsString($option_item, 'Value');

                    if (!isset($options[$option_name])) {
                        $option = new ProductOption($option_name);
                        $options[$option_name] = $option;
                    } else {
                        $option = $options[$option_name];
                    }

                    $variant = new ProductOptionVariant($option, $variant_name);
                    $option->setVariant($variant);

                    $variants[] = $variant;
                }

                $result[] = new ProductVariation($price, $quantity, $variants);
            }
        }

        return $result;
    }
}
