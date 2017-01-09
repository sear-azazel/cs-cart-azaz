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

// $Id: result.php by tommy from cs-cart.jp 2016


// SBPSからのIPアドレスのみ処理を許可
if( preg_match('/^61\.215\.213\.47/', $_SERVER['REMOTE_ADDR']) || preg_match('/^61\.215\.213\.20/', $_SERVER['REMOTE_ADDR']) ){

	define('AREA', 'C');
	define('SBPS_DATE_LENGTH', 12);
	require '../../init.php';

	// SBPSから決済結果データが送信された場合
	// ※ 結果の種類（成功・エラー・入金通知 etc）は考慮しない
	if( !empty($_REQUEST['res_result']) ){

		/////////////////////////////////////////////////////////////////////////
		// 注文データの存在チェック BOF
		/////////////////////////////////////////////////////////////////////////
		// 注文IDを取得
		$order_id = substr($_REQUEST['order_id'], 0, strlen($_REQUEST['order_id']) - SBPS_DATE_LENGTH);

        // 注文IDから該当するcompany_idをセット
        fn_payments_set_company_id($order_id);

		// CS-Cartに該当する注文IDが存在しない場合
		if( empty($order_id) ){
			// エラーメッセージを返す
			echo 'NG,NO ORDER ID FOUND,' . $order_id;
			exit;
		}

		// 入金通知の場合
		if($_REQUEST['res_result'] == 'PY'){
			// 注文情報を抽出
			$order_info = db_get_row("SELECT user_id, total, status FROM ?:orders WHERE order_id = ?i", $order_id);

		// その他の場合
		}else{
            // マーケットプレイス版の場合
            if (fn_allowed_for('MULTIVENDOR')) {
                // 注文情報を抽出
                $order_info = db_get_row("SELECT user_id, total, status FROM ?:orders WHERE order_id = ?i AND (status = 'N' OR status = 'T')", $order_id);
            // 通常版の場合
            }else{
                // 注文情報を抽出
                $order_info = db_get_row("SELECT user_id, total, status FROM ?:orders WHERE order_id = ?i AND status = 'N'", $order_id);
            }
		}

		// CS-Cartに該当する注文データが存在しない場合
		if( empty($order_info) ){
			// エラーメッセージを返す
			echo 'NG,NO ORDER DATA FOUND,' . $order_id;
			exit;
		}

		// ゲスト購入でCS-Cart内の注文データとSBPSから送信されたデータの内容が一致しない場合
		if( strpos($_REQUEST['cust_code'], 'guest') === 0 ){
			if( $order_info['user_id'] != 0 || $order_info['total'] != $_REQUEST['amount'] ){
				// エラーメッセージを返す
				echo 'NG,INVALID DATA RECEIVED';
				exit;
			}
		// 通常購入でCS-Cart内の注文データとSBPSから送信されたデータの内容が一致しない場合
		}else{
			if( ($order_info['user_id'] != $_REQUEST['cust_code']) || ($order_info['total'] != $_REQUEST['amount']) ){
				// エラーメッセージを返す
				echo 'NG,INVALID DATA RECEIVED';
				exit;
			}
		}
		/////////////////////////////////////////////////////////////////////////
		// 注文データの存在チェック EOF
		/////////////////////////////////////////////////////////////////////////


		// 処理結果ステータスに応じて処理
		switch( $_REQUEST['res_result'] ){

			// 要求処理OK
			case 'OK':
				// SBPSの設定情報を取得
				$sbps_order_info = fn_get_order_info($order_id);
				$sbps_settings = fn_get_payment_method_data($sbps_order_info['payment_id']);

				// 正常終了した場合
				if( ($_REQUEST['merchant_id'] == $sbps_settings['processor_params']['merchant_id']) && ($_REQUEST['amount'] == round($sbps_order_info['total'])) && empty($_REQUEST['res_err_code']) ){
					if ( fn_check_payment_script('sbps.php', $order_id) || fn_check_payment_script('sbps_rb.php', $order_id) ) {

						$pp_response = array();

						// 支払方法に応じた注文ステータスをセット
						$pp_response['order_status'] = fn_sbps_get_order_status($_REQUEST['res_pay_method']);

						// 支払内容をコメント欄などに追記
						fn_sbps_add_notes($order_id, $_REQUEST['res_pay_method'], $_REQUEST['res_tracking_id'], $_REQUEST['pay_type']);

						Tygh::$app['session']['sbps_process_order'] = 'Y';
						fn_finish_payment($order_id, $pp_response);
						fn_order_placement_routines('route', $order_id);
					}
				}
				break;

			// 入金通知
			case 'PY':

				switch($_REQUEST['res_pay_method']){

					case 'webcvs':			// Webコンビニ決済
					case 'payeasy':		// ペイジー決済
                    case 'banktransfer':	// 銀行振込

                        // 注文ステータスの変更対象となるかチェック
                        $fig_change_order_status = fn_sbps_check_payment_data($order_info['status'], $_REQUEST['res_pay_method'], $_REQUEST['res_payinfo_key']);

                        // 注文ステータスの変更対象である場合
                        if($fig_change_order_status){
                            // 注文ステータスを入金完了状態に変更
                            $force_notification = array();
                            $force_notification['C'] = true;
                            $force_notification['A'] = true;
                            fn_change_order_status($order_id, 'P', '', $force_notification);
                        }

						////////////////////////////////////////////////////////////
						// 入金通知日時をスタッフメモに追記 BOF
						////////////////////////////////////////////////////////////
						if( !empty($_REQUEST['res_payment_date']) ){

                            // 処理対象となる注文ID群を取得
                            $order_ids_to_process = fn_lcjp_get_order_ids_to_process($order_id);

                            // 処理対象となる注文ID群を格納する配列にセットされたすべての注文に対して処理を実施
                            foreach($order_ids_to_process as $order_id){
                                // 登録された注文データを抽出
                                $order_details = db_get_field("SELECT details FROM ?:orders WHERE order_id = ?i", $order_id);

                                // 入金日時
                                $payment_date = fn_sbps_format_payment_date($_REQUEST['res_payment_date']);

                                $sbps_details .= "\n\n" . __('jp_cvs_payment_date') . " ： " . $payment_date;

                                $details = $order_details . $sbps_details;

                                // 文頭の改行は削除
                                $data = array('details' => ltrim($details));

                                db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", $data, $order_id);
                            }
						}
						////////////////////////////////////////////////////////////
						// 入金通知日時をスタッフメモに追記 EOF
						////////////////////////////////////////////////////////////

						break;

					default:				// その他
						// do nothing
						break;
				}

				echo 'OK';
				exit;
				break;

			// その他
			default:
				// do nothing
				break;
		}

		// エラーメッセージを返す
		echo 'NG,INVALID DATA RECEIVED';

	}else{
		echo "INVALID ACCESS!!";
	}

}else{
	echo "INVALID ACCESS!!";
}




