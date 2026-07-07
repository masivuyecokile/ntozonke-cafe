USE ntozonke_cafe;

INSERT INTO users (name, username, password, role)
VALUES (
    'Super Admin',
    'admin',
    '$2y$10$8ydkQ0iLA47QrgoaDcJ7S.bOQqj6YeNDtZAm.Eu3t2JutlKqPgBVG',
    'super_admin'
);

INSERT INTO pcs (pc_name, status) VALUES
('PC 1', 'locked'),
('PC 2', 'locked'),
('PC 3', 'locked');

INSERT INTO settings (setting_key, setting_value) VALUES
('internet_rate_per_minute', '0.50'),
('print_bw_rate', '0.50'),
('print_colour_rate', '2.00'),
('business_name', 'Ntozonke Internet Cafe'),
('system_name', 'Internet Cafe Management');