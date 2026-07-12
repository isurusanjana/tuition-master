USE `tuition_master`;

-- ------------------------------------------------------------
-- SYSTEM ROLES (global, tuition_center_id = NULL)
-- level: 0 = highest authority
-- ------------------------------------------------------------
INSERT INTO roles (tuition_center_id, name, slug, description, level, is_system) VALUES
(NULL, 'Super Admin',      'super_admin',   'Full system access across all tuition centers', 0, 1),
(NULL, 'Tuition Center Admin', 'center_admin', 'Owner/administrator of a tuition center', 1, 1),
(NULL, 'Admin Staff',      'admin_staff',   'General administrative staff', 2, 1),
(NULL, 'Teacher',          'teacher',       'Teaching staff', 3, 1),
(NULL, 'Student',          'student',       'Enrolled student', 4, 1),
(NULL, 'Parent',           'parent',        'Parent / guardian of a student', 4, 1);

-- ------------------------------------------------------------
-- PERMISSIONS CATALOGUE (module_key + action)
-- ------------------------------------------------------------
INSERT INTO permissions (module_key, action, label) VALUES
('dashboard','view','View Dashboard'),
('tuition_centers','view','View Tuition Centers'),('tuition_centers','add','Add Tuition Center'),('tuition_centers','edit','Edit Tuition Center'),('tuition_centers','delete','Delete Tuition Center'),
('users','view','View Users'),('users','add','Add User'),('users','edit','Edit User'),('users','delete','Delete User'),
('roles','view','View Roles'),('roles','add','Add Role'),('roles','edit','Edit Role'),('roles','delete','Delete Role'),('roles','assign_permission','Assign Permissions'),
('classes','view','View Classes'),('classes','add','Add Class'),('classes','edit','Edit Class'),('classes','delete','Delete Class'),('classes','assign','Assign Teachers/Students'),
('attendance','view','View Attendance'),('attendance','mark','Mark Attendance'),('attendance','edit','Edit Attendance'),('attendance','delete','Delete Attendance'),
('exams','view','View Exams'),('exams','add','Add Exam'),('exams','edit','Edit Exam'),('exams','delete','Delete Exam'),('exams','assign','Assign Exam'),
('marks','view','View Marks'),('marks','add','Add Marks'),('marks','edit','Edit Marks'),('marks','delete','Delete Marks'),
('notes','view','View Notes'),('notes','add','Add Note'),('notes','edit','Edit Note'),('notes','delete','Delete Note'),
('lessons','view','View Lessons'),('lessons','add','Add Lesson'),('lessons','edit','Edit Lesson'),('lessons','delete','Delete Lesson'),('lessons','assign','Assign Lesson'),
('reports','view','View Reports'),
('payroll','view','View Payroll'),('payroll','add','Add Payroll'),('payroll','edit','Edit Payroll'),('payroll','delete','Delete Payroll'),
('inventory','view','View Inventory'),('inventory','add','Add Inventory Item'),('inventory','edit','Edit Inventory Item'),('inventory','delete','Delete Inventory Item'),
('notifications','view','View Notifications'),('notifications','add','Add Notification'),('notifications','delete','Delete Notification'),
('theme','view','View Theme Settings'),('theme','edit','Edit Theme Settings'),
('help','view','View Help');

-- ------------------------------------------------------------
-- MENU ITEMS
-- ------------------------------------------------------------
INSERT INTO menu_items (id, parent_id, label, icon, route, module_key, sort_order) VALUES
(1, NULL, 'Dashboard', 'bi-speedometer2', 'dashboard.index', 'dashboard', 1),
(2, NULL, 'Tuition Centers', 'bi-building', 'tuition_centers.index', 'tuition_centers', 2),
(3, NULL, 'Users', 'bi-people', 'users.index', 'users', 3),
(4, NULL, 'Roles & Permissions', 'bi-shield-lock', 'roles.index', 'roles', 4),
(5, NULL, 'Classes', 'bi-easel', 'classes.index', 'classes', 5),
(6, NULL, 'Attendance', 'bi-calendar-check', 'attendance.index', 'attendance', 6),
(7, NULL, 'Exams', 'bi-pencil-square', 'exams.index', 'exams', 7),
(8, NULL, 'Marks', 'bi-award', 'marks.index', 'marks', 8),
(9, NULL, 'Special Notes', 'bi-sticky', 'notes.index', 'notes', 9),
(10, NULL, 'Lesson Tools', 'bi-journal-richtext', 'lessons.index', 'lessons', 10),
(11, NULL, 'Reports', 'bi-bar-chart', 'reports.index', 'reports', 11),
(12, NULL, 'Payroll', 'bi-cash-stack', 'payroll.index', 'payroll', 12),
(13, NULL, 'Inventory', 'bi-box-seam', 'inventory.index', 'inventory', 13),
(14, NULL, 'Notifications', 'bi-bell', 'notifications.index', 'notifications', 14),
(15, NULL, 'Theme Settings', 'bi-palette', 'theme.index', 'theme', 15),
(16, NULL, 'Help & Tutorials', 'bi-question-circle', 'help.index', 'help', 16);

