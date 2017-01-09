<?php
/***************************************************************************
*                                                                          *
*    Copyright (c) 2009 Simbirsk Technologies Ltd. All rights reserved.    *
*                                                                          *
* This  is  commercial  software,  only  users  who have purchased a valid *
* license  and  accept  to the terms of the  License Agreement can install *
* and use this program.                                                    *
*                                                                          *
****************************************************************************
* PLEASE READ THE FULL TEXT  OF THE SOFTWARE  LICENSE   AGREEMENT  IN  THE *
* "copyright.txt" FILE PROVIDED WITH THIS DISTRIBUTION PACKAGE.            *
****************************************************************************/

// $Id: checkout.pre.php by tommy from cs-cart.jp 2016
// 「お届け日」「お届け時間帯」の指定に対応

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// カートに関するセッション変数が存在する場合
if( Tygh::$app['session']['cart'] ){
    // カートに関するデータがPOSTされた場合
    if( $_SERVER['REQUEST_METHOD'] == 'POST' ){
		// 配送方法が指定された場合
		if( isset($_REQUEST['shipping_ids']) ){
            // 指定された配送方法のIDをセット
			foreach($_REQUEST['shipping_ids'] as $_group_key => $_shipping_id){
                // 指定された配送方法においてお届け時間帯が指定されている場合
				if( isset($_REQUEST['delivery_time_selected_' . $_group_key . '_' . $_shipping_id]) ){
					Tygh::$app['session']['delivery_time_selected'][$_group_key][$_shipping_id] = $_REQUEST['delivery_time_selected_' . $_group_key . '_' . $_shipping_id];
				}else{
					// 指定された配送方法においてお届け時間帯が指定されていない場合 お届け時間帯用のセッション変数を解放する
					unset(Tygh::$app['session']['delivery_time_selected'][$_group_key][$_shipping_id]);
				}
				// 指定された配送方法においてお届け希望日が指定されている場合
				if( isset($_REQUEST['delivery_date_selected_' . $_group_key . '_' . $_shipping_id]) ){
					Tygh::$app['session']['delivery_date_selected'][$_group_key][$_shipping_id] = $_REQUEST['delivery_date_selected_' . $_group_key . '_' . $_shipping_id];
				}else{
					// 指定された配送方法においてお届け希望日が指定されていない場合 配送日のセッション変数を解放する
					unset(Tygh::$app['session']['delivery_date_selected'][$_group_key][$_shipping_id]);
				}
			}
		}
	}
}

if ($_SERVER['REQUEST_METHOD'] != 'POST') {
    // 注文完了時に届け時間帯・お届け日に関するセッション変数をクリア
    if($mode == 'complete'){
        if( isset(Tygh::$app['session']['delivery_time_selected']) ) unset(Tygh::$app['session']['delivery_time_selected']);
        if( isset(Tygh::$app['session']['delivery_date_selected']) ) unset(Tygh::$app['session']['delivery_date_selected']);
    }
}
