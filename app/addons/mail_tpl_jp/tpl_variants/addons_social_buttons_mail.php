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

// $Id: addons_social_buttons_mail.php by tommy from cs-cart.jp 2016
// 「友達に知らせる」メールで使用可能なテンプレート変数

use Tygh\Tools\SecurityHelper;

/////////////////////////////////////////////////////////////////////////////
// データ取得 BOF
/////////////////////////////////////////////////////////////////////////////
// メールテンプレート編集ページ以外の場合
if( empty($_edit_mail_tpl) ) {
	// 「友達に知らせる」フォームから送信されるデータ
	$tpl_send_data = $tpl_base_data['send_data']->value;
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
		'TO_NAME' => 
				array('desc' => 'mtpl_friend_name', 
						'value' => empty($_edit_mail_tpl) ? $tpl_send_data['to_name'] : ''),
		'TO_EMAIL' => 
				array('desc' => 'mtpl_friend_email', 
						'value' => empty($_edit_mail_tpl) ? $tpl_send_data['to_email'] : ''),
		'FROM_NAME' => 
				array('desc' => 'mtpl_your_name', 
						'value' => empty($_edit_mail_tpl) ? $tpl_send_data['from_name'] : ''),
		'FROM_EMAIL' => 
				array('desc' => 'mtpl_your_email', 
						'value' => empty($_edit_mail_tpl) ? $tpl_send_data['from_email'] : ''),
		'NOTES' => 
				array('desc' => 'mtpl_comments', 
						'value' => empty($_edit_mail_tpl) ? $tpl_send_data['notes'] : ''),
		'LINK' => 
				array('desc' => 'mtpl_product_link', 
						'value' => empty($_edit_mail_tpl) ? SecurityHelper::escapeHtml($tpl_base_data['link']->value,true) : ''),
	);

if( empty($_edit_mail_tpl) ) {
	fn_set_hook('mail_tpl_var_addons_send_to_friend_mail', $mail_tpl_var, $tpl_send_data, $mail_template);
}
/////////////////////////////////////////////////////////////////////////////
// 利用可能なテンプレート変数を定義 EOF
/////////////////////////////////////////////////////////////////////////////
