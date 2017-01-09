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
// 項目の表示有無と表示内容を取得するための変数 BOF
/////////////////////////////////////////////////////////////////////////////////////

// ルミーズクレジットカードの設定データを取得
$_remise_payment_id = db_get_field("SELECT payment_id FROM ?:payments WHERE template = ?s", 'views/orders/components/payments/remise_cc.tpl');
$_remise_payment_data = fn_get_payment_method_data($_remise_payment_id);

// ユーザーIDを取得
$_remise_tw_auth = $_SESSION['auth'];
$_remise_tw_user_id = $_remise_tw_auth['user_id'];
/////////////////////////////////////////////////////////////////////////////////////
// オプション項目の表示有無と表示内容を取得するための変数 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// 必ず表示する項目 BOF
/////////////////////////////////////////////////////////////////////////////////////

$schema = array (
	// 支払方法
	array (
		'option_id' => 1,
		'name' => 'remese_cc_payment_type',
		'description' => __('jp_remise_payment_method'),
		'value' => '',
		'option_type' =>  'S',
		'position' => 10,
		'option_variants' => fn_remise_tw_cc_payment_type($_remise_payment_data),
        'required' => true,
	),
);
/////////////////////////////////////////////////////////////////////////////////////
// 必ず表示する項目 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// ペイクイック機能の利用有無 BOF
/////////////////////////////////////////////////////////////////////////////////////

// カード番号の登録を許可している場合は表示する
$schema_payquick = array();
if( $_remise_payment_data['processor_params']['payquick'] == 'true' && $_remise_tw_user_id && $_remise_tw_user_id > 0 ){
	$schema_payquick = array (
		array (
			'option_id' => 2,
			'name' => 'remese_cc_payquick',
			'description' => __('jp_remise_use_payquick'),
			'value' => '',
			'option_type' =>  'S',
			'position' => 20,
			'option_variants' => fn_remise_tw_confirm_payquick()
		),
	);
}

if( !empty($schema_payquick) ){
	$schema = array_merge($schema, $schema_payquick);
}
/////////////////////////////////////////////////////////////////////////////////////
// ペイクイック機能の利用有無 EOF
/////////////////////////////////////////////////////////////////////////////////////




/////////////////////////////////////////////////////////////////////////////////////
// オプションの表示内容を取得する関数 BOF
/////////////////////////////////////////////////////////////////////////////////////
// カード決済で利用可能な支払方法を取得
function fn_remise_tw_cc_payment_type($_remise_payment_data)
{
	$variants = array();

	// １回払い
	$variants[] = array (
		'variant_id' => 10,
		'variant_name' => 10,
		'description' => __('jp_cc_onetime')
		);
	// 分割払い
	if( $_remise_payment_data['processor_params']['installment'] == 'true' ){
		$variants[] = array (
			'variant_id' => 61,
			'variant_name' => 61,
			'description' => __('jp_payment_installment')
			);
	}
	// リボ払い
	if( $_remise_payment_data['processor_params']['revo'] == 'true' ){
		$variants[] = array (
			'variant_id' => 80,
			'variant_name' => 80,
			'description' => __('jp_cc_revo')
			);
	}

	return $variants;
}




// ペイクイック機能の利用有無を確認するリストを取得
function fn_remise_tw_confirm_payquick()
{
	$variants[] = array (
		'variant_id' => 'payquick_yes',
		'variant_name' => 'Y',
		'description' => __('yes'),
		);

	$variants[] = array (
		'variant_id' => 'payquick_no',
		'variant_name' => 'N',
		'description' => __('no'),
		);

	return $variants;
}
/////////////////////////////////////////////////////////////////////////////////////
// オプションの表示内容を取得する関数 EOF
/////////////////////////////////////////////////////////////////////////////////////
return $schema;
