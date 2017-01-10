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

// $Id: func.php by tommy from cs-cart.jp 2016
//
// *** 関数名の命名ルール ***
// 混乱を避けるため、フックポイントで動作する関数とその他の命名ルールを明確化する。
// (1) init.phpで定義ししたフックポイントで動作する関数：fn_smartlink_[フックポイント名]
// (2) (1)以外の関数：fn_sln_[任意の名称]

use Tygh\Http;
use Tygh\Registry;

##########################################################################################
// START フックポイントで動作する関数
##########################################################################################

/**
 * クレジットカード情報を登録済みの会員に対してのみ「登録済カードによる支払」を表示
 *
 * @param $params
 * @param $payments
 */
function fn_smartlink_get_payments_post(&$params, &$payments)
{
    fn_lcjp_filter_payments($payments, 'smartlink_ccreg.tpl', 'smartlink_ccreg');
}




/**
 * スマートリンク決済では注文時に最初に割り当てられた注文ステータスの情報を支払情報から削除する
 * 【解説】
 * 決済代行サービスを利用した注文の場合、$pp_response["order_status"] にて注文後に割り当てる
 * 注文ステータスを指定している。
 * $pp_response["order_status"] が指定されている場合、関数「fn_finish_payment」にて呼び出される
 * 関数「fn_update_order_payment_info」により、注文時に最初に割り当てられた注文ステータスが
 * 支払情報に強制的に書き込まれる。
 * この情報は後から注文ステータスを変更しても書き換わらないため、混乱を避けるためスマートリンク決済
 * では注文完了時に支払情報から注文ステータスに関する記述を削除する。
 *
 * @param $order_id
 * @param $pp_response
 * @param $force_notification
 * @return bool
 */
function fn_smartlink_finish_payment(&$order_id, &$pp_response, &$force_notification)
{
	// 注文データ内の支払関連情報を取得
	$payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, 'P');

	// 注文データ内の支払関連情報が存在する場合
	if( !empty($payment_info) ){

		// 決済代行サービスのIDを取得
		$payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
		if( empty($payment_id) ) return false;
		$payment_method_data = fn_get_payment_method_data($payment_id);
		if( empty($payment_method_data) ) return false;
		$processor_id = $payment_method_data['processor_id'];
		if( empty($processor_id) ) return false;

		switch($processor_id){
			case '9130':
			case '9131':
			case '9132':
				// 支払情報が暗号化されている場合は復号化して変数にセット
				if( !is_array($payment_info)) {
					$info = @unserialize(fn_decrypt_text($payment_info));
				}else{
					// 支払情報を変数にセット
					$info = $payment_info;
				}

				// 支払情報から注文ステータスに関する記述を削除
				unset($info['order_status']);

				// 支払情報を暗号化
				$_data = fn_encrypt_text(serialize($info));

				// 注文データ内の支払関連情報を上書き
				db_query("UPDATE ?:order_data SET data = ?s WHERE order_id = ?i AND type = 'P'", $_data, $order_id);
				break;
			default:
				// do nothing
		}
	}
}




/**
 * スマートリンクが提供する決済収納代行サービスへリダイレクト
 *
 * @param $order_id
 * @param $force_notification
 * @param $order_info
 * @param $_error
 * @return bool
 */
function fn_smartlink_order_placement_routines(&$order_id, &$force_notification, &$order_info, &$_error)
{
    // セッション変数に暗号化決済番号がセットされていない場合は処理を終了
    if(empty(Tygh::$app['session']['SLN_CODE'])) return false;

    // 暗号化決済番号を変数にセット
    $sln_code = Tygh::$app['session']['SLN_CODE'];

    // 暗号化決済番号が格納されたセッション変数を削除
    unset(Tygh::$app['session']['SLN_CODE']);

    // 決済代行サービスのIDを取得
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
    if( empty($payment_id) ) return false;
    $payment_method_data = fn_get_payment_method_data($payment_id);
    if( empty($payment_method_data) ) return false;
    $processor_id = $payment_method_data['processor_id'];
    if( empty($processor_id) ) return false;

    // 当該注文で利用する決済代行サービスが「スマートリンクネットワーク（オンライン収納代行サービス）」の場合
    if($processor_id == '9132'){
        // 本番環境の場合、本番用リダイレクトURLをセット
        if( $payment_method_data['processor_params']['mode'] == 'live' ){
            $target_url = SLN_DAIKO_REDIRECT_URL_LIVE . "?code=" . $sln_code . "&rkbn=2";
        // テスト環境の場合、テスト用リダイレクトURLをセット
        }else{
            $target_url = SLN_DAIKO_REDIRECT_URL_TEST . "?code=" . $sln_code . "&rkbn=2";
        }

        // スマートリンクが提供する決済収納代行サービスへリダイレクト
        fn_redirect($target_url, true);
    }
}




/**
 * クレジット請求管理ページにおける注文情報の抽出・表示
 *
 * @param $params
 * @param $fields
 * @param $sortings
 * @param $condition
 * @param $join
 * @param $group
 */
function fn_smartlink_get_orders(&$params, &$fields, &$sortings, &$condition, &$join, &$group)
{
    // クレジット請求管理ページの場合
    if( Registry::get('runtime.controller') == 'sln_cc_manager' && Registry::get('runtime.mode') == 'manage'){
        // カード決済および登録済カードにより支払われた注文のみ抽出
        $processor_ids = array(9130, 9131);
        $sln_cc_payments = db_get_fields("SELECT payment_id FROM ?:payments WHERE processor_id IN (?a)", $processor_ids);
        $sln_cc_payments = implode(',', $sln_cc_payments);
        $condition .= " AND ?:orders.payment_id IN ($sln_cc_payments)";

        // 各注文にひもづけられたクレジット請求ステータスコードを抽出
        $fields[] = "?:jp_sln_cc_status.status_code as cc_status_code";
        $join .= " LEFT JOIN ?:jp_sln_cc_status ON ?:jp_sln_cc_status.order_id = ?:orders.order_id";
    }
}




/**
 * 注文情報削除時にスマートリンク側の
 * 注文情報削除時に後続処理のための ProcessId と ProcessPass をDBから削除
 * 注文情報削除時に各注文の請求ステータスに関するレコードを削除
 *
 * @param $order_id
 */
function fn_smartlink_delete_order(&$order_id)
{
    $type = false;

    // 支払IDを取得
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);

    if( !empty($payment_id) && fn_sln_is_deletable($payment_id) ){
        $status_code = db_get_field("SELECT status_code FROM ?:jp_sln_cc_status WHERE order_id = ?i", $order_id);

        switch($status_code){
            // 与信状態の注文については与信を取消
            case '1Auth':
                $type = 'cc_auth_cancel';
                break;
            // 売上処理が完了している注文については売上を取消
            case '1Gathering':
            case '1Capture':
            case 'sales_confirm':
                $type = 'cc_sales_cancel';
                break;
            // 「データ登録」「データ変更」状態の収納代行データについてはデータを削除
            case '2Add':
            case '2Chg':
                $type = 'daiko_del';
                break;
            default:
                // do nothing
        }

        // 取消・削除処理を実行
        if(!empty($type)) fn_sln_send_cc_request($order_id, $type);
    }

    db_query("DELETE FROM ?:jp_sln_process_info WHERE order_id = ?i", $order_id);
    db_query("DELETE FROM ?:jp_sln_cc_status WHERE order_id = ?i", $order_id);
}




/**
 * 注文データの削除時に売上取消（カード）またはデータ削除（収納代行）を実施
 *
 * @param $status_to
 * @param $status_from
 * @param $order_info
 * @param $force_notification
 * @param $order_statuses
 * @param $place_order
 * @return bool
 */
function fn_smartlink_change_order_status(&$status_to, &$status_from, &$order_info, &$force_notification, &$order_statuses, &$place_order)
{
    if( empty($order_info['payment_id']) ) return false;

    if($status_to == 'I' && fn_sln_is_deletable($order_info['payment_id']) ){

        $status_code = db_get_field("SELECT status_code FROM ?:jp_sln_cc_status WHERE order_id = ?i", $order_info['order_id']);

        switch($status_code){
            // 与信状態の注文については与信を取消
            case '1Auth':
                $type = 'cc_auth_cancel';
                break;
            // 売上処理が完了している注文については売上を取消
            case '1Gathering':
            case '1Capture':
            case 'sales_confirm':
                $type = 'cc_sales_cancel';
                break;
            // 売上処理が完了している注文については売上を取消
            case '2Add':
            case '2Chg':
                $type = 'daiko_del';
                break;
            default:
                return false;
        }

        // 取消処理を実行
        fn_sln_send_cc_request($order_info['order_id'], $type);

        // 注文情報を取得
        $tmp_order_info = fn_get_order_info($order_info['order_id']);

        // 処理通番を更新
        if( !empty($tmp_order_info['payment_info']['jp_sln_transaction_id']) ){
            $order_info['payment_info']['jp_sln_transaction_id'] = $tmp_order_info['payment_info']['jp_sln_transaction_id'];
        }

        // 請求ステータスを更新
        if( !empty($tmp_order_info['payment_info']['jp_sln_cc_status']) ){
            $order_info['payment_info']['jp_sln_cc_status'] = $tmp_order_info['payment_info']['jp_sln_cc_status'];
        }
    }
}




