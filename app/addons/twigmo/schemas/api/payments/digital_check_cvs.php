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
	// コンビ二名
	array (
		'option_id' => "jp_digital_check_cvs_name",
		'name' => 'jp_digital_check_cvs_cnvkind',
		'description' => __('jp_digital_check_cvs_select_cvs'),
		'value' => '',
		'option_type' =>  'S',
		'position' => 10,
		'option_variants' => fn_dgtlchck_tw_get_cvs_list(),
        'required' => true,
	),
);
/////////////////////////////////////////////////////////////////////////////////////
// 必ず表示する項目 EOF
/////////////////////////////////////////////////////////////////////////////////////
return $schema;
