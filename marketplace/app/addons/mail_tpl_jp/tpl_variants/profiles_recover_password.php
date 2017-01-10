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

// $Id: profiles_recover_password.php by tommy from cs-cart.jp 2016
// パスワードの再設定メールで使用可能なテンプレート変数


/////////////////////////////////////////////////////////////////////////////
// データ取得 BOF
/////////////////////////////////////////////////////////////////////////////
// メールテンプレート編集ページ以外の場合
if( empty($_edit_mail_tpl) ) {

	// パスワードリカバリの対象を取得
	$zone = $tpl_base_data['zone']->value;

	switch($zone){
		// 管理者用パスワードのリカバリの場合
		case 'A':
			$target = $tpl_base_data['config']->value['admin_index'];
			break;
		// 出品者パスワードのリカバリの場合
		case 'V':
			$target = $tpl_base_data['config']->value['vendor_index'];
			break;
		// 会員パスワードのリカバリの場合
		default:
			$target = $tpl_base_data['config']->value['customer_index'];
	}

	// パスワード再発行用URLをセット
	$tpl_link = $tpl_base_data['config']->value['http_location'] . '/' . $target . '?dispatch=auth.recover_password&ekey=' . $tpl_base_data['ekey']->value;
}else{
	$tpl_link = '';
}
/////////////////////////////////////////////////////////////////////////////
// データ取得 EOF
/////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////
// メールテンプレート取得 BOF
/////////////////////////////////////////////////////////////////////////////
// メールテンプレートコードとユーザーが使用中の言語コードでメールテンプレートを抽出
if( !empty($tpl_code) ) {
	$mtpl_lang_code = CART_LANGUAGE;
	$mail_template = fn_mtpl_get_email_contents($tpl_code, $mtpl_lang_code);
}
/////////////////////////////////////////////////////////////////////////////
// メールテンプレート取得 EOF
/////////////////////////////////////////////////////////////////////////////



/////////////////////////////////////////////////////////////////////////////
// 利用可能なテンプレート変数を定義 BOF
/////////////////////////////////////////////////////////////////////////////
$mail_tpl_var = 
	array(
		'LINK' => 
				array('desc' => 'mtpl_pass_recovery_link', 
						'value' => $tpl_link),
	);

if( empty($_edit_mail_tpl) ) {
	fn_set_hook('mail_tpl_var_profiles_recover_password', $mail_tpl_var, $tpl_link, $mail_template);
}
/////////////////////////////////////////////////////////////////////////////
// 利用可能なテンプレート変数を定義 EOF
/////////////////////////////////////////////////////////////////////////////