/**
 * ユーザー削除時にスマートリンクに登録されている会員情報も削除する
 *
 * @param $user_id
 * @param $user_data
 * @param $result
 * @return bool
 */
function fn_smartlink_post_delete_user(&$user_id, &$user_data, &$result)
{
    // CS-Cart側でのユーザー削除が完了した場合
    if($result){
        // ユーザーID決済に関するデータを取得
        $processor_data = fn_sln_get_processor_data('ccreg');

        // スマートリンク側の会員情報の削除に必要なパラメータを取得
        $order_info = array();
        $order_info['user_id'] = $user_id;
        $ccreg_delete_params = fn_sln_get_params('ccreg_delete', $order_info);

        // 会員パスワードが存在しない場合
        if( empty($ccreg_delete_params['KaiinPass']) ){
            // 削除済みクレジットカード情報が存在するかチェック
            $deleted_kaiin_pass = db_get_field("SELECT quickpay_id FROM ?:jp_sln_deleted_quickpay WHERE user_id = ?i", $user_id);

            // 削除済みクレジットカード情報が存在する場合
            if(!empty($deleted_kaiin_pass)){
                // 会員パスワードをセット
                $ccreg_delete_params['KaiinPass'] = $deleted_kaiin_pass;
            }
        }

        // スマートリンクの会員登録済みユーザーの場合
        if(!empty($ccreg_delete_params['KaiinPass'])){

            // スマートリンクに登録している会員情報のステータスを取得
            $card_info = fn_sln_get_registered_card_info($user_id, true);
            $kaiin_status = $card_info['status'];

            // 会員ステータスに応じて処理を実行
            switch($kaiin_status){
                case 0: // 有効
                case 1: // カード無効
                case 2: // Login回数無効
                    // 会員を無効化
                    fn_sln_delete_card_info($user_id, false);
                    break;
                case 3: // 会員無効
                    // do nothing
                    break;
                default:    // その他のステータス
                    // エラーメッセージを表示して処理を終了
                    fn_set_notification('E', __('error'), __('jp_sln_ccreg_delete_failed'));
                    return false;
            }

            // 会員情報の削除
            $result_params = fn_sln_send_request($ccreg_delete_params, $processor_data, 'ccreg');

            // スマートリンク側で正常に処理が終了した場合
            if ( !empty($result_params['ResponseCd']) && $result_params['ResponseCd'] == 'OK' ){
                db_query("DELETE FROM ?:jp_cc_quickpay WHERE user_id = ?i AND payment_method = ?s", $user_id, 'smartlink_ccreg');
                db_query("DELETE FROM ?:jp_sln_deleted_quickpay WHERE user_id = ?i", $user_id);
                fn_set_notification('N', __('information'), __('jp_sln_ccreg_delete_success'));
            // スマートリンク側でエラーが発生した場合
            }else{
                fn_set_notification('E', __('error'), __('jp_sln_ccreg_delete_failed'));
            }
        }
    }
}
##########################################################################################
// END フックポイントで動作する関数
##########################################################################################





##########################################################################################
// START アドオンのインストール・アンインストール時に動作する関数
##########################################################################################

##########################################################################################
// END アドオンのインストール・アンインストール時に動作する関数
##########################################################################################





##########################################################################################
// START アドオンの設定ページで動作する関数
##########################################################################################
/**
 * 注文金額と入金金額が相違した注文に割り当てる注文ステータスのリストを生成
 *
 * @return array
 */
function fn_settings_variants_addons_smartlink_pending_status()
{

    // 配列を初期化
    $variants = array();

    // 指定可能な注文ステータスを初期化
    $order_statuses = array();

    // 注文ステータスのコードと名称を取得
    $order_statuses = db_get_array("SELECT ?:statuses.status_id, ?:statuses.status, ?:status_descriptions.description FROM ?:statuses LEFT JOIN ?:status_descriptions ON ?:statuses.status_id = ?:status_descriptions.status_id WHERE ?:statuses.type = 'O' AND ?:status_descriptions.lang_code = ?s", DESCR_SL);

    // 在庫が減少する注文ステータスのみリストに表示する
    if($order_statuses){
        foreach($order_statuses as $order_status) {
            $inventory_setting = db_get_field("SELECT value FROM ?:status_data WHERE param = 'inventory' AND status_id = ?i", $order_status['status_id']);
            if($inventory_setting == 'D'){
                $variants[$order_status['status']] = $order_status['description'];
            }
        }
    }
    return $variants;
}
##########################################################################################
//  END  アドオンの設定ページで動作する関数
##########################################################################################





##########################################################################################
// START その他の関数
##########################################################################################

/////////////////////////////////////////////////////////////////////////////////////
// 各支払方法で共通の処理 BOF
/////////////////////////////////////////////////////////////////////////////////////

/**
 * 各支払方法共通の送信パラメータをセット
 *
 * @param $type

 * @param $order_info
 * @param $processor_data
 * @return array
 */
function fn_sln_get_params($type, $order_info='', $processor_data='')
{
	$params = array();

    // マーチャントID
    $params['MerchantId'] = Registry::get('addons.smartlink.merchant_id');

    // マーチャントパスワード
    $params['MerchantPass'] = Registry::get('addons.smartlink.merchant_pass');

    // 取引の処理日付（YYYYMMDD）
    $params['TransactionDate'] = date('Ymd');

    // 取引の処理ID
    $params['OperateId'] = fn_sln_get_operate_id($type, $processor_data);

    // 自由領域
    $params['MerchantFree1'] = "";
    $params['MerchantFree2'] = "";
    $params['MerchantFree3'] = "";

	// 支払方法別の送信パラメータをセット
	fn_sln_get_specific_params($params, $type, $order_info, $processor_data);

	return $params;
}




/**
 * スマートリンクに送信する取引電文種別を取得
 *
 * @param $type
 * @param $processor_data
 * @return bool|string
 */
function fn_sln_get_operate_id($type, $processor_data='')
{
    switch($type){
        case 'cc':
        case 'ccreg_payment':
            if($processor_data['processor_params']['process_type'] == 'auth_only'){
                // 与信
                return '1Auth';
            }else{
                // 与信売上計上
                return '1Gathering';
            }
        case 'ccreg_register':
            return '4MemAdd';
        case 'ccreg_update':
            return '4MemChg';
        case 'ccreg_ref':
            return '4MemRefM';
        case 'ccreg_invalidate':
            return '4MemInval';
        case 'ccreg_uninvalidate':
            return '4MemUnInval';
        case 'ccreg_delete':
            return '4MemDel';
        case 'daiko':
            return '2Add';
        case 'cc_sales_confirm':
            return '1Capture';
        case 'cc_auth_cancel':
        case 'cc_sales_cancel':
            return '1Delete';
        case 'cc_change':
            return '1Change';
        case 'cc_search':
            return '1Search';
        case 'daiko_change':
            return '2Chg';
        case 'daiko_del':
            return '2Del';
        default:
            return false;
    }
}




/**
 * 支払方法別の送信パラメータを取得
 *
 * @param $params
 * @param $type
 * @param $order_info
 * @param $processor_data
 */
