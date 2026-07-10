-- ============================================================
-- Portfolio OS — Seed Data
-- Run AFTER schema.sql
-- ============================================================

USE portfolio_db;

-- ---- Default Admin User ----
-- Password: Admin@1234  (CHANGE IMMEDIATELY AFTER FIRST LOGIN)
-- bcrypt hash of "Admin@1234" with cost 12
INSERT IGNORE INTO users (email, password_hash, role) VALUES
('admin@portfolio.local',
 '$2y$10$1Ec/LSgBcDzyqAHA6N2.oePKuLync9iRsBjPyScpIedKlNmLew6Wi',
 'owner');

-- ---- Hero Content ----
INSERT IGNORE INTO hero_content (id, full_name, title, taglines, cta_primary, cta_secondary) VALUES
(1,
 'Mudassir',
 'Software Engineering Student & Developer',
 '["Game Developer", "Web Developer", "Trading Systems Builder", "Co-founder @ Hexspire Solutions", "Flutter & Mobile Developer"]',
 'View Projects',
 'Contact Me');

-- ---- About Content ----
INSERT IGNORE INTO about_content (id, bio, education_institution, education_degree, education_years, timeline_items) VALUES
(1,
 'I am Mudassir, a passionate Software Engineering student at CUSIT Peshawar and co-founder of Hexspire Solutions. I love building things — from immersive Unity games to AI-powered trading systems and modern web applications. My work lives at the intersection of creativity and technology.',
 'CUSIT Peshawar',
 'BS Software Engineering',
 '2022 – Present',
 '[{"year":"2022","event":"Started BS Software Engineering","description":"Enrolled at CUSIT Peshawar"},{"year":"2023","event":"Co-founded Hexspire Solutions","description":"Started freelancing & building client projects"},{"year":"2024","event":"Built AlphaMind AI","description":"AI-powered trading analysis platform"},{"year":"2025","event":"Expanding Portfolio","description":"Game dev, mobile apps, and web projects"}]');

-- ---- Default Skills ----
INSERT IGNORE INTO skills (name, category, proficiency, icon, sort_order) VALUES
('Unity / C#',       'Game Development', 85, 'devicon-unity-original',       1),
('React / TypeScript','Web Frontend',     80, 'devicon-react-original',       2),
('PHP / MySQL',      'Web Backend',       80, 'devicon-php-plain',            3),
('Python',           'Programming',       75, 'devicon-python-plain',         4),
('Flutter / Dart',   'Mobile',           70, 'devicon-flutter-plain',         5),
('LangGraph / AI',   'AI & ML',          70, '🤖',                            6),
('JavaScript',       'Web Frontend',     80, 'devicon-javascript-plain',      7),
('Node.js',          'Web Backend',       65, 'devicon-nodejs-plain',         8),
('Git / GitHub',     'Tools',            85, 'devicon-github-original',       9),
('Kotlin / Android', 'Mobile',           60, 'devicon-kotlin-plain',          10);

-- ---- Default Languages ----
INSERT IGNORE INTO languages (name, lang_type, proficiency_level, sort_order) VALUES
('Urdu',    'spoken',       'Native',         1),
('English', 'spoken',       'Advanced',       2),
('Pashto',  'spoken',       'Native',         3),
('C#',      'programming',  'Advanced',       4),
('JavaScript','programming','Advanced',       5),
('PHP',     'programming',  'Advanced',       6),
('Python',  'programming',  'Intermediate',   7),
('Dart',    'programming',  'Intermediate',   8),
('Kotlin',  'programming',  'Elementary',     9);
