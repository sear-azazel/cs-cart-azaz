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

// Modified by tommy from cs-cart.jp 2016

$schema = array(
    'aup' => array(
        'tracking_url_template' => 'http://auspost.com.au/track/track.html?exp=b&id=[tracking_number]',
    ),
    'can' => array(
        'tracking_url_template' => 'https://www.canadapost.ca/cpotools/apps/track/personal/findByTrackNumber?trackingNumber=[tracking_number]',
    ),
    'dhl' => array(
        'tracking_url_template' => 'http://www.dhl.com/content/g0/en/express/tracking.shtml?AWB=[tracking_number]&brand=DHL',
    ),
    'fedex' => array(
        'tracking_url_template' => 'https://www.fedex.com/apps/fedextrack/?action=track&trackingnumber=[tracking_number]',
    ),
    'swisspost' => array(
        'tracking_url_template' => 'http://www.post.ch/swisspost-tracking?formattedParcelCodes=[tracking_number]',
    ),
    'temando' => array(
        'tracking_url_template' => 'http://temando.com/en/track?token=[tracking_number]&op=Track+Shipment&form_id=temando_tracking_form',
    ),
    'ups' => array(
        'tracking_url_template' => 'http://wwwapps.ups.com/etracking/tracking.cgi?tracknum=[tracking_number]',
    ),
    'usps' => array(
        'tracking_url_template' => 'https://tools.usps.com/go/TrackConfirmAction_input?qtc_tLabels1=[tracking_number]',
    ),
    'yamato' => array(
        'tracking_url_template' => 'http://jizen.kuronekoyamato.co.jp/jizen/servlet/crjz.b.NQ0010?id=[tracking_number]',
    ),
    'sagawa' => array(
        'tracking_url_template' => 'http://k2k.sagawa-exp.co.jp/p/web/okurijosearch.do?okurijoNo=[tracking_number]',
    ),
    'jpost' => array(
        'tracking_url_template' => 'http://tracking.post.japanpost.jp/service/singleSearch.do?org.apache.struts.taglib.html.TOKEN=&searchKind=S002&locale=ja&SVID=&reqCodeNo1=[tracking_number]',
    ),
    'jpems' => array(
        'tracking_url_template' => 'http://tracking.post.japanpost.jp/service/singleSearch.do?searchKind=S004&locale=ja&reqCodeNo1=[tracking_number]',
    ),
);

return $schema;