function fn_sln_get_specific_params(&$params, $type, $order_info='', $processor_data='')
{
    switch($type){
        // クレジットカード決済
        case 'cc':
            // クレジットカード番号（数値以外の値は削除）
            $card_number = mb_ereg_replace('[^0-9]', '', $order_info['payment_info']['card_number']);
            $params['CardNo'] = $card_number;

            // クレジットカード有効期限
            $params['CardExp'] = $order_info['payment_info']['expiry_year'] . $order_info['payment_info']['expiry_month'];

            // クレジットカード支払区分
            switch($order_info['payment_info']['jp_cc_method']){
                case '10':
                    $params['PayType'] = '01';
                    break;
                case '61':
                    $params['PayType'] = $order_info['payment_info']['jp_cc_installment_times'];
                    break;
                default:
                    $params['PayType'] = $order_info['payment_info']['jp_cc_method'];
            }

            // 利用金額
            $params['Amount'] = round($order_info['total']);

            // セキュリティコードによる認証を行う場合
            if( $processor_data['processor_params']['use_cvv'] == 'true' ){
                // セキュリティコード
                $params['SecCd'] = $order_info['payment_info']['cvv2'];

                // Twigmo3でセキュリティコードが入力された場合
                if(!empty($order_info['payment_info']['cvv_twg'])){
                    $params['SecCd'] = $order_info['payment_info']['cvv_twg'];
                }
            }

            // アドオン設定画面で店舗コードが設定されている場合
            if( Registry::get('addons.smartlink.tenant_id') ){
                // 店舗コードをセット
                $params['TenantId'] = Registry::get('addons.smartlink.tenant_id');
            }

            break;

        // 登録済みクレジットカード決済
        case 'ccreg_payment':
            // アドオン設定画面で店舗コードが設定されている場合
            if( Registry::get('addons.smartlink.tenant_id') ){
                // 店舗コードをセット
                $mem_prefix = Registry::get('addons.smartlink.tenant_id');
            }else{
                $mem_prefix = '';
            }
            // 会員ID
            $params['KaiinId'] = sprintf("%012s" , $mem_prefix . $order_info['user_id']);

            // 会員パスワード
            $params['KaiinPass'] = fn_sln_get_kaiin_pass($order_info['user_id']);

            // クレジットカード支払区分
            switch($order_info['payment_info']['jp_cc_method']){
                case '10':
                    $params['PayType'] = '01';
                    break;
                case '61':
                    $params['PayType'] = $order_info['payment_info']['jp_cc_installment_times'];
                    break;
                default:
                    $params['PayType'] = $order_info['payment_info']['jp_cc_method'];
            }

            // 利用金額
            $params['Amount'] = round($order_info['total']);

            // セキュリティコードによる認証を行う場合
            if( $processor_data['processor_params']['use_cvv'] == 'true' ){
                // セキュリティコード
                $params['SecCd'] = $order_info['payment_info']['cvv2'];

                // Twigmo3でセキュリティコードが入力された場合
                if($order_info['payment_info']['cvv_twg']){
                    $params['SecCd'] = $order_info['payment_info']['cvv_twg'];
                }
            }

            // アドオン設定画面で店舗コードが設定されている場合
            if( Registry::get('addons.smartlink.tenant_id') ){
                // 店舗コードをセット
                $params['TenantId'] = Registry::get('addons.smartlink.tenant_id');
            }

            break;

        // クレジットカード情報の登録・更新
        case 'ccreg_register':
        case 'ccreg_update':
            // アドオン設定画面で店舗コードが設定されている場合
            if( Registry::get('addons.smartlink.tenant_id') ){
                // 店舗コードをセット
                $mem_prefix = Registry::get('addons.smartlink.tenant_id');
            }else{
                $mem_prefix = '';
            }
            // 会員ID
            $params['KaiinId'] = sprintf("%012s" , $mem_prefix . $order_info['user_id']);

            // 会員パスワード
            $params['KaiinPass'] = fn_sln_get_kaiin_pass($order_info['user_id']);

            // クレジットカード番号（数値以外の値は削除）
            $card_number = mb_ereg_replace('[^0-9]', '', $order_info['payment_info']['card_number']);
            $params['CardNo'] = $card_number;

            // クレジットカード有効期限
            $params['CardExp'] = $order_info['payment_info']['expiry_year'] . $order_info['payment_info']['expiry_month'];

            break;

        // クレジットカード情報の照会・会員無効・会員無効解除・会員削除
        case 'ccreg_ref':
        case 'ccreg_invalidate':
        case 'ccreg_uninvalidate':
        case 'ccreg_delete':
            // アドオン設定画面で店舗コードが設定されている場合
            if( Registry::get('addons.smartlink.tenant_id') ){
                // 店舗コードをセット
                $mem_prefix = Registry::get('addons.smartlink.tenant_id');
            }else{
                $mem_prefix = '';
            }

            // 会員ID
            $params['KaiinId'] = sprintf("%012s" , $mem_prefix . $order_info['user_id']);

            // 会員パスワード
            $params['KaiinPass'] = fn_sln_get_kaiin_pass($order_info['user_id']);

            break;

        // オンライン収納代行サービスのデータ登録
        case 'daiko':
            // 利用金額
            $params['Amount'] = round($order_info['total']);

            $paylimit = (int)$processor_data['processor_params']['paylimit'];

            if( !empty($paylimit) ){
                // 支払期限
                $params['PayLimit'] = date("Ymd2359",strtotime("+" . $paylimit . " day"));
            }

            // ユーザー漢字氏名
            $params['NameKanji'] = fn_sln_format_kanji_name($order_info);

            // ユーザーカナ氏名
            $params['NameKana'] = fn_sln_format_kana_name($order_info);

            // 顧客電話番号
            $params['TelNo'] = fn_sln_format_telno($order_info['phone']);

            // アドオン設定画面で店舗コードが設定されている場合
            if( Registry::get('addons.smartlink.tenant_id') ){
                // 店舗コードをセット
                $mem_prefix = Registry::get('addons.smartlink.tenant_id') . '-';
            }else{
                $mem_prefix = '';
            }

            // 入金結果通知処理に利用する照会用IDをセット
            $params['MerchantFree1'] = $mem_prefix . $order_info['order_id'];
            break;

        // 売上計上 / 与信取消 / 売上取消 / カード決済取引参照 / 金額変更 / データ削除
        case 'cc_sales_confirm':
        case 'cc_auth_cancel':
        case 'cc_sales_cancel':
        case 'cc_search':
        case 'cc_change':
        case 'daiko_change':
        case 'daiko_del':
            // 後続処理用のプロセスIDおよびプロセスパスワードを取得
            list($process_id, $process_pass) = fn_sln_get_process_info($order_info['order_id']);

            // 後続処理用のプロセスIDおよびプロセスパスワードが存在する場合
            if( !empty($process_id) && !empty($process_pass) ){
                // プロセスID
                $params['ProcessId'] = $process_id;

                // プロセスパスワード
                $params['ProcessPass'] = $process_pass;
            }

            // 登録済みカード決済の場合、会員IDと会員パスワードもセットする
            if( !empty($processor_data['processor_script']) && $processor_data['processor_script'] == 'smartlink_ccreg.php' ){
                // アドオン設定画面で店舗コードが設定されている場合
                if( Registry::get('addons.smartlink.tenant_id') ){
                    // 店舗コードをセット
                    $mem_prefix = Registry::get('addons.smartlink.tenant_id');
                }else{
                    $mem_prefix = '';
                }

                // 会員ID
                $params['KaiinId'] = sprintf("%012s" , $mem_prefix . $order_info['user_id']);

                // 会員パスワード
                $params['KaiinPass'] = fn_sln_get_kaiin_pass($order_info['user_id']);
            }

            // 売上計上処理の場合
            if($type == 'cc_sales_confirm'){
                // 売上計上日（YYYYMMDD）
                $params['SalesDate'] = date('Ymd');
            // カード金額変更の場合
            }elseif($type == 'cc_change'){
                // 利用金額
                $params['Amount'] = round($order_info['total']);

            // 収納代行金額変更の場合
            }elseif($type == 'daiko_change'){
                // 利用金額
                $params['Amount'] = round($order_info['total']);

                $paylimit = (int)$processor_data['processor_params']['paylimit'];

                if( !empty($paylimit) ){
                    // 支払期限
                    $params['PayLimit'] = date("Ymd2359",strtotime("+" . $paylimit . " day"));
                }

                // アドオン設定画面で店舗コードが設定されている場合
                if( Registry::get('addons.smartlink.tenant_id') ){
                    // 店舗コードをセット
                    $mem_prefix = Registry::get('addons.smartlink.tenant_id') . '-';
                }else{
                    $mem_prefix = '';
                }

                // 入金結果通知処理に利用する照会用IDをセット
                $params['MerchantFree1'] = $mem_prefix . $order_info['order_id'];
            }

            break;

        default:
            // do nothing
            break;
    }
}




/**
 * スマートリンクへリクエストを送信
 *
 * @param $params
 * @param $processor_data
 * @param string $action
 * @return array
 */
function fn_sln_send_request($params, $processor_data, $action = 'checkout')
{
    switch($action){
        // 決済手続き
        case 'checkout':
            // 本番環境の場合
            if( $processor_data['processor_params']['mode'] == 'live' ){
                $target_url = 'https://www.e-scott.jp/online/aut/OAUT002.do';
            //  テスト環境の場合
            }else{
                $target_url = 'https://www.test.e-scott.jp/online/aut/OAUT002.do';
            }
            break;

        // カード情報お預かり機能
        case 'ccreg':
            // 本番環境の場合
            if( $processor_data['processor_params']['mode'] == 'live' ){
                $target_url = 'https://www.e-scott.jp/online/crp/OCRP005.do';
            //  テスト環境の場合
            }else{
                $target_url = 'https://www.test.e-scott.jp/online/crp/OCRP005.do';
            }
            break;

        // オンライン収納代行
        case 'daiko':
            // 本番環境の場合
            if( $processor_data['processor_params']['mode'] == 'live' ){
                $target_url = 'https://www.e-scott.jp/online/cnv/OCNV005.do';
            //  テスト環境の場合
            }else{
                $target_url = 'https://www.test.e-scott.jp/online/cnv/OCNV005.do';
            }
            break;

        default:
            // do nothing
            break;
    }

    // パラメータの値をすべてURLエンコードする
    foreach($params as $key => $param){
        $params[$key] = $params[$key];
    }

    // スマートリンクにデータを送信し、戻り値を配列化
    $result = Http::post($target_url, $params);
    $result = explode("&", $result);
    $result_params = array();
    foreach($result as $val){
        $key_val = explode('=', $val);
        if(!empty($key_val)) $result_params[$key_val[0]] = $key_val[1];
    }

    return $result_params;
}




