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

// $Id: sbps.php by tommy from cs-cart.jp 2016
// ソフトバンク・ペイメント・サービス（リンクタイプ）

use Tygh\Registry;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

if (defined('PAYMENT_NOTIFICATION')) {

	// SBPS側で決済処理を実行した場合
	if ($mode == 'process') {

		// SBPSの設定情報を取得
		$sbps_order_info = fn_get_order_info($_REQUEST['order_id']);
		$sbps_settings = fn_get_payment_method_data($sbps_order_info['payment_id']);

		// 正常終了した場合
		if( ($_REQUEST['merchant_id'] == $sbps_settings['processor_params']['merchant_id']) && ($_REQUEST['amount'] == round($sbps_order_info['total'])) && $_REQUEST['res_result'] == 'OK' && empty($_REQUEST['res_err_code']) ){
			if (fn_check_payment_script('sbps.php', $_REQUEST['order_id'])) {
				fn_order_placement_routines('route', $_REQUEST['order_id'], false);
			}
		// エラーが発生した場合もしくはショップIDや決済金額が不正な場合
		}else{

			$pp_response["order_status"] = 'F';

			if (fn_check_payment_script('sbps.php', $_REQUEST['order_id'])) {
				fn_finish_payment($_REQUEST['order_id'], $pp_response); // Force user notification
				fn_order_placement_routines('route', $_REQUEST['order_id']);
			}
		}

	// 決済処理をキャンセルした場合
	} elseif ($mode == 'cancelled' || $mode == 'error') {
		$pp_response["order_status"] = 'F';

		if (fn_check_payment_script('sbps.php', $_REQUEST['order_id'])) {
			fn_finish_payment($_REQUEST['order_id'], $pp_response); // Force user notification
			fn_order_placement_routines('route', $_REQUEST['order_id']);
		}
	}

} else {

	//////////////////////////////////////////////////////////////////////////
	// SBPSに送信するパラメーターをセット BOF
	//////////////////////////////////////////////////////////////////////////
	$sbps_params = array();

	// マーチャントID
	$sbps_params['merchant_id'] = $processor_data['processor_params']['merchant_id'];

	// サービスID
	$sbps_params['service_id'] = $processor_data['processor_params']['service_id'];

	// 会員登録済みの決済の場合
	if( !empty($order_info['user_id']) ){
		// ユーザーIDをセット
		$sbps_params['cust_code'] = (int)$order_info['user_id'];

	// ゲスト購入の場合
	}else{
		// ランダムなIDを発行
		mt_srand(microtime()*100000);
		$sbps_params['cust_code'] = 'guest' . rand(10000, 99999);
	}

	// 購入ID
	$sbps_params['order_id'] = $order_id . date('ymdHis');

	// 商品ID（「SBPS_PRODUCTS」で固定）
	$sbps_params['item_id'] = 'SBPS_PRODUCTS';

	////////////////////////////////////////////////////////////////
	// 商品名 BOF
	////////////////////////////////////////////////////////////////
	$tmp_items = $order_info['products'];

	// 最初の商品を取得
	$first_item = reset($tmp_items);

	// 注文商品が1種類のみの場合
	if( count($order_info['products']) == 1 ){
		// 商品名をセット（先頭40バイト分のみ有効。残りはSBPS側でカットされる）
		$item_name = $first_item['product'];
	// 注文商品が複数種類存在する場合
	}else{
		// 商品名の先頭36バイト分と " etc" をセット
		$item_name = mb_strcut($first_item['product'], 0, 36, 'UTF-8') . ' ' . __('jp_sbps_etc');
	}

	$sbps_params['item_name'] = $item_name;
	////////////////////////////////////////////////////////////////
	// 商品名 EOF
	////////////////////////////////////////////////////////////////

	// 金額（税込）
	$sbps_params['amount'] = round($order_info['total']);

	// 購入タイプ（「都度購入」で固定）
	$sbps_params['pay_type'] = 0;

	// サービスタイプ（「売上」で固定）
	$sbps_params['service_type'] = 0;

	// 顧客利用端末タイプ
	$sbps_params['terminal_type'] = 0;

	// 決済完了時URL
	$sbps_params['success_url'] = html_entity_decode(fn_url("payment_notification.process&payment=sbps&order_id=$order_id", AREA, 'current'), ENT_QUOTES, 'UTF-8');

	// 決済キャンセル時URL
	$sbps_params['cancel_url'] = html_entity_decode(fn_url("payment_notification.cancelled&payment=sbps&order_id=$order_id", AREA, 'current'), ENT_QUOTES, 'UTF-8');

	// エラー時URL
	$sbps_params['error_url'] = html_entity_decode(fn_url("payment_notification.error&payment=sbps&order_id=$order_id", AREA, 'current'), ENT_QUOTES, 'UTF-8');

    // 決済通知用CGI
    $sbps_params['pagecon_url'] = html_entity_decode(fn_lcjp_get_return_url('/jp_extras/sbps/result.php'), ENT_QUOTES, 'UTF-8');

	// リクエスト日時
	$sbps_params['request_date'] = date('YmdHis');

	// チェックサム
	$checksum = '';
	foreach($sbps_params as $key => $val){
		$checksum .= $val;
	}
	$checksum .= $processor_data['processor_params']['hashkey'];
	$sbps_params['sps_hashcode'] = sha1($checksum);

    ////////////////////////////////////////////////////////////////
	// 接続するURLをセット BOF
	////////////////////////////////////////////////////////////////
	if( $processor_data['processor_params']['mode'] == 'live' ){
		// 本番環境
		$connection_url = $processor_data['processor_params']['url_production'];
	}elseif( $processor_data['processor_params']['mode'] == 'test' ){
		// テスト環境
		$connection_url = $processor_data['processor_params']['url_test'];
	}else{
		// 接続支援サイト
		$connection_url = $processor_data['processor_params']['url_connection_support'];
	}

	// 接続先URLがセットされていない場合は、接続支援サイトへ強制的に接続する
	if( empty($connection_url) ){
		$connection_url = "https://stbfep.sps-system.com/Extra/BuyRequestAction.do";
	}
	////////////////////////////////////////////////////////////////
	// 接続するURLをセット EOF
	////////////////////////////////////////////////////////////////

	//////////////////////////////////////////////////////////////////////////
	// SBPSに送信するパラメーターをセット EOF
	//////////////////////////////////////////////////////////////////////////

    // この処理を入れないとSBPSで決済後表示されるリンクでCS-Cartに戻らず、CS-Cartを表示させた場合に再度同じ注文IDで決済が行われる
    // この処理を入れることにより受注処理未了の注文がずっと残るが、それよりも同一注文IDで意図しない注文処理が実行される方のリスクが高い。
    unset(Tygh::$app['session']['cart']['processed_order_id']);

echo <<<EOT
<html>
<body onLoad="document.charset='Shift_JIS'; document.process.submit();">
<form action="{$connection_url}" method="POST" name="process" Accept-charset="Shift_JIS">
EOT;

foreach($sbps_params as $key => $val){
	echo '<input type="hidden" name="' . $key . '" value="' . $val . '" />';
}

$msg = __('text_cc_processor_connection');
$msg = str_replace('[processor]', __('jp_sbps_company_name'), $msg);
echo <<<EOT
	</form>
	<div align=center>{$msg}</div>
 </body>
</html>
EOT;
}

exit;