// 決済方法に応じた注文ステータスをセット
function fn_sbps_get_order_status($pay_method)
{
	switch($pay_method){
		case 'credit':			// クレジット決済
		case 'credit3d':		// 3Dセキュアクレジットカード決済
		case 'webmoney':		// WebMoney
		case 'netcash':		// NET CASH
		case 'bitcash':		// ビットキャッシュ決済
		case 'cyberedy':		// サイバーエディ
		case 'mobileedy':		// モバイルEdy決済
		case 'suica':			// モバイルSuica決済
		case 'docomo':			// ドコモケータイ払い
		case 'softbank':		// S!まとめて支払い
		case 'sbmoney':		// SoftBankマネー
		case 'alipay':			// Alipay国際決済
		case 'paypal':			// PayPal
		case 'oempin':			// PIN決済
		case 'auone':			// auかんたん決済
		case 'rakuten':		// 楽天ID決済
		case 'gmoney':			// G-Money
		case 'netmile':		// ネットマイル
		case 'unionpay':		// 銀聯決済
			return 'P';
			break;

		case 'webcvs':			// Webコンビニ決済
		case 'payeasy':		// ペイジー決済
		case 'banktransfer':	// 銀行振込
			return 'O';
			break;		 

		default:				// その他の決済
			return 'O';
			break;
	}
}




// 支払方法を取得
function fn_sbps_get_payment_method($pay_method, $pay_type)
{
	$payment_method = '';

	switch($pay_method){
		case 'credit':          // クレジット決済
		case 'credit3d':        // 3Dセキュアクレジットカード決済
			$payment_method = __('jp_payment_cc');
            if($pay_type == 1) $payment_method .= ' (' . __('jp_subpay_subscription_payment') . ')';
			break;

		case 'webmoney':		  // WebMoney
			$payment_method = __('jp_payment_webmoney');
			break;

		case 'netcash':         // NET CASH
			$payment_method = __('jp_payment_netcash');
			break;

		case 'bitcash':		 // ビットキャッシュ決済
			$payment_method = __('jp_payment_bitcash');
			break;

		case 'cyberedy':        // サイバーエディ
			$payment_method = __('jp_payment_cyberedy');
			break;

		case 'mobileedy':		// モバイルEdy決済
			$payment_method = __('jp_payment_mobileedy');
			break;

		case 'suica':			// モバイルSuica決済
			$payment_method = __('jp_payment_suica');
			break;

		case 'yahoowallet':		// Yahoo!ウォレット
			$payment_method = __('jp_payment_yahoowallet');
			break;

		case 'webcvs':			// Webコンビニ決済
			$payment_method = __('jp_payment_cvs');
			break;

        case 'payeasy':		// ペイジー決済
			$payment_method = __('jp_payment_pez');
			break;

		case 'banktransfer':	// 銀行振込
			$payment_method = __('jp_payment_banktransfer');
			break;

		case 'docomo':			// ドコモケータイ払い
			$payment_method = __('jp_payment_docomo');
			break;

		case 'softbank':		// S!まとめて支払い
			$payment_method = __('jp_payment_softbank');
			break;

		case 'sbmoney':		// SoftBankマネー
			$payment_method = __('jp_payment_sbmoney');
			break;

		case 'alipay':			// Alipay国際決済
			$payment_method = __('jp_payment_alipay');
			break;

		case 'paypal':			// PayPal
			$payment_method = __('jp_payment_paypal');
			break;

		case 'oempin':			// PIN決済
			$payment_method = __('jp_payment_oempin');
			break;

		case 'auone':			// auかんたん決済
			$payment_method = __('jp_payment_auone');
			break;

		case 'rakuten':		// 楽天ID決済
			$payment_method = __('jp_payment_rakuten');
			break;

		case 'gmoney':			// G-Money
			$payment_method = __('jp_payment_gmoney');
			break;

		case 'netmile':		// ネットマイル
			$payment_method = __('jp_payment_netmile');
			break;

		case 'unionpay':		// 銀聯ネット決済
			$payment_method = __('jp_payment_unionpay');
			break;

		default:
			$payment_method = 'N/A';
			break;
	}

	return $payment_method;
}




