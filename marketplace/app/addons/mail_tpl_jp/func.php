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
// (1) init.phpで定義ししたフックポイントで動作する関数：fn_mail_tpl_jp_[フックポイント名]
// (2) (1)以外の関数：fn_mtpl_[任意の名称]

use Tygh\Registry;
use Tygh\Tools\SecurityHelper;

if (!defined('BOOTSTRAP')) { die('Access denied'); }

##########################################################################################
// START フックポイントで動作する関数
##########################################################################################

/**
 * メールテンプレートに基づいてメールの送信内容を生成
 *
 * @param $subject
 * @param $body_org
 * @param $body
 * @param $is_html
 * @param string $tmp_to
 */
function fn_mail_tpl_jp_before_setup_mail_contents(&$subject, &$body_org, &$body, &$is_html, &$tmp_to = '', &$from, &$from_name)
{
    $mail_template = '';
	$tpl_base_data = Registry::get('view')->tpl_vars;

	$mtpl_lang_code = CART_LANGUAGE;

	// 送信先Eメール
	$email_to = $tmp_to;

	// メールテンプレートコードを取得
	$tpl_code = str_replace('/', '_', str_replace('.tpl', '', $body_org));

	// システム管理者が出品者を登録した際に自動で送信される会員情報メールには専用のテンプレートを適用
	if(AREA == 'A' && fn_allowed_for('MULTIVENDOR') && $tpl_code == 'profiles_create_profile' && Registry::get('runtime.controller') == 'companies' && Registry::get('runtime.mode') == 'add'){
		$tpl_code = 'profiles_create_vendor_profile';
	}

	// メールテンプレートの存在チェック
	$is_tpl_exists = db_get_field("SELECT tpl_code FROM ?:jp_mtpl WHERE tpl_code = ?s AND status = 'A'", $tpl_code);

	// 各メールテンプレートで利用可能なテンプレート変数が定義されたファイル名
	$filename = Registry::get('config.dir.addons') . 'mail_tpl_jp/tpl_variants/' . $tpl_code . '.php';

	// フックポイントを設定
	fn_set_hook('get_addons_mail_tpl', $tpl_code, $filename);

	// 該当するメールテンプレートが存在する場合
	if( $is_tpl_exists && file_exists($filename) ){

		// テンプレート変数を初期化
		$mail_tpl_var = array();

		// 各メールテンプレートで利用可能なテンプレート変数が定義されたファイルを読み込み
		$_edit_mail_tpl = false;
		require($filename);

		// メールフッターを追加
		if($mail_template['use_footer'] == 'Y'){
			$mtpl_lang_code = CART_LANGUAGE;
			$mail_template['body_txt'] .= "\n\n" . fn_mtpl_get_email_footer($mtpl_lang_code);
		}

		// すべてのメールテンプレートで利用可能なテンプレート変数を取得
		$mail_tpl_common_var = fn_mtpl_get_common_tpl_var($tpl_base_data);

		// テンプレート変数が格納された2つの配列をマージ
		$mail_tpl_var = array_merge($mail_tpl_var, $mail_tpl_common_var);

		// メールテンプレートを利用したメールはテキスト形式固定
		$is_html = false;

        // メールの送信元書き換え用フックポイントを設定
        fn_set_hook('jp_mtpl_rewright_from', $tpl_code, $from, $from_name);

		// 件名
		$subject = preg_replace_callback('/{%(.+?)%}/',
			function($m) use ($mail_tpl_var) {
				return $mail_tpl_var[$m[1]]['value'];
			},
			$mail_template['subject']);


		$subject = htmlspecialchars_decode($subject, ENT_QUOTES);

		// 本文
		$_buf = preg_replace_callback('/{%(.+?)%}/',
			function($m) use ($mail_tpl_var) {
				return $mail_tpl_var[$m[1]]['value'];
			},
			$mail_template['body_txt']);

		// タブを除去
		$_buf = preg_replace('/\t/i', '', $_buf);
		// 改行タブを改行コードに変換
		$_buf = preg_replace('/<br>|<br \/>|<br>\n|<br \/>\n/i', "\n", $_buf);
		// 全てのhtmlタグを除去
        $body = html_entity_decode(strip_tags($_buf), ENT_QUOTES, 'UTF-8');
	}
}




/**
 * ショップ（出品者）を追加する際にメールテンプレート情報も作成
 *
 * @param $company_data
 * @param $company_id
 * @param $lang_code
 * @param $action
 */
function fn_mail_tpl_jp_update_company(&$company_data, &$company_id, &$lang_code, &$action)
{
    if (fn_allowed_for('ULTIMATE')) {
        // ショップ（出品者）の追加の場合
        if ($action == 'add' ){
            // デフォルトのメールテンプレート情報を取得
            $default_mtpl_descs = db_get_array("SELECT * FROM ?:jp_mtpl_descriptions WHERE company_id = ?i", 0);

            // デフォルトの送料情報が存在する場合
            if( !empty($default_mtpl_descs) && is_array($default_mtpl_descs) ){
                // デフォルトのメールテンプレートを新しいショップ（出品者）向けにコピー
                foreach($default_mtpl_descs as $default_mtpl_desc){
                    $_data = $default_mtpl_desc;
                    $_data['company_id'] = $company_id;
                    db_query("REPLACE INTO ?:jp_mtpl_descriptions ?e", $_data);
                }
            }
        }
    }
}




/**
 * ショップ（出品者）を削除する際にメールテンプレート情報レコードも削除
 *
 * @param $company_id
 */
function fn_mail_tpl_jp_delete_company(&$company_id)
{
    if (fn_allowed_for('ULTIMATE')) {
        db_query("DELETE FROM ?:jp_mtpl_descriptions WHERE company_id = ?i", $company_id);
    }
}
##########################################################################################
// END フックポイントで動作する関数
##########################################################################################





##########################################################################################
// START アドオンのインストール・アンインストール時に動作する関数
##########################################################################################

/**
 * アドオンのインストール時の動作
 */
