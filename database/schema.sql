-- ============================================================
-- Tuition Master - SAAS Database Schema
-- Engine: InnoDB | Charset: utf8mb4
-- ============================================================
SET NAMES utf8mb4;
SET FOREIGN_KEY_CHECKS = 0;

CREATE DATABASE IF NOT EXISTS `tuition_master` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `tuition_master`;

-- ------------------------------------------------------------
-- 1. TUITION CENTERS (Tenants)
-- ------------------------------------------------------------
CREATE TABLE tuition_centers (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    name VARCHAR(150) NOT NULL,
    code VARCHAR(20) NOT NULL UNIQUE,          -- short unique code, used in URLs/reports
    email VARCHAR(150),
    phone VARCHAR(30),
    address TEXT,
    logo VARCHAR(255) DEFAULT NULL,
    status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    created_by INT UNSIGNED NULL,              -- super admin who created it
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 2. ROLES  (system-level + tenant-level custom roles)
-- ------------------------------------------------------------
CREATE TABLE roles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NULL,       -- NULL = global/system role
    name VARCHAR(100) NOT NULL,
    slug VARCHAR(100) NOT NULL,                -- super_admin, center_admin, teacher, student, parent, staff...
    description VARCHAR(255),
    level INT NOT NULL DEFAULT 100,            -- lower number = higher authority (0 = super admin)
    is_system TINYINT(1) NOT NULL DEFAULT 0,   -- system roles cannot be deleted
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_role_center (tuition_center_id, slug),
    CONSTRAINT fk_roles_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 3. USERS  (all user types: super admin, center admin, admin staff, teacher, student, parent)
-- ------------------------------------------------------------
CREATE TABLE users (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NULL,       -- NULL only for super admin
    role_id INT UNSIGNED NOT NULL,
    parent_user_id INT UNSIGNED NULL,          -- the user who created / owns this user (hierarchy)
    first_name VARCHAR(80) NOT NULL,
    last_name VARCHAR(80),
    email VARCHAR(150) NOT NULL UNIQUE,
    phone VARCHAR(30),
    username VARCHAR(60) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    photo VARCHAR(255) DEFAULT NULL,
    gender ENUM('male','female','other') DEFAULT NULL,
    dob DATE DEFAULT NULL,
    address TEXT,
    status ENUM('active','inactive','suspended') NOT NULL DEFAULT 'active',
    last_login DATETIME NULL,
    remember_token VARCHAR(255) NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_users_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE,
    CONSTRAINT fk_users_role FOREIGN KEY (role_id) REFERENCES roles(id),
    CONSTRAINT fk_users_parent FOREIGN KEY (parent_user_id) REFERENCES users(id) ON DELETE SET NULL,
    INDEX idx_users_center (tuition_center_id),
    INDEX idx_users_parent (parent_user_id)
) ENGINE=InnoDB;

-- Extra profile info specific to students
CREATE TABLE student_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    admission_no VARCHAR(50),
    grade VARCHAR(30),
    guardian_name VARCHAR(120),
    guardian_phone VARCHAR(30),
    CONSTRAINT fk_sp_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Link students <-> parents (many-to-many, a parent can have many children)
CREATE TABLE student_parent (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    student_id INT UNSIGNED NOT NULL,
    parent_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uniq_sp (student_id, parent_id),
    CONSTRAINT fk_sp_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_sp_parent FOREIGN KEY (parent_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- Employment info for staff/teachers (used by payroll)
CREATE TABLE staff_profiles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL UNIQUE,
    employee_no VARCHAR(50),
    designation VARCHAR(100),
    basic_salary DECIMAL(12,2) DEFAULT 0,
    joined_date DATE,
    CONSTRAINT fk_staff_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 4. PERMISSIONS  (menu access + CRUD action permissions)
-- ------------------------------------------------------------
CREATE TABLE menu_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    parent_id INT UNSIGNED NULL,
    label VARCHAR(100) NOT NULL,
    icon VARCHAR(60) DEFAULT 'bi-circle',
    route VARCHAR(150) NOT NULL,               -- e.g. students.index
    module_key VARCHAR(60) NOT NULL,           -- e.g. students (used for CRUD permission lookup)
    sort_order INT DEFAULT 0,
    is_active TINYINT(1) DEFAULT 1,
    CONSTRAINT fk_menu_parent FOREIGN KEY (parent_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- permissions catalogue: one row per module+action (view/add/edit/delete/export...)
CREATE TABLE permissions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(60) NOT NULL,
    action VARCHAR(30) NOT NULL,               -- view, add, edit, delete, export, mark_attendance...
    label VARCHAR(120) NOT NULL,
    UNIQUE KEY uniq_perm (module_key, action)
) ENGINE=InnoDB;

-- role <-> menu visibility
CREATE TABLE role_menu (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    menu_item_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uniq_role_menu (role_id, menu_item_id),
    CONSTRAINT fk_rm_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rm_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- role <-> permission (default permission set per role)
CREATE TABLE role_permission (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    role_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uniq_role_perm (role_id, permission_id),
    CONSTRAINT fk_rp_role FOREIGN KEY (role_id) REFERENCES roles(id) ON DELETE CASCADE,
    CONSTRAINT fk_rp_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- USER-LEVEL overrides, set individually "user by user" by the admin (overrides role defaults)
CREATE TABLE user_menu_permission (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    menu_item_id INT UNSIGNED NOT NULL,
    allowed TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_user_menu (user_id, menu_item_id),
    CONSTRAINT fk_ump_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_ump_menu FOREIGN KEY (menu_item_id) REFERENCES menu_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE user_permission (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    user_id INT UNSIGNED NOT NULL,
    permission_id INT UNSIGNED NOT NULL,
    allowed TINYINT(1) NOT NULL DEFAULT 1,
    UNIQUE KEY uniq_user_perm (user_id, permission_id),
    CONSTRAINT fk_up_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_up_perm FOREIGN KEY (permission_id) REFERENCES permissions(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 5. CLASSES / SUBJECTS
-- ------------------------------------------------------------
CREATE TABLE classes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    name VARCHAR(120) NOT NULL,
    grade VARCHAR(30),
    subject VARCHAR(80),
    description TEXT,
    schedule VARCHAR(150),                    -- e.g. "Mon,Wed 4-6PM"
    capacity INT DEFAULT 0,
    status ENUM('active','inactive') DEFAULT 'active',
    created_by INT UNSIGNED,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_classes_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- teachers assigned to classes
CREATE TABLE class_teacher (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id INT UNSIGNED NOT NULL,
    teacher_id INT UNSIGNED NOT NULL,
    UNIQUE KEY uniq_class_teacher (class_id, teacher_id),
    CONSTRAINT fk_ct_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_ct_teacher FOREIGN KEY (teacher_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- students enrolled in classes
CREATE TABLE class_student (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    class_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    enrolled_at DATE DEFAULT NULL,
    UNIQUE KEY uniq_class_student (class_id, student_id),
    CONSTRAINT fk_cs_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_cs_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 6. ATTENDANCE
-- ------------------------------------------------------------
CREATE TABLE attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    marked_by INT UNSIGNED NOT NULL,           -- teacher/admin who marked it
    attendance_date DATE NOT NULL,
    status ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
    remarks VARCHAR(255),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_attendance (class_id, student_id, attendance_date),
    CONSTRAINT fk_att_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE,
    CONSTRAINT fk_att_marker FOREIGN KEY (marked_by) REFERENCES users(id)
) ENGINE=InnoDB;

-- teacher's own attendance (staff attendance)
CREATE TABLE staff_attendance (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED NOT NULL,
    marked_by INT UNSIGNED NOT NULL,
    attendance_date DATE NOT NULL,
    status ENUM('present','absent','late','excused') NOT NULL DEFAULT 'present',
    remarks VARCHAR(255),
    UNIQUE KEY uniq_staff_att (staff_id, attendance_date),
    CONSTRAINT fk_satt_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE,
    CONSTRAINT fk_satt_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 7. EXAMS / MARKS
-- ------------------------------------------------------------
CREATE TABLE exams (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NOT NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    exam_date DATE,
    total_marks DECIMAL(6,2) DEFAULT 100,
    pass_marks DECIMAL(6,2) DEFAULT 40,
    created_by INT UNSIGNED NOT NULL,
    status ENUM('draft','published','completed') DEFAULT 'draft',
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_exam_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE,
    CONSTRAINT fk_exam_class FOREIGN KEY (class_id) REFERENCES classes(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- exam assigned to specific students (subset of class, "assign exams to users under logged user")
CREATE TABLE exam_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_exam_student (exam_id, student_id),
    CONSTRAINT fk_ea_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_ea_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE marks (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    exam_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NOT NULL,
    marks_obtained DECIMAL(6,2) NOT NULL DEFAULT 0,
    grade VARCHAR(5),
    remarks VARCHAR(255),
    recorded_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_exam_student_mark (exam_id, student_id),
    CONSTRAINT fk_marks_exam FOREIGN KEY (exam_id) REFERENCES exams(id) ON DELETE CASCADE,
    CONSTRAINT fk_marks_student FOREIGN KEY (student_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 8. SPECIAL NOTES
-- ------------------------------------------------------------
CREATE TABLE notes (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    student_id INT UNSIGNED NULL,             -- optional: note about a particular student
    class_id INT UNSIGNED NULL,
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    note_type ENUM('general','behavioral','academic','medical') DEFAULT 'general',
    visibility ENUM('private','staff','parents') DEFAULT 'staff',
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_notes_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 9. LESSON TOOLS (documents / pdf / video)
-- ------------------------------------------------------------
CREATE TABLE lessons (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    class_id INT UNSIGNED NULL,
    title VARCHAR(150) NOT NULL,
    description TEXT,
    resource_type ENUM('document','pdf','video','link') NOT NULL DEFAULT 'document',
    file_path VARCHAR(255) DEFAULT NULL,
    external_url VARCHAR(255) DEFAULT NULL,
    uploaded_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_lesson_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- lessons assigned to specific users (students under the logged-in user's hierarchy)
CREATE TABLE lesson_assignments (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    lesson_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    assigned_by INT UNSIGNED NOT NULL,
    assigned_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    is_viewed TINYINT(1) DEFAULT 0,
    UNIQUE KEY uniq_lesson_user (lesson_id, user_id),
    CONSTRAINT fk_la_lesson FOREIGN KEY (lesson_id) REFERENCES lessons(id) ON DELETE CASCADE,
    CONSTRAINT fk_la_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 10. PAYROLL
-- ------------------------------------------------------------
CREATE TABLE payroll (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    staff_id INT UNSIGNED NOT NULL,
    pay_period_month TINYINT NOT NULL,
    pay_period_year SMALLINT NOT NULL,
    basic_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    allowances DECIMAL(12,2) NOT NULL DEFAULT 0,
    deductions DECIMAL(12,2) NOT NULL DEFAULT 0,
    net_salary DECIMAL(12,2) NOT NULL DEFAULT 0,
    status ENUM('pending','paid','cancelled') DEFAULT 'pending',
    paid_at DATETIME NULL,
    remarks VARCHAR(255),
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_payroll_period (staff_id, pay_period_month, pay_period_year),
    CONSTRAINT fk_payroll_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE,
    CONSTRAINT fk_payroll_staff FOREIGN KEY (staff_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 11. INVENTORY
-- ------------------------------------------------------------
CREATE TABLE inventory_items (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NOT NULL,
    name VARCHAR(150) NOT NULL,
    category VARCHAR(80),
    sku VARCHAR(60),
    quantity INT NOT NULL DEFAULT 0,
    unit VARCHAR(30) DEFAULT 'pcs',
    unit_price DECIMAL(10,2) DEFAULT 0,
    reorder_level INT DEFAULT 0,
    location VARCHAR(120),
    status ENUM('active','inactive') DEFAULT 'active',
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    updated_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
    CONSTRAINT fk_inv_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE inventory_transactions (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    item_id INT UNSIGNED NOT NULL,
    type ENUM('in','out','adjustment') NOT NULL,
    quantity INT NOT NULL,
    note VARCHAR(255),
    created_by INT UNSIGNED NOT NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_it_item FOREIGN KEY (item_id) REFERENCES inventory_items(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 12. NOTIFICATIONS / SYSTEM MESSAGES
-- ------------------------------------------------------------
CREATE TABLE notifications (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NULL,      -- NULL = system-wide (super admin broadcast)
    title VARCHAR(150) NOT NULL,
    message TEXT NOT NULL,
    type ENUM('info','warning','success','danger') DEFAULT 'info',
    target_role_id INT UNSIGNED NULL,         -- NULL = all users of the center
    created_by INT UNSIGNED NOT NULL,
    starts_at DATETIME NULL,
    ends_at DATETIME NULL,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    CONSTRAINT fk_notif_center FOREIGN KEY (tuition_center_id) REFERENCES tuition_centers(id) ON DELETE CASCADE
) ENGINE=InnoDB;

CREATE TABLE notification_reads (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    notification_id INT UNSIGNED NOT NULL,
    user_id INT UNSIGNED NOT NULL,
    read_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP,
    UNIQUE KEY uniq_notif_user (notification_id, user_id),
    CONSTRAINT fk_nr_notif FOREIGN KEY (notification_id) REFERENCES notifications(id) ON DELETE CASCADE,
    CONSTRAINT fk_nr_user FOREIGN KEY (user_id) REFERENCES users(id) ON DELETE CASCADE
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 13. SYSTEM / THEME SETTINGS (per tuition center)
-- ------------------------------------------------------------
CREATE TABLE settings (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NULL,      -- NULL = global default (super admin)
    setting_key VARCHAR(100) NOT NULL,
    setting_value TEXT,
    UNIQUE KEY uniq_setting (tuition_center_id, setting_key)
) ENGINE=InnoDB;
-- typical keys: theme_primary_color, theme_secondary_color, theme_header_color,
-- theme_sidebar_color, theme_footer_text, menu_orientation (vertical|horizontal),
-- site_logo, site_name

-- ------------------------------------------------------------
-- 14. HELP TIPS / TUTORIALS
-- ------------------------------------------------------------
CREATE TABLE help_articles (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    module_key VARCHAR(60) NOT NULL,          -- which page/module this help belongs to
    title VARCHAR(150) NOT NULL,
    content TEXT NOT NULL,
    video_url VARCHAR(255) DEFAULT NULL,
    role_visibility VARCHAR(255) DEFAULT NULL, -- comma separated role slugs, NULL = all
    sort_order INT DEFAULT 0,
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

-- ------------------------------------------------------------
-- 15. ACTIVITY / AUDIT LOG
-- ------------------------------------------------------------
CREATE TABLE activity_logs (
    id INT UNSIGNED AUTO_INCREMENT PRIMARY KEY,
    tuition_center_id INT UNSIGNED NULL,
    user_id INT UNSIGNED NULL,
    action VARCHAR(150) NOT NULL,
    module_key VARCHAR(60) NULL,
    description VARCHAR(255),
    ip_address VARCHAR(45),
    created_at DATETIME NOT NULL DEFAULT CURRENT_TIMESTAMP
) ENGINE=InnoDB;

SET FOREIGN_KEY_CHECKS = 1;
