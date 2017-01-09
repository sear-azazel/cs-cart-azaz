INSERT INTO cscart_payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9200, 'PGマルチペイメントサービス（プロトコルタイプ・カード決済）', 'gmo_multipayment_cc.php', 'addons/gmo_multipayment/views/orders/components/payments/gmo_multipayment_cc.tpl', 'gmo_multipayment_cc.tpl', 'N', 'P');

INSERT INTO cscart_payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9201, 'PGマルチペイメントサービス（プロトコルタイプ・登録済みカード決済）', 'gmo_multipayment_ccreg.php', 'addons/gmo_multipayment/views/orders/components/payments/gmo_multipayment_ccreg.tpl', 'gmo_multipayment_ccreg.tpl', 'N', 'P');

INSERT INTO cscart_payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9202, 'PGマルチペイメントサービス（プロトコルタイプ・コンビニ決済）', 'gmo_multipayment_cvs.php', 'addons/gmo_multipayment/views/orders/components/payments/gmo_multipayment_cvs.tpl', 'gmo_multipayment_cvs.tpl', 'N', 'P');

INSERT INTO cscart_payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9203, 'PGマルチペイメントサービス（プロトコルタイプ・ペイジー決済）', 'gmo_multipayment_payeasy.php', 'views/orders/components/payments/cc_outside.tpl', 'gmo_multipayment_payeasy.tpl', 'N', 'P');

CREATE TABLE cscart_jp_gmomp_cc_status (order_id mediumint(8) unsigned NOT NULL, status_code varchar(32) NOT NULL default '', access_id varchar(32) NOT NULL default '', access_pass varchar(32) NOT NULL default '', PRIMARY KEY (`order_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;