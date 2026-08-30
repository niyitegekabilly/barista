<?php

namespace Database\Seeders;

use App\Core\Database;

class DatabaseSeeder {
    private static ?Database $db = null;

    private static function db(): Database {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function run(): void {
        echo "Starting Beyond Barista Academy Database Seeder...\n";

        // 1. Roles
        $roles = [
            ['id' => 1, 'name' => 'Super Administrator', 'slug' => 'super_admin', 'description' => 'Full system access and configurations'],
            ['id' => 2, 'name' => 'Student', 'slug' => 'student', 'description' => 'Enrolls in courses, takes quizzes, earns certificates'],
            ['id' => 3, 'name' => 'Instructor', 'slug' => 'instructor', 'description' => 'Creates and manages courses, lessons, and quizzes'],
            ['id' => 4, 'name' => 'Academy Administrator', 'slug' => 'admin', 'description' => 'Manages academy operations, users, and content'],
            ['id' => 5, 'name' => 'Moderator', 'slug' => 'moderator', 'description' => 'Moderates discussions, reviews, and community content']
        ];

        foreach ($roles as $r) {
            Database::staticQuery("INSERT INTO roles (id, name, slug, description) VALUES (:id, :name, :slug, :description) ON DUPLICATE KEY UPDATE name = VALUES(name), description = VALUES(description)", $r);
        }
        echo "✓ Roles seeded.\n";

        // 2. Users & Profiles
        $users = [
            [
                'role_id' => 1,
                'name' => 'Beyond Barista Super Admin',
                'email' => 'admin@beyondbarista.rw',
                'password' => password_hash('Admin@2026', PASSWORD_DEFAULT),
                'headline' => 'Director of Learning Technologies',
                'phone' => '+250 788 123 456',
                'bio' => 'Overseeing academic excellence and digital learning standards across Rwanda.'
            ],
            [
                'role_id' => 3,
                'name' => 'Jean-Luc Mugisha',
                'email' => 'instructor@beyondbarista.rw',
                'password' => password_hash('Instructor@2026', PASSWORD_DEFAULT),
                'headline' => 'Head Barista Trainer & SCA Certified Roaster',
                'phone' => '+250 788 654 321',
                'bio' => 'Over 10 years of specialty coffee brewing, sensory judging, and training professional hospitality teams in East Africa.'
            ],
            [
                'role_id' => 2,
                'name' => 'Aline Uwase',
                'email' => 'student@beyondbarista.rw',
                'password' => password_hash('Student@2026', PASSWORD_DEFAULT),
                'headline' => 'Aspiring Specialty Cafe Manager',
                'phone' => '+250 789 987 654',
                'bio' => 'Enthusiastic hospitality student passionate about coffee craftsmanship and beverage service.'
            ]
        ];

        foreach ($users as $u) {
            $existing = self::db()->fetchOne("SELECT id FROM users WHERE email = :email", ['email' => $u['email']]);
            if (!$existing) {
                $userId = self::db()->insert('users', [
                    'role_id' => $u['role_id'],
                    'name' => $u['name'],
                    'email' => $u['email'],
                    'password' => $u['password'],
                    'status' => 'active',
                    'email_verified_at' => date('Y-m-d H:i:s'),
                ]);

                self::db()->insert('user_profiles', [
                    'user_id' => $userId,
                    'headline' => $u['headline'],
                    'phone' => $u['phone'],
                    'bio' => $u['bio'],
                    'country' => 'Rwanda',
                    'city' => 'Kigali',
                    'language' => 'en'
                ]);
            }
        }
        echo "✓ Users and profiles seeded.\n";

        // 3. Categories
        $categories = [
            ['name' => 'Professional Barista Skills', 'slug' => 'barista-skills', 'icon' => 'bi-cup-hot-fill', 'description' => 'Master espresso extraction, milk texturing, latte art, and high-volume bar workflow.'],
            ['name' => 'Coffee Roasting & Cupping', 'slug' => 'roasting-cupping', 'icon' => 'bi-fire', 'description' => 'Green coffee selection, roasting curve profiles, sensory analysis, and SCA cupping protocols.'],
            ['name' => 'Mixology & Beverage Craft', 'slug' => 'mixology-beverage', 'icon' => 'bi-droplet-half', 'description' => 'Cocktail and mocktail formulation, Rwandan botanical infusions, and bar hospitality.'],
            ['name' => 'Hotel Front Office & Operations', 'slug' => 'hotel-front-office', 'icon' => 'bi-building', 'description' => 'Guest relationship management, check-in systems, concierge excellence, and room division operations.'],
            ['name' => 'Culinary Arts & Kitchen Essentials', 'slug' => 'culinary-arts', 'icon' => 'bi-egg-fried', 'description' => 'Foundational knife skills, modern plating, pastry pairing, and commercial kitchen standards.'],
            ['name' => 'Food Safety & HACCP Standards', 'slug' => 'food-safety-haccp', 'icon' => 'bi-shield-check', 'description' => 'Hygiene regulations, critical control points, allergen awareness, and international food compliance.']
        ];

        foreach ($categories as $cat) {
            $existing = self::db()->fetchOne("SELECT id FROM categories WHERE slug = :slug", ['slug' => $cat['slug']]);
            if (!$existing) {
                self::db()->insert('categories', $cat);
            }
        }
        echo "✓ Categories seeded.\n";

        // 4. Courses, Modules, Lessons, and Quizzes
        $cat1 = self::db()->fetchOne("SELECT id FROM categories WHERE slug = 'barista-skills'");
        $instructor = self::db()->fetchOne("SELECT id FROM users WHERE email = 'instructor@beyondbarista.rw'");

        if ($cat1 && $instructor) {
            // Course 1: Foundation Barista Skills (Free Course)
            $course1Slug = 'foundation-barista-skills-espresso-mechanics';
            $course1 = self::db()->fetchOne("SELECT id FROM courses WHERE slug = :slug", ['slug' => $course1Slug]);

            if (!$course1) {
                $course1Id = self::db()->insert('courses', [
                    'title' => 'Foundation Barista Skills & Espresso Mechanics',
                    'slug' => $course1Slug,
                    'short_description' => 'Learn the core principles of specialty coffee extraction, grinder calibration, milk texturing, and workspace ergonomics.',
                    'description' => 'This comprehensive foundation course is tailored for aspiring baristas, cafe staff, and hospitality professionals. Developed by Beyond Barista Academy in Kigali, it bridges theoretical coffee science with practical bar workflows. You will discover origin profiles, master the 1:2 espresso brew ratio, calibrate on-demand grinders, texture silky microfoam for latte art, and understand daily machine maintenance.',
                    'category_id' => $cat1['id'],
                    'level' => 'beginner',
                    'price' => 0.00,
                    'discount_price' => null,
                    'is_free' => 1,
                    'is_featured' => 1,
                    'is_published' => 1,
                    'duration_hours' => 6.5,
                    'thumbnail' => 'course_barista_foundation.jpg',
                    'preview_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'requirements' => json_encode(['Passion for specialty coffee and hospitality', 'No prior barista experience required']),
                    'learning_outcomes' => json_encode([
                        'Understand specialty coffee processing methods from farm to cup in Rwanda',
                        'Dial in and calibrate commercial espresso grinders for optimal extraction yield',
                        'Steam and texture milk with glossy micro-foam consistency',
                        'Pour fundamental latte art patterns: heart, tulip, and rosetta',
                        'Execute proper station sanitization and backflushing protocols'
                    ]),
                    'certificate_included' => 1,
                    'passing_score' => 75,
                    'created_by' => $instructor['id']
                ]);

                self::db()->insert('course_instructors', [
                    'course_id' => $course1Id,
                    'user_id' => $instructor['id']
                ]);

                // Module 1
                $mod1Id = self::db()->insert('modules', [
                    'course_id' => $course1Id,
                    'title' => 'Module 1: The World of Rwandan Specialty Coffee',
                    'description' => 'History, terroirs, Arabica varietals, and washing station processing.',
                    'sort_order' => 1
                ]);

                self::db()->insert('lessons', [
                    'module_id' => $mod1Id,
                    'course_id' => $course1Id,
                    'title' => '1.1 Coffee Origins: The Rwandan Terroir & Red Bourbon',
                    'slug' => 'coffee-origins-rwandan-terroir',
                    'lesson_type' => 'video',
                    'content' => 'Specialty coffee in Rwanda thrives in volcanic soil and high-altitude microclimates. Learn why the Red Bourbon variety offers exceptional sweetness, floral brightness, and balanced acidity.',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'duration_minutes' => 15,
                    'is_free_preview' => 1,
                    'sort_order' => 1
                ]);

                self::db()->insert('lessons', [
                    'module_id' => $mod1Id,
                    'course_id' => $course1Id,
                    'title' => '1.2 Washing Stations & Processing Methods (Washed, Natural, Honey)',
                    'slug' => 'washing-stations-processing-methods',
                    'lesson_type' => 'text',
                    'content' => 'Processing dramatically influences green bean density, moisture content, and final cup profile. In this lesson, we study fully washed lots compared to experimental anaerobic naturals.',
                    'duration_minutes' => 12,
                    'is_free_preview' => 1,
                    'sort_order' => 2
                ]);

                // Module 2
                $mod2Id = self::db()->insert('modules', [
                    'course_id' => $course1Id,
                    'title' => 'Module 2: Espresso Science & Grinder Dial-In',
                    'description' => 'Extraction physics, channeling prevention, and precision dosing.',
                    'sort_order' => 2
                ]);

                self::db()->insert('lessons', [
                    'module_id' => $mod2Id,
                    'course_id' => $course1Id,
                    'title' => '2.1 The Golden Rule of Espresso Ratios (1:2 in 25-30s)',
                    'slug' => 'golden-rule-espresso-ratios',
                    'lesson_type' => 'video',
                    'content' => 'Learn how dry dose, liquid yield, temperature, and contact time dictate extraction balance between sour (under-extracted), sweet (balanced), and bitter (over-extracted).',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'duration_minutes' => 20,
                    'is_free_preview' => 0,
                    'sort_order' => 1
                ]);

                self::db()->insert('lessons', [
                    'module_id' => $mod2Id,
                    'course_id' => $course1Id,
                    'title' => '2.2 Professional Barista Handbook & Dial-In Chart',
                    'slug' => 'barista-handbook-dial-in-chart',
                    'lesson_type' => 'pdf',
                    'content' => 'Download our comprehensive 24-page dial-in reference chart for quick calibration during rush hour service.',
                    'duration_minutes' => 10,
                    'is_free_preview' => 0,
                    'sort_order' => 2
                ]);

                // Module 3
                $mod3Id = self::db()->insert('modules', [
                    'course_id' => $course1Id,
                    'title' => 'Module 3: Milk Steaming Chemistry & Latte Art',
                    'description' => 'Proteins, fats, stretching, vortexing, and pouring control.',
                    'sort_order' => 3
                ]);

                self::db()->insert('lessons', [
                    'module_id' => $mod3Id,
                    'course_id' => $course1Id,
                    'title' => '3.1 Steaming Microfoam to 60-65°C',
                    'slug' => 'steaming-microfoam-temperatures',
                    'lesson_type' => 'video',
                    'content' => 'Master the two distinct stages of milk steaming: stretching (air incorporation below 37°C) and rolling (vortex integration to break larger bubbles).',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'duration_minutes' => 18,
                    'is_free_preview' => 0,
                    'sort_order' => 1
                ]);

                self::db()->insert('lessons', [
                    'module_id' => $mod3Id,
                    'course_id' => $course1Id,
                    'title' => '3.2 Pouring the Classic Heart & Rosetta',
                    'slug' => 'pouring-classic-heart-rosetta',
                    'lesson_type' => 'video',
                    'content' => 'Detailed multi-angle video showing pitcher altitude, flow rate control, canvas base creation, and cut-through finishing.',
                    'video_provider' => 'youtube',
                    'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                    'duration_minutes' => 22,
                    'is_free_preview' => 0,
                    'sort_order' => 2
                ]);

                // Quiz for Course 1
                $quiz1Id = self::db()->insert('quizzes', [
                    'course_id' => $course1Id,
                    'module_id' => $mod3Id,
                    'title' => 'Barista Foundation Certification Exam',
                    'description' => 'Test your knowledge on espresso extraction, brew ratios, milk temperature, and safety standards. Passing score is 75%.',
                    'time_limit_minutes' => 20,
                    'passing_score' => 75,
                    'max_attempts' => 3,
                    'is_published' => 1
                ]);

                // Q1
                $q1 = self::db()->insert('quiz_questions', [
                    'quiz_id' => $quiz1Id,
                    'question_text' => 'What is the standard brew ratio commonly used as a starting baseline for a classic double espresso?',
                    'question_type' => 'single_choice',
                    'points' => 25,
                    'explanation' => 'A standard 1:2 ratio (e.g. 18g dry coffee to 36g liquid espresso) is the universal specialty benchmark.',
                    'sort_order' => 1
                ]);
                self::db()->insert('quiz_options', ['question_id' => $q1, 'option_text' => '1:1 (18g coffee in, 18g espresso out)', 'is_correct' => 0, 'sort_order' => 1]);
                self::db()->insert('quiz_options', ['question_id' => $q1, 'option_text' => '1:2 (18g coffee in, 36g espresso out)', 'is_correct' => 1, 'sort_order' => 2]);
                self::db()->insert('quiz_options', ['question_id' => $q1, 'option_text' => '1:5 (18g coffee in, 90g espresso out)', 'is_correct' => 0, 'sort_order' => 3]);
                self::db()->insert('quiz_options', ['question_id' => $q1, 'option_text' => '1:10 (18g coffee in, 180g espresso out)', 'is_correct' => 0, 'sort_order' => 4]);

                // Q2
                $q2 = self::db()->insert('quiz_questions', [
                    'quiz_id' => $quiz1Id,
                    'question_text' => 'If an espresso shot extracts 36g in only 12 seconds and tastes sour, what should the barista do?',
                    'question_type' => 'single_choice',
                    'points' => 25,
                    'explanation' => 'Adjusting to a finer grind increases water resistance, lengthening the extraction time to extract balanced sugars.',
                    'sort_order' => 2
                ]);
                self::db()->insert('quiz_options', ['question_id' => $q2, 'option_text' => 'Adjust grinder to a coarser setting', 'is_correct' => 0, 'sort_order' => 1]);
                self::db()->insert('quiz_options', ['question_id' => $q2, 'option_text' => 'Adjust grinder to a finer setting', 'is_correct' => 1, 'sort_order' => 2]);
                self::db()->insert('quiz_options', ['question_id' => $q2, 'option_text' => 'Decrease water temperature by 10°C', 'is_correct' => 0, 'sort_order' => 3]);
                self::db()->insert('quiz_options', ['question_id' => $q2, 'option_text' => 'Steam milk hotter to compensate', 'is_correct' => 0, 'sort_order' => 4]);

                // Q3
                $q3 = self::db()->insert('quiz_questions', [
                    'quiz_id' => $quiz1Id,
                    'question_text' => 'What is the optimal temperature range for steamed whole milk to preserve natural sweetness without scorching proteins?',
                    'question_type' => 'single_choice',
                    'points' => 25,
                    'explanation' => 'Between 60°C and 65°C, lactose sweetness is maximized before whey proteins denature above 70°C.',
                    'sort_order' => 3
                ]);
                self::db()->insert('quiz_options', ['question_id' => $q3, 'option_text' => '40°C – 45°C', 'is_correct' => 0, 'sort_order' => 1]);
                self::db()->insert('quiz_options', ['question_id' => $q3, 'option_text' => '60°C – 65°C', 'is_correct' => 1, 'sort_order' => 2]);
                self::db()->insert('quiz_options', ['question_id' => $q3, 'option_text' => '85°C – 90°C', 'is_correct' => 0, 'sort_order' => 3]);
                self::db()->insert('quiz_options', ['question_id' => $q3, 'option_text' => '100°C (Boiling)', 'is_correct' => 0, 'sort_order' => 4]);

                // Q4
                $q4 = self::db()->insert('quiz_questions', [
                    'quiz_id' => $quiz1Id,
                    'question_text' => 'True or False: Purging the steam wand before and immediately after milk steaming is required for sanitation and milk back-siphon prevention.',
                    'question_type' => 'true_false',
                    'points' => 25,
                    'explanation' => 'Purging clears condensed water before steaming and removes milk residues after steaming.',
                    'sort_order' => 4
                ]);
                self::db()->insert('quiz_options', ['question_id' => $q4, 'option_text' => 'True', 'is_correct' => 1, 'sort_order' => 1]);
                self::db()->insert('quiz_options', ['question_id' => $q4, 'option_text' => 'False', 'is_correct' => 0, 'sort_order' => 2]);
            }

            // Course 2: Specialty Roasting & Sensory Analysis (Premium)
            $cat2 = self::db()->fetchOne("SELECT id FROM categories WHERE slug = 'roasting-cupping'");
            if ($cat2) {
                $course2Slug = 'mastering-specialty-coffee-roasting-sensory-analysis';
                $course2 = self::db()->fetchOne("SELECT id FROM courses WHERE slug = :slug", ['slug' => $course2Slug]);

                if (!$course2) {
                    $course2Id = self::db()->insert('courses', [
                        'title' => 'Mastering Specialty Coffee Roasting & Sensory Analysis',
                        'slug' => $course2Slug,
                        'short_description' => 'Deep dive into thermodynamic roast profiles, Maillard reaction stages, development time ratios (DTR), and standardized SCA cupping.',
                        'description' => 'A master-level course designed for commercial coffee roasters, quality control leads, and cafe owners. Learn how heat transfer (conduction, convection, radiation) shapes cup acidity, body, and aroma. You will develop roast profiles for Rwandan washed and natural lots, evaluate roast defects (baking, scorching, tipping), and perform blind sensory triangulations.',
                        'category_id' => $cat2['id'],
                        'level' => 'advanced',
                        'price' => 85000.00,
                        'discount_price' => 65000.00,
                        'is_free' => 0,
                        'is_featured' => 1,
                        'is_published' => 1,
                        'duration_hours' => 12.0,
                        'thumbnail' => 'course_roasting_master.jpg',
                        'preview_video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'requirements' => json_encode(['Basic understanding of coffee brewing and tasting']),
                        'learning_outcomes' => json_encode([
                            'Operate commercial drum roasters safely and efficiently',
                            'Interpret Rate of Rise (RoR) curves and Development Time Ratio (DTR)',
                            'Identify and prevent common roast defects like baking and tipping',
                            'Perform official SCA sensory evaluation and score coffee lots out of 100'
                        ]),
                        'certificate_included' => 1,
                        'passing_score' => 80,
                        'created_by' => $instructor['id']
                    ]);

                    self::db()->insert('course_instructors', [
                        'course_id' => $course2Id,
                        'user_id' => $instructor['id']
                    ]);

                    // Add modules & lessons
                    $modR1 = self::db()->insert('modules', [
                        'course_id' => $course2Id,
                        'title' => 'Module 1: Thermodynamics & Roasting Machine Dynamics',
                        'description' => 'Heat transfer mechanics in single and double wall drum roasters.',
                        'sort_order' => 1
                    ]);

                    self::db()->insert('lessons', [
                        'module_id' => $modR1,
                        'course_id' => $course2Id,
                        'title' => '1.1 Charge Temperature, Turning Point, and Yellowing Phase',
                        'slug' => 'charge-temperature-turning-point-yellowing',
                        'lesson_type' => 'video',
                        'content' => 'Analyzing thermodynamic equilibrium in the drum and managing burner gas pressure for clean drying.',
                        'video_provider' => 'youtube',
                        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'duration_minutes' => 25,
                        'is_free_preview' => 1,
                        'sort_order' => 1
                    ]);

                    self::db()->insert('lessons', [
                        'module_id' => $modR1,
                        'course_id' => $course2Id,
                        'title' => '1.2 First Crack Dynamics & Exothermic Energy Management',
                        'slug' => 'first-crack-dynamics-exothermic-energy',
                        'lesson_type' => 'video',
                        'content' => 'Managing air-flow and gas reduction into first crack to avoid the dreaded RoR crash or flick.',
                        'video_provider' => 'youtube',
                        'video_url' => 'https://www.youtube.com/watch?v=dQw4w9WgXcQ',
                        'duration_minutes' => 30,
                        'is_free_preview' => 0,
                        'sort_order' => 2
                    ]);
                }
            }

            // Course 3: Food Safety & HACCP Standards (Free)
            $cat3 = self::db()->fetchOne("SELECT id FROM categories WHERE slug = 'food-safety-haccp'");
            if ($cat3) {
                $course3Slug = 'food-safety-haccp-standards-for-cafes';
                $course3 = self::db()->fetchOne("SELECT id FROM courses WHERE slug = :slug", ['slug' => $course3Slug]);

                if (!$course3) {
                    $course3Id = self::db()->insert('courses', [
                        'title' => 'Food Safety & HACCP Standards for Modern Cafes',
                        'slug' => $course3Slug,
                        'short_description' => 'Essential hygiene protocols, allergen management, cross-contamination prevention, and health inspection readiness.',
                        'description' => 'A mandatory foundational certification for all hospitality and cafe personnel in Rwanda. Covering personal hygiene, temperature danger zones (5°C – 60°C), chemical sanitation, critical control points, and pest prevention.',
                        'category_id' => $cat3['id'],
                        'level' => 'all_levels',
                        'price' => 0.00,
                        'discount_price' => null,
                        'is_free' => 1,
                        'is_featured' => 0,
                        'is_published' => 1,
                        'duration_hours' => 3.5,
                        'thumbnail' => 'course_food_safety.jpg',
                        'requirements' => json_encode(['None']),
                        'learning_outcomes' => json_encode([
                            'Implement HACCP principles in commercial food and beverage preparation',
                            'Maintain cold-chain compliance and temperature logs',
                            'Safely store dairy and food products according to FIFO rules'
                        ]),
                        'certificate_included' => 1,
                        'passing_score' => 70,
                        'created_by' => $instructor['id']
                    ]);

                    self::db()->insert('course_instructors', [
                        'course_id' => $course3Id,
                        'user_id' => $instructor['id']
                    ]);
                }
            }
        }
        echo "✓ Courses, modules, lessons, and quizzes seeded.\n";

        // 5. Membership Plans
        $plans = [
            [
                'name' => 'Free Community',
                'slug' => 'free',
                'description' => 'Access to all free foundational courses, hospitality community forums, and public events.',
                'price' => 0.00,
                'billing_interval' => 'month',
                'features' => json_encode(['Access to all free courses', 'Course completion badges', 'Public hospitality job board', 'Community discussion access']),
                'is_active' => 1,
                'sort_order' => 1
            ],
            [
                'name' => 'Professional Barista Monthly',
                'slug' => 'pro-monthly',
                'description' => 'Unlimited access to all courses, verified PDF certificates, masterclasses, and downloadable barista tools.',
                'price' => 15000.00,
                'billing_interval' => 'month',
                'features' => json_encode(['Unlimited access to 50+ courses', 'Verified QR-code digital certificates', 'Exclusive live cupping sessions', 'Downloadable recipes & dial-in manuals', 'Priority job placement assistance']),
                'is_active' => 1,
                'sort_order' => 2
            ],
            [
                'name' => 'Academy VIP Annual',
                'slug' => 'vip-annual',
                'description' => 'Best value for serious hospitality careers. Full access for 12 months with 2 months free + 1-on-1 practical lab session.',
                'price' => 120000.00,
                'billing_interval' => 'year',
                'features' => json_encode(['Everything in Professional Monthly', '2 months completely free discount', '1x In-person practical lab session in Kigali', 'Direct instructor mentorship & review', 'Official printed Academy Certificate']),
                'is_active' => 1,
                'sort_order' => 3
            ]
        ];

        foreach ($plans as $p) {
            $existing = self::db()->fetchOne("SELECT id FROM membership_plans WHERE slug = :slug", ['slug' => $p['slug']]);
            if (!$existing) {
                self::db()->insert('membership_plans', $p);
            }
        }
        echo "✓ Membership plans seeded.\n";

        // 6. Coupons
        $coupons = [
            ['code' => 'BARISTA2026', 'discount_type' => 'percentage', 'discount_value' => 20.00, 'min_spend' => 10000.00, 'max_uses' => 200, 'is_active' => 1],
            ['code' => 'KIGALI10', 'discount_type' => 'percentage', 'discount_value' => 10.00, 'min_spend' => 5000.00, 'max_uses' => 500, 'is_active' => 1],
            ['code' => 'WELCOME5000', 'discount_type' => 'fixed', 'discount_value' => 5000.00, 'min_spend' => 30000.00, 'max_uses' => 100, 'is_active' => 1],
        ];

        foreach ($coupons as $c) {
            $existing = self::db()->fetchOne("SELECT id FROM coupons WHERE code = :code", ['code' => $c['code']]);
            if (!$existing) {
                self::db()->insert('coupons', $c);
            }
        }
        echo "✓ Coupons seeded.\n";

        // 7. Blog Categories & Posts
        $admin = self::db()->fetchOne("SELECT id FROM users WHERE email = 'admin@beyondbarista.rw'");
        $blogCat = self::db()->fetchOne("SELECT id FROM blog_categories WHERE slug = 'coffee-guides'");
        if (!$blogCat) {
            $bCatId = self::db()->insert('blog_categories', [
                'name' => 'Coffee Guides & Brewing Tips',
                'slug' => 'coffee-guides',
                'description' => 'Practical guides for baristas, cafe owners, and coffee enthusiasts.'
            ]);
            self::db()->insert('blog_categories', [
                'name' => 'Hospitality Career Advice',
                'slug' => 'career-advice',
                'description' => 'Career development, resume building, and leadership in Rwanda hospitality.'
            ]);
            self::db()->insert('blog_categories', [
                'name' => 'Academy News & Events',
                'slug' => 'academy-news',
                'description' => 'Updates, graduations, and competitions at Beyond Barista Academy.'
            ]);
        } else {
            $bCatId = $blogCat['id'];
        }

        if ($admin) {
            $posts = [
                [
                    'title' => 'How to Dial In Your Espresso Grinder Every Morning Like a Pro',
                    'slug' => 'how-to-dial-in-espresso-grinder-every-morning',
                    'category_id' => $bCatId,
                    'user_id' => $admin['id'],
                    'excerpt' => 'Weather changes, humidity, and bean degassing affect your extraction time. Follow this 5-minute morning routine.',
                    'content' => '<p>Every professional barista knows that yesterday’s grind setting will rarely produce the perfect espresso today. Atmospheric humidity, ambient temperature, and CO2 degassing in roasted coffee all impact water flow resistance in the portafilter basket.</p><h3>The 5-Step Morning Dial-In Protocol</h3><ol><li><strong>Purge the grinder:</strong> Always run your grinder for 3 to 5 seconds to clear stale grounds retained in the burr chamber.</li><li><strong>Tare and weigh dry dose:</strong> Use an accurate 0.1g scale. For our standard Rwanda Bourbon roast, start with an 18.0g dry dose.</li><li><strong>Inspect extraction time:</strong> Aim for 36.0g liquid yield in 27 to 30 seconds.</li><li><strong>Evaluate flavor balance:</strong> Taste for the hallmark notes of Rwandan coffee — bright citrus acidity, black tea aromatics, and brown sugar sweetness.</li><li><strong>Lock in and record:</strong> Note your setting and temperature on the daily barista board.</li></ol>',
                    'is_published' => 1,
                    'published_at' => date('Y-m-d H:i:s')
                ],
                [
                    'title' => 'The Rise of Specialty Coffee Culture in Kigali: Trends & Opportunities',
                    'slug' => 'rise-of-specialty-coffee-culture-in-kigali',
                    'category_id' => $bCatId,
                    'user_id' => $admin['id'],
                    'excerpt' => 'From local consumption initiatives to world-class hospitality venues, Rwanda is redefining what it means to be a coffee producing nation.',
                    'content' => '<p>Rwanda has long been renowned worldwide as a producer of some of the finest washed Arabica coffees on earth. In recent years, a vibrant domestic specialty cafe scene has blossomed across Kigali, creating remarkable career opportunities for certified baristas, roasters, and managers.</p>',
                    'is_published' => 1,
                    'published_at' => date('Y-m-d H:i:s')
                ]
            ];

            foreach ($posts as $post) {
                $existing = self::db()->fetchOne("SELECT id FROM blog_posts WHERE slug = :slug", ['slug' => $post['slug']]);
                if (!$existing) {
                    self::db()->insert('blog_posts', $post);
                }
            }
        }
        echo "✓ Blog posts seeded.\n";

        // 8. Events
        $events = [
            [
                'title' => 'Kigali Sensory Cupping & Roast Profile Masterclass',
                'slug' => 'kigali-sensory-cupping-masterclass-2026',
                'description' => 'Join Beyond Barista Academy trainers for an immersive 4-hour hands-on sensory calibration session exploring single-origin washed and natural lots from Huye, Nyamasheke, and Gakenke districts.',
                'location' => 'Beyond Barista Academy Lab, KG 11 Ave, Kigali',
                'event_type' => 'workshop',
                'start_date' => date('Y-m-d 09:00:00', strtotime('+14 days')),
                'end_date' => date('Y-m-d 13:00:00', strtotime('+14 days')),
                'capacity' => 25,
                'price' => 15000.00,
                'is_free' => 0,
                'is_published' => 1
            ],
            [
                'title' => 'Rwanda National Barista Championship Preparation Workshop',
                'slug' => 'rwanda-barista-championship-prep-workshop',
                'description' => 'A free community masterclass for competitive baristas covering sensory scorecards, signature beverage formulation, presentation timing, and stage presence.',
                'location' => 'Beyond Barista Main Auditorium, Kigali',
                'event_type' => 'seminar',
                'start_date' => date('Y-m-d 14:00:00', strtotime('+28 days')),
                'end_date' => date('Y-m-d 17:00:00', strtotime('+28 days')),
                'capacity' => 60,
                'price' => 0.00,
                'is_free' => 1,
                'is_published' => 1
            ]
        ];

        foreach ($events as $ev) {
            $existing = self::db()->fetchOne("SELECT id FROM events WHERE slug = :slug", ['slug' => $ev['slug']]);
            if (!$existing) {
                self::db()->insert('events', $ev);
            }
        }
        echo "✓ Events seeded.\n";

        // 9. Hospitality Jobs
        $jobs = [
            [
                'title' => 'Head Barista & Quality Control Lead',
                'slug' => 'head-barista-quality-control-lead-kigali',
                'company' => 'Bourbon Coffee Rwanda',
                'location' => 'Kigali Heights, Kigali',
                'job_type' => 'full_time',
                'experience_level' => 'mid',
                'salary_range' => '350,000 - 500,000 RWF / month',
                'description' => 'We are seeking an experienced and certified Head Barista to oversee daily espresso bar operations, train junior baristas, maintain espresso equipment, and ensure high sensory standards.',
                'requirements' => 'Beyond Barista Academy certification or SCA Barista Skills Foundation/Intermediate. Minimum 2 years specialty cafe experience in Kigali.',
                'deadline' => date('Y-m-d', strtotime('+30 days')),
                'is_published' => 1
            ],
            [
                'title' => 'Food & Beverage Supervisor Intern',
                'slug' => 'fb-supervisor-intern-radisson-kigali',
                'company' => 'Kigali Convention Hospitality Services',
                'location' => 'Kimihurura, Kigali',
                'job_type' => 'internship',
                'experience_level' => 'entry',
                'salary_range' => '150,000 - 200,000 RWF / month',
                'description' => '6-month paid rotational internship covering banquet service, cocktail lounge operations, barista service, and inventory control.',
                'requirements' => 'Hospitality diploma or Beyond Barista Academy training. Excellent English and French communication skills.',
                'deadline' => date('Y-m-d', strtotime('+45 days')),
                'is_published' => 1
            ]
        ];

        foreach ($jobs as $job) {
            $existing = self::db()->fetchOne("SELECT id FROM jobs WHERE slug = :slug", ['slug' => $job['slug']]);
            if (!$existing) {
                self::db()->insert('jobs', $job);
            }
        }
        echo "✓ Jobs seeded.\n";

        // 10. Testimonials & FAQs
        $testimonials = [
            [
                'author_name' => 'Emmanuel Habimana',
                'author_title' => 'Head Barista',
                'author_company' => 'Kivu Specialty Cafe',
                'content' => 'The structured video modules and dial-in charts from Beyond Barista Academy gave me the confidence to step into a head barista role. The verified QR certificate helped me secure my job within two weeks of graduation!',
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 1
            ],
            [
                'author_name' => 'Diane Mukamana',
                'author_title' => 'F&B Manager',
                'author_company' => 'Serena Kigali Hotel',
                'content' => "We train all our new service and bar staff using Beyond Barista Academy's online platform. It ensures consistent international hospitality standards across our entire team.",
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 2
            ],
            [
                'author_name' => 'Jean Claude Mutuyimana',
                'author_title' => 'Coffee Roaster & Entrepreneur',
                'author_company' => 'Kigali Coffee Roasters Ltd.',
                'content' => 'From learning basic barista skills to mastering advanced roasting techniques, Beyond Barista Academy provided the comprehensive training I needed to start my own specialty coffee business.',
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 3
            ],
            [
                'author_name' => 'Anne-Marie Kanamugire',
                'author_title' => 'Hospitality Trainer',
                'author_company' => 'Rwanda Hotel & Restaurant Association',
                'content' => 'The curriculum is industry-aligned and covers everything from espresso extraction to customer service. Our hospitality partners love the quality of graduates from this academy.',
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 4
            ],
            [
                'author_name' => 'Marcus Uwizera',
                'author_title' => 'Coffee Exporter',
                'author_company' => 'Rwanda Premium Coffee Co.',
                'content' => 'Beyond Barista Academy understands the specialty coffee market in Rwanda. Their sensory cupping and quality control modules are world-class and recognized internationally.',
                'rating' => 5,
                'is_active' => 1,
                'sort_order' => 5
            ]
        ];

        foreach ($testimonials as $t) {
            $existing = self::db()->fetchOne("SELECT id FROM testimonials WHERE author_name = :author_name", ['author_name' => $t['author_name']]);
            if (!$existing) {
                self::db()->insert('testimonials', $t);
            }
        }

        $faqs = [
            ['question' => 'Are Beyond Barista Academy certificates recognized by employers in Rwanda?', 'answer' => 'Yes. Beyond Barista Academy is a leading hospitality training institution in Rwanda. Our certificates feature unique verification codes and QR codes that employers can verify instantly online.', 'category' => 'certificates', 'sort_order' => 1],
            ['question' => 'Can I learn online on my mobile phone?', 'answer' => 'Yes! The entire learning platform is fully responsive and optimized for mobile devices, laptops, and tablets. You can resume your lessons anytime from anywhere in Rwanda or internationally.', 'category' => 'learning', 'sort_order' => 2],
            ['question' => 'How do payments work in Rwanda?', 'answer' => 'We support MTN Mobile Money, Airtel Money, credit/debit cards (Visa/Mastercard via Stripe/Flutterwave), and PayPal. Free courses require zero payment details.', 'category' => 'payments', 'sort_order' => 3],
        ];

        foreach ($faqs as $f) {
            $existing = self::db()->fetchOne("SELECT id FROM faqs WHERE question = :question", ['question' => $f['question']]);
            if (!$existing) {
                self::db()->insert('faqs', $f);
            }
        }
        echo "✓ Testimonials and FAQs seeded.\n";

        // 11. Settings
        $settings = [
            ['key' => 'site_name', 'value' => 'Beyond Barista Academy', 'group' => 'general'],
            ['key' => 'site_tagline', 'value' => 'Rwanda’s Premier Hospitality & Barista Learning Platform', 'group' => 'general'],
            ['key' => 'contact_email', 'value' => 'info@beyondbarista.rw', 'group' => 'contact'],
            ['key' => 'contact_phone', 'value' => '+250 788 000 111', 'group' => 'contact'],
            ['key' => 'contact_address', 'value' => 'KG 11 Ave, Kigali Innovation Hub, Rwanda', 'group' => 'contact'],
            ['key' => 'currency_code', 'value' => 'RWF', 'group' => 'finance'],
            ['key' => 'currency_symbol', 'value' => 'RWF ', 'group' => 'finance'],
            ['key' => 'facebook_url', 'value' => 'https://facebook.com/beyondbaristarw', 'group' => 'social'],
            ['key' => 'instagram_url', 'value' => 'https://instagram.com/beyondbarista_rw', 'group' => 'social'],
            ['key' => 'linkedin_url', 'value' => 'https://linkedin.com/company/beyond-barista-rwanda', 'group' => 'social'],
            ['key' => 'youtube_url', 'value' => 'https://youtube.com/@beyondbaristarw', 'group' => 'social'],
            ['key' => 'enable_registration', 'value' => '1', 'group' => 'general'],
            ['key' => 'maintenance_mode', 'value' => '0', 'group' => 'general']
        ];

        foreach ($settings as $s) {
            $existing = self::db()->fetchOne("SELECT id FROM settings WHERE `key` = :key", ['key' => $s['key']]);
            if (!$existing) {
                self::db()->insert('settings', $s);
            }
        }
        echo "✓ System settings seeded.\n";

        echo "\n🎉 Beyond Barista Academy Database Setup & Seeding Successfully Completed!\n";
    }
}