/**
 * DBに保管する支払情報をフォーマット
 *
 * @param $type
 * @param $order_id
 * @param $payment_info
 * @param $result_params
 * @param bool $flg_comments
 * @return bool
 */
function fn_sln_format_payment_info($type, $order_id, $payment_info, $result_params, $flg_comments = false)
{
    // 注文IDが存在しない場合は処理を終了
    if( empty($order_id) ) return false;

    // 処理対象となる注文ID群を取得
    $order_ids_to_process = fn_lcjp_get_order_ids_to_process($order_id);

    // 処理対象となる注文ID群を格納する配列にセットされたすべての注文に対して処理を実施
    foreach($order_ids_to_process as $order_id){

        // 支払情報がすでに存在する場合
        if( !empty($payment_info) ){
            // 支払情報が暗号化されている場合は復号化して変数にセット
            if( !is_array($payment_info)) {
                $info = @unserialize(fn_decrypt_text($payment_info));
            }else{
                // 支払情報を変数にセット
                $info = $payment_info;
            }
        }

        /////////////////////////////////////////////////////////
        // 追記用コメントの初期化 BOF
        /////////////////////////////////////////////////////////
        // 既存のコメントを取得
        $order_comments = db_get_field("SELECT notes FROM ?:orders WHERE order_id = ?i", $order_id);

        // 既存のコメントが存在する場合、改行を追加
        if($order_comments != ''){
            $order_comments .= "\n\n";
        }

        // 見出し
        $order_comments .= __('jp_sln_'. $type . '_info') . "\n";
        /////////////////////////////////////////////////////////
        // 追記用コメントの初期化 EOF
        /////////////////////////////////////////////////////////

        // クレジットカード決済における分割払い判定フラグを初期化
        $flg_cc_installment = false;

        // 支払情報がすでに存在する場合
        if( !empty($info) ){
            ////////////////////////////////////////////////////////////////////
            // 必要に応じて既存の支払情報を変換 BOF
            ////////////////////////////////////////////////////////////////////
            foreach($info as $key => $val){
                switch($key){
                    ////////////////////////////////////////////////////////////////////
                    // クレジットカード決済 BOF
                    ////////////////////////////////////////////////////////////////////
                    // 支払方法はコードに対応した支払方法に変換
                    case "jp_cc_method":
                        switch($val){
                            // 一括
                            case '10':
                                $info[$key] = __('jp_cc_onetime');
                                break;
                            // 分割
                            case '61':
                                $info[$key] = __('jp_cc_installment');
                                $flg_cc_installment = true;
                                break;
                            // ボーナス一括
                            case '80':
                                $info[$key] = __('jp_cc_bonus_onetime');
                                break;
                            // リボ払い
                            case '88':
                                $info[$key] = __('jp_cc_revo');
                                break;
                            default:
                                // do nothing
                                break;
                        }
                        break;

                    case "jp_cc_installment_times":
                        // 支払回数には末尾に「回」を追記
                        if( $info['jp_cc_method'] == 61 || $flg_cc_installment ){
                            $info[$key] = $info[$key] . __('jp_paytimes_unit');
                        }else{
                            unset($info[$key]);
                        }
                        break;
                    ////////////////////////////////////////////////////////////////////
                    // クレジットカード決済 BOF
                    ////////////////////////////////////////////////////////////////////

                    // 一時的に保存されたカード番号などの情報はすべて削除
                    default:
                        unset($info[$key]);
                        break;
                }
            }
            ////////////////////////////////////////////////////////////////////
            // 必要に応じて既存の支払情報を変換 EOF
            ////////////////////////////////////////////////////////////////////
        }

        ////////////////////////////////////////////////////////////////////
        // 共通項目 BOF
        ////////////////////////////////////////////////////////////////////
        // 処理通番
        if( !empty($result_params['TransactionId']) ){
            $info['jp_sln_transaction_id'] = $result_params['TransactionId'];
        }

        // 取引日付
        if( !empty($result_params['TransactionDate']) ){
            $info['jp_sln_transaction_date'] = $result_params['TransactionDate'];
        }
        ////////////////////////////////////////////////////////////////////
        // 共通項目 EOF
        ////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////
        // クレジットカード BOF
        ////////////////////////////////////////////////////////////////////
        if( $type == 'cc' ){
            // オーソリが正常に完了した場合
            if( $result_params['ResponseCd'] == 'OK' ){
                // 決済データの処理方法
                if( !empty($result_params['OperateId']) ){
                    $info['jp_sln_cc_process_type'] = fn_sln_get_process_type($result_params['OperateId']);
                }

                // カード会社コード
                if( !empty($result_params['CompanyCd']) ){
                    $info['jp_sln_cc_company_code'] = $result_params['CompanyCd'];
                }

                // 承認番号
                if( !empty($result_params['ApproveNo']) ){
                    $info['jp_sln_cc_approve_no'] = $result_params['ApproveNo'];
                }
           }
        }
        ////////////////////////////////////////////////////////////////////
        // クレジットカード EOF
        ////////////////////////////////////////////////////////////////////

        ////////////////////////////////////////////////////////////////////
        // 収納代行サービス BOF
        ////////////////////////////////////////////////////////////////////
        if( $type == 'daiko' ){
            // 決済情報登録が正常に完了した場合
            if( $result_params['ResponseCd'] == 'OK' ){
                // 決済番号
                if( !empty($result_params['KessaiNumber']) ){
                    $info['jp_sln_daiko_kessai_number'] = $result_params['KessaiNumber'];
                }

                // 支払期限
                if( !empty($result_params['PayLimit']) ){
                    $info['jp_sln_daiko_paylimit'] = fn_sln_format_date($result_params['PayLimit']);
                }

                // 収納代行に関するデータを取得
                $processor_data = fn_sln_get_processor_data('daiko');

                if(!empty($result_params['FreeArea'])){
                    // 本番環境の場合
                    if( $processor_data['processor_params']['mode'] == 'live' ){
                        $target_url = SLN_DAIKO_REDIRECT_URL_LIVE . "?code=" . trim($result_params['FreeArea']) . "&rkbn=2";
                    //  テスト環境の場合
                    }else{
                        $target_url = SLN_DAIKO_REDIRECT_URL_TEST . "?code=" . trim($result_params['FreeArea']) . "&rkbn=2";
                    }

                    $order_comments .= __('jp_sln_daiko_instruction') . "\n";
                    $order_comments .= $target_url;
                    if( !empty($result_params['PayLimit']) ){
                        $order_comments .= "\n" . __('jp_sln_daiko_paylimit') . ' : ' . fn_sln_format_date($result_params['PayLimit']);
                    }
                }
            }
        }
        ////////////////////////////////////////////////////////////////////
        // 収納代行サービス EOF
        ////////////////////////////////////////////////////////////////////


        // 支払情報を暗号化
        $_data = fn_encrypt_text(serialize($info));

        // 注文データ内の支払関連情報の有無をチェック
        $tmp_order_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, 'P');

        // 注文データ内の支払関連情報が存在する場合
        if( !empty($tmp_order_id) ){
            // 注文データ内の支払関連情報を上書き
            db_query("UPDATE ?:order_data SET data = ?s WHERE order_id = ?i AND type = 'P'", $_data, $order_id);

        // 注文データ内の支払関連情報が存在しない場合
        }else{
            // 注文データ内の支払関連情報を追加
            $insert_data = array (
                'order_id' => $order_id,
                'type' => 'P',
                'data' => $_data,
            );
            db_query("REPLACE INTO ?:order_data ?e", $insert_data);
        }

        // 指定した場合のみコメント欄に支払情報を追記する
        if( $flg_comments ){
            $valid_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);

            // 正常なフローでの処理の場合のみ追記する
            if( !empty($valid_id) ){
                $data = array('notes' => $order_comments);
                db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", $data, $order_id);
            }
        }
    }
}




/**
 * 処理方法を取得
 *
 * @param $operate_id
 * @return bool|string
 */
function fn_sln_get_process_type($operate_id)
{
    if(empty($operate_id)) return false;

    switch($operate_id){
        case '1Check':
            return __('jp_sln_cc_check');
            break;
        case '1Auth':
            return __('jp_sln_cc_auth');
            break;
        case '1Capture':
            return __('jp_sln_cc_capture');
            break;
        case '1Gathering':
            return __('jp_sln_cc_gathering');
            break;
        case '1Change':
            return __('jp_sln_cc_change');
            break;
        case '1Delete':
            return __('jp_sln_cc_delete');
            break;
        case '1Search':
            return __('jp_sln_cc_search');
            break;
        case '1ReAuth':
            return __('jp_sln_cc_reauth');
            break;
    }
}




