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

// $Id: func_installer.php by tommy from cs-cart.jp 2016

use Tygh\Registry;
use Tygh\Settings;

##########################################################################################
// START アドオンのインストール・アンインストール時に動作する関数
##########################################################################################

//////////////////////////////////////////////////////////////
// 各種関数を利用するために必要なコアファイルを読み込み BOF
//////////////////////////////////////////////////////////////
require_once (Registry::get('config.dir.functions') . 'fn.cart.php');
require (Registry::get('config.dir.root') . '/app/controllers/backend/destinations.php');
//////////////////////////////////////////////////////////////
// 各種関数を利用するために必要なコアファイルを読み込み EOF
//////////////////////////////////////////////////////////////


// アドオンのインストール時の動作
function fn_lcjp_configure_japanese_version()
{
    // インストール済みの言語を取得
    $languages = db_get_hash_array("SELECT * FROM ?:languages", 'lang_code');

    /////////////////////////////////////////////////////////////////////////
    // 日本語以外に追加言語が設定されていない場合は、英語をオフにする BOF
    /////////////////////////////////////////////////////////////////////////
    $other_langs = db_get_field("SELECT COUNT(*) FROM ?:languages WHERE lang_code != ?s AND lang_code != ?s", 'en', 'ja');
    if( empty($other_langs) ){
        db_query("UPDATE ?:languages SET status = 'D' WHERE lang_code != 'ja'");
    }
    /////////////////////////////////////////////////////////////////////////
    // 日本語以外に追加言語が設定されていない場合は、英語をオフにする EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // 基本設定の設定値を変更 BOF
    /////////////////////////////////////////////////////////////////////////
    // 基本設定 -> 全般 の設定内容を変更
    Settings::instance()->updateValue('weight_symbol', 'kg', 'General');
    Settings::instance()->updateValue('weight_symbol_grams', 1000, 'General');
    Settings::instance()->updateValue('use_shipments', 'N', 'General');
    Settings::instance()->updateValue('default_country', 'JP', 'General');
    Settings::instance()->updateValue('default_zipcode', '107-0052', 'General');
    Settings::instance()->updateValue('default_state', '東京都', 'General');
    Settings::instance()->updateValue('default_city', '港区', 'General');
    Settings::instance()->updateValue('default_address', '赤坂1-2-34 CSビル5F', 'General');
    Settings::instance()->updateValue('default_phone', '01-2345-6789', 'General');
    Settings::instance()->updateValue('allow_usergroup_signup', 'N', 'General');
    Settings::instance()->updateValue('min_order_amount_type', 'P', 'General');
    Settings::instance()->updateValue('checkout_redirect', 'Y', 'General');
    Settings::instance()->updateValue('repay', 'N', 'General');
    Settings::instance()->updateValue('estimate_shipping_cost', 'N', 'General');

    // 基本設定 -> 全般 の住所の並び順を変更
    $_obj_id = Settings::instance()->getId('default_country', 'General');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 70) );
    $_obj_id = Settings::instance()->getId('default_zipcode', 'General');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 80) );
    $_obj_id = Settings::instance()->getId('default_state', 'General');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 90) );
    $_obj_id = Settings::instance()->getId('default_city', 'General');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 100) );
    $_obj_id = Settings::instance()->getId('default_address', 'General');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 110) );

    // 基本設定 -> 表示設定 の設定内容を変更
    Settings::instance()->updateValue('frontend_default_language', 'ja', 'Appearance');
    Settings::instance()->updateValue('backend_default_language', 'ja', 'Appearance');
    Settings::instance()->updateValue('date_format', '%Y/%m/%d', 'Appearance');
    Settings::instance()->updateValue('timezone', 'Asia/Tokyo', 'Appearance');
    Settings::instance()->updateValue('changes_warning', 'N', 'Appearance');

    // 基本設定 -> 会社概要 の設定内容を変更
    Settings::instance()->updateValue('company_name', 'CS-Cart.jp', 'Company');
    Settings::instance()->updateValue('company_country', 'JP', 'Company');
    Settings::instance()->updateValue('company_zipcode', '107-0052', 'Company');
    Settings::instance()->updateValue('company_state', '東京都', 'Company');
    Settings::instance()->updateValue('company_city', '港区', 'Company');
    Settings::instance()->updateValue('company_address', '赤坂1-2-34 CSビル5F', 'Company');
    Settings::instance()->updateValue('company_phone', '01-2345-6789', 'Company');
    Settings::instance()->updateValue('company_website', 'http://cs-cart.jp/', 'Company');
    Settings::instance()->updateValue('company_start_year', '2013', 'Company');

    // 基本設定 -> 会社概要 の住所の並び順を変更
    $_obj_id = Settings::instance()->getId('company_country', 'Company');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 10) );
    $_obj_id = Settings::instance()->getId('company_zipcode', 'Company');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 20) );
    $_obj_id = Settings::instance()->getId('company_state', 'Company');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 30) );
    $_obj_id = Settings::instance()->getId('company_city', 'Company');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 40) );
    $_obj_id = Settings::instance()->getId('company_address', 'Company');
    Settings::instance()->update( array('object_id' => $_obj_id, 'position' => 50) );

    // 基本設定 -> 画像認証 の設定内容を変更
    Settings::instance()->updateValue('use_for_login', 'N', 'Image_verification');
    Settings::instance()->updateValue('use_for_register', 'N', 'Image_verification');
    Settings::instance()->updateValue('use_for_checkout', 'N', 'Image_verification');
    Settings::instance()->updateValue('use_for_polls', 'N', 'Image_verification');
    /////////////////////////////////////////////////////////////////////////
    // 基本設定の設定値を変更 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // デフォルトの顧客情報を変更 BOF
    /////////////////////////////////////////////////////////////////////////
    db_query("UPDATE ?:users SET firstname = '鈴木', lastname = '一郎', lang_code = 'ja' WHERE user_login = 'customer'");
    db_query("UPDATE ?:user_profiles SET b_firstname = '鈴木', b_lastname = '一郎', b_country = 'JP', b_zipcode = '107-0052', b_state = '東京都', b_city = '港区', b_address = '赤坂5-6-78', b_address_2 = 'CS第二ビル7F', s_firstname = '鈴木', s_lastname = '一郎', s_country = 'JP', s_zipcode = '107-0052', s_state = '東京都', s_city = '港区', s_address = '赤坂5-6-78', s_address_2 = 'CSビル7F' WHERE user_id = 3 AND profile_id = 2");
    /////////////////////////////////////////////////////////////////////////
    // デフォルトの顧客情報を変更 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // デフォルトのショップ情報を変更 BOF
    /////////////////////////////////////////////////////////////////////////
    $_company_data = array (
        'company' => 'CS-Cart.jp',
        'lang_code' => 'ja',
        'address' => '赤坂1-2-34 CSビル5F',
        'city' => '港区',
        'state' => '東京都',
        'country' => 'JP',
        'zipcode' => '107-0052',
        'email' => 'cscartjp@example.com',
        'phone' => '01-2345-6789',
        'fax' => '',
        'url' => 'http://cs-cart.jp'
    );

    db_query("UPDATE ?:companies SET ?u WHERE company_id = ?i", $_company_data, 1);
    /////////////////////////////////////////////////////////////////////////
    // デフォルトのショップ情報を変更 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // デフォルトの支払方法に関する設定を変更 BOF
    /////////////////////////////////////////////////////////////////////////
    db_query("UPDATE ?:payments SET status = 'D' WHERE payment_id NOT IN ('1', '6')");
    db_query("UPDATE ?:payments SET status = 'A' WHERE payment_id IN ('1', '6')");
    /////////////////////////////////////////////////////////////////////////
    // デフォルトの支払方法に関する設定を変更 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // すべての注文ステータスについて注文管理担当者へ通知 BOF
    // マーケットプレイス版については出品者データ管理者にも通知
    /////////////////////////////////////////////////////////////////////////
    // 注文ステータスに関するステータスIDを取得
    $status_ids = db_get_fields("SELECT status_id FROM ?:statuses WHERE type = ?s", 'O');

    // マーケットプレイス版の場合
    if( fn_allowed_for('MULTIVENDOR') ){
        foreach ($status_ids as $status_id) {
            $_status_data = array(
                'status_id' => $status_id,
                'param' => 'notify_department',
                'value' => 'Y'
            );
            db_query("REPLACE INTO ?:status_data ?e", $_status_data);
            $_status_data = array(
                'status_id' => $status_id,
                'param' => 'notify_vendor',
                'value' => 'Y'
            );
            db_query("REPLACE INTO ?:status_data ?e", $_status_data);
        }

    // その他のエディションの場合
    }else{
        foreach ($status_ids as $status_id) {
            $_status_data = array(
                'status_id' => $status_id,
                'param' => 'notify_department',
                'value' => 'Y'
            );
            db_query("REPLACE INTO ?:status_data ?e", $_status_data);
        }
    }
    /////////////////////////////////////////////////////////////////////////
    // すべての注文ステータスについて注文管理担当者へ通知 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // 日本円を追加し、ベース通貨に設定 BOF
    /////////////////////////////////////////////////////////////////////////
    // 日本円が登録されているかチェック
    $is_exists = db_get_field("SELECT COUNT(*) FROM ?:currencies WHERE currency_code = ?s", 'JPY');

    // 日本円が登録されていない場合、登録してベース通貨に設定する
    if (empty($is_exists)) {
        // 通貨
        $currency = array(
            'currency_code' => 'JPY',
            'coefficient' => 1,
            'symbol'=> '円',
            'after' => 'Y',
            'status' => 'A',
            'thousands_separator'=> ',',
            'decimal_separator' => '.',
            'decimals' => 0,
            'is_primary' => 'Y'
        );

        // 通貨の説明
        $currency_desc = array(
            'currency_code' => $currency['currency_code']
        );

        // 既存通貨のステータスをすべてオフにする
        db_query("UPDATE ?:currencies SET status = 'D'");

        // 既存通貨のベース通貨指定を解除
        $update_data = array('is_primary' => 'N');
        db_query("UPDATE ?:currencies SET ?u WHERE is_primary = ?s", $update_data, 'Y');

        // 日本円をベース通貨として登録
        db_query("INSERT INTO ?:currencies ?e", $currency);

        foreach ($languages as $currency_desc['lang_code'] => $v) {
            if ($currency_desc['lang_code'] == 'ja' ) {
                $currency_desc['description'] = '日本円';
            }else{
                $currency_desc['description'] = 'Japanese Yen';
            }
            db_query("REPLACE INTO ?:currency_descriptions ?e", $currency_desc);
        }

        // 追加された日本円の currency_id を取得
        $jpy_currency_id = db_get_field("SELECT currency_id FROM ?:currencies WHERE currency_code =?s", 'JPY');

        // 登録済みのすべてのショップにおいて有効にする
        if (fn_allowed_for('ULTIMATE')){
            fn_share_object_to_all('currencies', $jpy_currency_id);
        }

        // 登録済み通貨を取得
        $registered_currencies = db_get_array("SELECT * FROM ?:currencies");

        // USD / EUR / GBP について対円の為替レートをセット
        foreach($registered_currencies as $registered_currency){
            // 為替レートを初期化
            $ex_rate = '';
            switch($registered_currency['currency_code']){
                case 'USD':
                    $ex_rate = 100;
                    break;
                case 'EUR':
                    $ex_rate = 130;
                    break;
                case 'GBP':
                    $ex_rate = 160;
                    break;
                default:
                    // do nothing
            }

            if(!empty($ex_rate)){
                db_query("UPDATE ?:currencies SET coefficient = ?i WHERE currency_code = ?s", $ex_rate, $registered_currency['currency_code']);
            }
        }
    }
    /////////////////////////////////////////////////////////////////////////
    // 日本円を追加し、ベース通貨に設定 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // 会員情報フィールドに姓・名フリガナを追加したうえで表示順を変更 BOF
    /////////////////////////////////////////////////////////////////////////
    // 姓名フリガナ
    $profile_kana = array(
        // 姓フリガナ（連絡先情報）
        'firstname_c' => array(
            'description' => '姓フリガナ',
            'position' => '0',
            'field_name' => 'firstname_kana',
            'field_type' => 'I',
            'section' => 'C',
            'profile_show' => 'N',
            'profile_required' => 'N',
            'checkout_show' => 'N',
            'checkout_required' => 'N',
            'class' => 'first-name-kana'
        ),
        // 名フリガナ（連絡先情報）
        'lastname_c' => array(
            'description' => '名フリガナ',
            'position' => '0',
            'field_name' => 'lastname_kana',
            'field_type' => 'I',
            'section' => 'C',
            'profile_show' => 'N',
            'profile_required' => 'N',
            'checkout_show' => 'N',
            'checkout_required' => 'N',
            'class' => 'last-name-kana'
        ),
        // 姓フリガナ（請求先/配送先住所）
        'firstname_bs' => array(
            'description' => '姓フリガナ',
            'position' => '0',
            'field_name' => 'firstname_kana',
            'field_type' => 'I',
            'section' => 'BS',
            'profile_show' => 'Y',
            'profile_required' => 'Y',
            'checkout_show' => 'Y',
            'checkout_required' => 'Y',
            'class' => 'first-name-kana'
        ),
        // 名フリガナ（請求先/配送先住所）
        'lastname_bs' => array(
            'description' => '名フリガナ',
            'position' => '0',
            'field_name' => 'lastname_kana',
            'field_type' => 'I',
            'section' => 'BS',
            'profile_show' => 'Y',
            'profile_required' => 'Y',
            'checkout_show' => 'Y',
            'checkout_required' => 'Y',
            'class' => 'last-name-kana'
        )
    );

    // 姓名フリガナフィールドを追加し、登録済みのすべてのショップにおいて有効にする
    foreach ($profile_kana as $profile_kana_data){
        $field_id = fn_update_profile_field($profile_kana_data, '');
        // フィールドを登録済みのすべてのショップにおいて有効にする
        if (fn_allowed_for('ULTIMATE')){
            fn_share_object_to_all('profile_fields', $field_id);
        }

        // 配送先/請求先住所欄のフィールドについては、請求先用フィールドに対応する配送先用フィールドも
        // 登録済みのすべてのショップにおいて有効にする
        if (fn_allowed_for('ULTIMATE')){
            $matching_id = db_get_field("SELECT matching_id FROM ?:profile_fields WHERE field_id = ?i", $field_id);
            if (!empty($matching_id)) fn_share_object_to_all('profile_fields', $matching_id);
        }
    }

    // 会員情報フィールドソート順
    $profile_sort_order = array(
        'firstname' => array('position' => 10, 'section' => 'C'),
        'lastname' => array('position' => 20, 'section' => 'C'),
        'firstname_kana' => array('position' => 30, 'section' => 'C'),
        'lastname_kana' => array('position' => 40, 'section' => 'C'),
        'company' => array('position' => 50, 'section' => 'C'),
        'phone' => array('position' => 60, 'section' => 'C'),
        'fax' => array('position' => 70, 'section' => 'C'),
        'url' => array('position' => 80, 'section' => 'C'),

        'b_firstname' => array('position' => 90, 'profile_required' => 'Y', 'checkout_required' => 'Y', 'section' => 'B'),
        'b_lastname' => array('position' => 100, 'profile_required' => 'Y', 'checkout_required' => 'Y', 'section' => 'B'),
        'b_firstname_kana' => array('position' => 110, 'section' => 'B'),
        'b_lastname_kana' => array('position' => 120, 'section' => 'B'),
        'email_b' => array('position' => 130, 'section' => 'B'),
        'b_phone' => array('position' => 140, 'profile_required' => 'Y', 'checkout_required' => 'Y', 'section' => 'B'),
        'b_country' => array('position' => 150, 'section' => 'B'),
        'b_zipcode' => array('position' => 160, 'section' => 'B'),
        'b_state' => array('position' => 170, 'section' => 'B'),
        'b_city' => array('position' => 180, 'section' => 'B'),
        'b_address' => array('position' => 190, 'section' => 'B'),
        'b_address_2' => array('position' => 200, 'section' => 'B'),

        's_firstname' => array('position' => 210, 'profile_required' => 'Y', 'checkout_required' => 'Y', 'section' => 'S'),
        's_lastname' => array('position' => 220, 'profile_required' => 'Y', 'checkout_required' => 'Y', 'section' => 'S'),
        's_firstname_kana' => array('position' => 230, 'section' => 'S'),
        's_lastname_kana' => array('position' => 240, 'section' => 'S'),
        'email_s' => array('position' => 250, 'section' => 'S'),
        's_phone' => array('position' => 260, 'profile_required' => 'Y', 'checkout_required' => 'Y', 'section' => 'S'),
        's_country' => array('position' => 270, 'section' => 'S'),
        's_zipcode' => array('position' => 280, 'section' => 'S'),
        's_state' => array('position' => 290, 'section' => 'S'),
        's_city' => array('position' => 300, 'section' => 'S'),
        's_address' => array('position' => 310, 'section' => 'S'),
        's_address_2' => array('position' => 320, 'section' => 'S'),
        's_address_type' => array('position' => 330, 'section' => 'S'),
    );

    // 各フィールドのソート順を変更
    foreach ($profile_sort_order as $k => $v) {

        if( $k == 'email_b' || $k == 'email_s' ){
            $field_name = 'email';
        }else{
            $field_name = $k;
        }
        db_query("UPDATE ?:profile_fields SET ?u WHERE field_name = ?s AND section = ?s", $v, $field_name, $v['section']);
    }

    // 追加フィールドにフィールド名があると内容の更新ができなくなるのでここで削除
    db_query("UPDATE ?:profile_fields SET field_name = '' WHERE field_name LIKE '%_kana'");
    /////////////////////////////////////////////////////////////////////////
    // 会員情報フィールドに姓・名フリガナを追加したうえで表示順を変更 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // 都道府県を追加 BOF
    /////////////////////////////////////////////////////////////////////////
    // 都道府県
    $states_data = array(
        'Hokkaido' => array(
            'state' => '北海道',
        ),
        'Aomori' => array(
            'state' => '青森県',
        ),
        'Iwate' => array(
            'state' => '岩手県',
        ),
        'Miyagi' => array(
            'state' => '宮城県',
        ),
        'Akita' => array(
            'state' => '秋田県',
        ),
        'Yamagata' => array(
            'state' => '山形県',
        ),
        'Fukushima' => array(
            'state' => '福島県',
        ),
        'Ibaraki' => array(
            'state' => '茨城県',
        ),
        'Tochigi' => array(
            'state' => '栃木県',
        ),
        'Gunma' => array(
            'state' => '群馬県',
        ),
        'Saitama' => array(
            'state' => '埼玉県',
        ),
        'Chiba' => array(
            'state' => '千葉県',
        ),
        'Tokyo' => array(
            'state' => '東京都',
        ),
        'Kanagawa' => array(
            'state' => '神奈川県',
        ),
        'Niigata' => array(
            'state' => '新潟県',
        ),
        'Toyama' => array(
            'state' => '富山県',
        ),
        'Ishikawa' => array(
            'state' => '石川県',
        ),
        'Fukui' => array(
            'state' => '福井県',
        ),
        'Yamanashi' => array(
            'state' => '山梨県',
        ),
        'Nagano' => array(
            'state' => '長野県',
        ),
        'Gifu' => array(
            'state' => '岐阜県',
        ),
        'Shizuoka' => array(
            'state' => '静岡県',
        ),
        'Aichi' => array(
            'state' => '愛知県',
        ),
        'Mie' => array(
            'state' => '三重県',
        ),
        'Shiga' => array(
            'state' => '滋賀県',
        ),
        'Kyoto' => array(
            'state' => '京都府',
        ),
        'Osaka' => array(
            'state' => '大阪府',
        ),
        'Hyogo' => array(
            'state' => '兵庫県',
        ),
        'Nara' => array(
            'state' => '奈良県',
        ),
        'Wakayama' => array(
            'state' => '和歌山県',
        ),
        'Tottori' => array(
            'state' => '鳥取県',
        ),
        'Shimane' => array(
            'state' => '島根県',
        ),
        'Okayama' => array(
            'state' => '岡山県',
        ),
        'Hiroshima' => array(
            'state' => '広島県',
        ),
        'Yamaguchi' => array(
            'state' => '山口県',
        ),
        'Tokushima' => array(
            'state' => '徳島県',
        ),
        'Kagawa' => array(
            'state' => '香川県',
        ),
        'Ehime' => array(
            'state' => '愛媛県',
        ),
        'Kouchi' => array(
            'state' => '高知県',
        ),
        'Fukuoka' => array(
            'state' => '福岡県',
        ),
        'Saga' => array(
            'state' => '佐賀県',
        ),
        'Nagasaki' => array(
            'state' => '長崎県',
        ),
        'Kumamoto' => array(
            'state' => '熊本県',
        ),
        'Oita' => array(
            'state' => '大分県',
        ),
        'Miyazaki' => array(
            'state' => '宮崎県',
        ),
        'Kagoshima' => array(
            'state' => '鹿児島県',
        ),
        'Okinawa' => array(
            'state' => '沖縄県',
        ),
    );

    $cnt_state = 9000;
    foreach ($states_data as $key => $value) {

        if ( !empty($value['state']) ) {

            $value['country_code'] = 'JP';
            $value['code'] = $value['state'];
            $value['status'] = 'A';
            $value['state_id'] = $cnt_state;

            db_query("REPLACE INTO ?:states ?e", $value);

            foreach ($languages as $value['lang_code'] => $_v) {
                db_query('REPLACE INTO ?:state_descriptions ?e', $value);
            }

            $cnt_state++;
        }
    }
    /////////////////////////////////////////////////////////////////////////
    // 都道府県を追加 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // ロケーション "USA" "Canada" を削除 BOF
    /////////////////////////////////////////////////////////////////////////
    $destination_ids = db_get_fields("SELECT destination_id FROM ?:destination_descriptions WHERE destination IN ('USA', 'Canada') AND lang_code = 'en'");
    fn_delete_destinations($destination_ids);
    /////////////////////////////////////////////////////////////////////////
    // ロケーション "USA" "Canada" を削除 EOF
    /////////////////////////////////////////////////////////////////////////

    /////////////////////////////////////////////////////////////////////////
    // US用の配送方法を削除 BOF
    /////////////////////////////////////////////////////////////////////////
    $shipping_ids = db_get_fields("SELECT shipping_id FROM ?:shipping_descriptions WHERE shipping IN ('FedEx 2nd day', 'UPS 3day Select', 'USPS Media Mail') AND lang_code = 'en'");

    foreach ($shipping_ids as $id) {
        fn_delete_shipping($id);
        if (fn_allowed_for('ULTIMATE')){
            db_query('DELETE FROM ?:ult_objects_sharing WHERE share_object_id = ?s AND share_object_type = ?s', $id, 'shippings');
        }
    } /////////////////////////////////////////////////////////////////////////
    // US用の配送方法を削除 EOF
    /////////////////////////////////////////////////////////////////////////


    /////////////////////////////////////////////////////////////////////////
    // VATを削除し、消費税を追加 BOF
    /////////////////////////////////////////////////////////////////////////
    // 税金
    $tax = array(
        'tax' => '消費税',
        'regnumber' => '',
        'priority' => 0,
        'address_type' => 'S',
        'status' => 'D',
        'price_includes_tax' => 'Y',
        'rates' => array(
            1 => array(
                'rate_id' => '',
                'rate_value' => 8,
                'rate_type' => 'P'
            )
        ),
    );

    $tax_ids = db_get_fields("SELECT tax_id FROM ?:tax_descriptions WHERE tax IN ('VAT') AND lang_code = 'en'");
    fn_delete_taxes($tax_ids);

    fn_update_tax($tax, '', $languages);
    /////////////////////////////////////////////////////////////////////////
    // VATを削除し、消費税を追加 EOF
    /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 言語変数を追加 BOF
        /////////////////////////////////////////////////////////////////////////
        // 言語変数の追加
        $lang_variables = array(
            array('name' => 'carrier_fukutsu', 'value' => '福山通運'),
            array('name' => 'carrier_jpost', 'value' => '日本郵便'),
            array('name' => 'carrier_jpems', 'value' => '日本郵便 (海外向け)'),
            array('name' => 'carrier_sagawa', 'value' => '佐川急便'),
            array('name' => 'carrier_yamato', 'value' => 'ヤマト運輸'),
            array('name' => 'jp_addons_unable_to_uninstall', 'value' => 'このアドオンはアンインストールできません。'),
            array('name' => 'jp_address_vendors', 'value' => '番地・ビル建物名'),
            array('name' => 'jp_after_replace', 'value' => '置換後'),
            array('name' => 'jp_apply_for_marketplace', 'value' => 'マーケットプレイスへの参加申請'),
            array('name' => 'jp_availability', 'value' => '在庫状況'),
            array('name' => 'jp_back_in_stock_add_email', 'value' => '入荷通知メールの追加'),
            array('name' => 'jp_cc_bonus_month', 'value' => '支払月'),
            array('name' => 'jp_cc_bonus_onetime', 'value' => 'ボーナス一括払い'),
            array('name' => 'jp_cc_installment', 'value' => '分割払い'),
            array('name' => 'jp_cc_installment_times', 'value' => '支払回数'),
            array('name' => 'jp_cc_method', 'value' => '支払方法'),
            array('name' => 'jp_cc_onetime', 'value' => '一括払い'),
            array('name' => 'jp_cc_revo', 'value' => 'リボ払い'),
            array('name' => 'jp_company_address', 'value' => '会社所在地'),
            array('name' => 'jp_company_description', 'value' => 'マーケットプレイスに表示する会社の紹介文（後から変更できます）'),
            array('name' => 'jp_company_info', 'value' => '会社情報'),
            array('name' => 'jp_consumption_tax', 'value' => '消費税'),
            array('name' => 'jp_cvs_ck', 'value' => 'サークルK'),
            array('name' => 'jp_cvs_company_code', 'value' => '企業コード'),
            array('name' => 'jp_cvs_dy', 'value' => 'デイリーヤマザキ'),
            array('name' => 'jp_cvs_fm', 'value' => 'ファミリーマート'),
            array('name' => 'jp_cvs_limit', 'value' => '支払期限'),
            array('name' => 'jp_cvs_ls', 'value' => 'ローソン'),
            array('name' => 'jp_cvs_ms', 'value' => 'ミニストップ'),
            array('name' => 'jp_cvs_name', 'value' => 'コンビ二名'),
            array('name' => 'jp_cvs_payment_barcode', 'value' => 'バーコード情報'),
            array('name' => 'jp_cvs_payment_date', 'value' => '入金日時'),
            array('name' => 'jp_cvs_payment_header', 'value' => '【[cvs_name]決済入金情報】'),
            array('name' => 'jp_cvs_payment_instruction_url', 'value' => '支払方法案内URL'),
            array('name' => 'jp_cvs_payment_number', 'value' => '払込番号'),
            array('name' => 'jp_cvs_payment_online_payment_number', 'value' => 'オンライン決済番号'),
            array('name' => 'jp_cvs_payment_slip', 'value' => 'コンビニ払込票'),
            array('name' => 'jp_cvs_payment_slip_jpost', 'value' => 'コンビニ払込票（郵便振替対応）'),
            array('name' => 'jp_cvs_receipt_no', 'value' => '受付番号'),
            array('name' => 'jp_cvs_se', 'value' => 'セブンイレブン'),
            array('name' => 'jp_cvs_sm', 'value' => 'セイコーマート'),
            array('name' => 'jp_cvs_ts', 'value' => 'サンクス'),
            array('name' => 'jp_cvs_url', 'value' => '払込票URL'),
            array('name' => 'jp_cvs_yd', 'value' => 'ヤマザキデイリーストア'),
            array('name' => 'jp_dear_casual', 'value' => 'さん'),
            array('name' => 'jp_dear_supplier', 'value' => 'ご担当者様'),
            array('name' => 'jp_delivery_date', 'value' => 'お届け希望日'),
            array('name' => 'jp_delivery_date_desc01', 'value' => '注文日の'),
            array('name' => 'jp_delivery_date_desc02', 'value' => '日後（'),
            array('name' => 'jp_delivery_date_desc03', 'value' => 'から'),
            array('name' => 'jp_delivery_date_desc04', 'value' => '日間'),
            array('name' => 'jp_delivery_date_display_days', 'value' => 'お届け日の表示日数'),
            array('name' => 'jp_delivery_date_enable', 'value' => 'お届け日を指定可能にする'),
            array('name' => 'jp_delivery_date_include_holidays', 'value' => '休業日を計算に含める'),
            array('name' => 'jp_delivery_date_not_specified', 'value' => 'お届け日を指定可能にする場合は表示日数を入力してください。'),
            array('name' => 'jp_delivery_date_specify', 'value' => 'お届け日指定'),
            array('name' => 'jp_editing_location', 'value' => 'ロケーションの編集'),
            array('name' => 'jp_edition_standard', 'value' => 'スタンダード版'),
            array('name' => 'jp_edition_marketplace', 'value' => 'マーケットプレイス版'),
            array('name' => 'jp_epsilon_company_name', 'value' => 'イプシロン株式会社'),
            array('name' => 'jp_epsilon_contract_code', 'value' => '契約コード'),
            array('name' => 'jp_epsilon_general_error', 'value' => '決済処理中にエラーが発生しました。<br />お手数ですがショップ管理者までお問い合わせください。'),
            array('name' => 'jp_epsilon_item_name', 'value' => 'お買い上げ商品'),
            array('name' => 'jp_epsilon_notes_header', 'value' => '【イプシロン決済情報】'),
            array('name' => 'jp_epsilon_order_url_production', 'value' => 'オーダー情報確認URL（本番）'),
            array('name' => 'jp_epsilon_order_url_test', 'value' => 'オーダー情報確認URL（テスト）'),
            array('name' => 'jp_epsilon_send_error', 'value' => 'データ送信エラー'),
            array('name' => 'jp_epsilon_trans_method', 'value' => '決済方法'),
            array('name' => 'jp_epsilon_url_production', 'value' => '本番環境接続先URL'),
            array('name' => 'jp_epsilon_url_test', 'value' => 'テスト環境接続先URL'),
            array('name' => 'jp_epsilon_xml_parse_error', 'value' => 'XMLパースエラー'),
            array('name' => 'jp_etc', 'value' => 'など'),
            array('name' => 'jp_exc_tax', 'value' => '税抜'),
            array('name' => 'jp_excluding_tax', 'value' => '税抜'),
            array('name' => 'jp_free', 'value' => 'フリー'),
            array('name' => 'jp_free_license_request_sent', 'value' => 'ライセンス番号発行のため「管理者用Eメールアドレス」および「CS-CartをインストールしたURL」が cs-cart.jp に送信されました。<br />3～5営業日程度で管理者用メールアドレスにライセンス番号をお知らせするメールが届きます。'),
            array('name' => 'jp_goto_namager', 'value' => '管理ページに移動'),
            array('name' => 'jp_holidays', 'value' => '休業日'),
            array('name' => 'jp_inventory_count', 'value' => '在庫数'),
            array('name' => 'jp_japanese_yen', 'value' => '円'),
            array('name' => 'jp_login_or_register', 'value' => 'ログインまたは会員登録'),
            array('name' => 'jp_make_a_fresh_snapshot', 'value' => 'スナップショットの取得'),
            array('name' => 'jp_marketplace_admin_mode', 'value' => 'マーケットプレイス管理モード'),
            array('name' => 'jp_mtpl_reward_points_aquired', 'value' => '獲得ポイント数'),
            array('name' => 'jp_not_specified', 'value' => '指定なし'),
            array('name' => 'jp_payment_alipay', 'value' => 'Alipay国際決済'),
            array('name' => 'jp_payment_auone', 'value' => 'auかんたん決済'),
            array('name' => 'jp_payment_banktransfer', 'value' => '銀行振込'),
            array('name' => 'jp_payment_bitcash', 'value' => 'ビットキャッシュ'),
            array('name' => 'jp_payment_cc', 'value' => 'クレジットカード決済'),
            array('name' => 'jp_payment_chocom', 'value' => 'ちょコム決済'),
            array('name' => 'jp_payment_cvs', 'value' => 'コンビニ決済'),
            array('name' => 'jp_payment_cyberedy', 'value' => 'CyberEdy'),
            array('name' => 'jp_payment_docomo', 'value' => 'ドコモケータイ払い'),
            array('name' => 'jp_payment_gmoney', 'value' => 'G-MONEY'),
            array('name' => 'jp_payment_installment', 'value' => '分割払い'),
            array('name' => 'jp_payment_jcb_premo', 'value' => 'JCB PREMO'),
            array('name' => 'jp_payment_jnb', 'value' => 'ジャパンネット銀行'),
            array('name' => 'jp_payment_mobileedy', 'value' => 'Mobile Edy'),
            array('name' => 'jp_payment_netcash', 'value' => 'NET CASH'),
            array('name' => 'jp_payment_netmile', 'value' => 'ネットマイル'),
            array('name' => 'jp_payment_oempin', 'value' => 'PIN決済'),
            array('name' => 'jp_payment_paypal', 'value' => 'Paypal決済'),
            array('name' => 'jp_payment_pez', 'value' => 'ペイジー'),
            array('name' => 'jp_payment_rakuten', 'value' => '楽天ID決済'),
            array('name' => 'jp_payment_rakutenbank', 'value' => '楽天銀行'),
            array('name' => 'jp_payment_sbmoney', 'value' => 'SoftBankマネー'),
            array('name' => 'jp_payment_softbank', 'value' => 'S!まとめて支払い'),
            array('name' => 'jp_payment_suica', 'value' => 'モバイルSuica'),
            array('name' => 'jp_payment_unionpay', 'value' => '銀聯ネット決済'),
            array('name' => 'jp_payment_webmoney', 'value' => 'ウェブマネー'),
            array('name' => 'jp_payment_yahoowallet', 'value' => 'Yahoo!ウォレット'),
            array('name' => 'jp_paypal_email', 'value' => '支払いを受けるPaypalアカウントのEメールアドレス'),
            array('name' => 'jp_paypal_pending', 'value' => '未決済'),
            array('name' => 'jp_paytimes', 'value' => '支払回数'),
            array('name' => 'jp_paytimes_unit', 'value' => '回'),
            array('name' => 'jp_pdfinv_customise_product', 'value' => '【カスタマイズ商品】'),
            array('name' => 'jp_pdfinv_full_colon', 'value' => '：'),
            array('name' => 'jp_pdfinv_invoice', 'value' => '納 品 書'),
            array('name' => 'jp_pdfinv_not_assigned', 'value' => '設定なし'),
            array('name' => 'jp_pdfinv_not_required', 'value' => '指定不要'),
            array('name' => 'jp_pdfinv_other_option', 'value' => 'その他..'),
            array('name' => 'jp_pdfinv_page_subtotal', 'value' => 'ページ小計'),
            array('name' => 'jp_pdfinv_person_in_charge', 'value' => '担当'),
            array('name' => 'jp_pdfinv_tab_name', 'value' => 'PDF納品書'),
            array('name' => 'jp_pdfinv_zip_title', 'value' => '〒'),
            array('name' => 'jp_pez_company_code', 'value' => '収納機関番号'),
            array('name' => 'jp_pez_limit', 'value' => '支払期限'),
            array('name' => 'jp_pez_receipt_no', 'value' => '確認番号'),
            array('name' => 'jp_product_no_track', 'value' => '在庫あり'),
            array('name' => 'jp_products_in_wishlist', 'value' => 'ほしい物リスト内商品数'),
            array('name' => 'jp_remise_company_name', 'value' => 'ルミーズ株式会社'),
            array('name' => 'jp_remise_csp_notify_url', 'value' => '収納情報通知URL'),
            array('name' => 'jp_remise_csp_notify_url_notice', 'value' => '加盟店バックヤードシステムの「収納情報通知URL」には、<br /><strong>[notify_url]</strong><br />を登録してください。'),
            array('name' => 'jp_remise_cvs_info', 'value' => '[コンビニ決済情報]'),
            array('name' => 'jp_remise_cvs_name', 'value' => 'コンビ二名'),
            array('name' => 'jp_remise_goods_name', 'value' => '商品一式'),
            array('name' => 'jp_remise_host_id', 'value' => 'ホスト番号'),
            array('name' => 'jp_remise_payment_method', 'value' => '支払い方法'),
            array('name' => 'jp_remise_payquick', 'value' => 'ペイクイック機能'),
            array('name' => 'jp_remise_payquick_click_to_delete', 'value' => '登録済みのクレジットカード情報を削除するにはボタンをクリックしてください。'),
            array('name' => 'jp_remise_payquick_delete_card_info', 'value' => '登録済みカード情報の削除'),
            array('name' => 'jp_remise_payquick_delete_success', 'value' => 'クレジットカード情報を削除しました。'),
            array('name' => 'jp_remise_payquick_desc', 'value' => 'ペイクイック機能を使うと２回目以降のお買い物でクレジットカード情報の入力が不要になります。'),
            array('name' => 'jp_remise_payquick_no_card_info', 'value' => 'カード情報は登録されていません'),
            array('name' => 'jp_remise_payquick_registered_card', 'value' => '登録済みカード情報'),
            array('name' => 'jp_remise_plan', 'value' => 'ご契約プラン'),
            array('name' => 'jp_remise_result_url_notice', 'value' => '加盟店バックヤードシステムの「結果通知URL」には、<br /><strong>[result_url]</strong><br />を登録してください。'),
            array('name' => 'jp_remise_s_paydate', 'value' => '支払い期限（日）'),
            array('name' => 'jp_remise_shop_code', 'value' => '加盟店コード'),
            array('name' => 'jp_remise_url_production', 'value' => '本番環境接続先URL'),
            array('name' => 'jp_remise_url_test', 'value' => 'テスト環境接続先URL'),
            array('name' => 'jp_remise_use_payquick', 'value' => 'ペイクイック機能を使う'),
            array('name' => 'jp_sbps_company_name', 'value' => 'ソフトバンク・ペイメント・サービス株式会社'),
            array('name' => 'jp_sbps_connection_support', 'value' => '接続支援サイト'),
            array('name' => 'jp_sbps_etc', 'value' => 'etc'),
            array('name' => 'jp_sbps_hashkey', 'value' => 'ハッシュキー'),
            array('name' => 'jp_sbps_item_name', 'value' => 'お買い上げ商品'),
            array('name' => 'jp_sbps_merchant_id', 'value' => 'マーチャントID'),
            array('name' => 'jp_sbps_notes_header', 'value' => '【SBPS決済情報】'),
            array('name' => 'jp_sbps_notice', 'value' => 'ソフトバンク・ペイメント・サービスに関する設定情報を入力してください。<br />※ <a href="http://www.cs-cart.jp/sbps.html" target="_blank">ソフトバンク・ペイメント・サービスとの契約</a>が必要です。お申し込みは <a href="http://www.cs-cart.jp/sbps.html" target="_blank"><b>こちら</b></a>'),
            array('name' => 'jp_sbps_service_id', 'value' => 'サービスID'),
            array('name' => 'jp_sbps_settings_connection', 'value' => '接続設定'),
            array('name' => 'jp_sbps_tracking_id', 'value' => 'トラッキングID'),
            array('name' => 'jp_sbps_trans_method', 'value' => '決済方法'),
            array('name' => 'jp_sbps_url_connection_support', 'value' => '接続支援サイトURL'),
            array('name' => 'jp_sbps_url_production', 'value' => '本番環境接続先URL'),
            array('name' => 'jp_sbps_url_test', 'value' => 'テスト環境接続先URL'),
            array('name' => 'jp_share_link_on_facebook', 'value' => 'Facebookでリンクをシェア'),
            array('name' => 'jp_shipment_tracking_url', 'value' => '配達状況確認URL'),
            array('name' => 'jp_shipping_carrier', 'value' => '運送会社'),
            array('name' => 'jp_shipping_delivery_time', 'value' => 'お届け時間帯'),
            array('name' => 'jp_shipping_no_config_params', 'value' => '選択した配送サービスに関する設定項目はありません。'),
            array('name' => 'jp_shipping_origination', 'value' => '出発地点'),
            array('name' => 'jp_shipping_rates_setting', 'value' => '送料設定'),
            array('name' => 'jp_shipping_rates_setting_menu_description', 'value' => '各種配送サービスにおける料金テーブルの管理を行います。'),
            array('name' => 'jp_shipping_rates_updated', 'value' => '配送料金を更新しました。'),
            array('name' => 'jp_shipping_select_service_and_origin', 'value' => '配送サービスと出発地点を指定してください'),
            array('name' => 'jp_shipping_service_name', 'value' => '配送サービス名'),
            array('name' => 'jp_shipping_service_name_short', 'value' => 'サービス名'),
            array('name' => 'jp_shipping_size', 'value' => 'サイズ'),
            array('name' => 'jp_shipping_weight', 'value' => '重量'),
            array('name' => 'jp_supplier_arrange_delivery', 'value' => '商品発送依頼'),
            array('name' => 'jp_telecomcredit_clientip', 'value' => 'クライアントIP'),
            array('name' => 'jp_telecomcredit_company_name', 'value' => 'テレコムクレジット株式会社'),
            array('name' => 'jp_telecomcredit_error_title', 'value' => '決済エラー'),
            array('name' => 'jp_text_epsilon_error', 'value' => 'イプシロン決済エラー'),
            array('name' => 'jp_text_epsilon_notice', 'value' => 'イプシロン決済に関する設定情報を入力してください。'),
            array('name' => 'jp_text_images_export_directory', 'value' => 'エクスポート用画像の出力ディレクトリを絶対パスで指定してください。'),
            array('name' => 'jp_text_no_updates_available', 'value' => '利用可能なアップデートはありません'),
            array('name' => 'jp_text_not_u2d', 'value' => '<p>CS-Cart本体のバージョン <b>[pkg_ver]</b> と日本語版のバージョン <b>[jp_ver]</b> が一致しません。<br />すぐに日本語版をアップデートしてください。<br />アップデートファイルは<br /><a href="[url]" target="_blank" class="underlined"><b>[url]</b></a><br />からダウンロードできます。<br />また、日本語版のアップデートファイルをサーバーにアップロード済みの場合は、<a href="[url_ud]" class="underlined"><b>こちら</b></a> からアップデートできます。</p>'),
            array('name' => 'jp_text_remise_cc_notice', 'value' => 'ルミーズクレジットカード決済に関する設定情報を入力してください。'),
            array('name' => 'jp_text_remise_csp_notice', 'value' => 'ルミーズマルチ決済に関する設定情報を入力してください。'),
            array('name' => 'jp_text_telecomcredit_notice', 'value' => 'テレコムクレジット決済に関する設定情報を入力してください。'),
            array('name' => 'jp_text_update_completed', 'value' => 'お使いのCS-Cart日本語版は最新バージョンにアップデートされました。'),
            array('name' => 'jp_text_update_failed', 'value' => 'CS-Cart日本語版のアップデートに失敗しました。'),
            array('name' => 'jp_text_version_file_not_exists', 'value' => '日本語版バージョン管理ファイル<br /><b>[file_path]</b><br />が存在しません。<br />ファイルをサーバーにアップロードしてください。'),
            array('name' => 'jp_text_version_file_not_writable', 'value' => '日本語版バージョン管理ファイル<br /><b>[file_path]</b><br />に書き込みできません。<br />ファイルのパーミッションを 666 に変更してください。'),
            array('name' => 'jp_trial_expired', 'value' => '試用期間が終了しました'),
            array('name' => 'jp_trial_expired_and_closed', 'value' => '30日間の試用期間が終了し、お使いのCS-Cartマーケットプレイス版で構築したショップは一時クローズされました。<br />ショップを再びオープンするには、 <a href="http://store.cs-cart.jp/marketplace.html" target="_blank">こちら</a> よりライセンスを購入いただき、<a class="cm-dialog-opener cm-dialog-auto-size" data-ca-target-id="store_mode_dialog">ショップモード</a> ページからライセンス番号を登録してください。<br />ライセンスが正しく認証され、フルモードが有効化された後に 基本設定 -> 全般 -> ショップを一時クローズ のチェックを外すとショップを再オープンできます。'),
            array('name' => 'jp_update', 'value' => 'アップデート'),
            array('name' => 'jp_update_center', 'value' => '日本語版アップデート'),
            array('name' => 'jp_update_contents', 'value' => 'アップデート内容'),
            array('name' => 'jp_welcome', 'value' => 'ようこそ、'),
            array('name' => 'jp_zeus_clientip', 'value' => 'IPコード'),
            array('name' => 'jp_zeus_company_name', 'value' => '株式会社ゼウス'),
            array('name' => 'jp_zeus_notes_header', 'value' => '【ゼウスカード決済情報】'),
            array('name' => 'jp_zeus_notice', 'value' => 'ゼウスカード決済に関する設定情報を入力してください。'),
            array('name' => 'jp_zeus_ordd', 'value' => 'オーダーID'),
        );

        foreach ($languages as $lc => $_v) {
            foreach ($lang_variables as $k1 => $v1) {
                if (!empty($v1['name'])) {
                    preg_match("/(^[a-zA-z0-9][a-zA-Z0-9_]*)/", $v1['name'], $matches);
                    if (strlen($matches[0]) == strlen($v1['name'])) {
                        $v1['lang_code'] = $lc;
                        db_query("REPLACE INTO ?:language_values ?e", $v1);
                    }
                }
            }
        }
        /////////////////////////////////////////////////////////////////////////
        // 言語変数を追加 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 日本語版オリジナルの言語変数で英語の値をセットすべきものに対応 BOF
        /////////////////////////////////////////////////////////////////////////
        db_query("UPDATE ?:language_values SET value = 'Availability' WHERE name = 'jp_availability' AND lang_code = 'en'");
        db_query("UPDATE ?:language_values SET value = 'exc tax' WHERE name = 'jp_exc_tax' AND lang_code = 'en'");
        db_query("UPDATE ?:language_values SET value = 'Make a fresh snapshot' WHERE name = 'jp_make_a_fresh_snapshot' AND lang_code = 'en'");
        db_query("UPDATE ?:language_values SET value = 'Excluding tax' WHERE name = 'jp_excluding_tax' AND lang_code = 'en'");
        db_query("UPDATE ?:language_values SET value = ' (Advanced Booking Available)' WHERE name = 'jp_order_allowed' AND lang_code = 'en'");
        /////////////////////////////////////////////////////////////////////////
        // 日本語版オリジナルの言語変数で英語の値をセットすべきものに対応 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 既存の運送会社のソート順を変更 BOF
        /////////////////////////////////////////////////////////////////////////
        // 既存の運送会社ソート順
        $existing_carriers_sort = array(
            'fedex_enabled' => 100,
            'ups_enabled' => 110,
            'usps_enabled' => 120,
            'dhl_enabled' => 130,
            'aup_enabled' => 140,
            'can_enabled' => 150,
            'swisspost_enabled' => 160,
            'temando_enabled' => 170,
        );

        foreach ($existing_carriers_sort as $key => $value) {
            $_obj_id = Settings::instance()->getId($key, 'Shippings');
            Settings::instance()->update( array('object_id' => $_obj_id, 'position' => $value) );
        }
        /////////////////////////////////////////////////////////////////////////
        // 既存の運送会社のソート順を変更 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 日本国内配送用テーブルを作成しデータをセット BOF
        /////////////////////////////////////////////////////////////////////////
        // 運送会社
        $jp_carriers = array(
            'sagawa' => '佐川急便',
            'yamato' => 'ヤマト運輸',
            'fukutsu' => '福山通運',
            'jpems' => '日本郵便（海外向け）',
            'jpost' => '日本郵便',
        );

        // 配送サービス
        $jp_carrier_services = array(
            array('service_id' => 9000, 'carrier_code' => 'sagawa', 'service_code' => 'standard', 'service_name' => '飛脚宅配便', 'sort_order' => 1),
            array('service_id' => 9001, 'carrier_code' => 'sagawa', 'service_code' => 'cool', 'service_name' => '飛脚クール便', 'sort_order' => 2),
            array('service_id' => 9002, 'carrier_code' => 'yamato', 'service_code' => 'standard', 'service_name' => '宅急便', 'sort_order' => 1),
            array('service_id' => 9003, 'carrier_code' => 'yamato', 'service_code' => 'cool', 'service_name' => 'クール宅急便', 'sort_order' => 2),
            array('service_id' => 9006, 'carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'service_name' => 'パーセルワン', 'sort_order' => 1),
            array('service_id' => 9007, 'carrier_code' => 'jpems', 'service_code' => 'ems', 'service_name' => 'EMS', 'sort_order' => 1),
            array('service_id' => 9008, 'carrier_code' => 'jpost', 'service_code' => 'standard', 'service_name' => 'ゆうパック', 'sort_order' => 1)
        );

        // 配送地域
        $jp_carrier_zones = array(
            array('zone_id' => 1, 'carrier_code' => 'sagawa', 'zone_code' => 'L', 'zone_name' => '南九州', 'sort_order' => 1),
            array('zone_id' => 2, 'carrier_code' => 'sagawa', 'zone_code' => 'K', 'zone_name' => '北九州', 'sort_order' => 2),
            array('zone_id' => 3, 'carrier_code' => 'sagawa', 'zone_code' => 'J', 'zone_name' => '四国', 'sort_order' => 3),
            array('zone_id' => 4, 'carrier_code' => 'sagawa', 'zone_code' => 'I', 'zone_name' => '中国', 'sort_order' => 4),
            array('zone_id' => 5, 'carrier_code' => 'sagawa', 'zone_code' => 'H', 'zone_name' => '関西', 'sort_order' => 5),
            array('zone_id' => 6, 'carrier_code' => 'sagawa', 'zone_code' => 'G', 'zone_name' => '北陸', 'sort_order' => 6),
            array('zone_id' => 7, 'carrier_code' => 'sagawa', 'zone_code' => 'F', 'zone_name' => '東海', 'sort_order' => 7),
            array('zone_id' => 8, 'carrier_code' => 'sagawa', 'zone_code' => 'E', 'zone_name' => '信越', 'sort_order' => 8),
            array('zone_id' => 9, 'carrier_code' => 'sagawa', 'zone_code' => 'D', 'zone_name' => '関東', 'sort_order' => 9),
            array('zone_id' => 10, 'carrier_code' => 'sagawa', 'zone_code' => 'C', 'zone_name' => '南東北', 'sort_order' => 10),
            array('zone_id' => 11, 'carrier_code' => 'sagawa', 'zone_code' => 'B', 'zone_name' => '北東北', 'sort_order' => 11),
            array('zone_id' => 12, 'carrier_code' => 'sagawa', 'zone_code' => 'A', 'zone_name' => '北海道', 'sort_order' => 12),
            array('zone_id' => 13, 'carrier_code' => 'sagawa', 'zone_code' => 'M', 'zone_name' => '沖縄', 'sort_order' => 13),

            array('zone_id' => 14, 'carrier_code' => 'yamato', 'zone_code' => 'A', 'zone_name' => '北海道', 'sort_order' => 1),
            array('zone_id' => 15, 'carrier_code' => 'yamato', 'zone_code' => 'B', 'zone_name' => '北東北', 'sort_order' => 2),
            array('zone_id' => 16, 'carrier_code' => 'yamato', 'zone_code' => 'C', 'zone_name' => '南東北', 'sort_order' => 3),
            array('zone_id' => 17, 'carrier_code' => 'yamato', 'zone_code' => 'D', 'zone_name' => '関東', 'sort_order' => 4),
            array('zone_id' => 18, 'carrier_code' => 'yamato', 'zone_code' => 'E', 'zone_name' => '信越', 'sort_order' => 5),
            array('zone_id' => 19, 'carrier_code' => 'yamato', 'zone_code' => 'F', 'zone_name' => '中部', 'sort_order' => 6),
            array('zone_id' => 20, 'carrier_code' => 'yamato', 'zone_code' => 'G', 'zone_name' => '北陸', 'sort_order' => 7),
            array('zone_id' => 21, 'carrier_code' => 'yamato', 'zone_code' => 'H', 'zone_name' => '関西', 'sort_order' => 8),
            array('zone_id' => 22, 'carrier_code' => 'yamato', 'zone_code' => 'I', 'zone_name' => '中国', 'sort_order' => 9),
            array('zone_id' => 23, 'carrier_code' => 'yamato', 'zone_code' => 'J', 'zone_name' => '四国', 'sort_order' => 10),
            array('zone_id' => 24, 'carrier_code' => 'yamato', 'zone_code' => 'K', 'zone_name' => '九州', 'sort_order' => 11),
            array('zone_id' => 25, 'carrier_code' => 'yamato', 'zone_code' => 'L', 'zone_name' => '沖縄', 'sort_order' => 12),

            array('zone_id' => 37, 'carrier_code' => 'fukutsu', 'zone_code' => 'A', 'zone_name' => '北海道', 'sort_order' => 1),
            array('zone_id' => 38, 'carrier_code' => 'fukutsu', 'zone_code' => 'B', 'zone_name' => '北東北', 'sort_order' => 2),
            array('zone_id' => 39, 'carrier_code' => 'fukutsu', 'zone_code' => 'C', 'zone_name' => '南東北', 'sort_order' => 3),
            array('zone_id' => 40, 'carrier_code' => 'fukutsu', 'zone_code' => 'D', 'zone_name' => '関東', 'sort_order' => 4),
            array('zone_id' => 41, 'carrier_code' => 'fukutsu', 'zone_code' => 'E', 'zone_name' => '信越', 'sort_order' => 5),
            array('zone_id' => 42, 'carrier_code' => 'fukutsu', 'zone_code' => 'F', 'zone_name' => '北陸', 'sort_order' => 6),
            array('zone_id' => 43, 'carrier_code' => 'fukutsu', 'zone_code' => 'G', 'zone_name' => '中部', 'sort_order' => 7),
            array('zone_id' => 44, 'carrier_code' => 'fukutsu', 'zone_code' => 'H', 'zone_name' => '関西', 'sort_order' => 8),
            array('zone_id' => 45, 'carrier_code' => 'fukutsu', 'zone_code' => 'I', 'zone_name' => '中国', 'sort_order' => 9),
            array('zone_id' => 46, 'carrier_code' => 'fukutsu', 'zone_code' => 'J', 'zone_name' => '四国', 'sort_order' => 10),
            array('zone_id' => 47, 'carrier_code' => 'fukutsu', 'zone_code' => 'K', 'zone_name' => '九州', 'sort_order' => 11),

            array('zone_id' => 48, 'carrier_code' => 'jpems', 'zone_code' => 'A', 'zone_name' => '第1地帯', 'sort_order' => 1),
            array('zone_id' => 49, 'carrier_code' => 'jpems', 'zone_code' => 'B', 'zone_name' => '第2-1地帯', 'sort_order' => 2),
            array('zone_id' => 50, 'carrier_code' => 'jpems', 'zone_code' => 'C', 'zone_name' => '第2-2地帯', 'sort_order' => 3),
            array('zone_id' => 51, 'carrier_code' => 'jpems', 'zone_code' => 'D', 'zone_name' => '第3地帯', 'sort_order' => 4),
            array('zone_id' => 52, 'carrier_code' => 'jpems', 'zone_code' => 'Z', 'zone_name' => '日本国内', 'sort_order' => 99),

            array('zone_id' => 53, 'carrier_code' => 'jpost', 'zone_code' => 'A', 'zone_name' => '北海道', 'sort_order' => 1),
            array('zone_id' => 54, 'carrier_code' => 'jpost', 'zone_code' => 'B', 'zone_name' => '東北', 'sort_order' => 2),
            array('zone_id' => 55, 'carrier_code' => 'jpost', 'zone_code' => 'C', 'zone_name' => '関東', 'sort_order' => 3),
            array('zone_id' => 56, 'carrier_code' => 'jpost', 'zone_code' => 'D', 'zone_name' => '信越', 'sort_order' => 4),
            array('zone_id' => 57, 'carrier_code' => 'jpost', 'zone_code' => 'E', 'zone_name' => '北陸', 'sort_order' => 5),
            array('zone_id' => 58, 'carrier_code' => 'jpost', 'zone_code' => 'F', 'zone_name' => '東海', 'sort_order' => 6),
            array('zone_id' => 59, 'carrier_code' => 'jpost', 'zone_code' => 'G', 'zone_name' => '近畿', 'sort_order' => 7),
            array('zone_id' => 60, 'carrier_code' => 'jpost', 'zone_code' => 'H', 'zone_name' => '中国', 'sort_order' => 8),
            array('zone_id' => 61, 'carrier_code' => 'jpost', 'zone_code' => 'I', 'zone_name' => '四国', 'sort_order' => 9),
            array('zone_id' => 62, 'carrier_code' => 'jpost', 'zone_code' => 'J', 'zone_name' => '九州', 'sort_order' => 10),
            array('zone_id' => 63, 'carrier_code' => 'jpost', 'zone_code' => 'K', 'zone_name' => '沖縄', 'sort_order' => 11),
        );

        // 送料
        $jp_shipping_rates = fn_lcjp_get_jp_shipping_rates_table();

        // プリセットする配送方法
        $jp_shipping_methods = array(
            array('shipping' => '飛脚宅配便', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9000, 'service_params' => array('jp_shipping' => 'Y')),
            array('shipping' => '飛脚クール便', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9001, 'service_params' => array('jp_shipping' => 'Y')),
            array('shipping' => '宅急便', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9002, 'service_params' => array('jp_shipping' => 'Y')),
            array('shipping' => 'クール宅急便', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9003, 'service_params' => array('jp_shipping' => 'Y')),
            array('shipping' => 'パーセルワン', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9006, 'service_params' => array('jp_shipping' => 'Y')),
            array('shipping' => 'EMS', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9007, 'service_params' => array('jp_shipping' => 'Y')),
            array('shipping' => 'ゆうパック', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9008, 'service_params' => array('jp_shipping' => 'Y')),
            array('shipping' => 'チルドゆうパック', 'min_weight' => '0.00', 'rate_calculation' => 'R', 'service_id' => 9008, 'service_params' => array('jp_shipping' => 'Y'))
        );

        $_data = array();
        $cnt = 0;
        db_query("DROP TABLE IF EXISTS ?:jp_carriers");
        db_query("CREATE TABLE ?:jp_carriers (carrier_id mediumint(8) unsigned NOT NULL auto_increment, carrier_code varchar(32) NOT NULL, carrier_name varchar(32) NOT NULL, lang_code varchar(4) NOT NULL, PRIMARY KEY  (carrier_id, lang_code)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        foreach ($jp_carriers as $key => $value) {
            $cnt++;
            // 運送会社に関する情報を配列にセット
            $_data['carrier_id'] = $cnt;
            $_data['carrier_code'] = $key;
            $_data['carrier_name'] = $value;

            // jp_carriersテーブルに運送会社のレコードを追加
            foreach ($languages as $_data['lang_code'] => $_v) {
                db_query("INSERT INTO ?:jp_carriers ?e", $_data);
            }
        }

        // 配送サービス
        $_data = array();
        $cnt = 0;
        db_query("DROP TABLE IF EXISTS ?:jp_carrier_services");
        db_query("CREATE TABLE ?:jp_carrier_services (service_id mediumint(8) unsigned NOT NULL auto_increment, carrier_code varchar(32) NOT NULL, service_code varchar(32) NOT NULL, service_name varchar(32) NOT NULL, lang_code varchar(4) NOT NULL, sort_order tinyint(3) unsigned NOT NULL, PRIMARY KEY  (service_id, lang_code)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        foreach ($jp_carrier_services as $_data) {
            $cnt++;

            // shipping_servicesテーブルに運送サービスに関するレコードを追加
            $_data_shipping_services = array();
            $_data_shipping_service_desc = array();

            $_data_shipping_services['service_id'] = $_data['service_id'];
            $_data_shipping_services['status'] = 'A';
            $_data_shipping_services['module'] = $_data['carrier_code'];
            $_data_shipping_services['code'] = strtoupper($_data['service_code']);
            $_data_shipping_service_desc['service_id'] = $_data['service_id'];

            db_query("INSERT INTO ?:shipping_services ?e", $_data_shipping_services);

            $_data['service_id'] = $cnt;

            foreach ($languages as $_data['lang_code'] => $_v) {
                // jp_carrier_servicesテーブルに運送サービスに関するレコードを追加
                db_query("INSERT INTO ?:jp_carrier_services ?e", $_data);

                // shipping_service_descriptionsテーブルに運送サービスに関するレコードを追加
                $_data_shipping_service_desc['lang_code'] = $_data['lang_code'];
                $_data_shipping_service_desc['description'] = $_data['service_name'];
                db_query("INSERT INTO ?:shipping_service_descriptions ?e", $_data_shipping_service_desc);
            }
        }

        // 配送地域
        $_data = array();
        $cnt = 0;
        db_query("DROP TABLE IF EXISTS ?:jp_carrier_zones");
        db_query("CREATE TABLE ?:jp_carrier_zones (zone_id mediumint(8) unsigned NOT NULL auto_increment, carrier_code varchar(32) NOT NULL, zone_code varchar(2) NOT NULL, zone_name varchar(12) NOT NULL, lang_code varchar(4) NOT NULL, sort_order tinyint(3) unsigned NOT NULL, PRIMARY KEY  (zone_id, lang_code)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        foreach ($jp_carrier_zones as $_data) {
            $cnt++;

            foreach ($languages as $_data['lang_code'] => $_v) {
                db_query("INSERT INTO ?:jp_carrier_zones ?e", $_data);
            }
        }

        // 送料
        $_data = array();
        db_query("DROP TABLE IF EXISTS ?:jp_shipping_rates");
        db_query("CREATE TABLE ?:jp_shipping_rates (rate_id mediumint(8) unsigned NOT NULL auto_increment, company_id mediumint(8) NOT NULL DEFAULT '0', carrier_code varchar(32) NOT NULL, service_code varchar(12) NOT NULL, zone_id mediumint(8) unsigned NOT NULL, shipping_rates text, PRIMARY KEY (rate_id)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");

        foreach ($jp_shipping_rates as $_data) {
            $_data['shipping_rates'] = serialize($_data['shipping_rates']);
            db_query("INSERT INTO ?:jp_shipping_rates ?e", $_data);
        }

        // 登録済みのショップ（出品者）のIDを取得
        $company_ids = db_get_fields("SELECT company_id FROM ?:companies");

        // 登録済みのショップ（出品者）が存在する場合
        if( !empty($company_ids) ){
            // デフォルトの送料情報を取得
            $default_shipping_rates = db_get_array("SELECT * FROM ?:jp_shipping_rates WHERE company_id = ?i", 0);

            // デフォルトの送料情報が存在する場合
            if( !empty($default_shipping_rates) && is_array($default_shipping_rates) ){
                // デフォルトの送料情報を新しいショップ（出品者）向けにコピー
                foreach($default_shipping_rates as $rate_info){
                    $_data = $rate_info;
                    unset($_data['rate_id']);
                    foreach( $company_ids as $company_id){
                        $_data['company_id'] = $company_id;
                        db_query("REPLACE INTO ?:jp_shipping_rates ?e", $_data);
                    }
                }
            }
        }

        // 日本向け配送方法を追加
        $_chilled_id = 0;
        foreach ($jp_shipping_methods as $_data) {
            $sid = fn_update_shipping($_data, '', $languages);

            // 登録済みのすべてのショップにおいて有効にする
            if (fn_allowed_for('ULTIMATE')){
                fn_share_object_to_all('shippings', $sid);
            }

            if($_data['shipping'] == 'チルドゆうパック') {
                $_chilled_id = $sid;
            }
        }
        // 日本郵便（チルドゆうパック）に送料データを追加する
        if($_chilled_id != 0) {
            $_rate_value = 'a:1:{s:1:"W";a:3:{i:0;a:3:{s:5:"value";d:190;s:4:"type";s:1:"F";s:8:"per_unit";s:1:"N";}i:4;a:3:{s:5:"value";d:340;s:4:"type";s:1:"F";s:8:"per_unit";s:1:"N";}i:8;a:3:{s:5:"value";d:640;s:4:"type";s:1:"F";s:8:"per_unit";s:1:"N";}}}';
            db_query("REPLACE INTO ?:shipping_rates (rate_value, destination_id, shipping_id) VALUES(?s, ?i, ?i)", $_rate_value, 0, $_chilled_id);
        }

        /////////////////////////////////////////////////////////////////////////
        // 日本国内配送用テーブルを作成しデータをセット EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 各配送方法のお届け希望日の表示内容登を録するテーブルを作成 BOF
        /////////////////////////////////////////////////////////////////////////
        db_query("DROP TABLE IF EXISTS ?:jp_delivery_date");
        db_query("CREATE TABLE ?:jp_delivery_date (delivery_id mediumint(8) unsigned NOT NULL auto_increment, shipping_id mediumint(8) unsigned NOT NULL, delivery_status varchar(1) NOT NULL, delivery_from smallint(6) NOT NULL, delivery_to smallint(6) NOT NULL, include_holidays varchar(1) NOT NULL, PRIMARY KEY (`delivery_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        /////////////////////////////////////////////////////////////////////////
        // 各配送方法のお届け希望日の表示内容を登録するテーブルを作成 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 注文における各配送方法のお届け時間帯・希望日を登録するテーブルを作成 BOF
        /////////////////////////////////////////////////////////////////////////
        db_query("DROP TABLE IF EXISTS ?:jp_order_delivery_info");
        db_query("CREATE TABLE ?:jp_order_delivery_info (order_id mediumint(8) unsigned NOT NULL, shipping_id mediumint(8) unsigned NOT NULL, group_key mediumint(8) unsigned NOT NULL,delivery_date varchar(64) NOT NULL default '', delivery_timing varchar(64) NOT NULL default '', PRIMARY KEY (`order_id`, `shipping_id`, `group_key`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        /////////////////////////////////////////////////////////////////////////
        // 注文における各配送方法のお届け時間帯・希望日を登録するテーブルを作成 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 日本では使用しない支払方法を削除 BOF
        /////////////////////////////////////////////////////////////////////////
        $payment_ids = db_get_fields("SELECT payment_id FROM ?:payment_descriptions WHERE payment IN ('Check', 'Money Order', 'Purchase Order', 'Personal Check', 'Business Check', 'Government Check', 'Traveller\'s Check') AND lang_code = 'en'");

        foreach($payment_ids as $payment_id){
            fn_delete_payment($payment_id);
            if (fn_allowed_for('ULTIMATE')){
                db_query('DELETE FROM ?:ult_objects_sharing WHERE share_object_id = ?s AND share_object_type = ?s', $payment_id, 'payments');
            }
        }
        /////////////////////////////////////////////////////////////////////////
        // 日本では使用しない支払方法を削除 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 支払方法の設定 BOF
        /////////////////////////////////////////////////////////////////////////
        // 追加する支払い方法
        $payment_methods = array(
            array('processor_id' => 9000,
                'processor' => 'ルミーズマルチ決済',
                'processor_script' => 'remise_csp.php',
                'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
                'admin_template' => 'remise_csp.tpl',
                'callback' => 'N',
                'type' => 'P',
            ),
            array('processor_id' => 9001,
                'processor' => 'ルミーズクレジットカード決済',
                'processor_script' => 'remise_cc.php',
                'processor_template' => 'views/orders/components/payments/remise_cc.tpl',
                'admin_template' => 'remise_cc.tpl',
                'callback' => 'N',
                'type' => 'P',
            ),
            array('processor_id' => 9004,
                'processor' => 'テレコムクレジット',
                'processor_script' => 'telecomcredit.php',
                'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
                'admin_template' => 'telecomcredit.tpl',
                'callback' => 'N',
                'type' => 'P',
            ),
            array('processor_id' => 9005,
                'processor' => 'イプシロン',
                'processor_script' => 'epsilon.php',
                'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
                'admin_template' => 'epsilon.tpl',
                'callback' => 'N',
                'type' => 'P',
            ),
            array('processor_id' => 9010,
                'processor' => 'ソフトバンク・ペイメント・サービス',
                'processor_script' => 'sbps.php',
                'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
                'admin_template' => 'sbps.tpl',
                'callback' => 'N',
                'type' => 'P',
            ),
            array('processor_id' => 9050,
                'processor' => 'ゼウスカード決済（リンク型）',
                'processor_script' => 'zeus.php',
                'processor_template' => 'views/orders/components/payments/cc_outside.tpl',
                'admin_template' => 'zeus.tpl',
                'callback' => 'N',
                'type' => 'P',
            ),
        );

        // プリセットする支払方法
        $payment_preset = array(
            array('usergroup_ids' => '0',
                'position' => 20,
                'status' => 'A',
                'template' => 'views/orders/components/payments/cc_outside.tpl',
                'params' => '',
                'a_surcharge' => '',
                'p_surcharge' => '',
                'localization' => '',
                'payment' => '銀行振込',
                'description' => '',
                'processor_script' => ''
            ),
        );

        $_data = array();
        foreach ( $payment_methods as $_data) {
            db_query("REPLACE INTO ?:payment_processors ?e", $_data);
        }

        $_data = array();
        foreach ($payment_preset as $_data) {
            $payment_id = fn_update_payment($_data, '');
            // 登録済みのすべてのショップにおいて有効にする
            if (fn_allowed_for('ULTIMATE')){
                fn_share_object_to_all('payments', $payment_id);
            }
        }

        // 各支払方法の支払カテゴリーは一律「tab3（その他の支払方法）に変更する
        db_query('UPDATE ?:payments SET payment_category = ?s', 'tab3');

        // クレジットカード決済において２回目以降のカード情報入力を省略するためのテーブルを作成
        db_query("CREATE TABLE ?:jp_cc_quickpay (user_id mediumint(8) NOT NULL, payment_method varchar(64) NOT NULL, quickpay_id varchar(64) NOT NULL, PRIMARY KEY (user_id, payment_method)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        /////////////////////////////////////////////////////////////////////////
        // 支払方法の設定 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 注文ステータスのソート順を管理するテーブルを作成 BOF
        /////////////////////////////////////////////////////////////////////////
        db_query("DROP TABLE IF EXISTS ?:jp_order_status_sort");
        db_query("CREATE TABLE ?:jp_order_status_sort (status char(1) NOT NULL default '', sort_id mediumint(8) unsigned NOT NULL, PRIMARY KEY (`status`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;");
        /////////////////////////////////////////////////////////////////////////
        // 注文ステータスのソート順を管理するテーブルを作成 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // CS-Cartのインストール日時を更新 BOF
        /////////////////////////////////////////////////////////////////////////
        $_data_install_date = array();
        $_data_install_date['object_id'] = 70024;
        $_data_install_date['value'] = time();
        $_data_install_date['object_type'] = 'O';

        foreach ($languages as $_data_install_date['lang_code'] => $_v) {
            Settings::instance()->updateDescription( $_data_install_date );
        }
        /////////////////////////////////////////////////////////////////////////
        // CS-Cartのインストール日時を更新 BOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 注文一覧とアドオン一覧に設定されたビューの名称を日本語化 BOF
        /////////////////////////////////////////////////////////////////////////
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', '新規', 1, 'orders');
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', '処理待ち', 2, 'orders');
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', '未完了', 3, 'orders');
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', '本日分', 4, 'orders');
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', '未インストール', 13, 'addons');
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', 'インストール済', 14, 'addons');
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', '無効', 15, 'addons');
        db_query('UPDATE ?:views SET name = ?s WHERE view_id =?i AND object =?s', '有効', 16, 'addons');
        /////////////////////////////////////////////////////////////////////////
        // 注文一覧とアドオン一覧に設定されたビューの名称を日本語化 EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // Amazonログイン&ペイメントアドオンをフルモードでのみ利用可能に BOF
        /////////////////////////////////////////////////////////////////////////
        db_query("REPLACE INTO ?:storage_data (data_key, data) VALUES(?s, ?s)", 'addons_snapshots', '51371ebab8cb424c75d867fe73e50bed,6775b3701963b392791fa8c687bac742,d0091c6ed89f0aedd76a40863775277a,da2fd5324f611f2b1d8b4fef9ae3179e,1c80f2768de5b4fb4d2b3944d370cc7a,c69b98dafc2dfacb204cfd71400f3ca8,03121c8182a3b49ee95c327b6d3940b2,235acb56aad0eac7acf7ce56c756115c,12f8524c45544e9bb9448c45bd191081,449a22bd4fd9e552309e9175dec5745e,bd8bc36eb41bc90c585ae7e902e9e284,4c3b118e2c4d898d99f7ed6756f239f0,9beedfe36624c1c064be3382b97f2eb7,bcafeedd7dd058cb267db6bfb7086f27,68249180d0f8ced902a75a5444104dd4,3b8c35e3f8f78f15c6e98f33345ad991,b50e298ae54c7c326d21425c9bc59a39,90b93be7713dbd6bad07926f7d6eb55f,c06cd01ce149aa26966db5feaccfef6c,eaf45716a98a4bafe872c75c4d245b32,9292d36f62272ba6fc7cd9f3b04f79f9,879494ec811609b65a1d03fdba267b21,952e8cf2c863b8ddc656bac6ad0b729b,5a8b93606dea3db356b524f621e7a8bb,e9741eb2a4ec7d4bc13ce20d13627fc6,7bc397e032bdaae9dca38e5f5452f9a6,a1eff01a6862aea0d5237eb513a378d3,d590327cacc0208d3dcb54fe719e5831,32dc190b81f0b4dd9911972550576baa,281211c4c174214495bd2deb623e9b9e,bf9ad0cf4d2ffc6e54348937e904b667,694779637169a7bc5536f826faa0a05f,da2b534385b751f3fb550c43198dc87c,d9ddf16079b7ba158c82e819d2c363d1,d2e43e8c7123cdf91e4edd3380281d75,aadb0c6e3f30f8db66b89578b82a8a35,c8e43e20a7128fc60f2425a93a0f82c2,b3230f212f048d3087bf992923735b84,0642f2352e66f384142539f5cdd39491,2506ead1700ca25630c8123e5d2a205d,ecbc903855420b66f9132051f282d08d,6831915d94c2407bba96774a64b92dd5,126ac9f6149081eb0e97c2e939eaad52,9b1506af19d73a7a113458414544c6df,6a96538f14b69b31f469028c921b05c7,509f8c419805dc16e7bd457e29155ef3,a8d2d1bc25ab0c4691aa6940d405f091,d57ac45256849d9b13e2422d91580fb9,9f71cf66aabc45aca700ccd19d277437,135ec079d5684f3b4a5ee738fe5932b8');
        /////////////////////////////////////////////////////////////////////////
        // Amazonログイン&ペイメントアドオンをフルモードでのみ利用可能に EOF
        /////////////////////////////////////////////////////////////////////////

        /////////////////////////////////////////////////////////////////////////
        // 試用期間を「パッケージダウンロードから30日間」から
        // 「インストールから30日間」に変更 BOF
        /////////////////////////////////////////////////////////////////////////
        $installation_timestamp = TIME;
        db_query("UPDATE ?:settings_objects SET value = $installation_timestamp WHERE name = 'current_timestamp'");
        /////////////////////////////////////////////////////////////////////////
        // 試用期間を「パッケージダウンロードから30日間」から
        // 「インストールから30日間」に変更 EOF
        /////////////////////////////////////////////////////////////////////////
}




function fn_lcjp_get_jp_shipping_rates_table()
{
    return array(
        // 佐川急便 - 飛脚宅配便
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 1, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1790, 'B' => 1370, 'C' => 1370, 'D' => 1160, 'E' => 1160, 'F' => 950, 'G' => 950, 'H' => 840, 'I' => 740, 'J' => 840, 'K' => 740, 'L' => 740, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2050, 'B' => 1630, 'C' => 1630, 'D' => 1420, 'E' => 1420, 'F' => 1210, 'G' => 1210, 'H' => 1110, 'I' => 1000, 'J' => 1110, 'K' => 1000, 'L' => 1000, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2310, 'B' => 1890, 'C' => 1890, 'D' => 1680, 'E' => 1680, 'F' => 1470, 'G' => 1470, 'H' => 1370, 'I' => 1260, 'J' => 1370, 'K' => 1260, 'L' => 1260, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2580, 'B' => 2160, 'C' => 2160, 'D' => 1950, 'E' => 1950, 'F' => 1740, 'G' => 1740, 'H' => 1630, 'I' => 1530, 'J' => 1630, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2840, 'B' => 2420, 'C' => 2420, 'D' => 2210, 'E' => 2210, 'F' => 2000, 'G' => 2000, 'H' => 1890, 'I' => 1790, 'J' => 1890, 'K' => 1790, 'L' => 1790, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 3380, 'B' => 2960, 'C' => 2960, 'D' => 2750, 'E' => 2750, 'F' => 2540, 'G' => 2540, 'H' => 2430, 'I' => 2330, 'J' => 2430, 'K' => 2330, 'L' => 2330, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3650, 'B' => 3230, 'C' => 3230, 'D' => 3020, 'E' => 3020, 'F' => 2810, 'G' => 2810, 'H' => 2700, 'I' => 2600, 'J' => 2700, 'K' => 2600, 'L' => 2600, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 4190, 'B' => 3770, 'C' => 3770, 'D' => 3560, 'E' => 3560, 'F' => 3350, 'G' => 3350, 'H' => 3240, 'I' => 3140, 'J' => 3240, 'K' => 3140, 'L' => 3140, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4730, 'B' => 4310, 'C' => 4310, 'D' => 4100, 'E' => 4100, 'F' => 3890, 'G' => 3890, 'H' => 3780, 'I' => 3680, 'J' => 3780, 'K' => 3680, 'L' => 3680, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5810, 'B' => 5390, 'C' => 5390, 'D' => 5180, 'E' => 5180, 'F' => 4970, 'G' => 4970, 'H' => 4860, 'I' => 4760, 'J' => 4860, 'K' => 4760, 'L' => 4760, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6890, 'B' => 6470, 'C' => 6470, 'D' => 6260, 'E' => 6260, 'F' => 6050, 'G' => 6050, 'H' => 5940, 'I' => 5840, 'J' => 5940, 'K' => 5840, 'L' => 5840, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 8240, 'B' => 7820, 'C' => 7820, 'D' => 7610, 'E' => 7610, 'F' => 7400, 'G' => 7400, 'H' => 7290, 'I' => 7190, 'J' => 7290, 'K' => 7190, 'L' => 7190, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 9590, 'B' => 9170, 'C' => 9170, 'D' => 8960, 'E' => 8960, 'F' => 8750, 'G' => 8750, 'H' => 8640, 'I' => 8540, 'J' => 8640, 'K' => 8540, 'L' => 8540, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 14180, 'B' => 13760, 'C' => 13760, 'D' => 13550, 'E' => 13550, 'F' => 13340, 'G' => 13340, 'H' => 13230, 'I' => 13130, 'J' => 13230, 'K' => 13130, 'L' => 13130, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19850, 'B' => 19430, 'C' => 19430, 'D' => 19220, 'E' => 19220, 'F' => 19010, 'G' => 19010, 'H' => 18900, 'I' => 18800, 'J' => 18900, 'K' => 18800, 'L' => 18800, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27680, 'B' => 27260, 'C' => 27260, 'D' => 27050, 'E' => 27050, 'F' => 26840, 'G' => 26840, 'H' => 26730, 'I' => 26630, 'J' => 26730, 'K' => 26630, 'L' => 26630, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 2, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1790, 'B' => 1370, 'C' => 1370, 'D' => 1160, 'E' => 1160, 'F' => 950, 'G' => 950, 'H' => 840, 'I' => 740, 'J' => 840, 'K' => 740, 'L' => 740, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2050, 'B' => 1630, 'C' => 1630, 'D' => 1420, 'E' => 1420, 'F' => 1210, 'G' => 1210, 'H' => 1110, 'I' => 1000, 'J' => 1110, 'K' => 1000, 'L' => 1000, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2310, 'B' => 1890, 'C' => 1890, 'D' => 1680, 'E' => 1680, 'F' => 1470, 'G' => 1470, 'H' => 1370, 'I' => 1260, 'J' => 1370, 'K' => 1260, 'L' => 1260, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2580, 'B' => 2160, 'C' => 2160, 'D' => 1950, 'E' => 1950, 'F' => 1740, 'G' => 1740, 'H' => 1630, 'I' => 1530, 'J' => 1630, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2840, 'B' => 2420, 'C' => 2420, 'D' => 2210, 'E' => 2210, 'F' => 2000, 'G' => 2000, 'H' => 1890, 'I' => 1790, 'J' => 1890, 'K' => 1790, 'L' => 1790, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 3380, 'B' => 2960, 'C' => 2960, 'D' => 2750, 'E' => 2750, 'F' => 2540, 'G' => 2540, 'H' => 2430, 'I' => 2330, 'J' => 2430, 'K' => 2330, 'L' => 2330, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3650, 'B' => 3230, 'C' => 3230, 'D' => 3020, 'E' => 3020, 'F' => 2810, 'G' => 2810, 'H' => 2700, 'I' => 2600, 'J' => 2700, 'K' => 2600, 'L' => 2600, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 4190, 'B' => 3770, 'C' => 3770, 'D' => 3560, 'E' => 3560, 'F' => 3350, 'G' => 3350, 'H' => 3240, 'I' => 3140, 'J' => 3240, 'K' => 3140, 'L' => 3140, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4730, 'B' => 4310, 'C' => 4310, 'D' => 4100, 'E' => 4100, 'F' => 3890, 'G' => 3890, 'H' => 3780, 'I' => 3680, 'J' => 3780, 'K' => 3680, 'L' => 3680, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5810, 'B' => 5390, 'C' => 5390, 'D' => 5180, 'E' => 5180, 'F' => 4970, 'G' => 4970, 'H' => 4860, 'I' => 4760, 'J' => 4860, 'K' => 4760, 'L' => 4760, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6890, 'B' => 6470, 'C' => 6470, 'D' => 6260, 'E' => 6260, 'F' => 6050, 'G' => 6050, 'H' => 5940, 'I' => 5840, 'J' => 5940, 'K' => 5840, 'L' => 5840, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 8240, 'B' => 7820, 'C' => 7820, 'D' => 7610, 'E' => 7610, 'F' => 7400, 'G' => 7400, 'H' => 7290, 'I' => 7190, 'J' => 7290, 'K' => 7190, 'L' => 7190, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 9590, 'B' => 9170, 'C' => 9170, 'D' => 8960, 'E' => 8960, 'F' => 8750, 'G' => 8750, 'H' => 8640, 'I' => 8540, 'J' => 8640, 'K' => 8540, 'L' => 8540, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 14180, 'B' => 13760, 'C' => 13760, 'D' => 13550, 'E' => 13550, 'F' => 13340, 'G' => 13340, 'H' => 13230, 'I' => 13130, 'J' => 13230, 'K' => 13130, 'L' => 13130, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19850, 'B' => 19430, 'C' => 19430, 'D' => 19220, 'E' => 19220, 'F' => 19010, 'G' => 19010, 'H' => 18900, 'I' => 18800, 'J' => 18900, 'K' => 18800, 'L' => 18800, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27680, 'B' => 27260, 'C' => 27260, 'D' => 27050, 'E' => 27050, 'F' => 26840, 'G' => 26840, 'H' => 26730, 'I' => 26630, 'J' => 26730, 'K' => 26630, 'L' => 26630, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 3, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1680, 'B' => 1260, 'C' => 1260, 'D' => 1050, 'E' => 1050, 'F' => 950, 'G' => 950, 'H' => 840, 'I' => 840, 'J' => 740, 'K' => 840, 'L' => 840, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1950, 'B' => 1530, 'C' => 1530, 'D' => 1320, 'E' => 1320, 'F' => 1210, 'G' => 1210, 'H' => 1110, 'I' => 1110, 'J' => 1000, 'K' => 1110, 'L' => 1110, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2210, 'B' => 1790, 'C' => 1790, 'D' => 1580, 'E' => 1580, 'F' => 1470, 'G' => 1470, 'H' => 1370, 'I' => 1370, 'J' => 1260, 'K' => 1370, 'L' => 1370, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2470, 'B' => 2050, 'C' => 2050, 'D' => 1840, 'E' => 1840, 'F' => 1740, 'G' => 1740, 'H' => 1630, 'I' => 1630, 'J' => 1530, 'K' => 1630, 'L' => 1630, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2730, 'B' => 2310, 'C' => 2310, 'D' => 2100, 'E' => 2100, 'F' => 2000, 'G' => 2000, 'H' => 1890, 'I' => 1890, 'J' => 1790, 'K' => 1890, 'L' => 1890, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 3270, 'B' => 2850, 'C' => 2850, 'D' => 2640, 'E' => 2640, 'F' => 2540, 'G' => 2540, 'H' => 2430, 'I' => 2430, 'J' => 2330, 'K' => 2430, 'L' => 2430, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3540, 'B' => 3120, 'C' => 3120, 'D' => 2910, 'E' => 2910, 'F' => 2810, 'G' => 2810, 'H' => 2700, 'I' => 2700, 'J' => 2600, 'K' => 2700, 'L' => 2700, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 4080, 'B' => 3660, 'C' => 3660, 'D' => 3450, 'E' => 3450, 'F' => 3350, 'G' => 3350, 'H' => 3240, 'I' => 3240, 'J' => 3140, 'K' => 3240, 'L' => 3240, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4620, 'B' => 4200, 'C' => 4200, 'D' => 3990, 'E' => 3990, 'F' => 3890, 'G' => 3890, 'H' => 3780, 'I' => 3780, 'J' => 3680, 'K' => 3780, 'L' => 3780, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5700, 'B' => 5280, 'C' => 5280, 'D' => 5070, 'E' => 5070, 'F' => 4970, 'G' => 4970, 'H' => 4860, 'I' => 4860, 'J' => 4760, 'K' => 4860, 'L' => 4860, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6780, 'B' => 6360, 'C' => 6360, 'D' => 6150, 'E' => 6150, 'F' => 6050, 'G' => 6050, 'H' => 5940, 'I' => 5940, 'J' => 5840, 'K' => 5940, 'L' => 5940, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 8130, 'B' => 7710, 'C' => 7710, 'D' => 7500, 'E' => 7500, 'F' => 7400, 'G' => 7400, 'H' => 7290, 'I' => 7290, 'J' => 7190, 'K' => 7290, 'L' => 7290, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 9480, 'B' => 9060, 'C' => 9060, 'D' => 8850, 'E' => 8850, 'F' => 8750, 'G' => 8750, 'H' => 8640, 'I' => 8640, 'J' => 8540, 'K' => 8640, 'L' => 8640, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 14070, 'B' => 13650, 'C' => 13650, 'D' => 13440, 'E' => 13440, 'F' => 13340, 'G' => 13340, 'H' => 13230, 'I' => 13230, 'J' => 13130, 'K' => 13230, 'L' => 13230, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19740, 'B' => 19320, 'C' => 19320, 'D' => 19110, 'E' => 19110, 'F' => 19010, 'G' => 19010, 'H' => 18900, 'I' => 18900, 'J' => 18800, 'K' => 18900, 'L' => 18900, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27570, 'B' => 27150, 'C' => 27150, 'D' => 26940, 'E' => 26940, 'F' => 26840, 'G' => 26840, 'H' => 26730, 'I' => 26730, 'J' => 26630, 'K' => 26730, 'L' => 26730, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 4, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1580, 'B' => 1160, 'C' => 1160, 'D' => 950, 'E' => 950, 'F' => 840, 'G' => 840, 'H' => 740, 'I' => 740, 'J' => 840, 'K' => 740, 'L' => 740, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1840, 'B' => 1420, 'C' => 1420, 'D' => 1210, 'E' => 1210, 'F' => 1110, 'G' => 1110, 'H' => 1000, 'I' => 1000, 'J' => 1110, 'K' => 1000, 'L' => 1000, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2100, 'B' => 1680, 'C' => 1680, 'D' => 1470, 'E' => 1470, 'F' => 1370, 'G' => 1370, 'H' => 1260, 'I' => 1260, 'J' => 1370, 'K' => 1260, 'L' => 1260, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2370, 'B' => 1950, 'C' => 1950, 'D' => 1740, 'E' => 1740, 'F' => 1630, 'G' => 1630, 'H' => 1530, 'I' => 1530, 'J' => 1630, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2630, 'B' => 2210, 'C' => 2210, 'D' => 2000, 'E' => 2000, 'F' => 1890, 'G' => 1890, 'H' => 1790, 'I' => 1790, 'J' => 1890, 'K' => 1790, 'L' => 1790, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 3170, 'B' => 2750, 'C' => 2750, 'D' => 2540, 'E' => 2540, 'F' => 2430, 'G' => 2430, 'H' => 2330, 'I' => 2330, 'J' => 2430, 'K' => 2330, 'L' => 2330, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3440, 'B' => 3020, 'C' => 3020, 'D' => 2810, 'E' => 2810, 'F' => 2700, 'G' => 2700, 'H' => 2600, 'I' => 2600, 'J' => 2700, 'K' => 2600, 'L' => 2600, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3980, 'B' => 3560, 'C' => 3560, 'D' => 3350, 'E' => 3350, 'F' => 3240, 'G' => 3240, 'H' => 3140, 'I' => 3140, 'J' => 3240, 'K' => 3140, 'L' => 3140, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4520, 'B' => 4100, 'C' => 4100, 'D' => 3890, 'E' => 3890, 'F' => 3780, 'G' => 3780, 'H' => 3680, 'I' => 3680, 'J' => 3780, 'K' => 3680, 'L' => 3680, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5600, 'B' => 5180, 'C' => 5180, 'D' => 4970, 'E' => 4970, 'F' => 4860, 'G' => 4860, 'H' => 4760, 'I' => 4760, 'J' => 4860, 'K' => 4760, 'L' => 4760, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6680, 'B' => 6260, 'C' => 6260, 'D' => 6050, 'E' => 6050, 'F' => 5940, 'G' => 5940, 'H' => 5840, 'I' => 5840, 'J' => 5940, 'K' => 5840, 'L' => 5840, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 8030, 'B' => 7610, 'C' => 7610, 'D' => 7400, 'E' => 7400, 'F' => 7290, 'G' => 7290, 'H' => 7190, 'I' => 7190, 'J' => 7290, 'K' => 7190, 'L' => 7190, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 9380, 'B' => 8960, 'C' => 8960, 'D' => 8750, 'E' => 8750, 'F' => 8640, 'G' => 8640, 'H' => 8540, 'I' => 8540, 'J' => 8640, 'K' => 8540, 'L' => 8540, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13970, 'B' => 13550, 'C' => 13550, 'D' => 13340, 'E' => 13340, 'F' => 13230, 'G' => 13230, 'H' => 13130, 'I' => 13130, 'J' => 13230, 'K' => 13130, 'L' => 13130, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19640, 'B' => 19220, 'C' => 19220, 'D' => 19010, 'E' => 19010, 'F' => 18900, 'G' => 18900, 'H' => 18800, 'I' => 18800, 'J' => 18900, 'K' => 18800, 'L' => 18800, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27470, 'B' => 27050, 'C' => 27050, 'D' => 26840, 'E' => 26840, 'F' => 26730, 'G' => 26730, 'H' => 26630, 'I' => 26630, 'J' => 26730, 'K' => 26630, 'L' => 26630, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 5, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1470, 'B' => 1050, 'C' => 950, 'D' => 840, 'E' => 840, 'F' => 740, 'G' => 740, 'H' => 740, 'I' => 740, 'J' => 840, 'K' => 840, 'L' => 840, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1740, 'B' => 1320, 'C' => 1210, 'D' => 1110, 'E' => 1110, 'F' => 1000, 'G' => 1000, 'H' => 1000, 'I' => 1000, 'J' => 1110, 'K' => 1110, 'L' => 1110, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2000, 'B' => 1580, 'C' => 1470, 'D' => 1370, 'E' => 1370, 'F' => 1260, 'G' => 1260, 'H' => 1260, 'I' => 1260, 'J' => 1370, 'K' => 1370, 'L' => 1370, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2260, 'B' => 1840, 'C' => 1740, 'D' => 1630, 'E' => 1630, 'F' => 1530, 'G' => 1530, 'H' => 1530, 'I' => 1530, 'J' => 1630, 'K' => 1630, 'L' => 1630, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2520, 'B' => 2100, 'C' => 2000, 'D' => 1890, 'E' => 1890, 'F' => 1790, 'G' => 1790, 'H' => 1790, 'I' => 1790, 'J' => 1890, 'K' => 1890, 'L' => 1890, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 3060, 'B' => 2640, 'C' => 2540, 'D' => 2430, 'E' => 2430, 'F' => 2330, 'G' => 2330, 'H' => 2330, 'I' => 2330, 'J' => 2430, 'K' => 2430, 'L' => 2430, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3330, 'B' => 2910, 'C' => 2810, 'D' => 2700, 'E' => 2700, 'F' => 2600, 'G' => 2600, 'H' => 2600, 'I' => 2600, 'J' => 2700, 'K' => 2700, 'L' => 2700, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3870, 'B' => 3450, 'C' => 3350, 'D' => 3240, 'E' => 3240, 'F' => 3140, 'G' => 3140, 'H' => 3140, 'I' => 3140, 'J' => 3240, 'K' => 3240, 'L' => 3240, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4410, 'B' => 3990, 'C' => 3890, 'D' => 3780, 'E' => 3780, 'F' => 3680, 'G' => 3680, 'H' => 3680, 'I' => 3680, 'J' => 3780, 'K' => 3780, 'L' => 3780, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5490, 'B' => 5070, 'C' => 4970, 'D' => 4860, 'E' => 4860, 'F' => 4760, 'G' => 4760, 'H' => 4760, 'I' => 4760, 'J' => 4860, 'K' => 4860, 'L' => 4860, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6570, 'B' => 6150, 'C' => 6050, 'D' => 5940, 'E' => 5940, 'F' => 5840, 'G' => 5840, 'H' => 5840, 'I' => 5840, 'J' => 5940, 'K' => 5940, 'L' => 5940, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7920, 'B' => 7500, 'C' => 7400, 'D' => 7290, 'E' => 7290, 'F' => 7190, 'G' => 7190, 'H' => 7190, 'I' => 7190, 'J' => 7290, 'K' => 7290, 'L' => 7290, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 9270, 'B' => 8850, 'C' => 8750, 'D' => 8640, 'E' => 8640, 'F' => 8540, 'G' => 8540, 'H' => 8540, 'I' => 8540, 'J' => 8640, 'K' => 8640, 'L' => 8640, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13860, 'B' => 13440, 'C' => 13340, 'D' => 13230, 'E' => 13230, 'F' => 13130, 'G' => 13130, 'H' => 13130, 'I' => 13130, 'J' => 13230, 'K' => 13230, 'L' => 13230, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19530, 'B' => 19110, 'C' => 19010, 'D' => 18900, 'E' => 18900, 'F' => 18800, 'G' => 18800, 'H' => 18800, 'I' => 18800, 'J' => 18900, 'K' => 18900, 'L' => 18900, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27360, 'B' => 26940, 'C' => 26840, 'D' => 26730, 'E' => 26730, 'F' => 26630, 'G' => 26630, 'H' => 26630, 'I' => 26630, 'J' => 26730, 'K' => 26730, 'L' => 26730, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 6, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 950, 'C' => 840, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 740, 'I' => 840, 'J' => 950, 'K' => 950, 'L' => 950, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1530, 'B' => 1210, 'C' => 1110, 'D' => 1000, 'E' => 1000, 'F' => 1000, 'G' => 1000, 'H' => 1000, 'I' => 1110, 'J' => 1210, 'K' => 1210, 'L' => 1210, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1790, 'B' => 1470, 'C' => 1370, 'D' => 1260, 'E' => 1260, 'F' => 1260, 'G' => 1260, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1470, 'L' => 1470, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2050, 'B' => 1740, 'C' => 1630, 'D' => 1530, 'E' => 1530, 'F' => 1530, 'G' => 1530, 'H' => 1530, 'I' => 1630, 'J' => 1740, 'K' => 1740, 'L' => 1740, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2310, 'B' => 2000, 'C' => 1890, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1790, 'I' => 1890, 'J' => 2000, 'K' => 2000, 'L' => 2000, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 2850, 'B' => 2540, 'C' => 2430, 'D' => 2330, 'E' => 2330, 'F' => 2330, 'G' => 2330, 'H' => 2330, 'I' => 2430, 'J' => 2540, 'K' => 2540, 'L' => 2540, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3120, 'B' => 2810, 'C' => 2700, 'D' => 2600, 'E' => 2600, 'F' => 2600, 'G' => 2600, 'H' => 2600, 'I' => 2700, 'J' => 2810, 'K' => 2810, 'L' => 2810, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3660, 'B' => 3350, 'C' => 3240, 'D' => 3140, 'E' => 3140, 'F' => 3140, 'G' => 3140, 'H' => 3140, 'I' => 3240, 'J' => 3350, 'K' => 3350, 'L' => 3350, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4200, 'B' => 3890, 'C' => 3780, 'D' => 3680, 'E' => 3680, 'F' => 3680, 'G' => 3680, 'H' => 3680, 'I' => 3780, 'J' => 3890, 'K' => 3890, 'L' => 3890, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5280, 'B' => 4970, 'C' => 4860, 'D' => 4760, 'E' => 4760, 'F' => 4760, 'G' => 4760, 'H' => 4760, 'I' => 4860, 'J' => 4970, 'K' => 4970, 'L' => 4970, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6360, 'B' => 6050, 'C' => 5940, 'D' => 5840, 'E' => 5840, 'F' => 5840, 'G' => 5840, 'H' => 5840, 'I' => 5940, 'J' => 6050, 'K' => 6050, 'L' => 6050, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7710, 'B' => 7400, 'C' => 7290, 'D' => 7190, 'E' => 7190, 'F' => 7190, 'G' => 7190, 'H' => 7190, 'I' => 7290, 'J' => 7400, 'K' => 7400, 'L' => 7400, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 9060, 'B' => 8750, 'C' => 8640, 'D' => 8540, 'E' => 8540, 'F' => 8540, 'G' => 8540, 'H' => 8540, 'I' => 8640, 'J' => 8750, 'K' => 8750, 'L' => 8750, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13650, 'B' => 13340, 'C' => 13230, 'D' => 13130, 'E' => 13130, 'F' => 13130, 'G' => 13130, 'H' => 13130, 'I' => 13230, 'J' => 13340, 'K' => 13340, 'L' => 13340, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19320, 'B' => 19010, 'C' => 18900, 'D' => 18800, 'E' => 18800, 'F' => 18800, 'G' => 18800, 'H' => 18800, 'I' => 18900, 'J' => 19010, 'K' => 19010, 'L' => 19010, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27150, 'B' => 26840, 'C' => 26730, 'D' => 26630, 'E' => 26630, 'F' => 26630, 'G' => 26630, 'H' => 26630, 'I' => 26730, 'J' => 26840, 'K' => 26840, 'L' => 26840, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 7, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 950, 'C' => 840, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 740, 'I' => 840, 'J' => 950, 'K' => 950, 'L' => 950, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1530, 'B' => 1210, 'C' => 1110, 'D' => 1000, 'E' => 1000, 'F' => 1000, 'G' => 1000, 'H' => 1000, 'I' => 1110, 'J' => 1210, 'K' => 1210, 'L' => 1210, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1790, 'B' => 1470, 'C' => 1370, 'D' => 1260, 'E' => 1260, 'F' => 1260, 'G' => 1260, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1470, 'L' => 1470, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2050, 'B' => 1740, 'C' => 1630, 'D' => 1530, 'E' => 1530, 'F' => 1530, 'G' => 1530, 'H' => 1530, 'I' => 1630, 'J' => 1740, 'K' => 1740, 'L' => 1740, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2310, 'B' => 2000, 'C' => 1890, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1790, 'I' => 1890, 'J' => 2000, 'K' => 2000, 'L' => 2000, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 2850, 'B' => 2540, 'C' => 2430, 'D' => 2330, 'E' => 2330, 'F' => 2330, 'G' => 2330, 'H' => 2330, 'I' => 2430, 'J' => 2540, 'K' => 2540, 'L' => 2540, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3120, 'B' => 2810, 'C' => 2700, 'D' => 2600, 'E' => 2600, 'F' => 2600, 'G' => 2600, 'H' => 2600, 'I' => 2700, 'J' => 2810, 'K' => 2810, 'L' => 2810, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3660, 'B' => 3350, 'C' => 3240, 'D' => 3140, 'E' => 3140, 'F' => 3140, 'G' => 3140, 'H' => 3140, 'I' => 3240, 'J' => 3350, 'K' => 3350, 'L' => 3350, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4200, 'B' => 3890, 'C' => 3780, 'D' => 3680, 'E' => 3680, 'F' => 3680, 'G' => 3680, 'H' => 3680, 'I' => 3780, 'J' => 3890, 'K' => 3890, 'L' => 3890, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5280, 'B' => 4970, 'C' => 4860, 'D' => 4760, 'E' => 4760, 'F' => 4760, 'G' => 4760, 'H' => 4760, 'I' => 4860, 'J' => 4970, 'K' => 4970, 'L' => 4970, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6360, 'B' => 6050, 'C' => 5940, 'D' => 5840, 'E' => 5840, 'F' => 5840, 'G' => 5840, 'H' => 5840, 'I' => 5940, 'J' => 6050, 'K' => 6050, 'L' => 6050, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7710, 'B' => 7400, 'C' => 7290, 'D' => 7190, 'E' => 7190, 'F' => 7190, 'G' => 7190, 'H' => 7190, 'I' => 7290, 'J' => 7400, 'K' => 7400, 'L' => 7400, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 9060, 'B' => 8750, 'C' => 8640, 'D' => 8540, 'E' => 8540, 'F' => 8540, 'G' => 8540, 'H' => 8540, 'I' => 8640, 'J' => 8750, 'K' => 8750, 'L' => 8750, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13650, 'B' => 13340, 'C' => 13230, 'D' => 13130, 'E' => 13130, 'F' => 13130, 'G' => 13130, 'H' => 13130, 'I' => 13230, 'J' => 13340, 'K' => 13340, 'L' => 13340, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19320, 'B' => 19010, 'C' => 18900, 'D' => 18800, 'E' => 18800, 'F' => 18800, 'G' => 18800, 'H' => 18800, 'I' => 18900, 'J' => 19010, 'K' => 19010, 'L' => 19010, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27150, 'B' => 26840, 'C' => 26730, 'D' => 26630, 'E' => 26630, 'F' => 26630, 'G' => 26630, 'H' => 26630, 'I' => 26730, 'J' => 26840, 'K' => 26840, 'L' => 26840, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 8, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1160, 'B' => 840, 'C' => 740, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 840, 'I' => 950, 'J' => 1050, 'K' => 1160, 'L' => 1160, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1420, 'B' => 1110, 'C' => 1000, 'D' => 1000, 'E' => 1000, 'F' => 1000, 'G' => 1000, 'H' => 1110, 'I' => 1210, 'J' => 1320, 'K' => 1420, 'L' => 1420, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1680, 'B' => 1370, 'C' => 1260, 'D' => 1260, 'E' => 1260, 'F' => 1260, 'G' => 1260, 'H' => 1370, 'I' => 1470, 'J' => 1580, 'K' => 1680, 'L' => 1680, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1950, 'B' => 1630, 'C' => 1530, 'D' => 1530, 'E' => 1530, 'F' => 1530, 'G' => 1530, 'H' => 1630, 'I' => 1740, 'J' => 1840, 'K' => 1950, 'L' => 1950, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2210, 'B' => 1890, 'C' => 1790, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1890, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 2210, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 2750, 'B' => 2430, 'C' => 2330, 'D' => 2330, 'E' => 2330, 'F' => 2330, 'G' => 2330, 'H' => 2430, 'I' => 2540, 'J' => 2640, 'K' => 2750, 'L' => 2750, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3020, 'B' => 2700, 'C' => 2600, 'D' => 2600, 'E' => 2600, 'F' => 2600, 'G' => 2600, 'H' => 2700, 'I' => 2810, 'J' => 2910, 'K' => 3020, 'L' => 3020, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3560, 'B' => 3240, 'C' => 3140, 'D' => 3140, 'E' => 3140, 'F' => 3140, 'G' => 3140, 'H' => 3240, 'I' => 3350, 'J' => 3450, 'K' => 3560, 'L' => 3560, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4100, 'B' => 3780, 'C' => 3680, 'D' => 3680, 'E' => 3680, 'F' => 3680, 'G' => 3680, 'H' => 3780, 'I' => 3890, 'J' => 3990, 'K' => 4100, 'L' => 4100, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5180, 'B' => 4860, 'C' => 4760, 'D' => 4760, 'E' => 4760, 'F' => 4760, 'G' => 4760, 'H' => 4860, 'I' => 4970, 'J' => 5070, 'K' => 5180, 'L' => 5180, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6260, 'B' => 5940, 'C' => 5840, 'D' => 5840, 'E' => 5840, 'F' => 5840, 'G' => 5840, 'H' => 5940, 'I' => 6050, 'J' => 6150, 'K' => 6260, 'L' => 6260, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7610, 'B' => 7290, 'C' => 7190, 'D' => 7190, 'E' => 7190, 'F' => 7190, 'G' => 7190, 'H' => 7290, 'I' => 7400, 'J' => 7500, 'K' => 7610, 'L' => 7610, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 8960, 'B' => 8640, 'C' => 8540, 'D' => 8540, 'E' => 8540, 'F' => 8540, 'G' => 8540, 'H' => 8640, 'I' => 8750, 'J' => 8850, 'K' => 8960, 'L' => 8960, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13550, 'B' => 13230, 'C' => 13130, 'D' => 13130, 'E' => 13130, 'F' => 13130, 'G' => 13130, 'H' => 13230, 'I' => 13340, 'J' => 13440, 'K' => 13550, 'L' => 13550, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19220, 'B' => 18900, 'C' => 18800, 'D' => 18800, 'E' => 18800, 'F' => 18800, 'G' => 18800, 'H' => 18900, 'I' => 19010, 'J' => 19110, 'K' => 19220, 'L' => 19220, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27050, 'B' => 26730, 'C' => 26630, 'D' => 26630, 'E' => 26630, 'F' => 26630, 'G' => 26630, 'H' => 26730, 'I' => 26840, 'J' => 26940, 'K' => 27050, 'L' => 27050, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 9, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1160, 'B' => 840, 'C' => 740, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 840, 'I' => 950, 'J' => 1050, 'K' => 1160, 'L' => 1160, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1420, 'B' => 1110, 'C' => 1000, 'D' => 1000, 'E' => 1000, 'F' => 1000, 'G' => 1000, 'H' => 1110, 'I' => 1210, 'J' => 1320, 'K' => 1420, 'L' => 1420, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1680, 'B' => 1370, 'C' => 1260, 'D' => 1260, 'E' => 1260, 'F' => 1260, 'G' => 1260, 'H' => 1370, 'I' => 1470, 'J' => 1580, 'K' => 1680, 'L' => 1680, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1950, 'B' => 1630, 'C' => 1530, 'D' => 1530, 'E' => 1530, 'F' => 1530, 'G' => 1530, 'H' => 1630, 'I' => 1740, 'J' => 1840, 'K' => 1950, 'L' => 1950, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2210, 'B' => 1890, 'C' => 1790, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1890, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 2210, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 2750, 'B' => 2430, 'C' => 2330, 'D' => 2330, 'E' => 2330, 'F' => 2330, 'G' => 2330, 'H' => 2430, 'I' => 2540, 'J' => 2640, 'K' => 2750, 'L' => 2750, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 3020, 'B' => 2700, 'C' => 2600, 'D' => 2600, 'E' => 2600, 'F' => 2600, 'G' => 2600, 'H' => 2700, 'I' => 2810, 'J' => 2910, 'K' => 3020, 'L' => 3020, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3560, 'B' => 3240, 'C' => 3140, 'D' => 3140, 'E' => 3140, 'F' => 3140, 'G' => 3140, 'H' => 3240, 'I' => 3350, 'J' => 3450, 'K' => 3560, 'L' => 3560, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 4100, 'B' => 3780, 'C' => 3680, 'D' => 3680, 'E' => 3680, 'F' => 3680, 'G' => 3680, 'H' => 3780, 'I' => 3890, 'J' => 3990, 'K' => 4100, 'L' => 4100, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5180, 'B' => 4860, 'C' => 4760, 'D' => 4760, 'E' => 4760, 'F' => 4760, 'G' => 4760, 'H' => 4860, 'I' => 4970, 'J' => 5070, 'K' => 5180, 'L' => 5180, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6260, 'B' => 5940, 'C' => 5840, 'D' => 5840, 'E' => 5840, 'F' => 5840, 'G' => 5840, 'H' => 5940, 'I' => 6050, 'J' => 6150, 'K' => 6260, 'L' => 6260, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7610, 'B' => 7290, 'C' => 7190, 'D' => 7190, 'E' => 7190, 'F' => 7190, 'G' => 7190, 'H' => 7290, 'I' => 7400, 'J' => 7500, 'K' => 7610, 'L' => 7610, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 8960, 'B' => 8640, 'C' => 8540, 'D' => 8540, 'E' => 8540, 'F' => 8540, 'G' => 8540, 'H' => 8640, 'I' => 8750, 'J' => 8850, 'K' => 8960, 'L' => 8960, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13550, 'B' => 13230, 'C' => 13130, 'D' => 13130, 'E' => 13130, 'F' => 13130, 'G' => 13130, 'H' => 13230, 'I' => 13340, 'J' => 13440, 'K' => 13550, 'L' => 13550, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19220, 'B' => 18900, 'C' => 18800, 'D' => 18800, 'E' => 18800, 'F' => 18800, 'G' => 18800, 'H' => 18900, 'I' => 19010, 'J' => 19110, 'K' => 19220, 'L' => 19220, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 27050, 'B' => 26730, 'C' => 26630, 'D' => 26630, 'E' => 26630, 'F' => 26630, 'G' => 26630, 'H' => 26730, 'I' => 26840, 'J' => 26940, 'K' => 27050, 'L' => 27050, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 10, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1050, 'B' => 740, 'C' => 740, 'D' => 740, 'E' => 740, 'F' => 840, 'G' => 840, 'H' => 950, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1370, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1320, 'B' => 1000, 'C' => 1000, 'D' => 1000, 'E' => 1000, 'F' => 1110, 'G' => 1110, 'H' => 1210, 'I' => 1420, 'J' => 1530, 'K' => 1630, 'L' => 1630, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1580, 'B' => 1260, 'C' => 1260, 'D' => 1260, 'E' => 1260, 'F' => 1370, 'G' => 1370, 'H' => 1470, 'I' => 1680, 'J' => 1790, 'K' => 1890, 'L' => 1890, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1840, 'B' => 1530, 'C' => 1530, 'D' => 1530, 'E' => 1530, 'F' => 1630, 'G' => 1630, 'H' => 1740, 'I' => 1950, 'J' => 2050, 'K' => 2160, 'L' => 2160, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2100, 'B' => 1790, 'C' => 1790, 'D' => 1790, 'E' => 1790, 'F' => 1890, 'G' => 1890, 'H' => 2000, 'I' => 2210, 'J' => 2310, 'K' => 2420, 'L' => 2420, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 2640, 'B' => 2330, 'C' => 2330, 'D' => 2330, 'E' => 2330, 'F' => 2430, 'G' => 2430, 'H' => 2540, 'I' => 2750, 'J' => 2850, 'K' => 2960, 'L' => 2960, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 2910, 'B' => 2600, 'C' => 2600, 'D' => 2600, 'E' => 2600, 'F' => 2700, 'G' => 2700, 'H' => 2810, 'I' => 3020, 'J' => 3120, 'K' => 3230, 'L' => 3230, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3450, 'B' => 3140, 'C' => 3140, 'D' => 3140, 'E' => 3140, 'F' => 3240, 'G' => 3240, 'H' => 3350, 'I' => 3560, 'J' => 3660, 'K' => 3770, 'L' => 3770, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 3990, 'B' => 3680, 'C' => 3680, 'D' => 3680, 'E' => 3680, 'F' => 3780, 'G' => 3780, 'H' => 3890, 'I' => 4100, 'J' => 4200, 'K' => 4310, 'L' => 4310, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 5070, 'B' => 4760, 'C' => 4760, 'D' => 4760, 'E' => 4760, 'F' => 4860, 'G' => 4860, 'H' => 4970, 'I' => 5180, 'J' => 5280, 'K' => 5390, 'L' => 5390, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6150, 'B' => 5840, 'C' => 5840, 'D' => 5840, 'E' => 5840, 'F' => 5940, 'G' => 5940, 'H' => 6050, 'I' => 6260, 'J' => 6360, 'K' => 6470, 'L' => 6470, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7500, 'B' => 7190, 'C' => 7190, 'D' => 7190, 'E' => 7190, 'F' => 7290, 'G' => 7290, 'H' => 7400, 'I' => 7610, 'J' => 7710, 'K' => 7820, 'L' => 7820, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 8850, 'B' => 8540, 'C' => 8540, 'D' => 8540, 'E' => 8540, 'F' => 8640, 'G' => 8640, 'H' => 8750, 'I' => 8960, 'J' => 9060, 'K' => 9170, 'L' => 9170, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13440, 'B' => 13130, 'C' => 13130, 'D' => 13130, 'E' => 13130, 'F' => 13230, 'G' => 13230, 'H' => 13340, 'I' => 13550, 'J' => 13650, 'K' => 13760, 'L' => 13760, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19110, 'B' => 18800, 'C' => 18800, 'D' => 18800, 'E' => 18800, 'F' => 18900, 'G' => 18900, 'H' => 19010, 'I' => 19220, 'J' => 19320, 'K' => 19430, 'L' => 19430, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 26940, 'B' => 26630, 'C' => 26630, 'D' => 26630, 'E' => 26630, 'F' => 26730, 'G' => 26730, 'H' => 26840, 'I' => 27050, 'J' => 27150, 'K' => 27260, 'L' => 27260, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 11, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 950, 'B' => 740, 'C' => 740, 'D' => 840, 'E' => 840, 'F' => 950, 'G' => 950, 'H' => 1050, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1370, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1210, 'B' => 1000, 'C' => 1000, 'D' => 1110, 'E' => 1110, 'F' => 1210, 'G' => 1210, 'H' => 1320, 'I' => 1420, 'J' => 1530, 'K' => 1630, 'L' => 1630, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1470, 'B' => 1260, 'C' => 1260, 'D' => 1370, 'E' => 1370, 'F' => 1470, 'G' => 1470, 'H' => 1580, 'I' => 1680, 'J' => 1790, 'K' => 1890, 'L' => 1890, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1740, 'B' => 1530, 'C' => 1530, 'D' => 1630, 'E' => 1630, 'F' => 1740, 'G' => 1740, 'H' => 1840, 'I' => 1950, 'J' => 2050, 'K' => 2160, 'L' => 2160, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2000, 'B' => 1790, 'C' => 1790, 'D' => 1890, 'E' => 1890, 'F' => 2000, 'G' => 2000, 'H' => 2100, 'I' => 2210, 'J' => 2310, 'K' => 2420, 'L' => 2420, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 2540, 'B' => 2330, 'C' => 2330, 'D' => 2430, 'E' => 2430, 'F' => 2540, 'G' => 2540, 'H' => 2640, 'I' => 2750, 'J' => 2850, 'K' => 2960, 'L' => 2960, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 2810, 'B' => 2600, 'C' => 2600, 'D' => 2700, 'E' => 2700, 'F' => 2810, 'G' => 2810, 'H' => 2910, 'I' => 3020, 'J' => 3120, 'K' => 3230, 'L' => 3230, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3350, 'B' => 3140, 'C' => 3140, 'D' => 3240, 'E' => 3240, 'F' => 3350, 'G' => 3350, 'H' => 3450, 'I' => 3560, 'J' => 3660, 'K' => 3770, 'L' => 3770, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 3890, 'B' => 3680, 'C' => 3680, 'D' => 3780, 'E' => 3780, 'F' => 3890, 'G' => 3890, 'H' => 3990, 'I' => 4100, 'J' => 4200, 'K' => 4310, 'L' => 4310, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 4970, 'B' => 4760, 'C' => 4760, 'D' => 4860, 'E' => 4860, 'F' => 4970, 'G' => 4970, 'H' => 5070, 'I' => 5180, 'J' => 5280, 'K' => 5390, 'L' => 5390, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 6050, 'B' => 5840, 'C' => 5840, 'D' => 5940, 'E' => 5940, 'F' => 6050, 'G' => 6050, 'H' => 6150, 'I' => 6260, 'J' => 6360, 'K' => 6470, 'L' => 6470, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7400, 'B' => 7190, 'C' => 7190, 'D' => 7290, 'E' => 7290, 'F' => 7400, 'G' => 7400, 'H' => 7500, 'I' => 7610, 'J' => 7710, 'K' => 7820, 'L' => 7820, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 8750, 'B' => 8540, 'C' => 8540, 'D' => 8640, 'E' => 8640, 'F' => 8750, 'G' => 8750, 'H' => 8850, 'I' => 8960, 'J' => 9060, 'K' => 9170, 'L' => 9170, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13340, 'B' => 13130, 'C' => 13130, 'D' => 13230, 'E' => 13230, 'F' => 13340, 'G' => 13340, 'H' => 13440, 'I' => 13550, 'J' => 13650, 'K' => 13760, 'L' => 13760, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 19010, 'B' => 18800, 'C' => 18800, 'D' => 18900, 'E' => 18900, 'F' => 19010, 'G' => 19010, 'H' => 19110, 'I' => 19220, 'J' => 19320, 'K' => 19430, 'L' => 19430, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 26840, 'B' => 26630, 'C' => 26630, 'D' => 26730, 'E' => 26730, 'F' => 26840, 'G' => 26840, 'H' => 26940, 'I' => 27050, 'J' => 27150, 'K' => 27260, 'L' => 27260, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 12, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 740, 'B' => 950, 'C' => 1050, 'D' => 1160, 'E' => 1160, 'F' => 1260, 'G' => 1260, 'H' => 1470, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 1790, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1000, 'B' => 1210, 'C' => 1320, 'D' => 1420, 'E' => 1420, 'F' => 1530, 'G' => 1530, 'H' => 1740, 'I' => 1840, 'J' => 1950, 'K' => 2050, 'L' => 2050, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1260, 'B' => 1470, 'C' => 1580, 'D' => 1680, 'E' => 1680, 'F' => 1790, 'G' => 1790, 'H' => 2000, 'I' => 2100, 'J' => 2210, 'K' => 2310, 'L' => 2310, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1530, 'B' => 1740, 'C' => 1840, 'D' => 1950, 'E' => 1950, 'F' => 2050, 'G' => 2050, 'H' => 2260, 'I' => 2370, 'J' => 2470, 'K' => 2580, 'L' => 2580, 'M' => 0, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 1790, 'B' => 2000, 'C' => 2100, 'D' => 2210, 'E' => 2210, 'F' => 2310, 'G' => 2310, 'H' => 2520, 'I' => 2630, 'J' => 2730, 'K' => 2840, 'L' => 2840, 'M' => 0, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 2330, 'B' => 2540, 'C' => 2640, 'D' => 2750, 'E' => 2750, 'F' => 2850, 'G' => 2850, 'H' => 3060, 'I' => 3170, 'J' => 3270, 'K' => 3380, 'L' => 3380, 'M' => 0, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 2600, 'B' => 2810, 'C' => 2910, 'D' => 3020, 'E' => 3020, 'F' => 3120, 'G' => 3120, 'H' => 3330, 'I' => 3440, 'J' => 3540, 'K' => 3650, 'L' => 3650, 'M' => 0, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 3140, 'B' => 3350, 'C' => 3450, 'D' => 3560, 'E' => 3560, 'F' => 3660, 'G' => 3660, 'H' => 3870, 'I' => 3980, 'J' => 4080, 'K' => 4190, 'L' => 4190, 'M' => 0, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 3680, 'B' => 3890, 'C' => 3990, 'D' => 4100, 'E' => 4100, 'F' => 4200, 'G' => 4200, 'H' => 4410, 'I' => 4520, 'J' => 4620, 'K' => 4730, 'L' => 4730, 'M' => 0, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 4760, 'B' => 4970, 'C' => 5070, 'D' => 5180, 'E' => 5180, 'F' => 5280, 'G' => 5280, 'H' => 5490, 'I' => 5600, 'J' => 5700, 'K' => 5810, 'L' => 5810, 'M' => 0, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 5840, 'B' => 6050, 'C' => 6150, 'D' => 6260, 'E' => 6260, 'F' => 6360, 'G' => 6360, 'H' => 6570, 'I' => 6680, 'J' => 6780, 'K' => 6890, 'L' => 6890, 'M' => 0, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 7190, 'B' => 7400, 'C' => 7500, 'D' => 7610, 'E' => 7610, 'F' => 7710, 'G' => 7710, 'H' => 7920, 'I' => 8030, 'J' => 8130, 'K' => 8240, 'L' => 8240, 'M' => 0, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 8540, 'B' => 8750, 'C' => 8850, 'D' => 8960, 'E' => 8960, 'F' => 9060, 'G' => 9060, 'H' => 9270, 'I' => 9380, 'J' => 9480, 'K' => 9590, 'L' => 9590, 'M' => 0, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 13130, 'B' => 13340, 'C' => 13440, 'D' => 13550, 'E' => 13550, 'F' => 13650, 'G' => 13650, 'H' => 13860, 'I' => 13970, 'J' => 14070, 'K' => 14180, 'L' => 14180, 'M' => 0, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 18800, 'B' => 19010, 'C' => 19110, 'D' => 19220, 'E' => 19220, 'F' => 19320, 'G' => 19320, 'H' => 19530, 'I' => 19640, 'J' => 19740, 'K' => 19850, 'L' => 19850, 'M' => 0, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 26630, 'B' => 26840, 'C' => 26940, 'D' => 27050, 'E' => 27050, 'F' => 27150, 'G' => 27150, 'H' => 27360, 'I' => 27470, 'J' => 27570, 'K' => 27680, 'L' => 27680, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'standard', 'zone_id' => 13, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 740, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 1000, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 1260, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 1530, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 1790, ), ), 170 => array ( 'size' => '170', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 2330, ), ), 180 => array ( 'size' => '180', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 2600, ), ), 200 => array ( 'size' => '200', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 3140, ), ), 220 => array ( 'size' => '220', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 3680, ), ), 240 => array ( 'size' => '240', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 4760, ), ), 260 => array ( 'size' => '260', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 5840, ), ), 280 => array ( 'size' => '280', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 7190, ), ), 300 => array ( 'size' => '300', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 8540, ), ), 350 => array ( 'size' => '350', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 13130, ), ), 400 => array ( 'size' => '400', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 18800, ), ), 450 => array ( 'size' => '450', 'weight' => '60', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 26630, ), ), )
        ),
        // 佐川急便 - 飛脚クール便
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 1, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1950, 'B' => 1530, 'C' => 1530, 'D' => 1320, 'E' => 1320, 'F' => 1110, 'G' => 1110, 'H' => 1000, 'I' => 900, 'J' => 1000, 'K' => 900, 'L' => 900, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2210, 'B' => 1790, 'C' => 1790, 'D' => 1580, 'E' => 1580, 'F' => 1370, 'G' => 1370, 'H' => 1270, 'I' => 1160, 'J' => 1270, 'K' => 1160, 'L' => 1160, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2580, 'B' => 2160, 'C' => 2160, 'D' => 1950, 'E' => 1950, 'F' => 1740, 'G' => 1740, 'H' => 1640, 'I' => 1530, 'J' => 1640, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2900, 'B' => 2480, 'C' => 2480, 'D' => 2270, 'E' => 2270, 'F' => 2060, 'G' => 2060, 'H' => 1950, 'I' => 1850, 'J' => 1950, 'K' => 1850, 'L' => 1850, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 2, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1950, 'B' => 1530, 'C' => 1530, 'D' => 1320, 'E' => 1320, 'F' => 1110, 'G' => 1110, 'H' => 1000, 'I' => 900, 'J' => 1000, 'K' => 900, 'L' => 900, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2210, 'B' => 1790, 'C' => 1790, 'D' => 1580, 'E' => 1580, 'F' => 1370, 'G' => 1370, 'H' => 1270, 'I' => 1160, 'J' => 1270, 'K' => 1160, 'L' => 1160, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2580, 'B' => 2160, 'C' => 2160, 'D' => 1950, 'E' => 1950, 'F' => 1740, 'G' => 1740, 'H' => 1640, 'I' => 1530, 'J' => 1640, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2900, 'B' => 2480, 'C' => 2480, 'D' => 2270, 'E' => 2270, 'F' => 2060, 'G' => 2060, 'H' => 1950, 'I' => 1850, 'J' => 1950, 'K' => 1850, 'L' => 1850, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 3, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1840, 'B' => 1420, 'C' => 1420, 'D' => 1210, 'E' => 1210, 'F' => 1110, 'G' => 1110, 'H' => 1000, 'I' => 1000, 'J' => 900, 'K' => 1000, 'L' => 1000, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2110, 'B' => 1690, 'C' => 1690, 'D' => 1480, 'E' => 1480, 'F' => 1370, 'G' => 1370, 'H' => 1270, 'I' => 1270, 'J' => 1160, 'K' => 1270, 'L' => 1270, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2480, 'B' => 2060, 'C' => 2060, 'D' => 1850, 'E' => 1850, 'F' => 1740, 'G' => 1740, 'H' => 1640, 'I' => 1640, 'J' => 1530, 'K' => 1640, 'L' => 1640, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2790, 'B' => 2370, 'C' => 2370, 'D' => 2160, 'E' => 2160, 'F' => 2060, 'G' => 2060, 'H' => 1950, 'I' => 1950, 'J' => 1850, 'K' => 1950, 'L' => 1950, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 4, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1740, 'B' => 1320, 'C' => 1320, 'D' => 1110, 'E' => 1110, 'F' => 1000, 'G' => 1000, 'H' => 900, 'I' => 900, 'J' => 1000, 'K' => 900, 'L' => 900, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2000, 'B' => 1580, 'C' => 1580, 'D' => 1370, 'E' => 1370, 'F' => 1270, 'G' => 1270, 'H' => 1160, 'I' => 1160, 'J' => 1270, 'K' => 1160, 'L' => 1160, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2370, 'B' => 1950, 'C' => 1950, 'D' => 1740, 'E' => 1740, 'F' => 1640, 'G' => 1640, 'H' => 1530, 'I' => 1530, 'J' => 1640, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2690, 'B' => 2270, 'C' => 2270, 'D' => 2060, 'E' => 2060, 'F' => 1950, 'G' => 1950, 'H' => 1850, 'I' => 1850, 'J' => 1950, 'K' => 1850, 'L' => 1850, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 5, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1630, 'B' => 1210, 'C' => 1110, 'D' => 1000, 'E' => 1000, 'F' => 900, 'G' => 900, 'H' => 900, 'I' => 900, 'J' => 1000, 'K' => 1000, 'L' => 1000, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1900, 'B' => 1480, 'C' => 1370, 'D' => 1270, 'E' => 1270, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1160, 'J' => 1270, 'K' => 1270, 'L' => 1270, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2270, 'B' => 1850, 'C' => 1740, 'D' => 1640, 'E' => 1640, 'F' => 1530, 'G' => 1530, 'H' => 1530, 'I' => 1530, 'J' => 1640, 'K' => 1640, 'L' => 1640, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2580, 'B' => 2160, 'C' => 2060, 'D' => 1950, 'E' => 1950, 'F' => 1850, 'G' => 1850, 'H' => 1850, 'I' => 1850, 'J' => 1950, 'K' => 1950, 'L' => 1950, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 6, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1420, 'B' => 1110, 'C' => 1000, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 900, 'H' => 900, 'I' => 1000, 'J' => 1110, 'K' => 1110, 'L' => 1110, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1690, 'B' => 1370, 'C' => 1270, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1270, 'J' => 1370, 'K' => 1370, 'L' => 1370, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2060, 'B' => 1740, 'C' => 1640, 'D' => 1530, 'E' => 1530, 'F' => 1530, 'G' => 1530, 'H' => 1530, 'I' => 1640, 'J' => 1740, 'K' => 1740, 'L' => 1740, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2370, 'B' => 2060, 'C' => 1950, 'D' => 1850, 'E' => 1850, 'F' => 1850, 'G' => 1850, 'H' => 1850, 'I' => 1950, 'J' => 2060, 'K' => 2060, 'L' => 2060, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 7, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1420, 'B' => 1110, 'C' => 1000, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 900, 'H' => 900, 'I' => 1000, 'J' => 1110, 'K' => 1110, 'L' => 1110, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1690, 'B' => 1370, 'C' => 1270, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1270, 'J' => 1370, 'K' => 1370, 'L' => 1370, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1950, 'B' => 1630, 'C' => 1530, 'D' => 1420, 'E' => 1420, 'F' => 1420, 'G' => 1420, 'H' => 1420, 'I' => 1530, 'J' => 1630, 'K' => 1630, 'L' => 1630, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2210, 'B' => 1900, 'C' => 1790, 'D' => 1690, 'E' => 1690, 'F' => 1690, 'G' => 1690, 'H' => 1690, 'I' => 1790, 'J' => 1900, 'K' => 1900, 'L' => 1900, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 8, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1320, 'B' => 1000, 'C' => 900, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 900, 'H' => 1000, 'I' => 1110, 'J' => 1210, 'K' => 1320, 'L' => 1320, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1580, 'B' => 1270, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1270, 'I' => 1370, 'J' => 1480, 'K' => 1580, 'L' => 1580, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1840, 'B' => 1530, 'C' => 1420, 'D' => 1420, 'E' => 1420, 'F' => 1420, 'G' => 1420, 'H' => 1530, 'I' => 1630, 'J' => 1740, 'K' => 1840, 'L' => 1840, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2270, 'B' => 1950, 'C' => 1850, 'D' => 1850, 'E' => 1850, 'F' => 1850, 'G' => 1850, 'H' => 1950, 'I' => 2060, 'J' => 2160, 'K' => 2270, 'L' => 2270, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 9, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1320, 'B' => 1000, 'C' => 900, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 900, 'H' => 1000, 'I' => 1110, 'J' => 1210, 'K' => 1320, 'L' => 1320, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1580, 'B' => 1270, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1270, 'I' => 1370, 'J' => 1480, 'K' => 1580, 'L' => 1580, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1950, 'B' => 1640, 'C' => 1530, 'D' => 1530, 'E' => 1530, 'F' => 1530, 'G' => 1530, 'H' => 1640, 'I' => 1740, 'J' => 1850, 'K' => 1950, 'L' => 1950, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2270, 'B' => 1950, 'C' => 1850, 'D' => 1850, 'E' => 1850, 'F' => 1850, 'G' => 1850, 'H' => 1950, 'I' => 2060, 'J' => 2160, 'K' => 2270, 'L' => 2270, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 10, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1210, 'B' => 900, 'C' => 900, 'D' => 900, 'E' => 900, 'F' => 1000, 'G' => 1000, 'H' => 1110, 'I' => 1320, 'J' => 1420, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1480, 'B' => 1160, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1270, 'G' => 1270, 'H' => 1370, 'I' => 1580, 'J' => 1690, 'K' => 1790, 'L' => 1790, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1850, 'B' => 1530, 'C' => 1530, 'D' => 1530, 'E' => 1530, 'F' => 1640, 'G' => 1640, 'H' => 1740, 'I' => 1950, 'J' => 2060, 'K' => 2160, 'L' => 2160, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2160, 'B' => 1850, 'C' => 1850, 'D' => 1850, 'E' => 1850, 'F' => 1950, 'G' => 1950, 'H' => 2060, 'I' => 2270, 'J' => 2370, 'K' => 2480, 'L' => 2480, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 11, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1110, 'B' => 900, 'C' => 900, 'D' => 1000, 'E' => 1000, 'F' => 1110, 'G' => 1110, 'H' => 1210, 'I' => 1320, 'J' => 1420, 'K' => 1530, 'L' => 1530, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1370, 'B' => 1160, 'C' => 1160, 'D' => 1270, 'E' => 1270, 'F' => 1370, 'G' => 1370, 'H' => 1480, 'I' => 1580, 'J' => 1690, 'K' => 1790, 'L' => 1790, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1740, 'B' => 1530, 'C' => 1530, 'D' => 1640, 'E' => 1640, 'F' => 1740, 'G' => 1740, 'H' => 1850, 'I' => 1950, 'J' => 2060, 'K' => 2160, 'L' => 2160, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2060, 'B' => 1850, 'C' => 1850, 'D' => 1950, 'E' => 1950, 'F' => 2060, 'G' => 2060, 'H' => 2160, 'I' => 2270, 'J' => 2370, 'K' => 2480, 'L' => 2480, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 12, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1060, 'B' => 1270, 'C' => 1370, 'D' => 1480, 'E' => 1480, 'F' => 1580, 'G' => 1580, 'H' => 1790, 'I' => 1900, 'J' => 2000, 'K' => 2110, 'L' => 2110, 'M' => 0, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1320, 'B' => 1530, 'C' => 1640, 'D' => 1740, 'E' => 1740, 'F' => 1850, 'G' => 1850, 'H' => 2060, 'I' => 2160, 'J' => 2270, 'K' => 2370, 'L' => 2370, 'M' => 0, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1800, 'B' => 2010, 'C' => 2120, 'D' => 2220, 'E' => 2220, 'F' => 2330, 'G' => 2330, 'H' => 2540, 'I' => 2640, 'J' => 2750, 'K' => 2850, 'L' => 2850, 'M' => 0, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2170, 'B' => 2380, 'C' => 2480, 'D' => 2590, 'E' => 2590, 'F' => 2690, 'G' => 2690, 'H' => 2900, 'I' => 3010, 'J' => 3110, 'K' => 3220, 'L' => 3220, 'M' => 0, ), ), )
        ),
        array('carrier_code' => 'sagawa', 'service_code' => 'cool', 'zone_id' => 13, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 900, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 1160, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 1530, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 0, 'B' => 0, 'C' => 0, 'D' => 0, 'E' => 0, 'F' => 0, 'G' => 0, 'H' => 0, 'I' => 0, 'J' => 0, 'K' => 0, 'L' => 0, 'M' => 1850, ), ), )
        ),
        // ヤマト運輸 - 宅急便
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 14, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 740, 'B' => 950, 'C' => 1050, 'D' => 1160, 'E' => 1160, 'F' => 1260, 'G' => 1260, 'H' => 1470, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 1890, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 950, 'B' => 1160, 'C' => 1260, 'D' => 1370, 'E' => 1370, 'F' => 1470, 'G' => 1470, 'H' => 1680, 'I' => 1790, 'J' => 1890, 'K' => 2000, 'L' => 2420, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1160, 'B' => 1370, 'C' => 1470, 'D' => 1580, 'E' => 1580, 'F' => 1680, 'G' => 1680, 'H' => 1890, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 2940, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1370, 'B' => 1580, 'C' => 1680, 'D' => 1790, 'E' => 1790, 'F' => 1890, 'G' => 1890, 'H' => 2100, 'I' => 2210, 'J' => 2310, 'K' => 2420, 'L' => 3470, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1580, 'B' => 1790, 'C' => 1890, 'D' => 2000, 'E' => 2000, 'F' => 2100, 'G' => 2100, 'H' => 2310, 'I' => 2420, 'J' => 2520, 'K' => 2630, 'L' => 3990, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1790, 'B' => 2000, 'C' => 2100, 'D' => 2210, 'E' => 2210, 'F' => 2310, 'G' => 2310, 'H' => 2520, 'I' => 2630, 'J' => 2730, 'K' => 2840, 'L' => 4520, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 15, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 950, 'B' => 740, 'C' => 740, 'D' => 840, 'E' => 840, 'F' => 950, 'G' => 950, 'H' => 1050, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1580, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1160, 'B' => 950, 'C' => 950, 'D' => 1050, 'E' => 1050, 'F' => 1160, 'G' => 1160, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 2100, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1370, 'B' => 1160, 'C' => 1160, 'D' => 1260, 'E' => 1260, 'F' => 1370, 'G' => 1370, 'H' => 1470, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 2630, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1580, 'B' => 1370, 'C' => 1370, 'D' => 1470, 'E' => 1470, 'F' => 1580, 'G' => 1580, 'H' => 1680, 'I' => 1790, 'J' => 1890, 'K' => 2000, 'L' => 3150, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1790, 'B' => 1580, 'C' => 1580, 'D' => 1680, 'E' => 1680, 'F' => 1790, 'G' => 1790, 'H' => 1890, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 3680, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2000, 'B' => 1790, 'C' => 1790, 'D' => 1890, 'E' => 1890, 'F' => 2000, 'G' => 2000, 'H' => 2100, 'I' => 2210, 'J' => 2310, 'K' => 2420, 'L' => 4200, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 16, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1050, 'B' => 740, 'C' => 740, 'D' => 740, 'E' => 740, 'F' => 840, 'G' => 840, 'H' => 950, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1470, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1260, 'B' => 950, 'C' => 950, 'D' => 950, 'E' => 950, 'F' => 1050, 'G' => 1050, 'H' => 1160, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 2000, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1470, 'B' => 1160, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1260, 'G' => 1260, 'H' => 1370, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 2520, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1680, 'B' => 1370, 'C' => 1370, 'D' => 1370, 'E' => 1370, 'F' => 1470, 'G' => 1470, 'H' => 1580, 'I' => 1790, 'J' => 1890, 'K' => 2000, 'L' => 3050, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1890, 'B' => 1580, 'C' => 1580, 'D' => 1580, 'E' => 1580, 'F' => 1680, 'G' => 1680, 'H' => 1790, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 3570, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2100, 'B' => 1790, 'C' => 1790, 'D' => 1790, 'E' => 1790, 'F' => 1890, 'G' => 1890, 'H' => 2000, 'I' => 2210, 'J' => 2310, 'K' => 2420, 'L' => 4100, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 17, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1160, 'B' => 840, 'C' => 740, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 840, 'I' => 950, 'J' => 1050, 'K' => 1160, 'L' => 1260, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1370, 'B' => 1050, 'C' => 950, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 1050, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1790, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1580, 'B' => 1260, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 2310, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1790, 'B' => 1470, 'C' => 1370, 'D' => 1370, 'E' => 1370, 'F' => 1370, 'G' => 1370, 'H' => 1470, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 2840, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2000, 'B' => 1680, 'C' => 1580, 'D' => 1580, 'E' => 1580, 'F' => 1580, 'G' => 1580, 'H' => 1680, 'I' => 1790, 'J' => 1890, 'K' => 2000, 'L' => 3360, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2210, 'B' => 1890, 'C' => 1790, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1890, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 3890, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 18, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1160, 'B' => 840, 'C' => 740, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 840, 'I' => 950, 'J' => 1050, 'K' => 1160, 'L' => 1370, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1370, 'B' => 1050, 'C' => 950, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 1050, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1890, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1580, 'B' => 1260, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 2420, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1790, 'B' => 1470, 'C' => 1370, 'D' => 1370, 'E' => 1370, 'F' => 1370, 'G' => 1370, 'H' => 1470, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 2940, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2000, 'B' => 1680, 'C' => 1580, 'D' => 1580, 'E' => 1580, 'F' => 1580, 'G' => 1580, 'H' => 1680, 'I' => 1790, 'J' => 1890, 'K' => 2000, 'L' => 3470, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2210, 'B' => 1890, 'C' => 1790, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1890, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 3990, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 19, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 950, 'C' => 840, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 740, 'I' => 840, 'J' => 950, 'K' => 950, 'L' => 1260, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1470, 'B' => 1160, 'C' => 1050, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 950, 'I' => 1050, 'J' => 1160, 'K' => 1160, 'L' => 1790, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1680, 'B' => 1370, 'C' => 1260, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1260, 'J' => 1370, 'K' => 1370, 'L' => 2310, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1890, 'B' => 1580, 'C' => 1470, 'D' => 1370, 'E' => 1370, 'F' => 1370, 'G' => 1370, 'H' => 1370, 'I' => 1470, 'J' => 1580, 'K' => 1580, 'L' => 2840, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2100, 'B' => 1790, 'C' => 1680, 'D' => 1580, 'E' => 1580, 'F' => 1580, 'G' => 1580, 'H' => 1580, 'I' => 1680, 'J' => 1790, 'K' => 1790, 'L' => 3360, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2310, 'B' => 2000, 'C' => 1890, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1790, 'I' => 1890, 'J' => 2000, 'K' => 2000, 'L' => 3890, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 20, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 950, 'C' => 840, 'D' => 740, 'E' => 740, 'F' => 740, 'G' => 740, 'H' => 740, 'I' => 840, 'J' => 950, 'K' => 950, 'L' => 1370, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1470, 'B' => 1160, 'C' => 1050, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 950, 'I' => 1050, 'J' => 1160, 'K' => 1160, 'L' => 1890, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1680, 'B' => 1370, 'C' => 1260, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1260, 'J' => 1370, 'K' => 1370, 'L' => 2420, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1890, 'B' => 1580, 'C' => 1470, 'D' => 1370, 'E' => 1370, 'F' => 1370, 'G' => 1370, 'H' => 1370, 'I' => 1470, 'J' => 1580, 'K' => 1580, 'L' => 2940, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2100, 'B' => 1790, 'C' => 1680, 'D' => 1580, 'E' => 1580, 'F' => 1580, 'G' => 1580, 'H' => 1580, 'I' => 1680, 'J' => 1790, 'K' => 1790, 'L' => 3470, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2310, 'B' => 2000, 'C' => 1890, 'D' => 1790, 'E' => 1790, 'F' => 1790, 'G' => 1790, 'H' => 1790, 'I' => 1890, 'J' => 2000, 'K' => 2000, 'L' => 3990, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 21, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1470, 'B' => 1050, 'C' => 950, 'D' => 840, 'E' => 840, 'F' => 740, 'G' => 740, 'H' => 740, 'I' => 740, 'J' => 840, 'K' => 840, 'L' => 1260, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1680, 'B' => 1260, 'C' => 1160, 'D' => 1050, 'E' => 1050, 'F' => 950, 'G' => 950, 'H' => 950, 'I' => 950, 'J' => 1050, 'K' => 1050, 'L' => 1790, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1890, 'B' => 1470, 'C' => 1370, 'D' => 1260, 'E' => 1260, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1160, 'J' => 1260, 'K' => 1260, 'L' => 2310, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2100, 'B' => 1680, 'C' => 1580, 'D' => 1470, 'E' => 1470, 'F' => 1370, 'G' => 1370, 'H' => 1370, 'I' => 1370, 'J' => 1470, 'K' => 1470, 'L' => 2840, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2310, 'B' => 1890, 'C' => 1790, 'D' => 1680, 'E' => 1680, 'F' => 1580, 'G' => 1580, 'H' => 1580, 'I' => 1580, 'J' => 1680, 'K' => 1680, 'L' => 3360, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2520, 'B' => 2100, 'C' => 2000, 'D' => 1890, 'E' => 1890, 'F' => 1790, 'G' => 1790, 'H' => 1790, 'I' => 1790, 'J' => 1890, 'K' => 1890, 'L' => 3890, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 22, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1580, 'B' => 1160, 'C' => 1160, 'D' => 950, 'E' => 950, 'F' => 840, 'G' => 840, 'H' => 740, 'I' => 740, 'J' => 840, 'K' => 740, 'L' => 1260, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1790, 'B' => 1370, 'C' => 1370, 'D' => 1160, 'E' => 1160, 'F' => 1050, 'G' => 1050, 'H' => 950, 'I' => 950, 'J' => 1050, 'K' => 950, 'L' => 1790, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2000, 'B' => 1580, 'C' => 1580, 'D' => 1370, 'E' => 1370, 'F' => 1260, 'G' => 1260, 'H' => 1160, 'I' => 1160, 'J' => 1260, 'K' => 1160, 'L' => 2310, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2210, 'B' => 1790, 'C' => 1790, 'D' => 1580, 'E' => 1580, 'F' => 1470, 'G' => 1470, 'H' => 1370, 'I' => 1370, 'J' => 1470, 'K' => 1370, 'L' => 2840, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2420, 'B' => 2000, 'C' => 2000, 'D' => 1790, 'E' => 1790, 'F' => 1680, 'G' => 1680, 'H' => 1580, 'I' => 1580, 'J' => 1680, 'K' => 1580, 'L' => 3360, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2630, 'B' => 2210, 'C' => 2210, 'D' => 2000, 'E' => 2000, 'F' => 1890, 'G' => 1890, 'H' => 1790, 'I' => 1790, 'J' => 1890, 'K' => 1790, 'L' => 3890, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 23, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1680, 'B' => 1260, 'C' => 1260, 'D' => 1050, 'E' => 1050, 'F' => 950, 'G' => 950, 'H' => 840, 'I' => 840, 'J' => 740, 'K' => 840, 'L' => 1260, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1890, 'B' => 1470, 'C' => 1470, 'D' => 1260, 'E' => 1260, 'F' => 1160, 'G' => 1160, 'H' => 1050, 'I' => 1050, 'J' => 950, 'K' => 1050, 'L' => 1790, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2100, 'B' => 1680, 'C' => 1680, 'D' => 1470, 'E' => 1470, 'F' => 1370, 'G' => 1370, 'H' => 1260, 'I' => 1260, 'J' => 1160, 'K' => 1260, 'L' => 2310, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2310, 'B' => 1890, 'C' => 1890, 'D' => 1680, 'E' => 1680, 'F' => 1580, 'G' => 1580, 'H' => 1470, 'I' => 1470, 'J' => 1370, 'K' => 1470, 'L' => 2840, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2520, 'B' => 2100, 'C' => 2100, 'D' => 1890, 'E' => 1890, 'F' => 1790, 'G' => 1790, 'H' => 1680, 'I' => 1680, 'J' => 1580, 'K' => 1680, 'L' => 3360, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2730, 'B' => 2310, 'C' => 2310, 'D' => 2100, 'E' => 2100, 'F' => 2000, 'G' => 2000, 'H' => 1890, 'I' => 1890, 'J' => 1790, 'K' => 1890, 'L' => 3890, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 24, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1790, 'B' => 1370, 'C' => 1370, 'D' => 1160, 'E' => 1160, 'F' => 950, 'G' => 950, 'H' => 840, 'I' => 740, 'J' => 840, 'K' => 740, 'L' => 1160, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2000, 'B' => 1580, 'C' => 1580, 'D' => 1370, 'E' => 1370, 'F' => 1160, 'G' => 1160, 'H' => 1050, 'I' => 950, 'J' => 1050, 'K' => 950, 'L' => 1680, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2210, 'B' => 1790, 'C' => 1790, 'D' => 1580, 'E' => 1580, 'F' => 1370, 'G' => 1370, 'H' => 1260, 'I' => 1160, 'J' => 1260, 'K' => 1160, 'L' => 2210, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2420, 'B' => 2000, 'C' => 2000, 'D' => 1790, 'E' => 1790, 'F' => 1580, 'G' => 1580, 'H' => 1470, 'I' => 1370, 'J' => 1470, 'K' => 1370, 'L' => 2730, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2630, 'B' => 2210, 'C' => 2210, 'D' => 2000, 'E' => 2000, 'F' => 1790, 'G' => 1790, 'H' => 1680, 'I' => 1580, 'J' => 1680, 'K' => 1580, 'L' => 3260, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2840, 'B' => 2420, 'C' => 2420, 'D' => 2210, 'E' => 2210, 'F' => 2000, 'G' => 2000, 'H' => 1890, 'I' => 1790, 'J' => 1890, 'K' => 1790, 'L' => 3780, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'standard', 'zone_id' => 25, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1890, 'B' => 1580, 'C' => 1470, 'D' => 1260, 'E' => 1370, 'F' => 1260, 'G' => 1370, 'H' => 1260, 'I' => 1260, 'J' => 1260, 'K' => 1160, 'L' => 740, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2420, 'B' => 2100, 'C' => 2000, 'D' => 1790, 'E' => 1890, 'F' => 1790, 'G' => 1890, 'H' => 1790, 'I' => 1790, 'J' => 1790, 'K' => 1680, 'L' => 950, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2940, 'B' => 2630, 'C' => 2520, 'D' => 2310, 'E' => 2420, 'F' => 2310, 'G' => 2420, 'H' => 2310, 'I' => 2310, 'J' => 2310, 'K' => 2210, 'L' => 1160, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 3470, 'B' => 3150, 'C' => 3050, 'D' => 2840, 'E' => 2940, 'F' => 2840, 'G' => 2940, 'H' => 2840, 'I' => 2840, 'J' => 2840, 'K' => 2730, 'L' => 1370, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 3990, 'B' => 3680, 'C' => 3570, 'D' => 3360, 'E' => 3470, 'F' => 3360, 'G' => 3470, 'H' => 3360, 'I' => 3360, 'J' => 3360, 'K' => 3260, 'L' => 1580, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 4520, 'B' => 4200, 'C' => 4100, 'D' => 3890, 'E' => 3990, 'F' => 3890, 'G' => 3990, 'H' => 3890, 'I' => 3890, 'J' => 3890, 'K' => 3780, 'L' => 1790, ), ), )
        ),
        // ヤマト運輸 - クール宅急便
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 14, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 950, 'B' => 1160, 'C' => 1260, 'D' => 1370, 'E' => 1370, 'F' => 1470, 'G' => 1470, 'H' => 1680, 'I' => 1790, 'J' => 1890, 'K' => 2000, 'L' => 2100, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1160, 'B' => 1370, 'C' => 1470, 'D' => 1580, 'E' => 1580, 'F' => 1680, 'G' => 1680, 'H' => 1890, 'I' => 2000, 'J' => 2100, 'K' => 2210, 'L' => 2630, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1470, 'B' => 1680, 'C' => 1780, 'D' => 1890, 'E' => 1890, 'F' => 1990, 'G' => 1990, 'H' => 2200, 'I' => 2310, 'J' => 2410, 'K' => 2520, 'L' => 3250, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1980, 'B' => 2190, 'C' => 2290, 'D' => 2400, 'E' => 2400, 'F' => 2500, 'G' => 2500, 'H' => 2710, 'I' => 2820, 'J' => 2920, 'K' => 3030, 'L' => 4080, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 15, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1160, 'B' => 950, 'C' => 950, 'D' => 1050, 'E' => 1050, 'F' => 1160, 'G' => 1160, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 1790, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1370, 'B' => 1160, 'C' => 1160, 'D' => 1260, 'E' => 1260, 'F' => 1370, 'G' => 1370, 'H' => 1470, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 2310, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1680, 'B' => 1470, 'C' => 1470, 'D' => 1570, 'E' => 1570, 'F' => 1680, 'G' => 1680, 'H' => 1780, 'I' => 1890, 'J' => 1990, 'K' => 2100, 'L' => 2940, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2190, 'B' => 1980, 'C' => 1980, 'D' => 2080, 'E' => 2080, 'F' => 2190, 'G' => 2190, 'H' => 2290, 'I' => 2400, 'J' => 2500, 'K' => 2610, 'L' => 3760, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 16, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 950, 'C' => 950, 'D' => 950, 'E' => 950, 'F' => 1050, 'G' => 1050, 'H' => 1160, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 1680, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1470, 'B' => 1160, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1260, 'G' => 1260, 'H' => 1370, 'I' => 1580, 'J' => 1680, 'K' => 1790, 'L' => 2210, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1780, 'B' => 1470, 'C' => 1470, 'D' => 1470, 'E' => 1470, 'F' => 1570, 'G' => 1570, 'H' => 1680, 'I' => 1890, 'J' => 1990, 'K' => 2100, 'L' => 2830, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2290, 'B' => 1980, 'C' => 1980, 'D' => 1980, 'E' => 1980, 'F' => 2080, 'G' => 2080, 'H' => 2190, 'I' => 2400, 'J' => 2500, 'K' => 2610, 'L' => 3660, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 17, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1370, 'B' => 1050, 'C' => 950, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 1050, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1470, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1580, 'B' => 1260, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 2000, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1890, 'B' => 1570, 'C' => 1470, 'D' => 1470, 'E' => 1470, 'F' => 1470, 'G' => 1470, 'H' => 1570, 'I' => 1680, 'J' => 1780, 'K' => 1890, 'L' => 2620, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2400, 'B' => 2080, 'C' => 1980, 'D' => 1980, 'E' => 1980, 'F' => 1980, 'G' => 1980, 'H' => 2080, 'I' => 2190, 'J' => 2290, 'K' => 2400, 'L' => 3450, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 18, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1370, 'B' => 1050, 'C' => 950, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 1050, 'I' => 1160, 'J' => 1260, 'K' => 1370, 'L' => 1580, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1580, 'B' => 1260, 'C' => 1160, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1260, 'I' => 1370, 'J' => 1470, 'K' => 1580, 'L' => 2100, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1890, 'B' => 1570, 'C' => 1470, 'D' => 1470, 'E' => 1470, 'F' => 1470, 'G' => 1470, 'H' => 1570, 'I' => 1680, 'J' => 1780, 'K' => 1890, 'L' => 2730, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2400, 'B' => 2080, 'C' => 1980, 'D' => 1980, 'E' => 1980, 'F' => 1980, 'G' => 1980, 'H' => 2080, 'I' => 2190, 'J' => 2290, 'K' => 2400, 'L' => 3550, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 19, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1470, 'B' => 1160, 'C' => 1050, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 950, 'I' => 1050, 'J' => 1160, 'K' => 1160, 'L' => 1470, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1680, 'B' => 1370, 'C' => 1260, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1260, 'J' => 1370, 'K' => 1370, 'L' => 2000, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1990, 'B' => 1680, 'C' => 1570, 'D' => 1470, 'E' => 1470, 'F' => 1470, 'G' => 1470, 'H' => 1470, 'I' => 1570, 'J' => 1680, 'K' => 1680, 'L' => 2620, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2500, 'B' => 2190, 'C' => 2080, 'D' => 1980, 'E' => 1980, 'F' => 1980, 'G' => 1980, 'H' => 1980, 'I' => 2080, 'J' => 2190, 'K' => 2190, 'L' => 3450, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 20, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1470, 'B' => 1160, 'C' => 1050, 'D' => 950, 'E' => 950, 'F' => 950, 'G' => 950, 'H' => 950, 'I' => 1050, 'J' => 1160, 'K' => 1160, 'L' => 1580, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1680, 'B' => 1370, 'C' => 1260, 'D' => 1160, 'E' => 1160, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1260, 'J' => 1370, 'K' => 1370, 'L' => 2100, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1990, 'B' => 1680, 'C' => 1570, 'D' => 1470, 'E' => 1470, 'F' => 1470, 'G' => 1470, 'H' => 1470, 'I' => 1570, 'J' => 1680, 'K' => 1680, 'L' => 2730, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2500, 'B' => 2190, 'C' => 2080, 'D' => 1980, 'E' => 1980, 'F' => 1980, 'G' => 1980, 'H' => 1980, 'I' => 2080, 'J' => 2190, 'K' => 2190, 'L' => 3550, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 21, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1680, 'B' => 1260, 'C' => 1160, 'D' => 1050, 'E' => 1050, 'F' => 950, 'G' => 950, 'H' => 950, 'I' => 950, 'J' => 1050, 'K' => 1050, 'L' => 1470, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1890, 'B' => 1470, 'C' => 1370, 'D' => 1260, 'E' => 1260, 'F' => 1160, 'G' => 1160, 'H' => 1160, 'I' => 1160, 'J' => 1260, 'K' => 1260, 'L' => 2000, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2200, 'B' => 1780, 'C' => 1680, 'D' => 1570, 'E' => 1570, 'F' => 1470, 'G' => 1470, 'H' => 1470, 'I' => 1470, 'J' => 1570, 'K' => 1570, 'L' => 2620, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2710, 'B' => 2290, 'C' => 2190, 'D' => 2080, 'E' => 2080, 'F' => 1980, 'G' => 1980, 'H' => 1980, 'I' => 1980, 'J' => 2080, 'K' => 2080, 'L' => 3450, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 22, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1790, 'B' => 1370, 'C' => 1370, 'D' => 1160, 'E' => 1160, 'F' => 1050, 'G' => 1050, 'H' => 950, 'I' => 950, 'J' => 1050, 'K' => 950, 'L' => 1470, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2000, 'B' => 1580, 'C' => 1580, 'D' => 1370, 'E' => 1370, 'F' => 1260, 'G' => 1260, 'H' => 1160, 'I' => 1160, 'J' => 1260, 'K' => 1160, 'L' => 2000, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2310, 'B' => 1890, 'C' => 1890, 'D' => 1680, 'E' => 1680, 'F' => 1570, 'G' => 1570, 'H' => 1470, 'I' => 1470, 'J' => 1570, 'K' => 1470, 'L' => 2620, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2820, 'B' => 2400, 'C' => 2400, 'D' => 2190, 'E' => 2190, 'F' => 2080, 'G' => 2080, 'H' => 1980, 'I' => 1980, 'J' => 2080, 'K' => 1980, 'L' => 3450, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 23, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1890, 'B' => 1470, 'C' => 1470, 'D' => 1260, 'E' => 1260, 'F' => 1160, 'G' => 1160, 'H' => 1050, 'I' => 1050, 'J' => 950, 'K' => 1050, 'L' => 1470, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2100, 'B' => 1680, 'C' => 1680, 'D' => 1470, 'E' => 1470, 'F' => 1370, 'G' => 1370, 'H' => 1260, 'I' => 1260, 'J' => 1160, 'K' => 1260, 'L' => 2000, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2410, 'B' => 1990, 'C' => 1990, 'D' => 1780, 'E' => 1780, 'F' => 1680, 'G' => 1680, 'H' => 1570, 'I' => 1570, 'J' => 1470, 'K' => 1570, 'L' => 2620, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2920, 'B' => 2500, 'C' => 2500, 'D' => 2290, 'E' => 2290, 'F' => 2190, 'G' => 2190, 'H' => 2080, 'I' => 2080, 'J' => 1980, 'K' => 2080, 'L' => 3450, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 24, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 2000, 'B' => 1580, 'C' => 1580, 'D' => 1370, 'E' => 1370, 'F' => 1160, 'G' => 1160, 'H' => 1050, 'I' => 950, 'J' => 1050, 'K' => 950, 'L' => 1370, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2210, 'B' => 1790, 'C' => 1790, 'D' => 1580, 'E' => 1580, 'F' => 1370, 'G' => 1370, 'H' => 1260, 'I' => 1160, 'J' => 1260, 'K' => 1160, 'L' => 1890, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 2520, 'B' => 2100, 'C' => 2100, 'D' => 1890, 'E' => 1890, 'F' => 1680, 'G' => 1680, 'H' => 1570, 'I' => 1470, 'J' => 1570, 'K' => 1470, 'L' => 2520, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 3030, 'B' => 2610, 'C' => 2610, 'D' => 2400, 'E' => 2400, 'F' => 2190, 'G' => 2190, 'H' => 2080, 'I' => 1980, 'J' => 2080, 'K' => 1980, 'L' => 3340, ), ), )
        ),
        array('carrier_code' => 'yamato', 'service_code' => 'cool', 'zone_id' => 25, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 2100, 'B' => 1790, 'C' => 1680, 'D' => 1470, 'E' => 1580, 'F' => 1470, 'G' => 1580, 'H' => 1470, 'I' => 1470, 'J' => 1470, 'K' => 1370, 'L' => 950, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 2630, 'B' => 2310, 'C' => 2210, 'D' => 2000, 'E' => 2100, 'F' => 2000, 'G' => 2100, 'H' => 2000, 'I' => 2000, 'J' => 2000, 'K' => 1890, 'L' => 1160, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 3250, 'B' => 2940, 'C' => 2830, 'D' => 2620, 'E' => 2730, 'F' => 2620, 'G' => 2730, 'H' => 2620, 'I' => 2620, 'J' => 2620, 'K' => 2520, 'L' => 1470, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 4080, 'B' => 3760, 'C' => 3660, 'D' => 3450, 'E' => 3550, 'F' => 3450, 'G' => 3550, 'H' => 3450, 'I' => 3450, 'J' => 3450, 'K' => 3340, 'L' => 1980, ), ), )
        ),
        // 福山通運 - パーセルワン
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 37, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 525, 'B' => 630, 'C' => 630, 'D' => 840, 'E' => 840, 'F' => 1260, 'G' => 945, 'H' => 1260, 'I' => 1260, 'J' => 1365, 'K' => 1470, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 630, 'B' => 840, 'C' => 840, 'D' => 1050, 'E' => 1050, 'F' => 1470, 'G' => 1155, 'H' => 1470, 'I' => 1470, 'J' => 1575, 'K' => 1680, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 840, 'B' => 1050, 'C' => 1155, 'D' => 1365, 'E' => 1365, 'F' => 1575, 'G' => 1365, 'H' => 1785, 'I' => 1785, 'J' => 1890, 'K' => 1995, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 893, 'B' => 1103, 'C' => 1208, 'D' => 1418, 'E' => 1418, 'F' => 1628, 'G' => 1418, 'H' => 1838, 'I' => 1838, 'J' => 1943, 'K' => 2048, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 945, 'B' => 1155, 'C' => 1260, 'D' => 1470, 'E' => 1470, 'F' => 1680, 'G' => 1470, 'H' => 1890, 'I' => 1890, 'J' => 1995, 'K' => 2100, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1050, 'B' => 1260, 'C' => 1365, 'D' => 1575, 'E' => 1575, 'F' => 1785, 'G' => 1575, 'H' => 1995, 'I' => 1995, 'J' => 2100, 'K' => 2205, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 38, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 630, 'B' => 525, 'C' => 525, 'D' => 630, 'E' => 630, 'F' => 630, 'G' => 840, 'H' => 840, 'I' => 945, 'J' => 945, 'K' => 1050, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 840, 'B' => 630, 'C' => 630, 'D' => 735, 'E' => 840, 'F' => 840, 'G' => 1050, 'H' => 1050, 'I' => 1155, 'J' => 1155, 'K' => 1260, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1050, 'B' => 840, 'C' => 945, 'D' => 1155, 'E' => 1260, 'F' => 1365, 'G' => 1260, 'H' => 1575, 'I' => 1680, 'J' => 1785, 'K' => 1890, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1103, 'B' => 893, 'C' => 998, 'D' => 1208, 'E' => 1313, 'F' => 1418, 'G' => 1313, 'H' => 1628, 'I' => 1733, 'J' => 1838, 'K' => 1943, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1155, 'B' => 945, 'C' => 1050, 'D' => 1260, 'E' => 1365, 'F' => 1470, 'G' => 1365, 'H' => 1680, 'I' => 1785, 'J' => 1890, 'K' => 1995, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1260, 'B' => 1050, 'C' => 1155, 'D' => 1365, 'E' => 1470, 'F' => 1575, 'G' => 1470, 'H' => 1785, 'I' => 1890, 'J' => 1995, 'K' => 2100, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 39, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 630, 'B' => 525, 'C' => 525, 'D' => 630, 'E' => 630, 'F' => 630, 'G' => 840, 'H' => 840, 'I' => 945, 'J' => 945, 'K' => 1050, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 840, 'B' => 630, 'C' => 630, 'D' => 735, 'E' => 840, 'F' => 840, 'G' => 1050, 'H' => 1050, 'I' => 1155, 'J' => 1155, 'K' => 1260, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1155, 'B' => 945, 'C' => 840, 'D' => 945, 'E' => 1050, 'F' => 1260, 'G' => 1155, 'H' => 1365, 'I' => 1470, 'J' => 1575, 'K' => 1680, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1208, 'B' => 998, 'C' => 893, 'D' => 998, 'E' => 1103, 'F' => 1313, 'G' => 1208, 'H' => 1418, 'I' => 1523, 'J' => 1628, 'K' => 1733, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1260, 'B' => 1050, 'C' => 945, 'D' => 1050, 'E' => 1155, 'F' => 1365, 'G' => 1260, 'H' => 1470, 'I' => 1575, 'J' => 1680, 'K' => 1785, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1365, 'B' => 1155, 'C' => 1050, 'D' => 1155, 'E' => 1260, 'F' => 1470, 'G' => 1365, 'H' => 1575, 'I' => 1680, 'J' => 1785, 'K' => 1890, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 40, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 840, 'B' => 630, 'C' => 630, 'D' => 525, 'E' => 525, 'F' => 525, 'G' => 630, 'H' => 630, 'I' => 630, 'J' => 735, 'K' => 840, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1050, 'B' => 735, 'C' => 735, 'D' => 630, 'E' => 630, 'F' => 630, 'G' => 735, 'H' => 735, 'I' => 840, 'J' => 945, 'K' => 1050, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1365, 'B' => 1155, 'C' => 945, 'D' => 840, 'E' => 840, 'F' => 945, 'G' => 945, 'H' => 1050, 'I' => 1155, 'J' => 1260, 'K' => 1470, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1418, 'B' => 1208, 'C' => 998, 'D' => 893, 'E' => 893, 'F' => 998, 'G' => 998, 'H' => 1103, 'I' => 1208, 'J' => 1313, 'K' => 1523, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1470, 'B' => 1260, 'C' => 1050, 'D' => 945, 'E' => 945, 'F' => 1050, 'G' => 1050, 'H' => 1155, 'I' => 1260, 'J' => 1365, 'K' => 1575, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1575, 'B' => 1365, 'C' => 1155, 'D' => 1050, 'E' => 1050, 'F' => 1155, 'G' => 1155, 'H' => 1260, 'I' => 1365, 'J' => 1470, 'K' => 1680, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 41, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 840, 'B' => 630, 'C' => 630, 'D' => 525, 'E' => 525, 'F' => 525, 'G' => 525, 'H' => 630, 'I' => 630, 'J' => 735, 'K' => 840, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1050, 'B' => 840, 'C' => 840, 'D' => 630, 'E' => 630, 'F' => 630, 'G' => 630, 'H' => 735, 'I' => 840, 'J' => 945, 'K' => 1050, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1365, 'B' => 1260, 'C' => 1050, 'D' => 840, 'E' => 840, 'F' => 840, 'G' => 840, 'H' => 945, 'I' => 1050, 'J' => 1155, 'K' => 1470, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1418, 'B' => 1313, 'C' => 1103, 'D' => 893, 'E' => 893, 'F' => 893, 'G' => 893, 'H' => 998, 'I' => 1103, 'J' => 1208, 'K' => 1523, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1470, 'B' => 1365, 'C' => 1155, 'D' => 945, 'E' => 945, 'F' => 945, 'G' => 945, 'H' => 1050, 'I' => 1155, 'J' => 1260, 'K' => 1575, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1575, 'B' => 1470, 'C' => 1260, 'D' => 1050, 'E' => 1050, 'F' => 1050, 'G' => 1050, 'H' => 1155, 'I' => 1260, 'J' => 1365, 'K' => 1680, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 42, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 630, 'C' => 630, 'D' => 525, 'E' => 525, 'F' => 525, 'G' => 525, 'H' => 525, 'I' => 630, 'J' => 630, 'K' => 840, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1470, 'B' => 840, 'C' => 840, 'D' => 630, 'E' => 630, 'F' => 630, 'G' => 630, 'H' => 630, 'I' => 735, 'J' => 735, 'K' => 1050, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1575, 'B' => 1365, 'C' => 1260, 'D' => 945, 'E' => 840, 'F' => 840, 'G' => 840, 'H' => 840, 'I' => 945, 'J' => 945, 'K' => 1470, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1628, 'B' => 1418, 'C' => 1313, 'D' => 998, 'E' => 893, 'F' => 945, 'G' => 945, 'H' => 945, 'I' => 1050, 'J' => 1050, 'K' => 1523, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1680, 'B' => 1470, 'C' => 1365, 'D' => 1050, 'E' => 945, 'F' => 945, 'G' => 945, 'H' => 945, 'I' => 1050, 'J' => 1050, 'K' => 1575, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1785, 'B' => 1575, 'C' => 1470, 'D' => 1155, 'E' => 1050, 'F' => 1050, 'G' => 1050, 'H' => 1050, 'I' => 1155, 'J' => 1155, 'K' => 1680, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 43, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 945, 'B' => 840, 'C' => 840, 'D' => 630, 'E' => 525, 'F' => 525, 'G' => 525, 'H' => 525, 'I' => 630, 'J' => 630, 'K' => 630, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1155, 'B' => 1050, 'C' => 1050, 'D' => 735, 'E' => 630, 'F' => 630, 'G' => 630, 'H' => 630, 'I' => 735, 'J' => 840, 'K' => 840, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1365, 'B' => 1260, 'C' => 1155, 'D' => 945, 'E' => 840, 'F' => 840, 'G' => 840, 'H' => 840, 'I' => 945, 'J' => 1050, 'K' => 1155, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1418, 'B' => 1313, 'C' => 1208, 'D' => 998, 'E' => 893, 'F' => 945, 'G' => 893, 'H' => 893, 'I' => 998, 'J' => 1103, 'K' => 1208, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1470, 'B' => 1365, 'C' => 1260, 'D' => 1050, 'E' => 945, 'F' => 945, 'G' => 945, 'H' => 945, 'I' => 1050, 'J' => 1155, 'K' => 1260, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1575, 'B' => 1470, 'C' => 1365, 'D' => 1155, 'E' => 1050, 'F' => 1050, 'G' => 1050, 'H' => 1050, 'I' => 1155, 'J' => 1260, 'K' => 1365, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 44, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 840, 'C' => 840, 'D' => 630, 'E' => 630, 'F' => 525, 'G' => 525, 'H' => 525, 'I' => 525, 'J' => 525, 'K' => 630, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1470, 'B' => 1050, 'C' => 1050, 'D' => 735, 'E' => 735, 'F' => 630, 'G' => 630, 'H' => 630, 'I' => 630, 'J' => 630, 'K' => 840, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1785, 'B' => 1575, 'C' => 1365, 'D' => 1050, 'E' => 945, 'F' => 840, 'G' => 840, 'H' => 840, 'I' => 840, 'J' => 840, 'K' => 1155, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1838, 'B' => 1628, 'C' => 1418, 'D' => 1103, 'E' => 998, 'F' => 945, 'G' => 893, 'H' => 893, 'I' => 893, 'J' => 893, 'K' => 1208, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1890, 'B' => 1680, 'C' => 1470, 'D' => 1155, 'E' => 1050, 'F' => 945, 'G' => 945, 'H' => 945, 'I' => 945, 'J' => 945, 'K' => 1260, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1995, 'B' => 1785, 'C' => 1575, 'D' => 1260, 'E' => 1155, 'F' => 1050, 'G' => 1050, 'H' => 1050, 'I' => 1050, 'J' => 1050, 'K' => 1365, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 45, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1260, 'B' => 945, 'C' => 945, 'D' => 630, 'E' => 630, 'F' => 630, 'G' => 630, 'H' => 525, 'I' => 525, 'J' => 630, 'K' => 525, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1470, 'B' => 1155, 'C' => 1155, 'D' => 840, 'E' => 840, 'F' => 735, 'G' => 735, 'H' => 630, 'I' => 630, 'J' => 735, 'K' => 630, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1785, 'B' => 1680, 'C' => 1470, 'D' => 1155, 'E' => 1050, 'F' => 945, 'G' => 945, 'H' => 840, 'I' => 840, 'J' => 945, 'K' => 840, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1838, 'B' => 1733, 'C' => 1523, 'D' => 1208, 'E' => 1103, 'F' => 1050, 'G' => 998, 'H' => 893, 'I' => 893, 'J' => 998, 'K' => 893, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1890, 'B' => 1785, 'C' => 1575, 'D' => 1260, 'E' => 1155, 'F' => 1050, 'G' => 1050, 'H' => 945, 'I' => 945, 'J' => 1050, 'K' => 945, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 1995, 'B' => 1890, 'C' => 1680, 'D' => 1365, 'E' => 1260, 'F' => 1155, 'G' => 1155, 'H' => 1050, 'I' => 1050, 'J' => 1155, 'K' => 1050, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 46, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1365, 'B' => 945, 'C' => 945, 'D' => 735, 'E' => 735, 'F' => 630, 'G' => 630, 'H' => 525, 'I' => 630, 'J' => 525, 'K' => 630, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1575, 'B' => 1155, 'C' => 1155, 'D' => 945, 'E' => 945, 'F' => 735, 'G' => 840, 'H' => 630, 'I' => 735, 'J' => 630, 'K' => 735, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1890, 'B' => 1785, 'C' => 1575, 'D' => 1260, 'E' => 1155, 'F' => 945, 'G' => 1050, 'H' => 840, 'I' => 945, 'J' => 840, 'K' => 1050, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 1943, 'B' => 1838, 'C' => 1628, 'D' => 1313, 'E' => 1208, 'F' => 1050, 'G' => 1103, 'H' => 893, 'I' => 998, 'J' => 893, 'K' => 1103, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 1995, 'B' => 1890, 'C' => 1680, 'D' => 1365, 'E' => 1260, 'F' => 1050, 'G' => 1155, 'H' => 945, 'I' => 1050, 'J' => 945, 'K' => 1155, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2100, 'B' => 1995, 'C' => 1785, 'D' => 1470, 'E' => 1365, 'F' => 1155, 'G' => 1260, 'H' => 1050, 'I' => 1155, 'J' => 1050, 'K' => 1260, ), ), )
        ),
        array('carrier_code' => 'fukutsu', 'service_code' => 'parcel1', 'zone_id' => 47, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '2', 'rates' => array ( 'A' => 1470, 'B' => 1050, 'C' => 1050, 'D' => 840, 'E' => 840, 'F' => 840, 'G' => 630, 'H' => 630, 'I' => 525, 'J' => 630, 'K' => 525, ), ), 80 => array ( 'size' => '80', 'weight' => '5', 'rates' => array ( 'A' => 1680, 'B' => 1260, 'C' => 1260, 'D' => 1050, 'E' => 1050, 'F' => 1050, 'G' => 840, 'H' => 840, 'I' => 630, 'J' => 735, 'K' => 630, ), ), 100 => array ( 'size' => '100', 'weight' => '10', 'rates' => array ( 'A' => 1995, 'B' => 1890, 'C' => 1680, 'D' => 1470, 'E' => 1470, 'F' => 1470, 'G' => 1155, 'H' => 1155, 'I' => 840, 'J' => 1050, 'K' => 840, ), ), 120 => array ( 'size' => '120', 'weight' => '15', 'rates' => array ( 'A' => 2048, 'B' => 1943, 'C' => 1733, 'D' => 1523, 'E' => 1523, 'F' => 1523, 'G' => 1208, 'H' => 1208, 'I' => 893, 'J' => 1103, 'K' => 893, ), ), 140 => array ( 'size' => '140', 'weight' => '20', 'rates' => array ( 'A' => 2100, 'B' => 1995, 'C' => 1785, 'D' => 1575, 'E' => 1575, 'F' => 1575, 'G' => 1260, 'H' => 1260, 'I' => 945, 'J' => 1155, 'K' => 945, ), ), 160 => array ( 'size' => '160', 'weight' => '25', 'rates' => array ( 'A' => 2205, 'B' => 2100, 'C' => 1890, 'D' => 1680, 'E' => 1680, 'F' => 1680, 'G' => 1365, 'H' => 1365, 'I' => 1050, 'J' => 1260, 'K' => 1050, ), ), )
        ),
        // 日本郵便 - EMS
        array('carrier_code' => 'jpems', 'service_code' => 'ems', 'zone_id' => 52, 'shipping_rates' => array ( 10 => array ( 'size' => '10', 'weight' => '0.30', 'rates' => array ( 'A' => 900, 'B' => 1200, 'C' => 1500, 'D' => 1700, ), ), 20 => array ( 'size' => '20', 'weight' => '0.50', 'rates' => array ( 'A' => 1100, 'B' => 1500, 'C' => 1800, 'D' => 2100, ), ), 30 => array ( 'size' => '30', 'weight' => '0.6', 'rates' => array ( 'A' => 1240, 'B' => 1680, 'C' => 2000, 'D' => 2440, ), ), 40 => array ( 'size' => '40', 'weight' => '0.70', 'rates' => array ( 'A' => 1380, 'B' => 1860, 'C' => 2200, 'D' => 2780, ), ), 50 => array ( 'size' => '50', 'weight' => '0.80', 'rates' => array ( 'A' => 1520, 'B' => 2040, 'C' => 2400, 'D' => 3120, ), ), 60 => array ( 'size' => '60', 'weight' => '0.90', 'rates' => array ( 'A' => 1660, 'B' => 2220, 'C' => 2600, 'D' => 3460, ), ), 70 => array ( 'size' => '70', 'weight' => '1', 'rates' => array ( 'A' => 1800, 'B' => 2400, 'C' => 2800, 'D' => 3800, ), ), 80 => array ( 'size' => '80', 'weight' => '1.25', 'rates' => array ( 'A' => 2100, 'B' => 2800, 'C' => 3250, 'D' => 4600, ), ), 90 => array ( 'size' => '90', 'weight' => '1.5', 'rates' => array ( 'A' => 2400, 'B' => 3200, 'C' => 3700, 'D' => 5400, ), ), 100 => array ( 'size' => '100', 'weight' => '1.75', 'rates' => array ( 'A' => 2700, 'B' => 3600, 'C' => 4150, 'D' => 6200, ), ), 110 => array ( 'size' => '110', 'weight' => '2', 'rates' => array ( 'A' => 3000, 'B' => 4000, 'C' => 4600, 'D' => 7000, ), ), 120 => array ( 'size' => '120', 'weight' => '2.5', 'rates' => array ( 'A' => 3500, 'B' => 4700, 'C' => 5400, 'D' => 8500, ), ), 130 => array ( 'size' => '130', 'weight' => '3', 'rates' => array ( 'A' => 4000, 'B' => 5400, 'C' => 6200, 'D' => 10000, ), ), 140 => array ( 'size' => '140', 'weight' => '3.5', 'rates' => array ( 'A' => 4500, 'B' => 6100, 'C' => 7000, 'D' => 11500, ), ), 150 => array ( 'size' => '150', 'weight' => '4', 'rates' => array ( 'A' => 5000, 'B' => 6800, 'C' => 7800, 'D' => 13000, ), ), 160 => array ( 'size' => '160', 'weight' => '4.5', 'rates' => array ( 'A' => 5500, 'B' => 7500, 'C' => 8600, 'D' => 14500, ), ), 170 => array ( 'size' => '170', 'weight' => '5', 'rates' => array ( 'A' => 6000, 'B' => 8200, 'C' => 9400, 'D' => 16000, ), ), 180 => array ( 'size' => '180', 'weight' => '5.5', 'rates' => array ( 'A' => 6500, 'B' => 8900, 'C' => 10200, 'D' => 17500, ), ), 190 => array ( 'size' => '190', 'weight' => '6', 'rates' => array ( 'A' => 7000, 'B' => 9600, 'C' => 11000, 'D' => 19000, ), ), 200 => array ( 'size' => '200', 'weight' => '7', 'rates' => array ( 'A' => 7800, 'B' => 10700, 'C' => 12300, 'D' => 21100, ), ), 210 => array ( 'size' => '210', 'weight' => '8', 'rates' => array ( 'A' => 8600, 'B' => 11800, 'C' => 13600, 'D' => 23200, ), ), 220 => array ( 'size' => '220', 'weight' => '9', 'rates' => array ( 'A' => 9400, 'B' => 12900, 'C' => 14900, 'D' => 25300, ), ), 230 => array ( 'size' => '230', 'weight' => '10', 'rates' => array ( 'A' => 10200, 'B' => 14000, 'C' => 16200, 'D' => 27400, ), ), 240 => array ( 'size' => '240', 'weight' => '11', 'rates' => array ( 'A' => 11000, 'B' => 15100, 'C' => 17500, 'D' => 29500, ), ), 250 => array ( 'size' => '250', 'weight' => '12', 'rates' => array ( 'A' => 11800, 'B' => 16200, 'C' => 18800, 'D' => 31600, ), ), 260 => array ( 'size' => '260', 'weight' => '13', 'rates' => array ( 'A' => 12600, 'B' => 17300, 'C' => 20100, 'D' => 33700, ), ), 270 => array ( 'size' => '270', 'weight' => '14', 'rates' => array ( 'A' => 13400, 'B' => 18400, 'C' => 21400, 'D' => 35800, ), ), 280 => array ( 'size' => '280', 'weight' => '15', 'rates' => array ( 'A' => 14200, 'B' => 19500, 'C' => 22700, 'D' => 37900, ), ), 290 => array ( 'size' => '290', 'weight' => '16', 'rates' => array ( 'A' => 15000, 'B' => 20600, 'C' => 24000, 'D' => 40000, ), ), 300 => array ( 'size' => '300', 'weight' => '17', 'rates' => array ( 'A' => 15800, 'B' => 21700, 'C' => 25300, 'D' => 42100, ), ), 310 => array ( 'size' => '310', 'weight' => '18', 'rates' => array ( 'A' => 16600, 'B' => 22800, 'C' => 26600, 'D' => 44200, ), ), 320 => array ( 'size' => '320', 'weight' => '19', 'rates' => array ( 'A' => 17400, 'B' => 23900, 'C' => 27900, 'D' => 46300, ), ), 330 => array ( 'size' => '330', 'weight' => '20', 'rates' => array ( 'A' => 18200, 'B' => 25000, 'C' => 29200, 'D' => 48400, ), ), 340 => array ( 'size' => '340', 'weight' => '21', 'rates' => array ( 'A' => 19000, 'B' => 26100, 'C' => 30500, 'D' => 50500, ), ), 350 => array ( 'size' => '350', 'weight' => '22', 'rates' => array ( 'A' => 19800, 'B' => 27200, 'C' => 31800, 'D' => 52600, ), ), 360 => array ( 'size' => '360', 'weight' => '23', 'rates' => array ( 'A' => 20600, 'B' => 28300, 'C' => 33100, 'D' => 54700, ), ), 370 => array ( 'size' => '370', 'weight' => '24', 'rates' => array ( 'A' => 21400, 'B' => 29400, 'C' => 34400, 'D' => 56800, ), ), 380 => array ( 'size' => '380', 'weight' => '25', 'rates' => array ( 'A' => 22200, 'B' => 30500, 'C' => 35700, 'D' => 58900, ), ), 390 => array ( 'size' => '390', 'weight' => '26', 'rates' => array ( 'A' => 23000, 'B' => 31600, 'C' => 37000, 'D' => 61000, ), ), 400 => array ( 'size' => '400', 'weight' => '27', 'rates' => array ( 'A' => 23800, 'B' => 32700, 'C' => 38300, 'D' => 63100, ), ), 410 => array ( 'size' => '410', 'weight' => '28', 'rates' => array ( 'A' => 24600, 'B' => 33800, 'C' => 39600, 'D' => 65200, ), ), 420 => array ( 'size' => '420', 'weight' => '29', 'rates' => array ( 'A' => 25400, 'B' => 34900, 'C' => 40900, 'D' => 67300, ), ), 430 => array ( 'size' => '430', 'weight' => '30', 'rates' => array ( 'A' => 26200, 'B' => 36000, 'C' => 42200, 'D' => 69400, ), ), )
        ),
        // 日本郵便 - ゆうパック
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 53, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 600, 'B' => 800, 'C' => 1000, 'D' => 1000, 'E' => 1100, 'F' => 1100, 'G' => 1200, 'H' => 1300, 'I' => 1300, 'J' => 1300, 'K' => 1300, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 800, 'B' => 1000, 'C' => 1200, 'D' => 1200, 'E' => 1300, 'F' => 1300, 'G' => 1400, 'H' => 1500, 'I' => 1500, 'J' => 1500, 'K' => 1500, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1000, 'B' => 1200, 'C' => 1400, 'D' => 1400, 'E' => 1500, 'F' => 1500, 'G' => 1600, 'H' => 1700, 'I' => 1700, 'J' => 1700, 'K' => 1700, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1200, 'B' => 1400, 'C' => 1600, 'D' => 1600, 'E' => 1700, 'F' => 1700, 'G' => 1800, 'H' => 1900, 'I' => 1900, 'J' => 1900, 'K' => 1900, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 1400, 'B' => 1600, 'C' => 1800, 'D' => 1800, 'E' => 1900, 'F' => 1900, 'G' => 2000, 'H' => 2100, 'I' => 2100, 'J' => 2100, 'K' => 2100, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 1600, 'B' => 1800, 'C' => 2000, 'D' => 2000, 'E' => 2100, 'F' => 2100, 'G' => 2200, 'H' => 2300, 'I' => 2300, 'J' => 2300, 'K' => 2300, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 1700, 'B' => 2000, 'C' => 2200, 'D' => 2200, 'E' => 2300, 'F' => 2300, 'G' => 2400, 'H' => 2500, 'I' => 2500, 'J' => 2500, 'K' => 2500, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 54, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 800, 'B' => 700, 'C' => 700, 'D' => 700, 'E' => 800, 'F' => 800, 'G' => 900, 'H' => 1000, 'I' => 1000, 'J' => 1200, 'K' => 1300, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1000, 'B' => 900, 'C' => 900, 'D' => 900, 'E' => 1000, 'F' => 1000, 'G' => 1100, 'H' => 1200, 'I' => 1200, 'J' => 1400, 'K' => 1500, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1200, 'B' => 1100, 'C' => 1100, 'D' => 1100, 'E' => 1200, 'F' => 1200, 'G' => 1300, 'H' => 1400, 'I' => 1400, 'J' => 1600, 'K' => 1700, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1400, 'B' => 1300, 'C' => 1300, 'D' => 1300, 'E' => 1400, 'F' => 1400, 'G' => 1500, 'H' => 1600, 'I' => 1600, 'J' => 1800, 'K' => 1900, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 1600, 'B' => 1500, 'C' => 1500, 'D' => 1500, 'E' => 1600, 'F' => 1600, 'G' => 1700, 'H' => 1800, 'I' => 1800, 'J' => 2000, 'K' => 2100, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 1800, 'B' => 1700, 'C' => 1700, 'D' => 1700, 'E' => 1800, 'F' => 1800, 'G' => 1900, 'H' => 2000, 'I' => 2000, 'J' => 2200, 'K' => 2300, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2000, 'B' => 1900, 'C' => 1900, 'D' => 1900, 'E' => 2000, 'F' => 2000, 'G' => 2100, 'H' => 2200, 'I' => 2200, 'J' => 2400, 'K' => 2500, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 55, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1000, 'B' => 700, 'C' => 700, 'D' => 700, 'E' => 700, 'F' => 700, 'G' => 800, 'H' => 900, 'I' => 900, 'J' => 1100, 'K' => 1200, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1200, 'B' => 900, 'C' => 900, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 1000, 'H' => 1100, 'I' => 1100, 'J' => 1300, 'K' => 1400, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1400, 'B' => 1100, 'C' => 1100, 'D' => 1100, 'E' => 1100, 'F' => 1100, 'G' => 1200, 'H' => 1300, 'I' => 1300, 'J' => 1500, 'K' => 1600, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1600, 'B' => 1300, 'C' => 1300, 'D' => 1300, 'E' => 1300, 'F' => 1300, 'G' => 1400, 'H' => 1500, 'I' => 1500, 'J' => 1700, 'K' => 1800, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 1800, 'B' => 1500, 'C' => 1500, 'D' => 1500, 'E' => 1500, 'F' => 1500, 'G' => 1600, 'H' => 1700, 'I' => 1700, 'J' => 1900, 'K' => 2000, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2000, 'B' => 1700, 'C' => 1700, 'D' => 1700, 'E' => 1700, 'F' => 1700, 'G' => 1800, 'H' => 1900, 'I' => 1900, 'J' => 2100, 'K' => 2200, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2200, 'B' => 1900, 'C' => 1900, 'D' => 1900, 'E' => 1900, 'F' => 1900, 'G' => 2000, 'H' => 2100, 'I' => 2100, 'J' => 2300, 'K' => 2400, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 56, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1000, 'B' => 700, 'C' => 700, 'D' => 700, 'E' => 700, 'F' => 700, 'G' => 800, 'H' => 900, 'I' => 900, 'J' => 1100, 'K' => 1300, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1200, 'B' => 900, 'C' => 900, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 1000, 'H' => 1100, 'I' => 1100, 'J' => 1300, 'K' => 1500, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1400, 'B' => 1100, 'C' => 1100, 'D' => 1100, 'E' => 1100, 'F' => 1100, 'G' => 1200, 'H' => 1300, 'I' => 1300, 'J' => 1500, 'K' => 1700, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1600, 'B' => 1300, 'C' => 1300, 'D' => 1300, 'E' => 1300, 'F' => 1300, 'G' => 1400, 'H' => 1500, 'I' => 1500, 'J' => 1700, 'K' => 1900, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 1800, 'B' => 1500, 'C' => 1500, 'D' => 1500, 'E' => 1500, 'F' => 1500, 'G' => 1600, 'H' => 1700, 'I' => 1700, 'J' => 1900, 'K' => 2100, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2000, 'B' => 1700, 'C' => 1700, 'D' => 1700, 'E' => 1700, 'F' => 1700, 'G' => 1800, 'H' => 1900, 'I' => 1900, 'J' => 2100, 'K' => 2300, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2200, 'B' => 1900, 'C' => 1900, 'D' => 1900, 'E' => 1900, 'F' => 1900, 'G' => 2000, 'H' => 2100, 'I' => 2100, 'J' => 2300, 'K' => 2500, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 57, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1100, 'B' => 800, 'C' => 700, 'D' => 700, 'E' => 700, 'F' => 700, 'G' => 700, 'H' => 800, 'I' => 800, 'J' => 900, 'K' => 1300, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1300, 'B' => 1000, 'C' => 900, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 900, 'H' => 1000, 'I' => 1000, 'J' => 1100, 'K' => 1500, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1500, 'B' => 1200, 'C' => 1100, 'D' => 1100, 'E' => 1100, 'F' => 1100, 'G' => 1100, 'H' => 1200, 'I' => 1200, 'J' => 1300, 'K' => 1700, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1700, 'B' => 1400, 'C' => 1300, 'D' => 1300, 'E' => 1300, 'F' => 1300, 'G' => 1300, 'H' => 1400, 'I' => 1400, 'J' => 1500, 'K' => 1900, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 1900, 'B' => 1600, 'C' => 1500, 'D' => 1500, 'E' => 1500, 'F' => 1500, 'G' => 1500, 'H' => 1600, 'I' => 1600, 'J' => 1700, 'K' => 2100, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2100, 'B' => 1800, 'C' => 1700, 'D' => 1700, 'E' => 1700, 'F' => 1700, 'G' => 1700, 'H' => 1800, 'I' => 1800, 'J' => 1900, 'K' => 2300, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2300, 'B' => 2000, 'C' => 1900, 'D' => 1900, 'E' => 1900, 'F' => 1900, 'G' => 1900, 'H' => 2000, 'I' => 2000, 'J' => 2100, 'K' => 2500, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 58, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1100, 'B' => 800, 'C' => 700, 'D' => 700, 'E' => 700, 'F' => 700, 'G' => 700, 'H' => 800, 'I' => 800, 'J' => 900, 'K' => 1200, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1300, 'B' => 1000, 'C' => 900, 'D' => 900, 'E' => 900, 'F' => 900, 'G' => 900, 'H' => 1000, 'I' => 1000, 'J' => 1100, 'K' => 1400, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1500, 'B' => 1200, 'C' => 1100, 'D' => 1100, 'E' => 1100, 'F' => 1100, 'G' => 1100, 'H' => 1200, 'I' => 1200, 'J' => 1300, 'K' => 1600, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1700, 'B' => 1400, 'C' => 1300, 'D' => 1300, 'E' => 1300, 'F' => 1300, 'G' => 1300, 'H' => 1400, 'I' => 1400, 'J' => 1500, 'K' => 1800, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 1900, 'B' => 1600, 'C' => 1500, 'D' => 1500, 'E' => 1500, 'F' => 1500, 'G' => 1500, 'H' => 1600, 'I' => 1600, 'J' => 1700, 'K' => 2000, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2100, 'B' => 1800, 'C' => 1700, 'D' => 1700, 'E' => 1700, 'F' => 1700, 'G' => 1700, 'H' => 1800, 'I' => 1800, 'J' => 1900, 'K' => 2200, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2300, 'B' => 2000, 'C' => 1900, 'D' => 1900, 'E' => 1900, 'F' => 1900, 'G' => 1900, 'H' => 2000, 'I' => 2000, 'J' => 2100, 'K' => 2400, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 59, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1200, 'B' => 900, 'C' => 800, 'D' => 800, 'E' => 700, 'F' => 700, 'G' => 700, 'H' => 700, 'I' => 700, 'J' => 800, 'K' => 1200, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1400, 'B' => 1100, 'C' => 1000, 'D' => 1000, 'E' => 900, 'F' => 900, 'G' => 900, 'H' => 900, 'I' => 900, 'J' => 1000, 'K' => 1400, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1600, 'B' => 1300, 'C' => 1200, 'D' => 1200, 'E' => 1100, 'F' => 1100, 'G' => 1100, 'H' => 1100, 'I' => 1100, 'J' => 1200, 'K' => 1600, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1800, 'B' => 1500, 'C' => 1400, 'D' => 1400, 'E' => 1300, 'F' => 1300, 'G' => 1300, 'H' => 1300, 'I' => 1300, 'J' => 1400, 'K' => 1800, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 2000, 'B' => 1700, 'C' => 1600, 'D' => 1600, 'E' => 1500, 'F' => 1500, 'G' => 1500, 'H' => 1500, 'I' => 1500, 'J' => 1600, 'K' => 2000, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2200, 'B' => 1900, 'C' => 1800, 'D' => 1800, 'E' => 1700, 'F' => 1700, 'G' => 1700, 'H' => 1700, 'I' => 1700, 'J' => 1800, 'K' => 2200, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2400, 'B' => 2100, 'C' => 2000, 'D' => 2000, 'E' => 1900, 'F' => 1900, 'G' => 1900, 'H' => 1900, 'I' => 1900, 'J' => 2000, 'K' => 2400, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 60, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1300, 'B' => 1000, 'C' => 900, 'D' => 900, 'E' => 800, 'F' => 800, 'G' => 700, 'H' => 700, 'I' => 700, 'J' => 700, 'K' => 1100, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1500, 'B' => 1200, 'C' => 1100, 'D' => 1100, 'E' => 1000, 'F' => 1000, 'G' => 900, 'H' => 900, 'I' => 900, 'J' => 900, 'K' => 1300, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1700, 'B' => 1400, 'C' => 1300, 'D' => 1300, 'E' => 1200, 'F' => 1200, 'G' => 1100, 'H' => 1100, 'I' => 1100, 'J' => 1100, 'K' => 1500, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1900, 'B' => 1600, 'C' => 1500, 'D' => 1500, 'E' => 1400, 'F' => 1400, 'G' => 1300, 'H' => 1300, 'I' => 1300, 'J' => 1300, 'K' => 1700, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 2100, 'B' => 1800, 'C' => 1700, 'D' => 1700, 'E' => 1600, 'F' => 1600, 'G' => 1500, 'H' => 1500, 'I' => 1500, 'J' => 1500, 'K' => 1900, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2300, 'B' => 2000, 'C' => 1900, 'D' => 1900, 'E' => 1800, 'F' => 1800, 'G' => 1700, 'H' => 1700, 'I' => 1700, 'J' => 1700, 'K' => 2100, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2500, 'B' => 2200, 'C' => 2100, 'D' => 2100, 'E' => 2000, 'F' => 2000, 'G' => 1900, 'H' => 1900, 'I' => 1900, 'J' => 1900, 'K' => 2300, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 61, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1300, 'B' => 1000, 'C' => 900, 'D' => 900, 'E' => 800, 'F' => 800, 'G' => 700, 'H' => 700, 'I' => 700, 'J' => 800, 'K' => 1200, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1500, 'B' => 1200, 'C' => 1100, 'D' => 1100, 'E' => 1000, 'F' => 1000, 'G' => 900, 'H' => 900, 'I' => 900, 'J' => 1000, 'K' => 1400, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1700, 'B' => 1400, 'C' => 1300, 'D' => 1300, 'E' => 1200, 'F' => 1200, 'G' => 1100, 'H' => 1100, 'I' => 1100, 'J' => 1200, 'K' => 1600, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1900, 'B' => 1600, 'C' => 1500, 'D' => 1500, 'E' => 1400, 'F' => 1400, 'G' => 1300, 'H' => 1300, 'I' => 1300, 'J' => 1400, 'K' => 1800, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 2100, 'B' => 1800, 'C' => 1700, 'D' => 1700, 'E' => 1600, 'F' => 1600, 'G' => 1500, 'H' => 1500, 'I' => 1500, 'J' => 1600, 'K' => 2000, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2300, 'B' => 2000, 'C' => 1900, 'D' => 1900, 'E' => 1800, 'F' => 1800, 'G' => 1700, 'H' => 1700, 'I' => 1700, 'J' => 1800, 'K' => 2200, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2500, 'B' => 2200, 'C' => 2100, 'D' => 2100, 'E' => 2000, 'F' => 2000, 'G' => 1900, 'H' => 1900, 'I' => 1900, 'J' => 2000, 'K' => 2400, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 62, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1300, 'B' => 1200, 'C' => 1100, 'D' => 1100, 'E' => 900, 'F' => 900, 'G' => 800, 'H' => 700, 'I' => 800, 'J' => 700, 'K' => 900, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1500, 'B' => 1400, 'C' => 1300, 'D' => 1300, 'E' => 1100, 'F' => 1100, 'G' => 1000, 'H' => 900, 'I' => 1000, 'J' => 900, 'K' => 1100, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1700, 'B' => 1600, 'C' => 1500, 'D' => 1500, 'E' => 1300, 'F' => 1300, 'G' => 1200, 'H' => 1100, 'I' => 1200, 'J' => 1100, 'K' => 1300, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1900, 'B' => 1800, 'C' => 1700, 'D' => 1700, 'E' => 1500, 'F' => 1500, 'G' => 1400, 'H' => 1300, 'I' => 1400, 'J' => 1300, 'K' => 1500, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 2100, 'B' => 2000, 'C' => 1900, 'D' => 1900, 'E' => 1700, 'F' => 1700, 'G' => 1600, 'H' => 1500, 'I' => 1600, 'J' => 1500, 'K' => 1700, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2300, 'B' => 2200, 'C' => 2100, 'D' => 2100, 'E' => 1900, 'F' => 1900, 'G' => 1800, 'H' => 1700, 'I' => 1800, 'J' => 1700, 'K' => 1900, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2500, 'B' => 2400, 'C' => 2300, 'D' => 2300, 'E' => 2100, 'F' => 2100, 'G' => 2000, 'H' => 1900, 'I' => 2000, 'J' => 1900, 'K' => 2100, ), ), )
        ),
        array('carrier_code' => 'jpost', 'service_code' => 'standard', 'zone_id' => 63, 'shipping_rates' => array ( 60 => array ( 'size' => '60', 'weight' => '30', 'rates' => array ( 'A' => 1300, 'B' => 1300, 'C' => 1200, 'D' => 1300, 'E' => 1300, 'F' => 1200, 'G' => 1200, 'H' => 1100, 'I' => 1200, 'J' => 900, 'K' => 600, ), ), 80 => array ( 'size' => '80', 'weight' => '30', 'rates' => array ( 'A' => 1500, 'B' => 1500, 'C' => 1400, 'D' => 1500, 'E' => 1500, 'F' => 1400, 'G' => 1400, 'H' => 1300, 'I' => 1400, 'J' => 1100, 'K' => 800, ), ), 100 => array ( 'size' => '100', 'weight' => '30', 'rates' => array ( 'A' => 1700, 'B' => 1700, 'C' => 1600, 'D' => 1700, 'E' => 1700, 'F' => 1600, 'G' => 1600, 'H' => 1500, 'I' => 1600, 'J' => 1300, 'K' => 1000, ), ), 120 => array ( 'size' => '120', 'weight' => '30', 'rates' => array ( 'A' => 1900, 'B' => 1900, 'C' => 1800, 'D' => 1900, 'E' => 1900, 'F' => 1800, 'G' => 1800, 'H' => 1700, 'I' => 1800, 'J' => 1500, 'K' => 1200, ), ), 140 => array ( 'size' => '140', 'weight' => '30', 'rates' => array ( 'A' => 2100, 'B' => 2100, 'C' => 2000, 'D' => 2100, 'E' => 2100, 'F' => 2000, 'G' => 2000, 'H' => 1900, 'I' => 2000, 'J' => 1700, 'K' => 1400, ), ), 160 => array ( 'size' => '160', 'weight' => '30', 'rates' => array ( 'A' => 2300, 'B' => 2300, 'C' => 2200, 'D' => 2300, 'E' => 2300, 'F' => 2200, 'G' => 2200, 'H' => 2100, 'I' => 2200, 'J' => 1900, 'K' => 1600, ), ), 170 => array ( 'size' => '170', 'weight' => '30', 'rates' => array ( 'A' => 2500, 'B' => 2500, 'C' => 2400, 'D' => 2500, 'E' => 2500, 'F' => 2400, 'G' => 2400, 'H' => 2300, 'I' => 2400, 'J' => 2100, 'K' => 1700, ), ), )
        ),
    );

}
##########################################################################################
// END アドオンのインストール・アンインストール時に動作する関数
##########################################################################################
