INSERT INTO settings (setting_key, setting_value) VALUES
('cafe_name', 'کافه آرک'),
('cafe_name_en', 'Coffee Arc'),
('cafe_phone', ''),
('cafe_address', ''),
('currency_label', 'تومان'),
('online_ordering', '1'),
('online_payment', '1'),
('pay_at_cafe', '1'),
('table_required', '1'),
('qr_per_table', '1'),
('item_customization', '1'),
('order_notes', '1'),
('customer_phone', '1'),
('customer_phone_required', '0'),
('discounts', '0'),
('staff_pos', '1'),
('table_board', '1'),
('simple_stats', '1'),
('zarinpal_merchant', 'TEST'),
('zarinpal_sandbox', '1'),
('default_lang', 'fa'),
('system_lang', 'fa'),
('installed', '0');

INSERT INTO cafe_tables (table_number, seats, status, qr_token) VALUES
('01', 2, 'free', 'tbl01token'),
('02', 4, 'free', 'tbl02token'),
('03', 4, 'free', 'tbl03token'),
('04', 2, 'free', 'tbl04token'),
('05', 6, 'free', 'tbl05token'),
('06', 4, 'free', 'tbl06token'),
('07', 2, 'free', 'tbl07token'),
('08', 4, 'free', 'tbl08token');

INSERT INTO discounts (code, type, value, is_active) VALUES
('WELCOME10', 'percent', 10, 1),
('FLAT20', 'fixed', 20000, 1);