// 支払内容をコメント欄などに追記
function fn_sbps_add_notes($order_id, $res_pay_method, $res_tracking_id, $pay_type)
{
    // 処理対象となる注文ID群を取得
    $order_ids_to_process = fn_lcjp_get_order_ids_to_process($order_id);

    // 処理対象となる注文ID群を格納する配列にセットされたすべての注文に対して処理を実施
    foreach($order_ids_to_process as $order_id){
        // 登録された注文データを抽出
        $order_notes = db_get_row("SELECT notes, details FROM ?:orders WHERE order_id = ?i", $order_id);

        $sbps_notes = "\n" . __('jp_sbps_notes_header');
        $sbps_notes .= "\n" . __('jp_sbps_trans_method') . " ： " . fn_sbps_get_payment_method($res_pay_method, $pay_type);
        $sbps_tracking_id = "\n" . __('jp_sbps_tracking_id') . " ： " . $res_tracking_id;

        switch($res_pay_method){

            case 'credit':		// クレジット決済
            case 'credit3d':	// 3Dセキュアクレジットカード決済
                // do nothing
                break;

            default:
                break;
        }

        // お客様コメント
        $notes = $order_notes['notes'] . $sbps_notes;

        // スタッフメモ
        $details = $order_notes['details'] . $sbps_notes . $sbps_tracking_id;

        // 文頭の改行は削除
        $data = array('notes' => ltrim($notes), 'details' => ltrim($details));

        $valid_id = db_get_field("SELECT order_id FROM ?:order_data WHERE order_id = ?i AND type = 'S'", $order_id);

        db_query("UPDATE ?:orders SET ?u WHERE order_id = ?i", $data, $order_id);
    }
}




// 通知処理日時の表示形式をフォーマット
function fn_sbps_format_payment_date($res_payment_date)
{
	// 正しい長さの日付データが渡された場合
	if( strlen($res_payment_date) == 14 ){
		$year = substr($res_payment_date, 0, 4);
		$month = substr($res_payment_date, 4, 2);
		$date = substr($res_payment_date, 6, 2);
		$hour = substr($res_payment_date, 8, 2);

		// 「YYYY/MM/DD HH:MM」形式の値を返す
		return $year . '/' . $month . '/' . $date . ' ' . $hour . ':00';

	// 正しい長さの日付データが渡されなかった場合
	}else{
		// 引数をそのまま返す
		return $res_payment_date;
	}
}




/**
 * 入金通知による注文ステータスの変更可否を判定
 *
 * @param $order_status
 * @param string $res_pay_method
 * @param string $res_payinfo_key
 * @return bool
 */
function fn_sbps_check_payment_data($order_status, $res_pay_method = '', $res_payinfo_key = '')
{
    // 支払方法または顧客決済情報が空の場合、falseを返す
    if(empty($res_pay_method) || empty($res_payinfo_key) ) return false;

    // 入金種別コードを取得
    $res_payinfo_array = explode(',', $res_payinfo_key);
    $notification_type = $res_payinfo_array[0];

    // 入金種別コードに応じて処理を実施
    switch($notification_type){
        case 'P':	// 速報
        case 'D':	// 確報
            // 現在の注文ステータスが「O : 処理待ち」または「N : 受注処理未了」の場合
            if($order_status == 'O' || $order_status == 'N'){
                // 注文ステータスの変更可
                return true;
            }else{
                // 注文ステータスの変更不可
                return false;
            }
        default :
            // 注文ステータスの変更不可
            return false;
    }
}
