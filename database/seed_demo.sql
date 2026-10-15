-- Comic Book World: demo/reference seed data
-- Run this ONLY after database.sql, on a fresh development database.
-- Contains public demo catalog data and role definitions only.
-- It intentionally contains no customer accounts, admin credentials, orders, or contact messages.
USE `comic_book_world`;
SET NAMES utf8mb4;


-- Built-in role definitions. All five are system roles.
INSERT IGNORE INTO admin_roles (id, name, description, is_system) VALUES
(1, 'Super Admin', 'Full administrative access to the entire system.', 1),
(2, 'Store Manager', 'Manages normal store operations without security administration.', 1),
(3, 'Order Staff', 'Manages customer orders and order status.', 1),
(4, 'Inventory Staff', 'Manages products and inventory.', 1),
(5, 'Secondary Admin', 'Backup administrator with broad operational access and restricted security/role-management privileges.', 1);

-- Permission catalog.
INSERT IGNORE INTO admin_permissions (permission_key, description) VALUES
('dashboard.view', 'View the admin dashboard'),
('analytics.view', 'View store analytics'),
('reports.view', 'View and generate reports'),
('orders.view', 'View customer orders'),
('orders.update', 'Update order status'),
('products.view', 'View products'),
('products.create', 'Create products'),
('products.update', 'Edit products'),
('products.delete', 'Delete products'),
('customers.view', 'View customers'),
('customers.block', 'Block or unblock customers'),
('promotions.view', 'View promotions'),
('promotions.create', 'Create promotions'),
('promotions.update', 'Edit promotions'),
('promotions.delete', 'Delete promotions'),
('faq.view', 'View FAQs'),
('faq.create', 'Create FAQs'),
('faq.update', 'Edit FAQs'),
('faq.delete', 'Delete FAQs'),
('admins.view', 'View administrators'),
('admins.create', 'Create administrators'),
('admins.update', 'Edit administrators'),
('admins.suspend', 'Suspend administrators'),
('roles.view', 'View roles and permissions'),
('roles.manage', 'Manage roles and permissions'),
('activity.view', 'View administrative activity'),
('settings.view', 'View system settings'),
('settings.manage', 'Manage system settings'),
('system.freeze', 'Freeze administrative changes'),
('system.emergency_access', 'Grant emergency administrative access');

-- Super Admin receives every defined permission.
INSERT IGNORE INTO admin_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM admin_roles r CROSS JOIN admin_permissions p
WHERE r.name = 'Super Admin';


-- Store Manager permissions.
INSERT IGNORE INTO admin_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM admin_roles r CROSS JOIN admin_permissions p
WHERE r.name = 'Store Manager' AND p.permission_key IN ('dashboard.view', 'analytics.view', 'reports.view', 'orders.view', 'orders.update', 'products.view', 'products.create', 'products.update', 'customers.view', 'promotions.view', 'promotions.create', 'promotions.update', 'faq.view', 'faq.create', 'faq.update', 'activity.view');


-- Order Staff permissions.
INSERT IGNORE INTO admin_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM admin_roles r CROSS JOIN admin_permissions p
WHERE r.name = 'Order Staff' AND p.permission_key IN ('dashboard.view', 'orders.view', 'orders.update', 'customers.view');


-- Inventory Staff permissions.
INSERT IGNORE INTO admin_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM admin_roles r CROSS JOIN admin_permissions p
WHERE r.name = 'Inventory Staff' AND p.permission_key IN ('dashboard.view', 'products.view', 'products.create', 'products.update', 'customers.view');


-- Secondary Admin permissions.
INSERT IGNORE INTO admin_role_permissions (role_id, permission_id)
SELECT r.id, p.id FROM admin_roles r CROSS JOIN admin_permissions p
WHERE r.name = 'Secondary Admin' AND p.permission_key IN ('dashboard.view', 'analytics.view', 'reports.view', 'orders.view', 'orders.update', 'products.view', 'products.create', 'products.update', 'products.delete', 'customers.view', 'customers.block', 'promotions.view', 'promotions.create', 'promotions.update', 'promotions.delete', 'faq.view', 'faq.create', 'faq.update', 'faq.delete', 'activity.view', 'settings.view');


