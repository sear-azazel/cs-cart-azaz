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

// $Id: digital_check_uid.php by tommy from cs-cart.jp 2015
// ペイデザイン（ユーザーID決済）

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

// ショップフロントもしくは注文の編集でペイデザインに接続して決済手続きを再実行する場合
if( ($mode == 'place_order' || $mode == 'process' || $mode == 'repay') && (AREA == 'C' || (AREA == 'A' && Registry::get('runtime.action') != 'save')) ){

	//ペイデザインに送信するパラメータをセット
	$params = array();
	$params = fn_dgtlchck_get_params('uid', $order_id, $order_info, $processor_data);

	// オーソリ依頼
	$return_val = fn_dgtlchck_send_request('uid', $params);
	$response = $return_val['response'];
	$request = $return_val['request'];

	// リクエスト送信が正常終了した場合
	if (!PEAR::isError($response)) {

		// 応答内容の解析
		$res_content = $request->getResponseBody();

		// ペイデザインから受信した請求情報を配列に格納
		$digital_check_results = fn_dgtlchck_get_result_array($res_content);

		// DBに保管する支払い情報を生成
		fn_dgtlchck_format_payment_info('uid', $order_id, $order_info['payment_info'], $digital_check_results);

		// オーソリでエラーが発生している場合
		if( $digital_check_results[0] != 'OK' ){
			// 注文処理ページへリダイレクト
			$err_msg = fn_dgtlchck_encode_err_msg($digital_check_results[2]);
			if( $err_msg ){
				fn_set_notification('E', __('jp_digital_check_cc_error'), __('jp_digital_check_cc_failed') . '<br />' . $err_msg);
			}
			$return_url = fn_lcjp_get_error_return_url();
			fn_redirect($return_url, true);

		// オーソリが正常に完了した場合
		}else{
			if (fn_check_payment_script('digital_check_uid.php', $order_id)) {
				// 注文処理ページへリダイレクト
				$pp_response = array();
				$pp_response['order_status'] = 'P';
				fn_finish_payment($order_id, $pp_response);
				fn_order_placement_routines('route', $order_id);
			}
		}
	// リクエスト送信が異常終了した場合
	}else{
		// 注文処理ページへリダイレクト
		fn_set_notification('E', __('jp_digital_check_cc_error'), __('jp_digital_check_cc_invalid'));
		$return_url = fn_lcjp_get_error_return_url();
		fn_redirect($return_url, true);
	}
}
