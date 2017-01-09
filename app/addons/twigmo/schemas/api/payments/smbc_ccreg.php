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

/////////////////////////////////////////////////////////////////////////////////////
// 必ず表示する項目 BOF
/////////////////////////////////////////////////////////////////////////////////////

$schema = array (
	// 登録済みカード番号
	array (
		'option_id' => 1,
		'name' => 'registered_card_number',
		'description' => __('card_number'),
		'value' => '',
		'option_type' =>  'S',
		'position' => 10,
		'option_variants' => fn_smbcks_tw_get_registered_card_number(),
        'required' => true,
	),
	// 支払方法
	array (
		'option_id' => 3,
		'name' => 'jp_cc_method',
		'description' => __('jp_cc_method'),
		'value' => '',
		'option_type' =>  'S',
		'position' => 30,
		'option_variants' => fn_smbcks_tw_get_ccreg_methods(),
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
$_smbc_ccreg_payment_id = db_get_field("SELECT payment_id FROM ?:payments WHERE template = ?s", 'views/orders/components/payments/smbc_ccreg.tpl');
$_smbc_ccreg_payment_data = fn_get_payment_method_data($_smbc_ccreg_payment_id);
/////////////////////////////////////////////////////////////////////////////////////
// オプション項目の表示有無と表示内容を取得するための変数 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// セキュリティコード BOF
/////////////////////////////////////////////////////////////////////////////////////

// セキュリティコードの入力を必須化している場合は表示する
$schema_cvv = array();
if( $_smbc_ccreg_payment_data['processor_params']['use_cvv'] == 'true' ){
	$schema_cvv = array (
		array (
			'option_id' => 2,
			'name' => 'cvv_twg',
			'description' => __('jp_smbc_security_code'),
			'value' => '',
			'option_type' =>  'I',
			'position' => 20,
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
return $schema;
