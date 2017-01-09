INSERT INTO ?:payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9230, 'クロネコwebコレクト（クレジットカード払い）', 'krnkwc_cc.php', 'addons/kuroneko_webcollect/views/orders/components/payments/krnkwc_cc.tpl', 'krnkwc_cc.tpl', 'N', 'P');

INSERT INTO ?:payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9231, 'クロネコwebコレクト（登録済みクレジットカード払い）', 'krnkwc_ccreg.php', 'addons/kuroneko_webcollect/views/orders/components/payments/krnkwc_ccreg.tpl', 'krnkwc_ccreg.tpl', 'N', 'P');

INSERT INTO ?:payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9232, 'クロネコwebコレクト（コンビニ（オンライン）払い）', 'krnkwc_cvs.php', 'addons/kuroneko_webcollect/views/orders/components/payments/krnkwc_cvs.tpl', 'krnkwc_cvs.tpl', 'N', 'P');

INSERT INTO ?:payment_processors (processor_id, processor, processor_script, processor_template, admin_template, callback, type) VALUES (9240, 'クロネコ代金後払いサービス', 'krnkab.php', 'addons/kuroneko_webcollect/views/orders/components/payments/krnkab.tpl', 'krnkab.tpl', 'N', 'P');

CREATE TABLE ?:jp_krnkwc_cc_status (order_id mediumint(8) unsigned NOT NULL, status_code varchar(32) NOT NULL default '', order_no mediumtext NOT NULL, PRIMARY KEY (`order_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;

CREATE TABLE ?:jp_krnkwc_shipments (shipment_id mediumint(8) unsigned NOT NULL, tracking_url mediumtext NOT NULL, PRIMARY KEY (`shipment_id`)) ENGINE=MyISAM DEFAULT CHARSET=utf8;