-- ------------------------------------------------------------
-- Map ALL menus + permissions to super_admin (id 1) and center_admin (id 2)
-- Admin Staff (id 3) gets the same broad access as center_admin, minus tuition-center management,
-- since they are general administrative staff within a single center.
-- ------------------------------------------------------------
INSERT INTO role_menu (role_id, menu_item_id) SELECT 1, id FROM menu_items;
INSERT INTO role_menu (role_id, menu_item_id) SELECT 2, id FROM menu_items WHERE module_key <> 'tuition_centers';
INSERT INTO role_menu (role_id, menu_item_id) SELECT 3, id FROM menu_items WHERE module_key <> 'tuition_centers';
INSERT INTO role_permission (role_id, permission_id) SELECT 1, id FROM permissions;
INSERT INTO role_permission (role_id, permission_id) SELECT 2, id FROM permissions WHERE module_key <> 'tuition_centers';
INSERT INTO role_permission (role_id, permission_id) SELECT 3, id FROM permissions WHERE module_key <> 'tuition_centers';

-- Teacher (role id 4): dashboard, classes(view), attendance(mark/view), exams(view/add/edit),
-- marks(add/edit/view), notes, lessons, help
INSERT INTO role_menu (role_id, menu_item_id) SELECT 4, id FROM menu_items WHERE module_key IN
 ('dashboard','classes','attendance','exams','marks','notes','lessons','reports','help');
INSERT INTO role_permission (role_id, permission_id) SELECT 4, id FROM permissions WHERE
 (module_key='dashboard' AND action='view') OR
 (module_key='classes' AND action='view') OR
 (module_key='attendance' AND action IN ('view','mark','edit')) OR
 (module_key='exams' AND action IN ('view','add','edit')) OR
 (module_key='marks' AND action IN ('view','add','edit')) OR
 (module_key='notes' AND action IN ('view','add','edit')) OR
 (module_key='lessons' AND action IN ('view','add','edit','assign')) OR
 (module_key='reports' AND action='view') OR
 (module_key='help' AND action='view');

-- Student (role id 5): dashboard, exams(view), marks(view), lessons(view), notes(view), help
INSERT INTO role_menu (role_id, menu_item_id) SELECT 5, id FROM menu_items WHERE module_key IN
 ('dashboard','exams','marks','notes','lessons','reports','help');
INSERT INTO role_permission (role_id, permission_id) SELECT 5, id FROM permissions WHERE
 (module_key='dashboard' AND action='view') OR
 (module_key IN ('exams','marks','notes','lessons') AND action='view') OR
 (module_key='reports' AND action='view') OR
 (module_key='help' AND action='view');

-- Parent (role id 6): dashboard, reports(view of children), notes(view), attendance(view), marks(view), help
INSERT INTO role_menu (role_id, menu_item_id) SELECT 6, id FROM menu_items WHERE module_key IN
 ('dashboard','attendance','marks','notes','reports','help');
INSERT INTO role_permission (role_id, permission_id) SELECT 6, id FROM permissions WHERE
 (module_key='dashboard' AND action='view') OR
 (module_key IN ('attendance','marks','notes') AND action='view') OR
 (module_key='reports' AND action='view') OR
 (module_key='help' AND action='view');

-- ------------------------------------------------------------
-- DEFAULT SUPER ADMIN USER
-- Username: superadmin | Password: Admin@123  (hashed with password_hash, bcrypt)
-- ------------------------------------------------------------
INSERT INTO users (tuition_center_id, role_id, parent_user_id, first_name, last_name, email, phone, username, password, status)
VALUES (NULL, 1, NULL, 'Super', 'Admin', 'superadmin@tuitionmaster.test', '0000000000', 'superadmin',
'$2y$10$92Ix5gYQK5s1p1kFO4dY9OqVwqLQnE5U0nqzq0m8b0kzYQxG3G3S2', 'active');
-- NOTE: The hash above is a placeholder. Run `php database/make_admin.php` (see README)
-- OR simply reset the password after first login using "Forgot Password" flow.

-- ------------------------------------------------------------
-- HELP ARTICLES (sample)
-- ------------------------------------------------------------
INSERT INTO help_articles (module_key, title, content, sort_order) VALUES
('dashboard','Welcome to Tuition Master','This dashboard gives you a quick overview of your tuition center: total students, teachers, classes, attendance and recent notifications. Use the sidebar to navigate between modules.',1),
('users','Managing Users','Super Admin can create Tuition Centers with a Center Admin. Center Admins can then add Admin Staff, Teachers, Students and Parents. Every user you create is linked under you, so you can only manage users under your own hierarchy.',1),
('roles','Roles & Permissions','Each role has a default set of menu items and CRUD permissions. You can further fine-tune access for an individual user from Users > Permissions, overriding the role defaults.',1),
('classes','Classes','Create a class, then assign teachers and students to it from the class detail page. Only assigned teachers can mark attendance or add exams/marks for that class.',1),
('attendance','Marking Attendance','Select a class and date to mark attendance for every enrolled student. You can also mark staff attendance from the Staff Attendance tab.',1),
('exams','Exams & Marks','Create an exam for a class, optionally assign it only to specific students, then record marks per student once the exam is completed.',1),
('lessons','Lesson Tools','Upload documents, PDFs or link videos as lessons, then assign them to specific students or an entire class.',1),
('payroll','Payroll','Generate a monthly payroll entry per staff member with basic salary, allowances and deductions. Mark as Paid once processed.',1),
('inventory','Inventory','Track stock items, quantities and reorder levels. Use Stock In / Stock Out to record transactions.',1),
('theme','Theme Settings','Customize your tuition center colors, logo and choose between a vertical or horizontal menu layout.',1);
