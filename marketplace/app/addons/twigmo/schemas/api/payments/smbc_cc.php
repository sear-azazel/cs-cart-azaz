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

////////////////////////////////////////////////////////////////////////////////
// 必ず表示する項目 BOF
/////////////////////////////////////////////////////////////////////////////////////

$schema = array (
	// カード番号
	array (
		'option_id' => 2,
		'name' => 'card_number',
		'description' => __('card_number'),
		'value' => '',
		'option_type' =>  'I',
		'position' => 20,
        'required' => true,
	),
	// 有効期限
	array (
		'option_id' => 3,
		'name' => 'expiry_date',
		'description' => __('expiry_date'),
		'value' => '',
		'option_type' =>  'D',
		'position' => 30,
        'required' => true,
	),
	// 支払方法
	array (
		'option_id' => 5,
		'name' => 'jp_cc_method',
		'description' => __('jp_cc_method'),
		'value' => '',
		'option_type' =>  'S',
		'position' => 50,
		'option_variants' => fn_smbcks_tw_get_cc_methods(),
        'required' => true,
	),
);
/////////////////////////////////////////////////////////////////////////////////////
// 必ず表示する項目 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// オプション項目の表示有無と表示内容を取得するための変数 BOF
/////////////////////////////////////////////////////////////////////////////////////

// CS-Cartマルチ決済（登録済みカード決済）の設定データを取得
$_smbc_cc_payment_id = db_get_field("SELECT payment_id FROM ?:payments WHERE template = ?s", 'views/orders/components/payments/smbc_cc.tpl');
$_smbc_cc_payment_data = fn_get_payment_method_data($_smbc_cc_payment_id);

// ユーザーIDを取得
$_sbmc_tw_auth = $_SESSION['auth'];
$_smbc_tw_user_id = $_sbmc_tw_auth['user_id'];
/////////////////////////////////////////////////////////////////////////////////////
// オプション項目の表示有無と表示内容を取得するための変数 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// セキュリティコード BOF
/////////////////////////////////////////////////////////////////////////////////////

// セキュリティコードの入力を必須化している場合は表示する
$schema_cvv = array();
if( $_smbc_cc_payment_data['processor_params']['use_cvv'] == 'true' ){
	$schema_cvv = array (
		array (
			'option_id' => 4,
			'name' => 'cvv_twg',
			'description' => __('jp_smbc_security_code'),
			'value' => '',
			'option_type' =>  'I',
			'position' => 40,
            'required' => true,
		),
	);
}

if( !empty($schema_cvv) ){
	$schema = array_merge($schema, $schema_cvv);
}
/////////////////////////////////////////////////////////////////////////////////////
// セキュリティコード EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// カード番号の登録有無 BOF
/////////////////////////////////////////////////////////////////////////////////////

// カード番号の登録を許可している場合は表示する
$schema_register_cc = array();
if( $_smbc_cc_payment_data['processor_params']['register_card_info'] == 'true' && $_smbc_tw_user_id && $_smbc_tw_user_id > 0 ){
	$schema_register_cc = array (
		array (
			'option_id' => 6,
			'name' => 'register_card_info',
			'description' => __('jp_smbc_cc_register_card_info_use'),
			'value' => '',
			'option_type' =>  'S',
			'position' => 60,
			'option_variants' => fn_smbcks_tw_confirm_card_register()
		),
	);
}

if( !empty($schema_register_cc) ){
	$schema = array_merge($schema, $schema_register_cc);
}
/////////////////////////////////////////////////////////////////////////////////////
// カード番号の登録有無 EOF
/////////////////////////////////////////////////////////////////////////////////////
return $schema;