/**
 * スマートリンクの取消およびデータ削除処理の対象となる注文であるかを判定
 *
 * @param $payment_id
 * @return bool
 */
function fn_sln_is_deletable($payment_id)
{
    // 注文で使用されている決済代行業者IDを取得
    $payment_method_data = fn_get_payment_method_data($payment_id);
    if( empty($payment_method_data) ) return false;
    $processor_id = $payment_method_data['processor_id'];
    if( empty($processor_id) ) return false;

    // 決済代行業者がスマートリンクの場合はtrueを返す
    switch($processor_id){
        case '9130':    // スマートリンクネットワーク（カード決済）
        case '9131':    // スマートリンクネットワーク（登録済みカード決済）
        case '9132':    // スマートリンクネットワーク（オンライン収納代行サービス）
            return true;
            break;
        default:
            return false;
    }
}




/**
 * 指定された決済方法に関するデータを取得
 *
 * @param $type
 * @return array|bool
 */
function fn_sln_get_processor_data($type)
{
    // 指定された決済方法で使用するスクリプトファイル名を取得
    switch($type){
        case 'cc':
            $script = 'smartlink_cc.php';
            break;
        case 'ccreg':
            $script = 'smartlink_ccreg.php';
            break;
        case 'daiko':
            $script = 'smartlink_daiko.php';
            break;
        default;
            return false;
    }

    // 指定された決済方法に関するデータを取得
    $payment_id = db_get_field("SELECT ?:payments.payment_id FROM ?:payments LEFT JOIN ?:payment_processors ON ?:payment_processors.processor_id = ?:payments.processor_id WHERE ?:payment_processors.processor_script = ?s AND ?:payments.status = 'A'", $script);
    $processor_data = fn_get_processor_data($payment_id);

    // 決済方法に関するデータが存在する場合
    if( !empty($processor_data) ){
        // 取得したデータを返す
        return $processor_data;
    // 決済方法に関するデータが存在しない場合
    }else{
        // falseを返す
        return false;
    }
}
/////////////////////////////////////////////////////////////////////////////////////
// 各支払方法で共通の処理 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// クレジットカード決済 BOF
/////////////////////////////////////////////////////////////////////////////////////
/**
 * クレジットカード情報の登録・更新
 *
 * @param $order_info
 * @param $processor_data
 */
function fn_sln_register_cc_info($order_info, $processor_data)
{
    // クレジットカード情報を登録済みかチェック
    $kaiin_pass = db_get_field("SELECT quickpay_id FROM ?:jp_cc_quickpay WHERE user_id = ?i AND payment_method =?s", $order_info['user_id'], 'smartlink_ccreg');

    // 削除済みクレジットカード情報を初期化
    $deleted_kaiin_pass = '';

    // クレジットカード情報が登録済みの場合
    if(!empty($kaiin_pass)){
        // 実行する処理は「カード情報の更新」
        $type = 'ccreg_update';

    // クレジットカード情報が未登録の場合
    }else{
        // 削除済みクレジットカード情報が存在するかチェック
        $deleted_kaiin_pass = db_get_field("SELECT quickpay_id FROM ?:jp_sln_deleted_quickpay WHERE user_id = ?i", $order_info['user_id']);

        // 削除済みクレジットカード情報が存在する場合
        if(!empty($deleted_kaiin_pass)){
            // 実行する処理は「カード情報の更新」
            $type = 'ccreg_update';
        }else{
            // 実行する処理は「カード情報の登録」
            $type = 'ccreg_register';
        }
    }

    // 会員が無効化されている場合は有効化する
    if($type == 'ccreg_update'){
        // スマートリンクに登録している会員情報のステータスを取得
        $card_info = fn_sln_get_registered_card_info($order_info['user_id'], true);
        $kaiin_status = $card_info['status'];

        // 会員ステータスに応じて処理を実行
        switch($kaiin_status){
            case 0: // 有効
                // do nothing
                break;
            case 1: // カード無効
            case 2: // Login回数無効
            case 3: // 会員無効
                // 会員無効解除に必要なパラメータを取得
                $ccreg_uninvalidate_params = fn_sln_get_params('ccreg_uninvalidate', $order_info, $processor_data);

                // 削除済みクレジットカード情報が存在する場合、削除済みカード情報に含まれる会員パスワードをセット
                if(!empty($deleted_kaiin_pass)){
                    $ccreg_uninvalidate_params['KaiinPass'] = $deleted_kaiin_pass;
                }

                // 会員無効解除
                $ccreg_uninvalidate_result_params = fn_sln_send_request($ccreg_uninvalidate_params, $processor_data, 'ccreg');

                // 会員無効解除に失敗した場合
                if ( empty($ccreg_uninvalidate_result_params['ResponseCd']) || $ccreg_uninvalidate_result_params['ResponseCd'] != 'OK' ) {
                    // エラーメッセージを表示して処理を終了
                    fn_set_notification('E', __('jp_sln_ccreg_error'), __('jp_sln_ccreg_register_failed') . '<br />' . __('jp_sln_ccreg_error_code') . ' : ' . $ccreg_uninvalidate_result_params['ResponseCd']);
                    return false;

                }
                break;
            default:    // その他のステータス
                // エラーメッセージを表示して処理を終了
                fn_set_notification('E', __('jp_sln_ccreg_error'), __('jp_sln_ccreg_register_failed'));
                return false;
        }
    }

    // クレジットカード情報の登録・更新に必要なパラメータを取得
    $ccreg_params = fn_sln_get_params($type, $order_info, $processor_data);

    // 削除済みクレジットカード情報が存在する場合、削除済みカード情報に含まれる会員パスワードをセット
    if(!empty($deleted_kaiin_pass)){
        $ccreg_params['KaiinPass'] = $deleted_kaiin_pass;
    }

    // クレジットカード情報の登録・更新
    $result_params = fn_sln_send_request($ccreg_params, $processor_data, 'ccreg');

    // スマートリンクより処理結果が返された場合
    if ( !empty($result_params['TransactionId']) ) {
        // 処理でエラーが発生している場合
        if( $result_params['ResponseCd'] != 'OK' ){
            // エラーメッセージを表示
            fn_set_notification('E', __('jp_sln_ccreg_error'), __('jp_sln_ccreg_register_failed') . '<br />' . __('jp_sln_ccreg_error_code') . ' : ' . $result_params['ResponseCd']);

        // クレジットカード情報の登録・更新が正常に終了した場合
        }else{
            $_data = array('user_id' => $order_info['user_id'],
                'payment_method' => 'smartlink_ccreg',
                'quickpay_id' => $ccreg_params['KaiinPass'],
            );
            db_query("REPLACE INTO ?:jp_cc_quickpay ?e", $_data);

            // 登録・更新完了メッセージを表示
            fn_set_notification('N', __('information'), __('jp_sln_ccreg_register_success'));
        }
    }
}




/**
 * スマートリンクに送信する会員パスワードを取得
 *
 * @param $user_id
 * @return array|string
 */
function fn_sln_get_kaiin_pass($user_id)
{
    // クレジットカード情報を登録済みかチェック
    $kaiin_pass = db_get_field("SELECT quickpay_id FROM ?:jp_cc_quickpay WHERE user_id = ?i AND payment_method =?s", $user_id, 'smartlink_ccreg');

    // クレジットカード情報を登録済みの場合
    if(!empty($kaiin_pass)){
        // 登録に使用したパスワードを返す
        return $kaiin_pass;
    }else{
        // 暗号化されたCS-Cartログインパスワードの先頭12桁を返す
        $encrypted_password = db_get_field("SELECT password FROM ?:users WHERE user_id = ?i", $user_id);
        return substr($encrypted_password, 0, 12);
    }
}




/**
 * 登録済みカード情報を取得
 *
 * @param $user_id
 */