function fn_mtpl_addon_install()
{
	/////////////////////////////////////////////////////////////////////////
	// メールテンプレート BOF
	/////////////////////////////////////////////////////////////////////////
	// メールテンプレート
	$mail_templates_main = array(
		array('tpl_code' => 'orders_order_notification'),
		array('tpl_code' => 'orders_edp_access'),
		array('tpl_code' => 'orders_low_stock'),
		array('tpl_code' => 'orders_track'),
		array('tpl_code' => 'shipments_shipment_products'),
		array('tpl_code' => 'profiles_create_profile'),
		array('tpl_code' => 'profiles_activate_profile'),
		array('tpl_code' => 'profiles_profile_activated'),
		array('tpl_code' => 'profiles_update_profile'),
		array('tpl_code' => 'profiles_recover_password'),
		array('tpl_code' => 'profiles_usergroup_request'),
		array('tpl_code' => 'profiles_usergroup_activation'),
		array('tpl_code' => 'profiles_usergroup_disactivation'),
		array('tpl_code' => 'profiles_reminder'),
		array('tpl_code' => 'promotions_give_coupon'),
		array('tpl_code' => 'addons_discussion_notification'),
		array('tpl_code' => 'addons_email_marketing_confirmation'),
		array('tpl_code' => 'addons_email_marketing_welcome'),
		array('tpl_code' => 'addons_email_marketing_welcome_2optin'),
		array('tpl_code' => 'addons_form_builder_form'),
		array('tpl_code' => 'addons_gift_certificates_gift_certificate'),
		array('tpl_code' => 'addons_reward_points_notification'),
		array('tpl_code' => 'addons_rma_slip_notification'),
		array('tpl_code' => 'addons_social_buttons_mail'),
		array('tpl_code' => 'addons_suppliers_notification'),
        array('tpl_code' => 'addons_hybrid_auth_create_profile'),
		array('tpl_code' => 'product_back_in_stock_notification'),
		array('tpl_code' => 'companies_status_a_d_notification'),
		array('tpl_code' => 'companies_status_d_a_notification'),
		);

    // メールテンプレート（マーケットプレイス版）
    $mail_templates_mve = array(
        array('tpl_code' => 'companies_apply_for_vendor_notification'),
        array('tpl_code' => 'companies_status_a_p_notification'),
        array('tpl_code' => 'companies_status_n_a_notification'),
        array('tpl_code' => 'companies_status_n_d_notification'),
        array('tpl_code' => 'companies_status_n_p_notification'),
        array('tpl_code' => 'companies_status_p_a_notification'),
        array('tpl_code' => 'companies_status_p_d_notification'),
        array('tpl_code' => 'companies_payment_notification'),
        array('tpl_code' => 'addons_vendor_data_premoderation_notification'),
		array('tpl_code' => 'profiles_create_vendor_profile'),
    );

    // メールテンプレート（フッター）
    $mail_templates_footer = array(
        array('tpl_code' => 'mtpl_email_footer'),
    );

    // マーケットプレイス版の場合
    if (fn_allowed_for('MULTIVENDOR')) {
        $mail_templates = array_merge($mail_templates_main, $mail_templates_mve, $mail_templates_footer);
    // マーケットプレイス版以外の場合
    }else{
        $mail_templates = array_merge($mail_templates_main, $mail_templates_footer);
    }

	// メールテンプレートの詳細
	$mail_template_desc_main = array(
		array('tpl_name' => "注文内容", 
				'tpl_trigger' => "受注 / 注文ステータス変更", 
				'subject' => "【{%SP_NAME%}】 注文番号{%ORDER_ID%}{%SUBJECT%}", 
				'body_txt' => "{%LASTNAME%} {%FIRSTNAME%}様\r\n\r\n{%HEADER%}\r\n\r\n----------------------------------------\r\n注文情報\r\n----------------------------------------\r\nご注文番号 ： {%ORDER_ID%}\r\nステータス ： {%STATUS%}\r\n日時 ： {%DATE%}\r\n\r\n\r\n----------------------------------------\r\nお買い上げ商品\r\n----------------------------------------\r\n{%P_BLK%}\r\n----------------------------------------\r\n小計 : {%O_SUBTOTAL%}\r\n{%O_MISC%}\r\n----------------------------------------\r\n総額 : {%O_TOTAL%}\r\n\r\n\r\n----------------------------------------\r\n配送方法・お届け先\r\n----------------------------------------\r\n配送方法 : {%SHIPPING%}\r\nお届け希望日 : {%DELIVERY_DATE%}\r\nお届け時間帯 : {%DELIVERY_TIMING%}\r\n追跡番号 : {%TRACK_NO%}\r\nお客様名 : {%S_LASTNAME%} {%S_FIRSTNAME%}様\r\n郵便番号 : {%S_ZIPCODE%}\r\n住所 : {%S_STATE%}{%S_CITY%} {%S_ADDRESS%} {%S_ADDRESS2%}\r\n電話番号 : {%S_PHONE%}\r\n\r\n\r\n----------------------------------------\r\n支払方法・請求先\r\n----------------------------------------\r\n支払方法 : {%PAYMENT%}\r\nお客様名 : {%B_LASTNAME%} {%B_FIRSTNAME%}様\r\n郵便番号 : {%B_ZIPCODE%}\r\n住所 : {%B_STATE%}{%B_CITY%} {%B_ADDRESS%} {%B_ADDRESS2%}\r\n電話番号 : {%B_PHONE%}\r\n\r\n\r\n----------------------------------------\r\nコメント\r\n----------------------------------------\r\n{%COMMENT%}"
				),
		array('tpl_name' => "商品ダウンロード", 
				'tpl_trigger' => "商品ダウンロードの有効化", 
				'subject' => "【{%SP_NAME%}】 商品をダウンロードいただけます", 
				'body_txt' => "{%LASTNAME%} {%FIRSTNAME%}様\r\n\r\nお買い上げ商品をダウンロードいただけるようになりました。\r\n\r\n{%LINK_BLK%}"
				),
		array('tpl_name' => "在庫数低下", 
				'tpl_trigger' => "商品の在庫数が設定値を下回った場合", 
				'subject' => "【{%SP_NAME%}】 {%P_NAME%} の在庫数が少なくなっています", 
				'body_txt' => "以下の商品の在庫数が少なくなっています。\r\n\r\n商品名 : {%P_NAME%}\r\nID : {%P_ID%}\r\n型番 : {%P_CODE%}\r\n数量 : {%P_QTY%}\r\n{%P_OPTIONS%}"
				),
		array('tpl_name' => "注文検索", 
				'tpl_trigger' => "ログインしていない状態で注文検索を実施", 
				'subject' => "【{%SP_NAME%}】 注文検索結果", 
				'body_txt' => "お客様による注文検索の結果は以下の通りです。\r\n\r\n{%LINK_BLK%}"
				),
		array('tpl_name' => "商品発送", 
				'tpl_trigger' => "「配送管理」機能を利用した商品発送", 
				'subject' => "【{%SP_NAME%}】 [注文番号：{%ORDER_ID%}] 商品発送のお知らせ", 
				'body_txt' => "{%LASTNAME%} {%FIRSTNAME%}様\r\n\r\nご注文の商品を以下の通り発送いたしました。\r\n\r\n注文番号 : {%ORDER_ID%} \r\n配送方法 : {%SHIPPING_METHOD%}\r\n発送日時 : {%DATE%}\r\n運送会社 : {%CARRIER%}\r\n追跡番号 : {%TRACK_NO%}\r\n\r\n\r\n商品 ：\r\n{%P_BLK%}\r\n\r\n\r\nコメント：\r\n{%COMMENTS%}"
				),
		array('tpl_name' => "会員登録", 
				'tpl_trigger' => "会員登録", 
				'subject' => "【{%SP_NAME%}】 会員登録ありがとうございます", 
				'body_txt' => "{%U_LASTNAME%}様\r\n\r\n会員登録いただき誠にありがとうございます。\r\n\r\n----------------------------------------\r\nログイン情報\r\n----------------------------------------\r\nEメールアドレス ： {%U_EMAIL%}\r\nパスワード : {%U_PASSWORD%}\r\n\r\n\r\n----------------------------------------\r\n請求先住所\r\n----------------------------------------\r\n〒{%B_ZIP%}\r\n{%B_STATE%}{%B_CITY%}{%B_ADDRESS%}{%B_ADDRESS2%}\r\n{%B_LASTNAME%} {%B_FIRSTNAME%} 様\r\n電話 ： {%B_PHONE%}\r\n\r\n\r\n----------------------------------------\r\n配送先住所\r\n----------------------------------------\r\n〒{%S_ZIP%}\r\n{%S_STATE%}{%S_CITY%}{%S_ADDRESS%}{%S_ADDRESS2%}\r\n{%S_LASTNAME%} {%S_FIRSTNAME%} 様\r\n電話 ： {%S_PHONE%}\r\n\r\n"
				),
		array('tpl_name' => "アカウント有効化依頼", 
				'tpl_trigger' => "会員アカウント登録 （管理者の承認が必要な場合のみ）", 
				'subject' => "【{%SP_NAME%}】 会員アカウントを有効化してください", 
				'body_txt' => "ログインID 「{%U_LOGIN%}」 の会員アカウントについてアクティベーション（有効化）が必要です。\r\n\r\n以下のURLにアクセスしてアカウントを有効化してください。\r\n{%URL%}\r\n\r\n\r\n\r\n"
				),
		array('tpl_name' => "アカウント有効化", 
				'tpl_trigger' => "会員アカウントの有効化", 
				'subject' => "【{%SP_NAME%}】 お客様アカウントが有効化されました", 
				'body_txt' => "{%U_LASTNAME%} {%U_FIRSTNAME%}様\r\n\r\nお客様のアカウントが有効化されました。\r\n前回のメールに記載されたユーザー名を使用してログインいただけます。 \r\n"
				),
		array('tpl_name' => "会員情報の更新", 
				'tpl_trigger' => "会員情報の更新", 
				'subject' => "【{%SP_NAME%}】  会員情報が更新されました", 
				'body_txt' => "{%U_LASTNAME%}様\r\n\r\nお客様の会員情報が更新されました。\r\n\r\n----------------------------------------\r\nログイン情報\r\n----------------------------------------\r\nログインID ： {%U_LOGIN%}\r\n\r\n\r\n----------------------------------------\r\n連絡先情報\r\n----------------------------------------\r\n氏名 ：{%U_LASTNAME%} {%U_FIRSTNAME%} 様 （{%U36_姓フリガナ%} {%U37_名フリガナ%}）\r\nE-Mail :{%U_EMAIL%}\r\n電話 ： {%U_PHONE%}\r\n\r\n\r\n----------------------------------------\r\n請求先住所\r\n----------------------------------------\r\n〒{%B_ZIP%}\r\n{%B_STATE%}{%B_CITY%}{%B_ADDRESS%}{%B_ADDRESS2%}\r\n{%B_LASTNAME%} {%B_FIRSTNAME%} 様\r\n電話 ： {%B_PHONE%}\r\n\r\n\r\n----------------------------------------\r\n配送先住所\r\n----------------------------------------\r\n〒{%S_ZIP%}\r\n{%S_STATE%}{%S_CITY%}{%S_ADDRESS%}{%S_ADDRESS2%}\r\n{%S_LASTNAME%} {%S_FIRSTNAME%} 様\r\n電話 ： {%S_PHONE%}\r\n"
				),
		array('tpl_name' => "パスワードの再設定", 
				'tpl_trigger' => "ログインパスワードの再設定", 
				'subject' => "【{%SP_NAME%}】 ログインパスワードの再設定", 
				'body_txt' => "以下のURLにアクセスしてパスワードを再設定してください。\r\n\r\n{%LINK%}"
				),
		array('tpl_name' => "ユーザーグループ登録申請", 
				'tpl_trigger' => "ユーザーグループへの登録申請", 
				'subject' => "【{%SP_NAME%}】 ユーザーグループへの登録申請がありました", 
				'body_txt' => "お客様よりユーザーグループへの登録申請がありました。\r\n\r\nお客様氏名 : {%U_LASTNAME%} {%U_FIRSTNAME%}様\r\nログインID : {%U_LOGIN%}\r\nユーザーグループ : {%U_USERGROUPS%}"
				),
		array('tpl_name' => "ユーザーグループ登録承認", 
				'tpl_trigger' => "ユーザーグループへの登録申請の承認", 
				'subject' => "【{%SP_NAME%}】 ユーザーグループが有効化されました", 
				'body_txt' => "{%U_LASTNAME%} {%U_FIRSTNAME%}様\r\n\r\nお客様の会員アカウントに対して、ユーザーグループ\r\n　{%U_USERGROUPS%}\r\nが有効化されました。"
				),
		array('tpl_name' => "ユーザーグループ無効化", 
				'tpl_trigger' => "ユーザーグループへの登録無効化", 
				'subject' => "【{%SP_NAME%}】 ユーザーグループが無効化されました", 
				'body_txt' => "{%U_LASTNAME%} {%U_FIRSTNAME%}様\r\n\r\nお客様の会員アカウントに対して、ユーザーグループ\r\n　{%U_USERGROUPS%}\r\nが無効化されました。\r\n\r\n詳細はお問い合わせください。"
				),
		array('tpl_name' => "管理者用パスワード変更依頼", 
				'tpl_trigger' => "管理者用パスワード変更期限の到来", 
				'subject' => "【{%SP_NAME%}】 管理者用パスワードを変更してください", 
				'body_txt' => "{%U_LASTNAME%}さん\r\n\r\n以下の管理者用アカウントのログインパスワードは{%DAYS%}日前に設定されたものです。\r\nセキュリティレベルを維持するため変更をお願いします。\r\n\r\nログインID : {%U_LOGIN%}\r\nEメール : {%U_EMAIL%}"
				),
		array('tpl_name' => "クーポン発行",
				'tpl_trigger' => "クーポンの発行", 
				'subject' => "【{%SP_NAME%}】 クーポンが発行されました", 
				'body_txt' => "{%LASTNAME%} {%FIRSTNAME%}様\r\n\r\nキャンペーン「{%PROMO_NAME%}」が適用され、\r\nクーポンコード ： {%COUPON_CODE%}\r\nが発行されました。\r\n\r\n----------------------------------------\r\nクーポンコード「{%COUPON_CODE%}」の内容\r\n----------------------------------------\r\n{%COUPON_DESC%}"
				),
		array('tpl_name' => "コメント投稿", 
				'tpl_trigger' => "ユーザーによるコメント投稿", 
				'subject' => "【{%SP_NAME%}】 {%O_NAME%}に関するコメントが投稿されました", 
				'body_txt' => "{%O_NAME%} 「{%O_DESC%}」 に関するコメントが投稿されました。\r\n\r\n投稿者名 : {%P_NAME%}\r\n{%P_RATING%}{%P_COMMENTS%}{%P_APPROVAL%}\r\nコメントの表示 :\r\n{%P_URL%}\r\n"
				),
		array('tpl_name' => "[Eメールマーケティング] メルマガ購読確認",
			'tpl_trigger' => "ダブルオプトイン形式のメルマガに対する購読申込",
			'subject' => "【{%SP_NAME%}】 メールマガジン購読確認のお願い",
			'body_txt' => "当ショップのメールマガジンの購読をお申込みいただき、誠にありがとうございます。\r\n\r\n以下のURLをクリックして購読手続きを完了してください。\r\n{%CONFIRMATION_URL%}\r\n"
		),
		array('tpl_name' => "[Eメールマーケティング] メルマガ購読手続き完了",
			'tpl_trigger' => "メルマガの購読手続き完了",
			'subject' => "【{%SP_NAME%}】 メールマガジンの購読手続きが完了しました",
			'body_txt' => "メールマガジンの購読をお申込みいただき、誠にありがとうございます。\r\n\r\n購読解除をご希望の場合は、以下のURLをクリックしてください。\r\n{%UNSUBSCRIBE_URL%}\r\n"
		),
		array('tpl_name' => "[Eメールマーケティング] メルマガ購読手続き完了（ダブルオプトイン）",
			'tpl_trigger' => "ダブルオプトイン形式のメルマガに対する購読手続き完了",
			'subject' => "【{%SP_NAME%}】 メルマガ購読手続き完了のお知らせ",
			'body_txt' => "メールマガジンの購読手続きが完了しました。\r\n\r\n購読解除をご希望の場合は、以下のURLをクリックしてください。\r\n{%UNSUBSCRIBE_URL%}\r\n"
		),
		array('tpl_name' => "フォーム",
				'tpl_trigger' => "フォームビルダーで作成したフォームの送信", 
				'subject' => "【{%SP_NAME%}】 {%F_TITLE%}", 
				'body_txt' => "「{%F_TITLE%}」フォームから以下の内容が送信されました。\r\n{%F_BLK%}"
				),
		array('tpl_name' => "ギフト券発行", 
				'tpl_trigger' => "ギフト券の発行", 
				'subject' => "【{%SP_NAME%}】 ギフト券発行のお知らせ", 
				'body_txt' => "{%GIFT_TO%}様\r\n\r\n{%HEADER%}\r\n\r\nギフト券コード : {%CODE%}\r\n金額 : {%AMOUNT%}\r\n\r\n{%ADDRESS%}{%PRODUCTS%}\r\n\r\nメッセージ :\r\n{%MESSAGE%}\r\n\r\n今すぐお買い物 : {%URL%}"
				),
		array('tpl_name' => "ポイント加算・減算",
				'tpl_trigger' => "ポイントの加算・減算", 
				'subject' => "【{%SP_NAME%}】 {%MSG%}", 
				'body_txt' => "{%LASTNAME%}様\r\n\r\n{%MSG%}\r\n\r\n理由 : \r\n{%REASON%}"
				),
		array('tpl_name' => "返品申請受付・ステータス更新", 
				'tpl_trigger' => "返品申請の受付 / 申請ステータスの変更", 
				'subject' => "【{%SP_NAME%}】 {%HEADER%}", 
				'body_txt' => "{%LASTNAME%} {%FIRSTNAME%}様\r\n\r\n{%HEADER%}\r\n\r\n----------------------------------------\r\n返品申請情報\r\n----------------------------------------\r\n返品申請ID : {%RETURN_ID%}\r\nご希望の対応方法 : {%ACTION%}\r\nステータス : {%STATUS%}\r\n\r\n\r\n----------------------------------------\r\n返品が申請された商品 （注文番号 : {%ORDER_ID%})\r\n----------------------------------------\r\n{%P_BLK%}\r\n\r\n\r\n----------------------------------------\r\nコメント\r\n----------------------------------------\r\n{%COMMENT%}"
				),
		array('tpl_name' => "友達に知らせる", 
				'tpl_trigger' => "「友達に知らせる」機能の利用", 
				'subject' => "【{%SP_NAME%}】 {%FROM_NAME%}様からおすすめの商品があります", 
				'body_txt' => "{%TO_NAME%}様\r\n\r\n{%FROM_NAME%}様が以下の商品をおすすめしています。\r\n{%LINK%}\r\n\r\n----------------------------------------\r\n{%FROM_NAME%}様からのコメント\r\n----------------------------------------\r\n{%NOTES%}"
				),
		array('tpl_name' => "サプライヤーへの発送依頼", 
				'tpl_trigger' => "サプライヤー取扱商品の受注", 
				'subject' => "【{%SP_NAME%}】 商品の発送をお願いいたします", 
				'body_txt' => "ご担当者様\r\n\r\nお世話になります。\r\n弊社にて以下の商品を受注しましたので発送をお願いいたします。\r\n\r\n----------------------------------------\r\nお買い上げ商品\r\n----------------------------------------\r\n{%P_BLK%}\r\n\r\n\r\n----------------------------------------\r\n配送方法・お届け先\r\n----------------------------------------\r\n配送方法 : {%SHIPPING%}\r\nお届け希望日 : {%DELIVERY_DATE%}\r\nお届け時間帯 : {%DELIVERY_TIMING%}\r\nお客様名 : {%S_LASTNAME%} {%S_FIRSTNAME%}様\r\n郵便番号 : {%S_ZIPCODE%}\r\n住所 : {%S_STATE%}{%S_CITY%} {%S_ADDRESS%} {%S_ADDRESS2%}\r\n電話番号 : {%S_PHONE%}\r\n"
				),
        array('tpl_name' => "会員登録（ソーシャルログイン）",
                'tpl_trigger' => "ソーシャルログイン経由での会員登録",
                'subject' => "【{%SP_NAME%}】 会員登録ありがとうございます",
                'body_txt' => "お客様の会員登録が完了しました。\r\n\r\nEメールとパスワードでログインする場合には以下のパスワードをご利用ください\r\n----------------------------------------\r\n{%PASSWORD%}\r\n----------------------------------------\r\n\r\nまた、パスワードはこちらから変更いただけます。\r\n----------------------------------------\r\n{%URL%}\r\n----------------------------------------"
                ),
		array('tpl_name' => "入荷通知", 
				'tpl_trigger' => "在庫数をゼロから1以上に変更", 
				'subject' => "【{%SP_NAME%}】 {%P_NAME%}が入荷しました！", 
				'body_txt' => "{%C_NAME%}\r\n\r\nお待たせいたしました。\r\n{%P_NAME%} が入荷いたしましたのでご案内いたします。\r\n\r\n商品は以下のURLよりご購入いただけます。\r\n{%URL%}"
				),
		array('tpl_name' => ( fn_allowed_for('MULTIVENDOR')) ? "出品者登録の無効化" : "サプライヤー登録の無効化",
				'tpl_trigger' => ( fn_allowed_for('MULTIVENDOR')) ? "出品者登録の無効化" : "サプライヤー登録の無効化",
				'subject' => ( fn_allowed_for('MULTIVENDOR')) ? "【{%SP_NAME%}】 御社の出品者登録が無効化されました。" : "【{%SP_NAME%}】 御社のサプライヤー登録が無効化されました。",
				'body_txt' => ( fn_allowed_for('MULTIVENDOR')) ? "{%COMPANY_NAME%}様\r\n\r\n御社の出品者登録が無効化されました。\r\n\r\n" : "{%COMPANY_NAME%}様\r\n\r\n御社のサプライヤーが無効化されました。\r\n\r\n"
				),
		array('tpl_name' => ( fn_allowed_for('MULTIVENDOR')) ? "出品者登録の有効化" : "サプライヤー登録の有効化",
				'tpl_trigger' => ( fn_allowed_for('MULTIVENDOR')) ? "出品者登録の有効化" : "サプライヤー登録の有効化",
				'subject' => ( fn_allowed_for('MULTIVENDOR')) ? "【{%SP_NAME%}】 御社の出品者登録が有効化されました。" : "【{%SP_NAME%}】 御社のサプライヤー登録が有効化されました。",
				'body_txt' => ( fn_allowed_for('MULTIVENDOR')) ? "{%COMPANY_NAME%}様\r\n\r\n御社の出品者登録が有効化されました。\r\n" : "{%COMPANY_NAME%}様\r\n\r\n御社のサプライヤー登録が有効化されました。\r\n"
				),
		);

    // メールテンプレートの詳細（マーケットプレイス版）
    $mail_template_desc_mve = array(
        array('tpl_name' => "マーケットプレイスへの参加申請通知",
            'tpl_trigger' => "マーケットプレイスへの参加申請",
            'subject' => "【{%SP_NAME%}】 マーケットプレイスへの参加申請がありました",
            'body_txt' => "以下の内容でマーケットプレイスへの参加申請がありました。\r\n\r\n会社名：\r\n{%COMPANY_NAME%}\r\n\r\n会社の紹介文：\r\n{%COMPANY_DESCRIPTION%}\r\n\r\nEメールアドレス：\r\n{%COMPANY_EMAIL%}\r\n\r\nTEL：\r\n{%COMPANY_PHONE%} \r\n\r\n会社住所：\r\n〒{%COMPANY_ZIPCODE%} {%COMPANY_STATE%}{%COMPANY_CITY%}{%COMPANY_ADDRESS%}"
        ),
        array('tpl_name' => "出品者情報更新の承認待ち",
            'tpl_trigger' => "出品者による出品者情報の更新（管理者の承認が必要な場合のみ）",
            'subject' => "【{%SP_NAME%}】 出品者情報の更新は承認待ちの状態です。",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\n出品者情報が更新されました。更新内容がシステム管理者に承認されるまでは御社の出品者情報および取扱商品はマーケットプレイスに表示されません。\r\n"
        ),
        array('tpl_name' => "出品者登録申請の承認",
            'tpl_trigger' => "出品者ステータスを「新規」から「有効」に変更",
            'subject' => "【{%SP_NAME%}】 マーケットプレイスへの参加申請が承認されました。",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\nマーケットプレイスへの参加申請が承認されました。\r\n\r\n-------------------------------\r\n管理画面へのログイン情報\r\n-------------------------------\r\n{%COMPANY_LOGIN_INFO%}\r\n"
        ),
        array('tpl_name' => "出品者登録申請の却下",
            'tpl_trigger' => "出品者ステータスを「新規」から「拒否」に変更",
            'subject' => "【{%SP_NAME%}】 マーケットプレイスへの参加申請が却下されました。",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\n御社のマーケットプレイスへの参加申請が却下されました。\r\n"
        ),
        array('tpl_name' => "出品者登録申請の保留",
            'tpl_trigger' => "出品者ステータスを「新規」から「保留」に変更",
            'subject' => "【{%SP_NAME%}】 マーケットプレイスへの参加申請が承認されました。",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\nマーケットプレイスへの参加申請が承認されました。\r\n申請いただいた内容を後ほど弊社にてマーケットプレイスに登録いたします。\r\n\r\n-------------------------------\r\n管理画面へのログイン情報\r\n-------------------------------\r\n{%COMPANY_LOGIN_INFO%}\r\n"
        ),
        array('tpl_name' => "出品者情報登録・更新の承認",
            'tpl_trigger' => "出品者ステータスを「保留」から「有効」に変更",
            'subject' => "【{%SP_NAME%}】 出品者情報の登録・更新が承認されました。",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\n御社の出品者情報の登録または更新が承認され、マーケットプレイスに反映されました。\r\n"
        ),
        array('tpl_name' => "出品者情報登録・更新の却下",
            'tpl_trigger' => "出品者ステータスを「保留」から「無効」に変更",
            'subject' => "【{%SP_NAME%}】 出品者情報の登録・更新が却下されました。",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\n御社の出品者情報の登録または更新が却下されました。\r\n却下の理由などにつきましてはマーケットプレイス管理者までお問い合わせください。\r\n"
        ),
        array('tpl_name' => "出品者への支払通知",
            'tpl_trigger' => "出品者への支払情報の追加",
            'subject' => "【{%SP_NAME%}】 御社売上金額のお支払について",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\nお世話になります。\r\n\r\n{%SP_NAME%}における御社取扱商品の売上金額を以下の通りお支払いいたしました。\r\nお手数ですがご確認をお願いいたします。\r\n（当社規定の手数料を控除した金額をお支払いしております。）\r\n\r\n対象販売期間：\r\n{%PAYMENT_START_DATE%} - {%PAYMENT_END_DATE%}\r\n\r\nお支払い金額：\r\n{%PAYMENT_AMOUNT%}\r\n\r\nお支払い方法：\r\n{%PAYMENT_METHOD%}\r\n\r\nコメント：\r\n{%PAYMENT_COMMENTS%}\r\n"
        ),
        array('tpl_name' => "出品者取扱商品のステータス変更",
            'tpl_trigger' => "出品者取扱商品のウェブサイトへの掲載を承認または拒否",
            'subject' => "【{%SP_NAME%}】 御社取扱商品の掲載が{%COMPANY_PROD_STATUS%}されました。",
            'body_txt' => "{%COMPANY_NAME%}様\r\n\r\n{%COMPANY_P_BLK%}\r\n\r\n{%COMPANY_REASON%}"
        ),
		array('tpl_name' => "出品者登録時に自動作成される出品者データ管理者のログイン情報",
			'tpl_trigger' => "システム管理者による出品者の新規登録",
			'subject' => "【{%COMPANY_NAME%}】 出品者データ管理者登録のお知らせ",
			'body_txt' => "ご担当者様\r\n\r\n{%SP_NAME%} の出品者として {%COMPANY_NAME%} が登録されました。\r\n出品者用管理画面には以下の情報でログインできます。\r\n--------------------------------------------------------------------------------\r\nEメール : {%U_EMAIL%}\r\nパスワード : {%PASSWORD%}\r\nログインURL : {%LOGIN_URL%}\r\n--------------------------------------------------------------------------------"
		),
    );

    // メールテンプレートの詳細（フッター）
    $mail_template_desc_footer = array(
        array('tpl_name' => "【共通】 Eメールのフッター",
            'tpl_trigger' => "「フッターを表示する」がチェックされたメールの送信",
            'subject' => "Eメールのフッターに表示する情報を入力してください。",
            'body_txt' => "━━━━━━━━━━━━━━━━━━━━━━\r\n{%SP_NAME%}\r\n━━━━━━━━━━━━━━━━━━━━━━\r\nURL : {%SP_URL%}\r\ne-mail: {%SP_EMAIL_ADMIN%}\r\n住所 : 〒{%SP_ZIPCODE%} {%SP_STATE%}{%SP_CITY%}{%SP_ADDRESS%}\r\nTEL : {%SP_PHONE%}\r\n━━━━━━━━━━━━━━━━━━━━━━"
        ),
    );

    // マーケットプレイス版の場合
    if (fn_allowed_for('MULTIVENDOR')) {
        $mail_template_desc = array_merge($mail_template_desc_main, $mail_template_desc_mve, $mail_template_desc_footer);
    // マーケットプレイス版以外の場合
    }else{
        $mail_template_desc = array_merge($mail_template_desc_main, $mail_template_desc_footer);
    }

	// メールテンプレートを管理するテーブルを作成
	db_query("CREATE TABLE ?:jp_mtpl (tpl_id mediumint(8) unsigned NOT NULL auto_increment, tpl_code varchar(255) NOT NULL, status char(1) NOT NULL default 'A', PRIMARY KEY  (tpl_id)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
	db_query("CREATE TABLE ?:jp_mtpl_descriptions (tpl_id mediumint(8) unsigned NOT NULL, company_id mediumint(8) NOT NULL DEFAULT '0', tpl_name varchar(255) NOT NULL, tpl_trigger varchar(255) NOT NULL, subject varchar(255) NOT NULL, body_txt text NOT NULL, lang_code char(2) NOT NULL default 'EN', use_footer char(1) NOT NULL default 'Y', PRIMARY KEY  (tpl_id, company_id, lang_code)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

	// インストールされた言語を取得
	$languages = db_get_hash_array("SELECT * FROM ?:languages", 'lang_code');

	// メールテンプレートを管理するテーブルにデータをプリセット
	foreach($mail_templates as $_data){
		$tpl_id = db_query("REPLACE INTO ?:jp_mtpl ?e", $_data);

		foreach ($languages as $lc => $_v) {
			$array_num = $tpl_id - 1;
			$mail_template_desc[$array_num]['tpl_id'] = $tpl_id;
			$mail_template_desc[$array_num]['lang_code'] = $lc;
			db_query("REPLACE INTO ?:jp_mtpl_descriptions ?e", $mail_template_desc[$array_num]);
		}
	}

    if (fn_allowed_for('ULTIMATE')){
        // 登録済みのショップ（出品者）のIDを取得
        $company_ids = db_get_fields("SELECT company_id FROM ?:companies");

        // 登録済みのショップ（出品者）が存在する場合
        if( !empty($company_ids) ){
            // デフォルトのメールテンプレート情報を取得
            $default_mtpl_descs = db_get_array("SELECT * FROM ?:jp_mtpl_descriptions WHERE company_id = ?i", 0);

            // デフォルトのメールテンプレート情報が存在する場合
            if( !empty($default_mtpl_descs) && is_array($default_mtpl_descs) ){
                // デフォルトのメールテンプレート情報を新しいショップ（出品者）向けにコピー
                foreach($default_mtpl_descs as $default_mtpl_desc){
                    $_data = $default_mtpl_desc;
                    foreach( $company_ids as $company_id){
                        $_data['company_id'] = $company_id;
                        db_query("REPLACE INTO ?:jp_mtpl_descriptions ?e", $_data);
                    }
                }
            }
        }
    }
    /////////////////////////////////////////////////////////////////////////
	// メールテンプレート EOF
	/////////////////////////////////////////////////////////////////////////
}




/**
 * アドオンのアンインストール時の動作
 */
function fn_mtpl_addon_uninstall()
{
	// アドオンのアンインストールを許可しない
	//fn_set_notification('E', __('error'), __('jp_addons_unable_to_uninstall'));
	//fn_redirect('addons.manage', true);
}
##########################################################################################
// END アドオンのインストール・アンインストール時に動作する関数
##########################################################################################





##########################################################################################
// START その他の関数
##########################################################################################

/**
 * すべてのメールテンプレートで利用可能なテンプレート変数を取得
 *
 * @param $tpl_base_data
 * @return array
 */
function fn_mtpl_get_common_tpl_var($tpl_base_data)
{
	$mail_tpl_var_common = array();

	if( !empty($tpl_base_data['settings']) ){
		$shop_data = $tpl_base_data['settings']->value['Company'];
		$_edit_mail_tpl = false;
	}else{
		$shop_data = array();
		$_edit_mail_tpl = true;
	}


	// 利用可能なテンプレート変数を定義
	$mail_tpl_var_common = 
		array(
			'SP_NAME' => 
					array('desc' => 'vendor_name',
							'value' => !$_edit_mail_tpl ? $shop_data['company_name'] : ''),
			'SP_COUNRTY' => 
					array('desc' => 'mtpl_cm_shop_country', 
							'value' => (!$_edit_mail_tpl && !empty($shop_data['company_country_descr'])) ? $shop_data['company_country_descr'] : ''),
			'SP_ZIPCODE' => 
					array('desc' => 'mtpl_cm_shop_zipcode', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_zipcode'] : ''),
			'SP_STATE' => 
					array('desc' => 'mtpl_cm_shop_state', 
							'value' => (!$_edit_mail_tpl && !empty($shop_data['company_state_descr'])) ? $shop_data['company_state_descr'] : ''),
			'SP_CITY' => 
					array('desc' => 'mtpl_cm_shop_city', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_city'] : ''),
			'SP_ADDRESS' => 
					array('desc' => 'mtpl_cm_shop_address', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_address'] : ''),
			'SP_PHONE' => 
					array('desc' => 'mtpl_cm_shop_phone', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_phone'] : ''),
			'SP_PHONE2' => 
					array('desc' => 'mtpl_cm_shop_phone_2', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_phone_2'] : ''),
			'SP_FAX' => 
					array('desc' => 'mtpl_cm_shop_fax', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_fax'] : ''),
			'SP_URL' => 
					array('desc' => 'mtpl_cm_shop_url', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_website'] : ''),
			'SP_EMAIL_ADMIN' => 
					array('desc' => 'mtpl_cm_shop_email_admin', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_site_administrator'] : ''),
			'SP_EMAIL_CS' => 
					array('desc' => 'mtpl_cm_shop_email_cs', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_users_department'] : ''),
			'SP_EMAIL_ORDER' => 
					array('desc' => 'mtpl_cm_shop_email_order', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_orders_department'] : ''),
			'SP_EMAIL_SUPPORT' => 
					array('desc' => 'mtpl_cm_shop_email_support', 
							'value' => !$_edit_mail_tpl ? $shop_data['company_support_department'] : ''),
		);

	return $mail_tpl_var_common;
}




/**
 * メールテンプレート用データの更新
 *
 * @param $mail_template_data
 * @param int $tpl_id
 * @param $lang_code
 * @return int
 */
function fn_mtpl_update_mail_template($mail_template_data, $tpl_id = 0, $lang_code = CART_LANGUAGE)
{
	// we do not need empty title
	if (empty($mail_template_data['subject'])) {
		unset($mail_template_data['subject']);
	}

	if($mail_template_data['use_footer'] != 'Y'){
		$mail_template_data['use_footer'] = 'N';
	}

	$_data = $mail_template_data;

	db_query("UPDATE ?:jp_mtpl SET ?u WHERE tpl_id = ?i", $_data, $tpl_id);

    // 表示するショップ（出品者）のIDを抽出条件に加える
    if (fn_allowed_for('ULTIMATE')) {
        $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id');
    }else{
        $company_condition = '';
    }

	db_query("UPDATE ?:jp_mtpl_descriptions SET ?u WHERE tpl_id=?i AND lang_code=?s $company_condition", $_data, $tpl_id, $lang_code);

	fn_set_hook('update_mail_template', $mail_template_data, $tpl_id);

	return $tpl_id;
}




/**
 * 指定したメールテンプレートに関するデータの取得
 *
 * @param $tpl_id
 * @param $lang_code
 * @return array
 */
function fn_mtpl_get_mail_template_data($tpl_id, $lang_code = CART_LANGUAGE)
{
	$status_condition = (AREA == 'A') ? '' : " AND ?:jp_mtpl.status='A' ";

    // 表示するショップ（出品者）のIDを抽出条件に加える
    if (fn_allowed_for('ULTIMATE')) {
        $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id');
    }else{
        $company_condition = '';
    }

	$mail_template = db_get_row("SELECT * FROM ?:jp_mtpl LEFT JOIN ?:jp_mtpl_descriptions ON ?:jp_mtpl_descriptions.tpl_id = ?:jp_mtpl.tpl_id AND ?:jp_mtpl_descriptions.lang_code = ?s $company_condition WHERE ?:jp_mtpl.tpl_id = ?i $status_condition", $lang_code, $tpl_id);

	return $mail_template;
}




/**
 * メールテンプレート一覧用データの取得
 *
 * @param $params
 * @param int $items_per_page
 * @param $lang_code
 * @return array
 */
function fn_mtpl_get_mail_templates($params, $items_per_page = 0, $lang_code = CART_LANGUAGE)
{
    $default_params = array (
        'page' => 1,
        'items_per_page' => $items_per_page
    );

    // 表示するショップ（出品者）のIDを抽出条件に加える
    if (fn_allowed_for('ULTIMATE')) {
        $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id');
    }else{
        $company_condition = '';
    }

    $params = array_merge($default_params, $params);

    $limit = '';

	$page = empty($params['page']) ? 1 : $params['page'];

    if (!empty($params['items_per_page'])) {
        $params['total_items'] = db_get_field("SELECT COUNT(*) FROM ?:jp_mtpl");
        $limit = db_paginate($params['page'], $params['items_per_page']);
    }

    $limit = db_paginate($page, Registry::get('settings.Appearance.admin_elements_per_page'));

	$mail_templates = db_get_array("SELECT ?:jp_mtpl.tpl_id, ?:jp_mtpl.tpl_code, ?:jp_mtpl.status, ?:jp_mtpl_descriptions.tpl_name, ?:jp_mtpl_descriptions.tpl_trigger, ?:jp_mtpl_descriptions.subject, ?:jp_mtpl_descriptions.body_txt FROM ?:jp_mtpl LEFT JOIN ?:jp_mtpl_descriptions ON ?:jp_mtpl_descriptions.tpl_id=?:jp_mtpl.tpl_id AND ?:jp_mtpl_descriptions.lang_code= ?s $company_condition ORDER BY ?:jp_mtpl.tpl_id ASC, ?:jp_mtpl.status $limit", $lang_code);

    return array($mail_templates, $params);
}




/**
 * 指定されたメールテンプレートの文面を取得
 *
 * @param $tpl_code
 * @param $lang_code
 * @return array
 */
function fn_mtpl_get_email_contents($tpl_code, $lang_code = DESCR_SL, $order_id = '', $user_id = '')
{
    // 通常版CS-Cartの場合は表示するショップ（出品者）のIDを抽出条件に加える
    if (fn_allowed_for('ULTIMATE')) {
        // 注文IDが指定されている場合
        if( !empty($order_id) ){
			// 指定された注文に紐付けられたショップIDを取得
			$company_id = fn_get_company_id('orders', 'order_id', $order_id);

		// ユーザーIDが指定されている場合
		}elseif( !empty($user_id) ){
			// 指定されたユーザーに紐付けられたショップIDを取得
			$company_id = fn_get_company_id('users', 'user_id', $user_id);

        // 注文IDが指定されていない場合
        }else{
            // ショップIDを取得
            $company_id = Registry::get('runtime.company_id');
        }

        // ショップIDが0（ショップ情報無し）の場合
        if ( empty($company_id) ){
            // CS-Cartに唯一登録されているショップのIDを取得
            $forced_company_id = Registry::get('runtime.forced_company_id');
            // CS-Cartに唯一登録されているショップのIDに紐付いたメールテンプレートを抽出
            $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id', true, $forced_company_id);
        // ショップIDが0以外の場合
        }else{
            // 注文IDまたはユーザーIDが指定されている場合
            if( !empty($order_id) || !empty($user_id) ){
                // 指定された注文IDまたはユーザーIDに紐付けられたショップIDを抽出条件に追加
                $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id', true, $company_id);
            // 注文IDが指定されていない場合
            }else{
                $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id');
            }
        }
    }else{
        $company_condition = '';
    }

    // メールテンプレートコードと注文が実行された際のお客様選択言語コードでメールテンプレートを抽出
    $email_contents = db_get_row("SELECT * FROM ?:jp_mtpl LEFT JOIN ?:jp_mtpl_descriptions ON ?:jp_mtpl_descriptions.tpl_id = ?:jp_mtpl.tpl_id AND ?:jp_mtpl_descriptions.lang_code = ?s $company_condition WHERE ?:jp_mtpl.tpl_code = ?s", $lang_code, $tpl_code);

    return $email_contents;
}




/**
 * メールフッターの内容を取得
 *
 * @param $lang_code
 * @return array
 */
function fn_mtpl_get_email_footer($lang_code)
{
    // 表示するショップ（出品者）のIDを抽出条件に加える
    if (fn_allowed_for('ULTIMATE')) {
        // ショップIDを取得
        $company_id = Registry::get('runtime.company_id');

        // ショップIDが0（ショップ情報無し）の場合
        if ( empty($company_id) ){
            // CS-Cartに唯一登録されているショップのIDを取得
            $forced_company_id = Registry::get('runtime.forced_company_id');
            // CS-Cartに唯一登録されているショップのIDに紐付いたメールテンプレートを抽出
            $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id', true, $forced_company_id);
        }else{
            $company_condition = fn_get_company_condition('?:jp_mtpl_descriptions.company_id');
        }
    }else{
        $company_condition = '';
    }

	return db_get_field("SELECT body_txt FROM ?:jp_mtpl LEFT JOIN ?:jp_mtpl_descriptions ON ?:jp_mtpl_descriptions.tpl_id = ?:jp_mtpl.tpl_id AND ?:jp_mtpl_descriptions.lang_code = ?s WHERE ?:jp_mtpl.tpl_code = ?s $company_condition", $lang_code, 'mtpl_email_footer');

}




/**
 * 有効なユーザーグループ名を取得
 *
 * @param array $usergroup_array
 * @param $lang_code
 * @return string
 */
function fn_mtpl_get_usrgroups($usergroup_array = array(), $lang_code = CART_LANGUAGE)
{
	$usergroup_names = array();

	if(!empty($usergroup_array)){
		// ステータスが有効なユーザーグループ名を取得
		foreach($usergroup_array as $k => $v){
			if($v['status'] == 'A'){
				$usergroup_names[] = db_get_field("SELECT usergroup FROM ?:usergroup_descriptions WHERE usergroup_id = ?i AND lang_code = ?s", $k, $lang_code);
			}
		}
	}

	return implode(' / ', $usergroup_names);
}




/**
 * 有効化 / 無効化されたユーザーグループ名を取得
 *
 * @param array $usergroup_array
 * @param $lang_code
 * @return string
 */
function fn_mtpl_get_processed_usrgroups($usergroup_array = array(), $lang_code = CART_LANGUAGE)
{
	$usergroup_names = array();

	if(!empty($usergroup_array)){
		// ステータスが有効なユーザーグループ名を取得
		foreach($usergroup_array as $usergroup_id){
			$usergroup_names[] = db_get_field("SELECT usergroup FROM ?:usergroup_descriptions WHERE usergroup_id = ?i AND lang_code = ?s", $usergroup_id, $lang_code);
		}
	}

	return implode(' / ', $usergroup_names);
}




/**
 * 運送会社名を取得
 *
 * @param string $carrier_code
 * @param $lang_code
 * @return string
 */
function fn_mtpl_get_carrier_name($carrier_code = '', $lang_code = CART_LANGUAGE)
{
	$carrier_name = '';

	if( !empty($carrier_code) ){
		switch ($carrier_code) {
			case 'yamato':
				if($lang_code = 'ja'){
					$carrier_name = 'ヤマト運輸';
				}else{
					$carrier_name = 'Yamato Transport';
				}
				break;
			case 'sagawa':
				if($lang_code = 'ja'){
					$carrier_name = '佐川急便';
				}else{
					$carrier_name = 'Sagawa Express';
				}
				break;
			case 'jpost':
				if($lang_code = 'ja'){
					$carrier_name = '日本郵便';
				}else{
					$carrier_name = 'Japan Post';
				}
				break;
			case 'fukutsu':
				if($lang_code = 'ja'){
					$carrier_name = '福山通運';
				}else{
					$carrier_name = 'Fukuyama Transporting';
				}
				break;
			case 'jpems':
				if($lang_code = 'ja'){
					$carrier_name = '日本郵便 (海外向け)';
				}else{
					$carrier_name = 'Japan Post (Overseas)';
				}
				break;
            default:
                $carrier_name = __($carrier_code);
		}
	}

	return $carrier_name;
}




/**
 * 各運送会社の配達状況確認確認URLを取得
 *
 * @param string $carrier_code
 * @param string $tracking_number
 * @return string
 */
function fn_mtpl_get_shipment_tracking_url($carrier_code = '', $tracking_number = '')
{
	$trackint_url = 'N/A';

	if( !empty($carrier_code) && !empty($tracking_number) ){
		switch ($carrier_code) {
			case 'yamato':
				$trackint_url = "http://jizen.kuronekoyamato.co.jp/jizen/servlet/crjz.b.NQ0010?id=" . $tracking_number;
				break;
			case 'sagawa':
				$trackint_url = "http://k2k.sagawa-exp.co.jp/p/web/okurijosearch.do?okurijoNo=" . $tracking_number;
				break;
			case 'jpost':
				$trackint_url = "http://tracking.post.japanpost.jp/service/singleSearch.do?org.apache.struts.taglib.html.TOKEN=&searchKind=S002&locale=ja&SVID=&reqCodeNo1=" . $tracking_number;
				break;
			case 'jpems':
				$trackint_url = "http://tracking.post.japanpost.jp/service/singleSearch.do?searchKind=S004&locale=ja&reqCodeNo1=" . $tracking_number;
				break;
		}
	}

	return $trackint_url;
}




/**
 * 表示金額を取得
 *
 * @param $price
 * @param $tpl_base_data
 * @return string
 */
function fn_mtpl_get_display_price($price, $tpl_base_data)
{
	$display_price = '';

	$tpl_settings_general = $tpl_base_data['settings']->value['General'];
	$tpl_currencies = $tpl_base_data['currencies']->value;
	$tpl_primary_currency = $tpl_base_data['primary_currency']->value;
	$tpl_secondary_currency = $tpl_base_data['secondary_currency']->value;

	if($tpl_settings_general['alternative_currency'] == 'Y'){

		$display_price .= fn_mtpl_format_price($price, $tpl_currencies[$tpl_primary_currency]);

		if($tpl_primary_currency != $tpl_primary_currency){
			$display_price .= ' (' . fn_mtpl_format_price($price, $tpl_currencies[$tpl_secondary_currency]) . ')';
		}

	}else{
		$display_price .= fn_mtpl_format_price($price, $tpl_currencies[$tpl_secondary_currency]);
	}

	return $display_price;
}




/**
 * 金額をフォーマット
 *
 * @param $price
 * @param $currency
 * @return string
 */
function fn_mtpl_format_price($price, $currency)
{
	$glue = '';
	$value = fn_format_rate_value($price, 'F', $currency['decimals'], $currency['decimals_separator'], $currency['thousands_separator'], $currency['coefficient']);

	$data = array($value);

	// 通貨記号がテキストメールで文字化けする通貨は通貨コードを表示する
	if ($currency['after'] == 'Y') {
		if( strtoupper($currency['currency_code']) == 'JPY' || strtoupper($currency['currency_code']) == 'USD' ){
			array_push($data, $currency['symbol']);
		}else{
			array_push($data, $currency['currency_code']);
		}
	} else {
		if( strtoupper($currency['currency_code']) == 'JPY' || strtoupper($currency['currency_code']) == 'USD' ){
			array_unshift($data, $currency['symbol']);
		}else{
			array_unshift($data, $currency['currency_code']);
			$glue = ' ';
		}
	}

	return implode($glue, $data);
}




/**
 * 注文商品を出力
 *
 * @param $items_ordered
 * @param $tpl_base_data
 * @param string $type
 * @param null $supplier_id
 * @return string
 */
function fn_mtpl_get_ordered_products($items_ordered, $tpl_base_data, $type = 'NORMAL', $supplier_id = null)
{
	// 注文商品情報を格納する変数を初期化
	$ordered_products = '';

	// 注文商品
	if( !empty($items_ordered) ){

		$cnt =0;
		foreach($items_ordered as $k => $v){

			// サプライヤーに通知する場合には当該サプライヤーの担当商品のみ抽出
            if( $v > 0 && ( $type == 'NORMAL' || ($type == 'SUPPLIERS' && $v['extra']['supplier_id'] == $supplier_id) ) ){
                $cnt++;
                $ordered_products .= $cnt . ". " . SecurityHelper::escapeHtml($v['product'], true);
                $ordered_products .= " : " . fn_mtpl_get_display_price($v['price'], $tpl_base_data) . " × " . $v['amount'] . " = ";
                $ordered_products .= !empty($v['extra']['exclude_from_calculate']) ? __('free') : fn_mtpl_get_display_price($v['display_subtotal'], $tpl_base_data);

                if( !empty($v['product_options']) ){
                    $ordered_products .= "\n　" . __('options', null, $tpl_base_data['order_info']->value['lang_code']) . ' - ';
                    $ordered_products .= fn_mtpl_get_product_options_block($v['product_options'], $tpl_base_data);
                }

                if( $cnt < sizeof($items_ordered) ){
                    $ordered_products .= "\n\n";
                }
			}
		}

		// 不要な改行を削除
		$ordered_products = rtrim($ordered_products, "\n\n");
	}

	// 注文商品の出力内容を変更するためのフックポイント
	fn_set_hook('post_mtpl_get_ordered_products', $ordered_products, $items_ordered, $tpl_base_data, $type, $supplier_id);

	return $ordered_products;
}




/**
 * 発送商品を出力
 *
 * @param $items_ordered
 * @param $items_shipped
 * @param $tpl_base_data
 * @return string
 */
function fn_mtpl_get_shipped_products($items_ordered, $items_shipped, $tpl_base_data)
{
	// 発送商品情報を格納する変数を初期化
	$shipped_products = '';

	// 発送商品
	if( !empty($items_shipped) ){

		$cnt =0;
		foreach($items_shipped as $k => $v){
			if($v > 0){
				$cnt++;

				$shipped_products .= $v . ' x ' . SecurityHelper::escapeHtml($items_ordered[$k]['product'], true);

				if($items_ordered[$k]['product_options']){
					$shipped_products .=  "\n　" . __('options', null, $tpl_base_data['order_info']->value['lang_code']) . ' - ';

					$shipped_products .= fn_mtpl_get_product_options_block($items_ordered[$k]['product_options'], $tpl_base_data);
				}

				if( $cnt < sizeof($items_shipped) ){
					$shipped_products .= "\n\n";
				}
			}
		}
	}

	return $shipped_products;
}




/**
 * 返品申請商品を出力
 *
 * @param $items_ordered
 * @param $tpl_return_info
 * @param $tpl_base_data
 * @return string
 */
function fn_mtpl_get_return_products($items_ordered, $tpl_return_info, $tpl_base_data)
{
	$items_returned = $tpl_return_info['items'][RETURN_PRODUCT_ACCEPTED];

	// 返品申請商品を格納する変数を初期化
	$returned_products = '';

	// 返品申請商品
	if( !empty($items_returned) ){

		$cnt =0;
		foreach($items_returned as $k => $v){
			if($v > 0){
				$cnt++;

				$returned_products .= $v['amount'] . ' x ' . $v['product'];
				$returned_products .= ($items_ordered[$k]['product_code']) ? ( ' (' . __('sku', null, $tpl_base_data['order_info']->value['lang_code']) . ':' . SecurityHelper::escapeHtml($items_ordered[$k]['product_code'], true) . ')') : '';

				if($v['product_options']){
					$returned_products .=  "\n　" . __('options', null, $tpl_base_data['order_info']->value['lang_code']) . ' - ';
					$returned_products .= fn_mtpl_get_product_options_block($v['product_options'], $tpl_base_data);
				}

				$returned_products .= "\n";
				$returned_products .= (!$v['price']) ? __('free', null, $tpl_base_data['order_info']->value['lang_code']) : __('price', null, $tpl_base_data['order_info']->value['lang_code']) . ' : ' . fn_mtpl_get_display_price($v['price'], $tpl_base_data);
				$returned_products .= "\n";

				$returned_products .= __('reason', null, $tpl_base_data['order_info']->value['lang_code']) . ' : ' . fn_mtpl_get_rma_property($v['reason'], $tpl_base_data['order_info']->value['lang_code']);

				if( $cnt < sizeof($items_returned) ){
					$returned_products .= "\n\n";
				}
			}
		}
	}else{
		$returned_products = __('text_no_products_found', null, $tpl_base_data['order_info']->value['lang_code']);
	}

	return $returned_products;
}




/**
 * 返品時の希望の対応方法や返品理由を取得
 *
 * @param $property_id
 * @param $lang_code
 * @return array
 */
function fn_mtpl_get_rma_property($property_id, $lang_code)
{
	$property_name = db_get_field("SELECT property FROM ?:rma_property_descriptions WHERE property_id = ?i AND lang_code = ?s", $property_id, $lang_code);
	return $property_name;
}




/**
 * 商品オプションを出力
 *
 * @param $product_options
 * @param $tpl_base_data
 * @return string
 */
function fn_mtpl_get_product_options_block($product_options, $tpl_base_data)
{
	$tpl_settings = $tpl_base_data['settings']->value;
	$tpl_currencies = $tpl_base_data['currencies']->value;
	$tpl_primary_currency = $tpl_base_data['primary_currency']->value;
	$tpl_secondary_currency = $tpl_base_data['secondary_currency']->value;

	$options_variants = array();

	foreach($product_options as $product_option){

		$options_variant = $product_option['option_name'] . ' : ' . $product_option['variant_name'];

		if( !empty($product_option['modifier']) && floatval($product_option['modifier']) > 0 ){

			$options_variant .= ' ('; 

			if($product_option['modifier'] > 0){
				$options_variant .= '+';
			}else{
				$options_variant .= '-';
			}

			if($product_option['modifier_type'] == 'A' || $product_option['modifier_type'] == 'F'){
				$options_variant .= fn_mtpl_get_display_price(abs($product_option['modifier']), $tpl_base_data);
			}
			$options_variant .= ')';
		}

		$options_variants[] = $options_variant;
	}

	return implode(', ', $options_variants);
}




/**
 * 配送情報を取得
 *
 * @param $order_info
 * @param null $supplier_id
 * @return array
 */
function fn_mtpl_get_shipping_info($order_info, $supplier_id = null)
{
	$shipping_info = array();
    $shippings = $order_info['shipping'];

	if( !empty($shippings) ){

        // サプライヤー/出品者への発送依頼の場合
        if( !empty($supplier_id) ){
            // 当該サプライヤー以外の商品に関する配送情報を削除
            foreach($shippings as $key => $shipping){
                if($key != $supplier_id){
                    unset($shippings[$key]);
                }
            }
        }

		$shipping_methods = array();
		$tracking_nums = array();
        $delivery_dates = array();
        $delivery_timings = array();

		foreach($shippings as $shipping){
			$shipping_methods[] = $shipping['shipping'];
            $tracking_numbers = fn_mtpl_get_tracking_numbers($shipping['shipping_id'], $order_info['shipment_ids']);
            if( !empty($tracking_numbers) && is_array($tracking_numbers) ){
                foreach($tracking_numbers as $tracking_number){
                    $tracking_nums[] = $tracking_number;
                }
            }

            if( !empty($shipping['delivery_date']) ){
                $delivery_dates[] = $shipping['delivery_date'];
            }

            if( !empty($shipping['delivery_timing']) ){
                $delivery_timings[] = $shipping['delivery_timing'];
            }
		}

		$shipping_info = array('shipping_method' => !empty($shipping_methods) ? implode(', ', $shipping_methods) : '',
                                'tracking_number' => !empty($tracking_nums) ? implode(', ', $tracking_nums) : __('mtpl_tbd'),
                                'delivery_date' => !empty($delivery_dates) ? implode(', ', $delivery_dates) : __('jp_not_specified'),
                                'delivery_timing' => !empty($delivery_timings) ? implode(', ', $delivery_timings) : __('jp_not_specified')
        );
	}

	return $shipping_info;
}




/**
 * 注文情報で保持しているユーザー情報を取得
 *
 * @param $order_info
 * @param $lang_code
 * @return array
 */
function fn_mtpl_get_invoice_profile_fields($order_info = array(), $lang_code = CART_LANGUAGE)
{
	$invoice_profile_fields = array();

	// ユーザーフィールドに関する情報
	$profile_fields = fn_get_profile_fields('I', '', $lang_code);

	// 連絡先情報
	if( !empty($profile_fields['C']) ){
		$profields_c = fn_fields_from_multi_level($profile_fields['C'], 'field_name', 'field_id');

		foreach( $profields_c as $profield_c_key => $profield_c_val ){
			if( !empty($order_info[$profield_c_key]) ){
				$invoice_profile_fields['c_' . $profield_c_key] = $profields_c[$profield_c_key] ? $order_info[$profield_c_key] : '';
			}else{
				$invoice_profile_fields['c_' . $profield_c_key] = '';
			}
		}

		$invoice_profile_fields['c_extra_fields'] = fn_mtpl_get_profiles_extra_fields_by_order($profile_fields['C'], $order_info);
	}

	// 請求先情報
	if( !empty($profile_fields['B']) ){
		$profields_b = fn_fields_from_multi_level($profile_fields['B'], 'field_name', 'field_id');

		foreach( $profields_b as $profield_b_key => $profield_b_val ){
			switch($profield_b_key){
				case 'b_country':
				case 'b_state':
					if( !empty($order_info[$profield_b_key . '_descr']) ){
						$invoice_profile_fields[$profield_b_key] = $order_info[$profield_b_key . '_descr'];
					}else{
						$invoice_profile_fields[$profield_b_key] = '';
					}
					break;
				default:
					if( !empty($order_info[$profield_b_key]) ){
						$invoice_profile_fields[$profield_b_key] = $order_info[$profield_b_key] ? $order_info[$profield_b_key] : '';
					}else{
						$invoice_profile_fields[$profield_b_key] = '';
					}
			}
		}

		$invoice_profile_fields['b_extra_fields'] = fn_mtpl_get_profiles_extra_fields_by_order($profile_fields['B'], $order_info);
	}

	// 配送先情報
	if( !empty($profile_fields['S']) ){
		$profields_s = fn_fields_from_multi_level($profile_fields['S'], 'field_name', 'field_id');

		foreach( $profields_s as $profield_s_key => $profield_s_val ){
			switch($profield_s_key){
				case 's_country':
				case 's_state':
					if( !empty($order_info[$profield_s_key . '_descr']) ){
						$invoice_profile_fields[$profield_s_key] = $order_info[$profield_s_key . '_descr'];
					}else{
						$invoice_profile_fields[$profield_s_key] = '';
					}
					break;
				default:
					if( !empty($order_info[$profield_s_key]) ){
						$invoice_profile_fields[$profield_s_key] = $order_info[$profield_s_key];
					}else{
						$invoice_profile_fields[$profield_s_key] = '';
					}
			}
		}

		$invoice_profile_fields['s_extra_fields'] = fn_mtpl_get_profiles_extra_fields_by_order($profile_fields['S'], $order_info);
	}

	return $invoice_profile_fields;
}




/**
 * 注文情報に含まれるユーザー追加フィールドの取得
 *
 * @param $fields
 * @param $order_info
 * @return array
 */
function fn_mtpl_get_profiles_extra_fields_by_order($fields, $order_info = array())
{
	$extra_fields = array();

	foreach($fields as $field){

		// フィールド名を持たないフィールドをユーザー追加フィールドと見なす
		if(!$field['field_name']){

			// フィールドIDと説明文を連想配列のキーにする
			$key = $field['field_id'];
			$field_desc = $field['description'];
			if(!empty($order_info)){
				$field_val = $order_info['fields'][$field['field_id']];
			}else{
				$field_val = '';
			}

			if($field['field_type'] == 'C'){
				$field_val = ($field_val == 'Y') ? __('yes') : __('no');

			}elseif($field['field_type'] == 'D'){
				$field_val = date('Y/m/d', $field_val);

			}elseif( strpos('RS', $field['field_type']) !== false ){
				$field_val = $field['values'][$field_val];

			}else{
				$field_val = !empty($field_val) ? $field_val : '';
			}

			$extra_fields[$key] = array(
												'field_desc' => $field_desc,
												'field_val' => $field_val
												);
		}
	}

	return $extra_fields;
}




/**
 * 会員情報に含まれるユーザー追加フィールドの取得
 *
 * @param $tpl_user_data
 * @param $section
 * @return array
 */
function fn_mtpl_get_profiles_extra_fields($tpl_user_data, $section)
{
	$extra_fields = array();

	// メールテンプレート管理画面
	if(!$tpl_user_data){

		// ユーザー追加フィールドのIDを取得
		$extra_field_ids = db_get_fields("SELECT field_id FROM ?:profile_fields WHERE field_name = '' AND section = ?s", $section);

		// ユーザー追加フィールドの情報を取得
		foreach($extra_field_ids as $extra_field_id){

			$extra_field_desc = db_get_field("SELECT description FROM ?:profile_field_descriptions WHERE object_id = ?i AND lang_code = ?s", $extra_field_id, Registry::get('settings.Appearance.backend_default_language'));

			$extra_fields[$extra_field_id] = array(
											'field_desc' => $extra_field_desc,
											'field_val' => ''
											);
		}

	// メールテンプレートを利用したメール送信時
	}else{

		// ユーザー追加フィールドの情報を取得
		foreach($tpl_user_data['fields'] as $field_id => $field_val){

			$extra_field_desc = db_get_field("SELECT ?:profile_field_descriptions.description FROM ?:profile_fields LEFT JOIN ?:profile_field_descriptions ON ?:profile_field_descriptions.object_id = ?:profile_fields.field_id AND ?:profile_field_descriptions.lang_code = ?s WHERE ?:profile_fields.field_id = ?i AND ?:profile_fields.section = ?s", $tpl_user_data['lang_code'], $field_id, $section);

			if($extra_field_desc){
				$extra_fields[$field_id] = array(
												'field_desc' => $extra_field_desc,
												'field_val' => $field_val
												);
			}
		}
	}

	return $extra_fields;
}




/**
 * フォームビルダーからの送信内容を取得
 *
 * @param $form_value
 * @param $element_type
 * @return array|string
 */
function fn_mtpl_get_form_value($form_value, $element_type)
{
	switch($element_type){
		case 'S':
		case 'R':
			$form_value = db_get_field("SELECT description FROM ?:form_descriptions WHERE object_id = ?i AND lang_code= ?s", $form_value, CART_LANGUAGE);
			break;

		case 'M':
		case 'N':
			$form_values = array();
			foreach($form_value as $v){
				$form_values[] = db_get_field("SELECT description FROM ?:form_descriptions WHERE object_id = ?i AND lang_code= ?s", $v, CART_LANGUAGE);
			}
			$form_value = implode(", ", $form_values);
			break;

		case 'V':
			$form_value = fn_date_format($form_value, Registry::get('settings.Appearance.date_format'));
			break;

		default:
			// do nothing
			break;
	}

	return $form_value;
}




/**
 * 割引きや送料、各種手数料に関する情報を取得
 *
 * @param $order_info
 * @param $tpl_base_data
 * @return string
 */
function fn_mtpl_get_ot_misc($order_info, $tpl_base_data)
{
	$ot_misc = array();

	$lang_code = $tpl_base_data['order_info']->value['lang_code'];

	// 送料
	if( !empty($order_info['shipping']) ){
		$ot_misc[] = __('shipping_cost', null, $lang_code) . ' : ' .  fn_mtpl_get_display_price($order_info['display_shipping_cost'], $tpl_base_data);
	}

	// ご注文割引
	if( !empty($order_info['subtotal_discount']) && floatval($order_info['subtotal_discount']) > 0 ){
		// 利用ポイント数が存在する場合
		if( !empty($order_info['points_info']['in_use']) ){
			// メールに表記する「ご注文割引」金額から利用ポイント金額をマイナス
			$order_info['subtotal_discount'] = $order_info['subtotal_discount'] - $order_info['points_info']['in_use']['cost'];
		}
		$ot_misc[] = __('order_discount', null, $lang_code) . ' : -' .  fn_mtpl_get_display_price($order_info['subtotal_discount'], $tpl_base_data);
	}

	// 手数料
	if( !empty($order_info['subtotal_surcharge']) && floatval($order_info['subtotal_surcharge']) > 0 ){
		$ot_misc[] = __('order_surcharge', null, $lang_code) . ' : ' .  fn_mtpl_get_display_price($order_info['subtotal_surcharge'], $tpl_base_data);
	}

	// クーポン
	if( !empty($order_info['coupons']) ){
		foreach($order_info['coupons'] as $key => $val){
			$ot_misc[] = '  - ' . __('coupon_code', null, $lang_code) . ' : ' .  $key;
		}
	}

	// ギフト券
	if( !empty($order_info['use_gift_certificates']) ){
		foreach($order_info['use_gift_certificates'] as $code => $certificate){
			$ot_misc[] =
				__('gift_certificates', null, $lang_code) .
				' (' . $code . ')' . ' : -' . fn_mtpl_get_display_price($certificate['cost'], $tpl_base_data);
		}
	}

	// 利用ポイント数
	if( !empty($order_info['points_info']['in_use']) ){
		$ot_misc[] =
			__('points_in_use', null, $lang_code) .
			' (' . $order_info['points_info']['in_use']['points'] . __('points', null, $lang_code) . ')' . ' : -' .
			fn_mtpl_get_display_price($order_info['points_info']['in_use']['cost'], $tpl_base_data);
	}

	// 免税
	if( !empty($order_info['tax_exempt']) && $order_info['tax_exempt'] == 'Y' ){
		$ot_misc[] = __('tax_exempt', null, $lang_code);
	}

	// 税金
	if( !empty($order_info['taxes']) ){

		foreach($order_info['taxes'] as $tax_data){

			// 内税の場合は税額は表記しない
			if($tax_data['price_includes_tax'] != 'Y'){
				$tax = $tax_data['description'];
				$tax_included = '';

				if($tax_data['rate_type'] == 'A' || $tax_data['rate_type'] == 'F'){
					$tax .= ' ' . $tax_included . fn_mtpl_get_display_price(abs($tax_data['rate_value']), $tpl_base_data);
				}else{
					$tax .= '(' . $tax_included . abs($tax_data['rate_value']) . "%) :";
				}

				if($tax_data['regnumber']){
					$tax .= ' (' . $tax_data['regnumber'] . ')';
				}

				$tax .= ' ' . fn_mtpl_get_display_price($tax_data['tax_subtotal'], $tpl_base_data);

				$ot_misc[] = $tax;
			} 
		}
	}

	// 支払手数料
	if( !empty($order_info['payment_surcharge']) && floatval($order_info['payment_surcharge']) > 0 ){
		$ot_misc[] = __('payment_surcharge', null, $lang_code) . ' : ' .  fn_mtpl_get_display_price($order_info['payment_surcharge'], $tpl_base_data);
	}

	// 獲得ポイント数
	if( !empty($order_info['points_info']['reward']) ){
		$ot_misc[] = __('jp_mtpl_reward_points_aquired', null, $lang_code) . ' : ' . $order_info['points_info']['reward'];
	}

	return implode("\n", $ot_misc);
}




/**
 * smarty_modifier_wordwrapのコピー
 *
 * @param $str
 * @param int $width
 * @param string $break
 * @param bool $cut
 * @return string
 */
function fn_mtpl_wordwrap($str, $width = 80, $break = "\n", $cut = false)
{
	if (!$cut) {
		$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){' . $width . ',}\b#U';
	} else {
		$regexp = '#^(?:[\x00-\x7F]|[\xC0-\xFF][\x80-\xBF]+){' . $width . '}#';
	}

	$parts = explode($break, $str);

	$new_str = array();
	foreach ($parts as $val) {
		if (function_exists('mb_strlen')) {
			$str_len = mb_strlen($val, 'UTF-8');
		} else {
			$str_len = preg_match_all('/[\x00-\x7F\xC0-\xFD]/', $val, $var_empty);
		}

		$while_what = ceil($str_len / $width);
		$i = 1;
		$return = '';
		$_str = $val;

		while ($i < $while_what) {
			preg_match($regexp, $_str, $matches);
			$string = $matches[0];
			$return .= $string . $break;
			$_str = substr($_str, strlen($string));
			$i++;
		}
		$new_str[] = $return . $_str;
	}

	return join($break, $new_str);
}




/**
 * レビュー評価の取得
 *
 * @param $rating_value
 * @return string
 */
function fn_mtpl_get_review($rating_value)
{
	$rating_desc = '';

	switch((int)$rating_value){
		case 5:
			$rating_desc = 'excellent';
			break;
		case 4:
			$rating_desc = 'very_good';
			break;
		case 3:
			$rating_desc = 'average';
			break;
		case 2:
			$rating_desc = 'fair';
			break;
		case 1:
			$rating_desc = 'poor';
			break;
		default:
			// do nothing
			break;
	}

	return __($rating_desc);
}




/**
 * ギフト券送付先の取得
 *
 * @param $gift_cert_data
 * @return string
 */
function fn_mtpl_get_gc_address($gift_cert_data)
{
	$gift_cert_data = '';

	if( !empty($gift_cert_data) ){
		$gc_address = __('desc') . " : \n";
		$gc_address .= $gift_cert_data('zipcode') . ' ';
		$gc_address .= $gift_cert_data('descr_state') . $gift_cert_data('city') . $gift_cert_data('address');
		$gc_address .= ( $gift_cert_data('address_2') ? "\n" . $gift_cert_data('address_2') : '');
		$gc_address .= ( $gift_cert_data('phone') ? "\n" . $gift_cert_data('phone') : '');
	}

	return $gc_address;
}




/**
 * ギフト券に付与された無料商品の取得
 *
 * @param $products
 * @param $tpl_base_data
 * @return string
 */
function fn_mtpl_get_free_products($products, $tpl_base_data)
{
	$free_products = '';

	if( !empty($products) ){
		$free_products .= __('free_products') . " :\n";

		foreach($products as $product){
			$free_products .= $product['amount'] . ' x ' . fn_get_product_name($product['product_id']) . "\n";
			if( !empty($product['product_options']) ){
				$free_products .= '  ' . fn_mtpl_get_product_options_block( fn_get_selected_product_options_info($product['product_options']), $tpl_base_data) . "\n";
			}
		}

	}

	return rtrim($free_products, "\n");
}




/**
 * Eメールからお客様名を取得
 *
 *
 * @param $email_to
 * @return string
 */
function fn_mtpl_get_customer_name_by_email($email_to)
{
    if(is_array($email_to)){
        $email_to = array_shift($email_to);
    }

	$customer_name = '';
	$customer = db_get_row("SELECT * FROM ?:users WHERE email = ?s", $email_to);

	if( !empty($customer) ){
		if( $customer['lang_code'] == 'ja' ){
			$customer_name = $customer['firstname'] . ' ' . $customer['lastname'] . __('dear');
		}else{
			$customer_name = __('dear') . ' ' . $customer['firstname'] . ' ' . $customer['lastname'] . ',';
		}
	}else{
		if( CART_LANGUAGE == 'ja' ){
			$customer_name = __('customer');
		}else{
			$customer_name = __('dear') . ' ' . __('customer') . ',';
		}
	}

	return $customer_name; 
}




/**
 * 配送番号と配送方法から追跡番号を取得
 *
 * @param null $shipping_id
 * @param null $shipment_ids
 * @return array|bool
 */
function fn_mtpl_get_tracking_numbers($shipping_id = null, $shipment_ids = null)
{
    if(empty($shipping_id) || empty($shipment_ids)) return false;
    $tracking_numbers = array();

    foreach($shipment_ids as $shipment_id){
        $tracking_number = db_get_field("SELECT tracking_number FROM ?:shipments WHERE shipping_id = ?i AND shipment_id= ?i", $shipping_id, $shipment_id);
        if(!empty($tracking_number)){
            $tracking_numbers[] = $tracking_number;
        }
    }

    return $tracking_numbers;
}
##########################################################################################
// END その他の関数
##########################################################################################