-- Default global administrative control state (does not overwrite an existing row).
INSERT IGNORE INTO admin_system_controls (id, administrative_freeze)
VALUES (1, 0);


-- Starter FAQ content.
INSERT IGNORE INTO faq (id, question, answer) VALUES
(1, 'Do you sell Marvel comics?', 'Yes, we stock a range of Marvel comics.'),
(2, 'Do you sell DC comics?', 'Yes, our catalog includes DC titles such as Batman and Superman.'),
(3, 'Do you deliver across Sri Lanka?', 'Delivery options are shown during checkout.'),
(4, 'Can I register an account?', 'Yes. Use the registration page to create a customer account.'),
(5, 'How can I contact Comic Book World?', 'Use the Contact Us page to send a message.');

-- Starter product catalog. Image paths refer to files in Product/.
INSERT IGNORE INTO products (id, title, category, description, price, image, stock) VALUES
(1, 'Avengers By Jason Aaron Vol 2', 'Marvel', 'Popular Avengers comic', 3500.00, 'comic1.jpg', 50),
(2, 'Avengers Then to Now', 'Marvel', 'Classic Avengers story', 4200.00, 'comic2.jpg', 10),
(3, 'Ultimate Comic Avengers Vol1', 'Marvel', 'Ultimate universe comic', 4500.00, 'comic3.jpg', 10),
(4, 'Batman Year One', 'DC', 'Batman origin story', 3900.00, 'comic4.jpg', 10),
(5, 'X-Men Epic Collection', 'Marvel', 'X-Men collection', 5000.00, 'comic5.jpg', 10),
(6, 'Asterix in Lusitania', 'Graphic Novel', 'Asterix in Lusitania is the 41st installment in the beloved Asterix series', 3199.00, 'comic13.jpg', 10),
(7, 'Diary of a Wimpy Kid: The Last Straw', 'Graphic Novel', 'The third book in the Diary of a Wimpy Kid series.', 1560.00, 'comic14.jpg', 10),
(8, 'Spy X Family: Volume 11', 'Manga', 'The action-packed comedy about a fake family.', 2000.00, 'comic15.jpg', 10),
(9, 'Jujutsu Kaisen: The Official Anime Guide', 'Manga', 'The official companion to the first season of Jujutsu Kaisen.', 7000.00, 'comic16.jpg', 10),
(10, 'Demon Slayer Academy Volume 4', 'Manga', 'The Demon Slayer Corps returns for another challenge.', 4000.00, 'comic17.jpg', 10),
(11, 'Dragon Ball Full Color: Freeza Arc Volume 2', 'Manga', 'Goku and his friends journey to Namek in this full-color edition.', 9999.00, 'comic18.jpg', 10),
(12, 'Yu-Gi-Oh! 3-in-1 Volume 8', 'Graphic Novel', 'Collects volumes from the original Yu-Gi-Oh! manga series.', 6000.00, 'comic19.jpg', 10),
(13, 'One Piece 3-in-1', 'Manga', 'Join Monkey D. Luffy and his crew in search of the One Piece.', 7590.00, 'comic20.jpg', 10);

-- Demo promotional codes (not connected to a payment processor).
INSERT IGNORE INTO promotions (id, code, discount_percent, minimum_order, maximum_discount, starts_at, expires_at, usage_limit, used_count, active) VALUES
(1, 'WELCOME10', 10.00, 5000.00, 2500.00, NULL, '2030-12-31 23:59:59', 100, 0, 1),
(2, 'COMIC5', 5.00, 2500.00, 1000.00, NULL, '2030-12-31 23:59:59', 50, 0, 1);

SELECT 'Demo seed data loaded.' AS setup_status;