function fn_sln_get_registered_card_info($user_id, $get_deleted = false)
{
    // クレジットカード情報が未登録の場合はfalseを返す
    $kaiin_pass = db_get_field("SELECT quickpay_id FROM ?:jp_cc_quickpay WHERE user_id = ?i AND payment_method =?s", $user_id, 'smartlink_ccreg');

    // 削除済みカード情報も検索する場合
    if( empty($kaiin_pass) && $get_deleted){
        $kaiin_pass = db_get_field("SELECT quickpay_id FROM ?:jp_sln_deleted_quickpay WHERE user_id = ?i", $user_id);
    }

    // クレジットカード情報が未登録の場合はfalseを返す
    if(empty($kaiin_pass)) return false;

    // ユーザーID決済に関するデータを取得
    $processor_data = fn_sln_get_processor_data('ccreg');

    // クレジットカード情報の照会に必要なパラメータを取得
    $order_info = array();
    $order_info['user_id'] = $user_id;
    $cc_ref_params = fn_sln_get_params('ccreg_ref', $order_info);

    // クレジットカード情報の照会
    $result_params = fn_sln_send_request($cc_ref_params, $processor_data, 'ccreg');

    // スマートリンクより処理結果が返された場合
    if ( !empty($result_params['TransactionId']) ) {
        // 処理でエラーが発生している場合
        if( $result_params['ResponseCd'] != 'OK' || empty($result_params['CardNo']) ){
            return '';
        }else{
            $card_info = array('card_number' => $result_params['CardNo'], 'card_exp' => $result_params['CardExp'], 'status' => $result_params['KaiinStatus']);
            return $card_info;
        }
    }
    return '';
}




/**
 * 登録済みカード情報の削除（CS-CartのDBからのみ削除。スマートリンク側の情報は削除しない）
 *
 * @param $user_id
 */
function fn_sln_delete_card_info($user_id, $flg_notify = true)
{
    /////////////////////////////////////////////////////////////////////////////////////////////
    // メモ
    // スマートリンクでは一度削除した会員IDは再利用できないため、
    // 登録済カード情報を削除する場合にはCS-Cart側のレコードのみ削除する
    // そのため、CS-Cart側で登録済みカード情報を削除し、その後再度登録する場合には
    // 「新規登録(4MemAdd)」ではなく「更新(4MemChg)」となる。
    // 「更新(4MemChg)」の際には「会員ID」（店舗ID+CS-CartのユーザーID）と「パスワード」が必要。
    // jp_cc_quickpayテーブルから単純にレコードを削除してしまうとパスワードがわからなくなるので、
    // jp_sln_deleted_quickpay にデータを移しておく処理が必要
    /////////////////////////////////////////////////////////////////////////////////////////////

    // スマートリンクに登録した会員パスワードを取得
    $kaiin_pass = db_get_field("SELECT quickpay_id FROM ?:jp_cc_quickpay WHERE user_id = ?i AND payment_method =?s", $user_id, 'smartlink_ccreg');

    // ユーザーID決済に関するデータを取得
    $processor_data = fn_sln_get_processor_data('ccreg');

    // スマートリンク側の会員情報の無効化に必要なパラメータを取得
    $order_info = array();
    $order_info['user_id'] = $user_id;
    $ccreg_params = fn_sln_get_params('ccreg_invalidate', $order_info);

    // 会員情報の無効化
    $result_params = fn_sln_send_request($ccreg_params, $processor_data, 'ccreg');

    // スマートリンク側で正常に処理が終了した場合
    if ( !empty($result_params['ResponseCd']) && $result_params['ResponseCd'] == 'OK' ){
        // CS-CartのDB上から登録済みカード決済用レコードを削除
        db_query("DELETE FROM ?:jp_cc_quickpay WHERE user_id = ?i AND payment_method = ?s", $user_id, 'smartlink_ccreg');

        // スマートリンクネットワークの削除済みカード情報を格納するテーブルにレコードをセット
        $_data['user_id'] = $user_id;
        $_data['quickpay_id'] = $kaiin_pass;
        db_query("REPLACE INTO ?:jp_sln_deleted_quickpay ?e", $_data);

        if($flg_notify){
            fn_set_notification('N', __('notice'), __('jp_sln_ccreg_delete_success'));
        }

    // スマートリンク側でエラーが発生した場合
    }else{
        fn_set_notification('E', __('error'), __('jp_sln_ccreg_delete_failed'));
    }
}




/**
 * エラーメッセージを取得する
 *
 * @param $response_cd
 * @return string
 */
function fn_sln_get_err_msg($response_cd)
{
    $err_msg = $response_cd . ' : ';

    switch($response_cd){
        case 'G44':
            $err_msg .= '入力されたセキュリティコードが正しくありません。';
            break;
        case 'G45':
            $err_msg .= 'セキュリティコードが入力されていません。';
            break;
        case 'G54':
            $err_msg .= '1日のカード利用回数または利用金額オーバーです。';
            break;
        case 'G55':
            $err_msg .= '1日のカード利用限度額オーバーです。';
            break;
        case 'G56':
            $err_msg .= 'クレジットカードが無効です。';
            break;
        case 'G60':
        case 'G96':
            $err_msg .= '事故カードが入力されました。';
            break;
        case 'G61':
            $err_msg .= '無効カードが入力されました。';
            break;
        case 'G65':
        case 'C16':
        case 'C17':
            $err_msg .= '入力されたカード番号が正しくありません。';
            break;
        case 'G75':
            $err_msg .= '分割払いの下限金額を下回っています。';
            break;
        case 'G83':
            $err_msg .= '入力された有効期限が正しくありません。';
            break;
        case 'C02':
            $err_msg .= 'e-SCOTTシステムエラー';
            break;
        case 'C03':
            $err_msg .= 'e-SCOTT通信エラー';
            break;
        case 'C11':
            $err_msg .= 'ボーナス払いご利用対象外期間です。';
            break;
        case 'C13':
            $err_msg .= '入力された有効期限が正しくないか、有効期限切れのカードです。';
            break;
        case 'C15':
            $err_msg .= 'ボーナス払いの下限金額未満の注文です。';
            break;
        case 'K82':
            $err_msg .= 'カード番号が適切ではありません。';
            break;
        case 'K83':
            $err_msg .= 'カード有効期限が適切ではありません。';
            break;
        default:
            $err_msg = __('jp_sln_cc_error_code') . ' : ' . $response_cd;
    }

    return $err_msg;
}




/**
 * 決済ステータスおよび処理通番をDBに保存
 *
 * @param $order_id
 * @param $status_code
 * @param string $transaction_id
 * @return bool
 */
function fn_sln_update_cc_status_code($order_id, $status_code, $transaction_id = '')
{
    // 利用額変更の場合は「与信/売上計上/与信売上計上」といったステータスは変更されないので処理を終了
    if($status_code == '1Change') return false;

    //////////////////////////////////////////////////////////////////////
    // 決済ステータスをDBに保存 BOF
    //////////////////////////////////////////////////////////////////////
    $_data = array (
        'order_id' => $order_id,
        'status_code' => $status_code,
    );
    db_query("REPLACE INTO ?:jp_sln_cc_status ?e", $_data);
    //////////////////////////////////////////////////////////////////////
    // 決済ステータスをDBに保存 EOF
    //////////////////////////////////////////////////////////////////////

    // 収納代行サービスの場合はここで処理を終了
    if($status_code == '2Add' || $status_code == '2Chg' || $status_code == '2Del') return false;

    //////////////////////////////////////////////////////////////////////
    // クレジット請求ステータスと処理通番を注文詳細に表示 BOF
    //////////////////////////////////////////////////////////////////////
    // 注文データ内の支払関連情報を取得
    $payment_info = db_get_field("SELECT data FROM ?:order_data WHERE order_id = ?i AND type = ?s", $order_id, 'P');

    // 注文データ内に支払関連情報が存在する場合
    if( !empty($payment_info) ){
        $flg_payment_info_exists = true;

        // 支払情報が暗号化されている場合は復号化して変数にセット
        if( !is_array($payment_info)) {
            $info = @unserialize(fn_decrypt_text($payment_info));
        }else{
            // 支払情報を変数にセット
            $info = $payment_info;
        }

    // 注文データ内の支払関連情報が存在しない場合
    }else{
        $flg_payment_info_exists = false;
        $info = array();
    }

    // 請求ステータスをセット
    $info['jp_sln_cc_status'] = fn_sln_get_cc_status_name($status_code);

    // 処理通番をセット
    if( !empty($transaction_id) ){
        $info['jp_sln_transaction_id'] = $transaction_id;
    }

    // 支払情報を暗号化
    $_data = fn_encrypt_text(serialize($info));

    // 注文データ内の支払関連情報が存在する場合
    if( $flg_payment_info_exists ){
        // 注文データ内の支払関連情報を上書き
        db_query("UPDATE ?:order_data SET data = ?s WHERE order_id = ?i AND type = 'P'", $_data, $order_id);
    // 注文データ内の支払関連情報が存在しない場合
    }else{
        // 注文データ内の支払関連情報を追加
        $insert_data = array (
            'order_id' => $order_id,
            'type' => 'P',
            'data' => $_data,
        );
        db_query("REPLACE INTO ?:order_data ?e", $insert_data);
    }
    //////////////////////////////////////////////////////////////////////
    // クレジット請求ステータスと処理通番を注文詳細に表示 EOF
    //////////////////////////////////////////////////////////////////////
}




/**
 * 後続処理のための ProcessId と ProcessPass をDBに保存する
 *
 * @param $order_id
 * @param $result_params
 * @return bool
 */
