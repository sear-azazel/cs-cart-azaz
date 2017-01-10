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

// $Id: companies_status_n_a_notification.php by tommy from cs-cart.jp 2013
// マーケットプレイスの参加申請ステータスを「新規」から「有効」に変更した際に
// 送信されるメールで使用可能なテンプレート変数


/////////////////////////////////////////////////////////////////////////////
// データ取得 BOF
/////////////////////////////////////////////////////////////////////////////
// 出品者情報
$tpl_company_data = $tpl_base_data['company_data']->value;

// 出品者データ管理ページURL
$tpl_vendor_area = fn_url('', 'V', 'http');
/////////////////////////////////////////////////////////////////////////////
// データ取得 EOF
/////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////
// メールテンプレート取得 BOF
/////////////////////////////////////////////////////////////////////////////
// メールテンプレートコードと出品者の言語コードでメールテンプレートを抽出
$mtpl_lang_code = $tpl_company_data['lang_code'];
$mail_template = fn_mtpl_get_email_contents($tpl_code, $mtpl_lang_code);
/////////////////////////////////////////////////////////////////////////////
// メールテンプレート取得 EOF
/////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////
// ログイン情報 BOF
/////////////////////////////////////////////////////////////////////////////
$tpl_base_account = $tpl_base_data['e_account']->value;
$tpl_base_username = $tpl_base_data['e_username']->value;
$tpl_base_password = $tpl_base_data['e_password']->value;

// 通常ユーザーから出品者データ管理者に変更された場合
if( $tpl_base_account == 'updated' ){
    $blk_msg =  __("text_company_status_new_to_active_administrator_updated", array('[link]' => $tpl_vendor_area, '[login]' => $tpl_base_username));
// 出品者データ管理者が作成された場合
}else{
    $blk_msg =  __("text_company_status_new_to_active_administrator_created", array('[link]' => $tpl_vendor_area, '[login]' => $tpl_base_username, '[password]' => $tpl_base_password));
}
/////////////////////////////////////////////////////////////////////////////
// ログイン情報 EOF
/////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////
// 利用可能なテンプレート変数を定義 BOF
/////////////////////////////////////////////////////////////////////////////
$mail_tpl_var = 
	array(
		'COMPANY_NAME' => 
				array('desc' => 'company_name', 
						'value' => html_entity_decode($tpl_company_data['company_name'], ENT_QUOTES, 'UTF-8') ),
		'COMPANY_LOGIN_INFO' => 
				array('desc' => 'user_account_info', 
						'value' => $blk_msg),
	);

fn_set_hook('mail_tpl_var_companies_status_n_a_notification', $mail_tpl_var, $tpl_company_data, $tpl_vendor_url, $mail_template);
/////////////////////////////////////////////////////////////////////////////
// 利用可能なテンプレート変数を定義 EOF
/////////////////////////////////////////////////////////////////////////////