function fn_sln_update_set_process_info($order_id, $result_params)
{
    if( empty($order_id) || empty($result_params['ProcessId']) || empty($result_params['ProcessPass']) ){
        return false;
    }else{
        $_process_info = array(
            'order_id' => $order_id,
            'process_id' => $result_params['ProcessId'],
            'process_pass' => $result_params['ProcessPass']
        );

        db_query("REPLACE INTO ?:jp_sln_process_info ?e", $_process_info);
    }
}




/**
 * 後続処理用のプロセスIDとプロセスパスワードを取得
 *
 * @param $order_id
 * @return array|bool
 */
function fn_sln_get_process_info($order_id)
{
    // 注文IDが指定されていない場合はfalseを返す
    if( empty($order_id) ) return false;

    // 後続処理用のプロセスIDとプロセスパスワードを抽出
    $process_info = db_get_row("SELECT * FROM ?:jp_sln_process_info WHERE order_id = ?i", $order_id);

    // 後続処理用のプロセスIDとプロセスパスワードが存在する場合
    if( !empty($process_info) ){
        // 後続処理用のプロセスIDとプロセスパスワードを返す
        return array($process_info['process_id'], $process_info['process_pass']);

    // 後続処理用のプロセスIDとプロセスパスワードが存在しない場合
    }else{
        // falseを返す
        return false;
    }
}




/**
 * 売上計上 / 取消 / 利用額変更 / データ削除 の実行
 *
 * @param $order_id
 * @param string $type
 */
function fn_sln_send_cc_request($order_id, $type = 'cc_sales_confirm')
{
    // 注文IDが指定されていない場合はfalseを返す
    if( empty($order_id) ) return false;

    // 指定した処理を行うのに適した注文であるかを判定
    $is_valid_order = fn_sln_check_process_validity($order_id, $type);

    if(!$is_valid_order) return false;

    // 支払方法に関するデータを取得
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
    $processor_data = fn_get_processor_data($payment_id);

    // 注文情報を取得
    $order_info = fn_get_order_info($order_id);

    // 売上計上 / 取消 / 利用額変更 / データ変更 / データ削除 に必要なパラメータを取得
    $params = fn_sln_get_params($type, $order_info, $processor_data);

    // 処理の実行（売上計上 / 取消 / 利用額変更 / データ変更 / データ削除）
    if($type == 'daiko_del'){
        $action = 'daiko';
    }else{
        $action = 'checkout';
    }
    $result_params = fn_sln_send_request($params, $processor_data, $action);

    // 処理が正常終了した場合
    if( $result_params['ResponseCd'] == 'OK' ){
        $transaction_id = '';
        if( !empty($result_params['TransactionId']) ) $transaction_id = $result_params['TransactionId'];
        // CS-Cart注文情報内の請求ステータスと請求ステータスコードを更新
        fn_sln_update_cc_status($order_id, $type, $transaction_id);
    // 処理でエラーが発生した場合
    }else{
        // エラーメッセージを表示
        $is_valid_order = false;
        fn_sln_get_cc_error_message($type, $order_id, $result_params);
    }
    return $is_valid_order;
}




/**
 * 注文データ内に格納されたクレジット請求ステータスや処理通番を更新
 *
 * @param $order_id
 * @param string $type
 * @param string $transaction_id
 */
function fn_sln_update_cc_status( $order_id, $type = 'cc_sales_confirm', $transaction_id = '')
{
    // クレジット請求ステータスを初期化
    $status_code = '';

    // 処理内容に応じてセットする値を変更
    switch($type){
        // 売上計上
        case 'cc_sales_confirm':
            $status_code = 'sales_confirm';
            $msg = __('jp_sln_cc_sales_completed');
            break;
        // 与信取消
        case 'cc_auth_cancel':
            $status_code = 'auth_cancel';
            $msg = __('jp_sln_cc_auth_cancelled');
            break;
        // 売上取消
        case 'cc_sales_cancel':
            $status_code = 'sales_cancel';
            $msg = __('jp_sln_cc_sales_cancelled');
            break;
        // データ削除
        case 'daiko_del':
            $status_code = '2Del';
            $msg = __('jp_sln_daiko_deleted');
            break;
        // その他
        default:
            // do nothing
    }

    // クレジット請求ステータスが設定されている場合
    if( !empty($status_code) ){
        // クレジット請求ステータスを更新
        fn_sln_update_cc_status_code($order_id, $status_code, $transaction_id);
        // 処理完了メッセージを表示
        fn_set_notification('N', __('information'), $msg, 'K');
    }
}




/**
 * 売上計上 / 取消 / 利用額変更に関するエラーメッセージを取得
 *
 * @param $type
 * @param $order_id
 * @param $result_params
 */
function fn_sln_get_cc_error_message($type, $order_id, $result_params)
{
    $is_diplay = true;

    switch($type){
        // 売上計上時のエラー
        case 'cc_sales_confirm':
            $title = __('jp_sln_cc_sales_confirm_error');
            $msg = str_replace('[oid]', $order_id, __('jp_sln_cc_sales_confirm_failed'));
            break;
        // 与信取消時のエラー
        case 'cc_auth_cancel':
            $title = __('jp_sln_cc_auth_cancel_error');
            $msg = str_replace('[oid]', $order_id, __('jp_sln_cc_auth_cancel_failed'));
            break;
        // 売上取消時のエラー
        case 'cc_sales_cancel':
            $title = __('jp_sln_cc_sales_cancel_error');
            $msg = str_replace('[oid]', $order_id, __('jp_sln_cc_sales_cancel_failed'));
            break;
        default:
            $is_diplay = false;
    }

    if(	$is_diplay ){
        // エラーメッセージを表示
        fn_set_notification('E', $title, '<br />' . $msg . '<br />' . __('jp_sln_cc_error_code') . ' : ' . $result_params['ResponseCd'], 'K');
    }
}




/** 指定した処理を行うのに適した注文であるかを判定（複数注文の一括処理時に使用）
 * @param $order_id
 * @param $type
 * @return bool
 */
function fn_sln_check_process_validity( $order_id, $type )
{
    // 注文データからクレジット請求ステータスを取得
    $cc_status = db_get_field("SELECT status_code FROM ?:jp_sln_cc_status WHERE order_id = ?i", $order_id);

    switch($type){
        // 売上計上または与信取消
        case 'cc_sales_confirm':
        case 'cc_auth_cancel':
            // 請求ステータスが「与信(1Auth)」の場合に処理可能
            if( $cc_status == '1Auth' ) return true;
            break;
        // 売上取消
        case 'cc_sales_cancel':
            // 請求ステータスが「与信売上計上」または「売上計上」の場合に処理可能
            if( $cc_status == '1Gathering' || $cc_status == 'sales_confirm' ) return true;
            break;
        // データ削除
        case 'daiko_del':
            // ステータスが「データ登録」または「データ変更」の場合に処理可能
            if( $cc_status = '2Add' || $cc_status = '2Del') return true;
            break;
        // その他
        default:
            // do nothing
    }

    return false;
}




/**
 * 利用額変更処理が可能な注文であるかを判定
 *
 * @param $order_id
 * @return bool
 */
function fn_sln_cc_is_changeable($order_id, $order_info, $processor_data)
{
    // 利用額変更可否フラグを初期化
    $flg_changeable = false;

    // プロセスIDとプロセスパスワードを取得
    list($process_id, $process_pass) = fn_sln_get_process_info($order_id);

    // プロセスIDが存在する場合
    if( !empty($process_id) ){
        // 取引参照を実行
        $params = fn_sln_get_params('cc_search', $order_info, $processor_data);
        $action = 'checkout';
        $result_params = fn_sln_send_request($params, $processor_data, $action);

        // 取引参照処理が正常に完了して利用金額が取得できた場合
        if( $result_params['ResponseCd'] == 'OK' && !empty($result_params['Amount']) ){
            // 注文編集前後の注文金額を変数にセット
            $org_amount = (int)$result_params['Amount'];
            $chg_amount = (int)$order_info['total'];

            // 編集後の注文金額が編集前よりも小さい（減額処理）場合
            if($org_amount > $chg_amount){
                // 編集前の注文で利用された決済方法を取得
                $org_payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);
                $org_payment_method_data = fn_get_payment_method_data($org_payment_id);
                $org_processor_id = $org_payment_method_data['processor_id'];

                // 編集前後で同じ決済方法が選択されている場合
                if( !empty($org_processor_id) && $org_processor_id == $processor_data['processor_id'] ){
                    // ステータスコードを取得
                    $status_code = db_get_field("SELECT status_code FROM ?:jp_sln_cc_status WHERE order_id = ?i", $order_id);

                    // ステータスコードが存在する場合
                    if( !empty($status_code) ){
                        // 特定のステータスコードを持つ注文のみ利用額変更処理を許可
                        switch($status_code){
                            case '1Auth':           // 与信
                            case '1Capture':        // 売上計上
                            case '1Change':         // 利用額変更
                            case '1Gathering':      // 与信売上計上
                            case 'sales_confirm':   // 売上計上
                                $flg_changeable = true;
                                break;
                            default:
                                // do nothing;
                        }
                    }
                }
            }
        }
    }
    return $flg_changeable;
}
/////////////////////////////////////////////////////////////////////////////////////
//  クレジットカード決済 EOF
/////////////////////////////////////////////////////////////////////////////////////





/////////////////////////////////////////////////////////////////////////////////////
// クレジット請求管理 BOF
/////////////////////////////////////////////////////////////////////////////////////

// クレジット請求ステータス名を取得
function fn_sln_get_cc_status_name($cc_status)
{
    return __('jp_sln_cc_' . strtolower($cc_status));
}
/////////////////////////////////////////////////////////////////////////////////////
// クレジット請求管理 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
//  収納代行サービス BOF
/////////////////////////////////////////////////////////////////////////////////////
/**
 * ユーザー漢字氏名を生成
 *
 * @param $order_info
 * @return string
 */
function fn_sln_format_kanji_name($order_info)
{
    $kanji_name = mb_convert_kana($order_info['b_firstname'], 'RNASKV', 'UTF-8') . ' ' . mb_convert_kana($order_info['b_lastname'], 'RNASKV', 'UTF-8');
    $kanji_name = mb_strimwidth($kanji_name, 0 , SLN_MAXLEN_NAME_KANJI, '', 'UTF-8');

    return $kanji_name;
}




/**
 * 顧客カナ名を生成する
 *
 * @param $order_info
 * @return string
 */
function fn_sln_format_kana_name($order_info)
{
    $kana_name = '';

    // 顧客カナ名のフィールド番号を取得
    $familyname_kana_b = (int)Registry::get('addons.localization_jp.jp_familyname_kana_b');
    $firstname_kana_b = (int)Registry::get('addons.localization_jp.jp_firstname_kana_b');

    // 顧客カナ名のフィールド番号が存在する場合
    if( !empty($familyname_kana_b) && !empty($firstname_kana_b) ){
        // 顧客カナ名
        $kana_name = mb_convert_kana($order_info['fields'][$familyname_kana_b] . $order_info['fields'][$firstname_kana_b], 'rnaskh', 'UTF-8');
        $kana_name = mb_strimwidth($kana_name, 0 , SLN_MAXLEN_NAME_KANA, '', 'UTF-8');
    }

    return $kana_name;
}




/**
 * 電話番号をフォーマット
 *
 * @param null $bill_phone
 * @return bool|mixed
 */
function fn_sln_format_telno($bill_phone = null)
{
    // CS-Cartに登録されている電話番号について、数値以外の値を取り除く
    $tell_no = preg_replace("/[^0-9]+/", '', mb_convert_kana(mb_strimwidth($bill_phone, 0, 13, '', 'UTF-8'), "a", 'UTF-8'));

    // 数値以外の値を取り除いた電話番号の長さを取得
    $bill_phone_length = strlen($tell_no);

    // 電話番号が9桁未満、もしくは12桁以上の場合、エラーを返す
    if( $bill_phone_length < 9 || $bill_phone_length > 11 ){
        return false;
    }

    return $tell_no;
}




/**
 * スマートリンクから受信した入金通知データのバリデーション
 *
 * @param $post
 */
function fn_sln_validate_notification($post)
{
    // 送信されたマーチャントIDとCS-Cartに登録されたマーチャントIDが一致しない場合はエラー
    if( $post['MerchantId'] != Registry::get('addons.smartlink.merchant_id') ) return false;

    // プロセスパスワード+受付番号+マーチャントパスワードのmd5ハッシュがスマートリンクから受信したハッシュコードと異なる場合はエラー
    $hash_base = $post['ProcessPass'] . $post['RecvNum'] . Registry::get('addons.smartlink.merchant_pass');
    $hash_md5 = md5($hash_base);
    if( $post['HashCd'] != $hash_md5 ) return false;

    return true;
}




/**
 * スマートリンクから受信した「貴社自由領域1」からCS-Cartの注文IDを抽出
 *
 * @param $merchantfree1
 */
function fn_sln_get_order_id($merchantfree1)
{
    if(empty($merchantfree1)) return false;

    $order_id_array = explode('-', $merchantfree1);
    if( count($order_id_array) > 1 ){
        $order_id = $order_id_array[1];
    }else{
        $order_id = $order_id_array[0];
    }

    return $order_id;
}




/**
 * 収納機関名を取得
 *
 * @param $cvscd
 */
function fn_sln_get_cvs_name($cvscd)
{
    switch($cvscd){
        case 'LSN':
            $cvs_name = __('jp_sln_daiko_cvscd_lsn');
            break;
        case 'FAM':
            $cvs_name = __('jp_sln_daiko_cvscd_fam');
            break;
        case 'SAK':
            $cvs_name = __('jp_sln_daiko_cvscd_sak');
            break;
        case 'CCK':
            $cvs_name = __('jp_sln_daiko_cvscd_cck');
            break;
        case 'ATM':
            $cvs_name = __('jp_sln_daiko_cvscd_atm');
            break;
        case 'ONL':
            $cvs_name = __('jp_sln_daiko_cvscd_onl');
            break;
        case 'LNK':
            $cvs_name = __('jp_sln_daiko_cvscd_lnk');
            break;
        case 'SEV':
            $cvs_name = __('jp_sln_daiko_cvscd_sev');
            break;
        case 'MNS':
            $cvs_name = __('jp_sln_daiko_cvscd_mns');
            break;
        case 'DAY':
            $cvs_name = __('jp_sln_daiko_cvscd_day');
            break;
        case 'EBK':
            $cvs_name = __('jp_sln_daiko_cvscd_ebk');
            break;
        case 'JNB':
            $cvs_name = __('jp_sln_daiko_cvscd_jnb');
            break;
        case 'EDY':
            $cvs_name = __('jp_sln_daiko_cvscd_edy');
            break;
        case 'SUI':
            $cvs_name = __('jp_sln_daiko_cvscd_sui');
            break;
        case 'FFF':
            $cvs_name = __('jp_sln_daiko_cvscd_fff');
            break;
        case 'JIB':
            $cvs_name = __('jp_sln_daiko_cvscd_jib');
            break;
        case 'SNB':
            $cvs_name = __('jp_sln_daiko_cvscd_snb');
            break;
        case 'SCM':
            $cvs_name = __('jp_sln_daiko_cvscd_scm');
            break;
        default:
            $cvs_name = '';
    }

    return $cvs_name;
}




/**
 * 印紙の有無を取得
 *
 * @param $stampflag
 */
function fn_sln_get_stamp_status($stampflag)
{
    if(intval($stampflag) == 1){
        return __('jp_sln_daiko_stamp_yes');
    }else{
        return __('jp_sln_daiko_stamp_no');
    }
}




/**
 * 収納代行サービスのデータ変更可否を判定
 *
 * @param $order_id
 * @return bool
 */
function fn_sln_daiko_is_changeable($order_id)
{
    // 利用額変更可否フラグを初期化
    $flg_changeable = false;

    // 編集前の注文で利用された支払方法を取得
    $payment_id = db_get_field("SELECT payment_id FROM ?:orders WHERE order_id = ?i", $order_id);

    // プロセスIDを取得
    $process_id = db_get_field("SELECT process_id FROM ?:jp_sln_process_info WHERE order_id = ?i", $order_id);

    // 支払方法IDとプロセスIDが存在する場合
    if( !empty($payment_id) && !empty($process_id) ){
        $payment_method_data = fn_get_payment_method_data($payment_id);
        if( !empty($payment_method_data['processor_id']) ){
            // 決済代行サービスIDを取得
            $processor_id = $payment_method_data['processor_id'];
            if(!empty($processor_id) && $processor_id == '9132'){
                // ステータスコードが "2Add" （データ登録）場合はデータ変更可能
                $status_code = db_get_field("SELECT status_code FROM ?:jp_sln_cc_status WHERE order_id = ?i", $order_id);
                if(!empty($status_code) && $status_code == '2Add'){
                    $flg_changeable = true;
                }
            }
        }
    }

    return $flg_changeable;
}




/**
 * 支払日付の表示形式をフォーマット
 *
 * @param $paylimit
 * @return string
 */
function fn_sln_format_date($paylimit)
{
    $_year = substr($paylimit, 0, 4);
    $_month = substr($paylimit, 4, 2);
    $_date = substr($paylimit, 6, 2);
    $_hour = substr($paylimit, 8, 2);
    $_minute = substr($paylimit, 10, 2);

    return $_year . '/' . $_month . '/' . $_date . ' ' . $_hour . ':' . $_minute;
}
/////////////////////////////////////////////////////////////////////////////////////
//  収納代行サービス EOF
/////////////////////////////////////////////////////////////////////////////////////

##########################################################################################
// END その他の関数
##########################################################################################
