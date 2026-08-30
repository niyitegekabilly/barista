<?php

namespace Database\Seeders;

use App\Core\Database;

/**
 * BbaCoursesSeeder
 * Seeds the full BBA course catalog based on the official class plan.
 * Run after DatabaseSeeder has seeded roles, users, and base categories.
 */
class BbaCoursesSeeder {
    private static ?Database $db = null;

    private static function db(): Database {
        if (self::$db === null) {
            self::$db = Database::getInstance();
        }
        return self::$db;
    }

    public static function run(): void {
        echo "Starting BBA Full Course Catalog Seeder...\n";

        $instructor = self::db()->fetchOne("SELECT id FROM users WHERE email = 'instructor@beyondbarista.rw'");
        $adminUser  = self::db()->fetchOne("SELECT id FROM users WHERE email = 'admin@beyondbarista.rw'");
        $instructorId = $instructor ? $instructor['id'] : ($adminUser ? $adminUser['id'] : 1);

        // ── 1. Expand Categories ──────────────────────────────────────────────
        $cats = [
            ['name' => 'Professional Barista Skills',         'slug' => 'barista-skills',       'icon' => 'bi-cup-hot-fill',    'description' => 'Master espresso extraction, milk texturing, latte art, and high-volume bar workflow.',                   'sort_order' => 1],
            ['name' => 'Bartender & Mixology',                'slug' => 'mixology-beverage',     'icon' => 'bi-droplet-half',    'description' => 'Classic cocktails, mocktail craft, Rwandan botanicals, and professional bar hospitality.',             'sort_order' => 2],
            ['name' => 'Sommelier & Wine Studies',            'slug' => 'sommelier-wine',        'icon' => 'bi-award',           'description' => 'Wine regions, grape varietals, WSET fundamentals, service protocols, and food pairing.',             'sort_order' => 3],
            ['name' => 'Culinary Arts & Kitchen Essentials',  'slug' => 'culinary-arts',         'icon' => 'bi-egg-fried',       'description' => 'Foundational knife skills, modern plating, pastry pairing, and commercial kitchen standards.',        'sort_order' => 4],
            ['name' => 'Domestic Hospitality',                'slug' => 'domestic-hospitality',  'icon' => 'bi-house-heart',     'description' => 'Professional home management, hosting etiquette, child care services, and housekeeping standards.',   'sort_order' => 5],
            ['name' => 'Food Safety & HACCP Standards',       'slug' => 'food-safety-haccp',     'icon' => 'bi-shield-check',    'description' => 'Hygiene regulations, critical control points, allergen awareness, and international compliance.',       'sort_order' => 6],
            ['name' => 'Herbalism & Wellness Beverages',      'slug' => 'herbalism-wellness',    'icon' => 'bi-flower1',         'description' => 'Medicinal plants, herbal teas, tinctures, and wellness beverage formulation.',                        'sort_order' => 7],
            ['name' => 'Hotel Front Office & Operations',     'slug' => 'hotel-front-office',    'icon' => 'bi-building',        'description' => 'Guest relationship management, check-in systems, concierge excellence, and room division operations.', 'sort_order' => 8],
            ['name' => 'Coffee Roasting & Cupping',           'slug' => 'roasting-cupping',      'icon' => 'bi-fire',            'description' => 'Green coffee selection, roasting curve profiles, sensory analysis, and SCA cupping protocols.',         'sort_order' => 9],
            ['name' => 'BBA Specialty & Bonus Courses',       'slug' => 'bba-specialty',         'icon' => 'bi-stars',           'description' => 'Exclusive short courses: personal branding, entrepreneurship, AI & hospitality, and more.',            'sort_order' => 10],
        ];

        foreach ($cats as $c) {
            $exists = self::db()->fetchOne("SELECT id FROM categories WHERE slug = :slug", ['slug' => $c['slug']]);
            if (!$exists) {
                self::db()->insert('categories', $c);
            } else {
                // Update icon and sort_order
                self::db()->update('categories', [
                    'icon'       => $c['icon'],
                    'sort_order' => $c['sort_order'],
                ], ['slug' => $c['slug']]);
            }
        }
        echo "✓ Categories upserted.\n";

        // Helper to get category id
        $catId = function(string $slug): ?int {
            $row = self::db()->fetchOne("SELECT id FROM categories WHERE slug = :s", ['s' => $slug]);
            return $row ? (int)$row['id'] : null;
        };

        // ── 2. Course definitions from class plan.md ──────────────────────────
        $courses = [
            // ── BARISTA ──────────────────────────────────────────────────────
            [
                'title'            => 'Foundation Barista Skills & Espresso Mechanics',
                'slug'             => 'foundation-barista-skills-espresso-mechanics',
                'category'         => 'barista-skills',
                'level'            => 'beginner',
                'price'            => 0.00,
                'is_free'          => 1,
                'is_featured'      => 1,
                'duration_hours'   => 8,
                'thumbnail'        => 'barista.jpeg',
                'short_description'=> 'Master espresso extraction, grinder calibration, milk texturing, and daily machine maintenance — completely free.',
                'description'      => 'This comprehensive foundation course bridges theoretical coffee science with practical bar workflow. You will learn the 1:2 espresso brew ratio, calibrate on-demand grinders, texture silky microfoam for latte art, and understand daily machine maintenance protocols aligned with SCA standards.',
                'requirements'     => ['Passion for specialty coffee', 'No prior barista experience required'],
                'outcomes'         => [
                    'Understand specialty coffee processing methods from farm to cup',
                    'Dial in commercial espresso grinders for optimal extraction',
                    'Steam milk with professional micro-foam consistency',
                    'Pour latte art: heart, tulip, rosetta',
                    'Execute proper machine sanitization protocols'
                ],
                'modules' => [
                    ['title' => 'Module 01: Introduction to Coffee',                          'lessons' => ['Coffee Origins & Rwanda Terroir', 'Arabica vs Robusta & Varietals', 'Washing Stations & Processing Methods']],
                    ['title' => 'Module 02: Brewing Methods',                                 'lessons' => ['Espresso Ratios (1:2 in 25-30s)', 'Pour Over & Filter Brewing', 'Cold Brew & Immersion Methods']],
                    ['title' => 'Module 03: Roasting Science',                                'lessons' => ['Green Bean Grading & Density', 'Maillard Reaction & First Crack', 'Reading a Roast Curve']],
                    ['title' => 'Module 04: Sensory Skills & Flavor Development',             'lessons' => ['SCA Cupping Protocol', 'Taste Descriptors & Flavor Wheel', 'Identifying Defects in the Cup']],
                    ['title' => 'Module 05: Green Coffee Grading & Quality Control',          'lessons' => ['Physical Green Bean Analysis', 'Moisture Content & Water Activity', 'SCA Defect Categories']],
                    ['title' => 'Module 06: Barista Skills',                                  'lessons' => ['Espresso Dosing & Distribution', 'Grinder Calibration Drill', 'Milk Texturing & Latte Art Basics']],
                    ['title' => 'Module 07: Coffee Shop Operations',                          'lessons' => ['Station Setup & Workflow', 'Rush Hour Bar Management', 'Inventory & Par Levels']],
                    ['title' => 'Module 08: Customer Service & Barista Professionalism',      'lessons' => ['Guest Communication Skills', 'Handling Complaints & Recovery', 'Professional Barista Etiquette']],
                    ['title' => 'Module 09: Menu & Recipe Development',                       'lessons' => ['Signature Drink Creation', 'Costing & Pricing Beverages', 'Menu Layout & Presentation']],
                    ['title' => 'Module 10: Prepare Iced & Specialty Drinks',                 'lessons' => ['Iced Espresso & Cold Brew Drinks', 'Specialty Frappé Techniques', 'Nitrogen & Carbonated Coffee']],
                ],
            ],
            [
                'title'            => 'Advanced Barista: Latte Art, Competition & Personal Branding',
                'slug'             => 'advanced-barista-latte-art-competition',
                'category'         => 'barista-skills',
                'level'            => 'advanced',
                'price'            => 65000.00,
                'is_free'          => 0,
                'is_featured'      => 1,
                'duration_hours'   => 12,
                'thumbnail'        => 'cappuccino.jpg',
                'short_description'=> 'Take your barista skills to competition level: advanced latte art, interview performance, and personal branding for career elevation.',
                'description'      => 'Designed for working baristas ready to compete and lead. Covers advanced milk texturing, competition scoring rubrics, Rwandan coffee culture advocacy, interview performance, public speaking, personal branding strategy, and beverage entrepreneurship.',
                'requirements'     => ['Completed Foundation Barista Skills or equivalent experience', '6+ months hands-on bar experience'],
                'outcomes'         => [
                    'Execute competition-level 3D and 5-layer latte art designs',
                    'Understand SCA Barista Championship scoring sheets',
                    'Articulate a personal barista brand story',
                    'Lead a team during high-volume service',
                    'Develop a business plan for a specialty coffee concept'
                ],
                'modules' => [
                    ['title' => 'Module 11: Basic Cooking Skills for Baristas',         'lessons' => ['Food Pairing with Coffee', 'Simple Patisserie for Café Display', 'Allergy Awareness in Food Prep']],
                    ['title' => 'Module 12: Prepare Ice Cream & Frozen Desserts',        'lessons' => ['Gelato Bases & Overrun', 'Affogato & Coffee Desserts', 'Freezing Curves & Storage']],
                    ['title' => 'Module 13: Drinking Water Science',                     'lessons' => ['Water Hardness & TDS Impact on Espresso', 'Filtration Systems', 'Water Quality Standards']],
                    ['title' => 'Module 14: Welcoming Drinks & Food Pairing',            'lessons' => ['Arrival Drink Concepts', 'Rwandan Botanical Infusions', 'Pairing Theory: Texture, Weight, Flavour']],
                    ['title' => 'Module 15: Hot & Cold Beverage Preparation',            'lessons' => ['Hot Chocolate Masterclass', 'Matcha & Specialty Teas', 'Batch Brew & Dispense Systems']],
                    ['title' => 'Module 16: Machine Cleaning & Maintenance',             'lessons' => ['Daily Backflushing Protocols', 'Group Head & Steam Wand Deep Clean', 'Grinder Burr Maintenance Schedule']],
                    ['title' => 'Module 17: Tobacco & Cigar Service',                   'lessons' => ['Pairing Cigars with Coffee & Spirits', 'Proper Cutting & Lighting Technique', 'Storage & Humidor Management']],
                    ['title' => 'Module 18: POS & Point of Sales Operations',           'lessons' => ['POS Navigation & Menu Setup', 'Cash Handling & Reconciliation', 'Digital Payments & MTN Mobile Money']],
                    ['title' => 'Module 19: Coffee Mixology',                            'lessons' => ['Espresso Martini & Coffee Cocktails', 'Cold Brew Infusions & Tinctures', 'Non-Alcoholic Coffee Mixology']],
                    ['title' => 'Module 20: Home Barista Mastery',                      'lessons' => ['Best Home Espresso Machines 2026', 'Dialing In for Home Gear', 'Home Roasting Fundamentals']],
                    ['title' => 'Module 21: Interview Performance & Public Speaking',    'lessons' => ['CV & Portfolio Building', 'Mock Interview Techniques', 'Presenting on Stage & Media Presence']],
                    ['title' => 'Module 22: Personal Branding',                         'lessons' => ['Defining Your Barista Identity', 'Social Media & Content Strategy', 'Building an Online Coffee Portfolio']],
                    ['title' => 'Module 23: Rwandan Barista & Coffee Culture',          'lessons' => ['Rwanda in the Specialty Coffee World', 'Farm-to-Cup Story Telling', 'Cultural Etiquette in Coffee Service']],
                    ['title' => 'Module 24: Beverage Entrepreneurship',                 'lessons' => ['Business Plan for a Coffee Shop', 'Cost Control & Profitability', 'Licensing & Regulatory Requirements in Rwanda']],
                ],
            ],

            // ── BARTENDER ────────────────────────────────────────────────────
            [
                'title'            => 'Professional Bartender & Bar Management',
                'slug'             => 'professional-bartender-bar-management',
                'category'         => 'mixology-beverage',
                'level'            => 'beginner',
                'price'            => 55000.00,
                'is_free'          => 0,
                'is_featured'      => 1,
                'duration_hours'   => 15,
                'thumbnail'        => 'coffee-cups.jpg',
                'short_description'=> 'From classic cocktails to responsible service — a complete professional bartender certification program.',
                'description'      => 'Master bartending from fundamentals to advanced service techniques. Covers spirits knowledge, classic and modern cocktail recipes, bar hygiene, responsible alcohol service, high-volume bar workflow, and professional bar management aligned with international hospitality standards.',
                'requirements'     => ['18+ years of age', 'Basic literacy in English'],
                'outcomes'         => [
                    'Identify and serve 50+ classic international cocktails',
                    'Apply responsible alcohol service (RABS) principles',
                    'Execute efficient high-volume bar workflow',
                    'Build and cost a bar menu',
                    'Manage bar inventory and supplier relationships'
                ],
                'modules' => [
                    ['title' => 'Module 01: Introduction to Bartending & Bar Tools',       'lessons' => ['Essential Bar Tools & Equipment', 'Glassware Guide', 'Station Setup & Mise en Place']],
                    ['title' => 'Module 02: Spirits Knowledge',                            'lessons' => ['Whisky, Rum, Gin, Vodka, Tequila Origins', 'Distillation & Aging Processes', 'Reading Spirit Labels & ABV']],
                    ['title' => 'Module 03: Cocktail Foundations',                         'lessons' => ['Mixing Techniques: Stir, Shake, Build, Muddle', 'Classic Cocktail Families', 'Balance: Sweet, Sour, Strong, Weak']],
                    ['title' => 'Module 04: Wine & Beer Basics for Bartenders',            'lessons' => ['Wine Service by the Glass', 'Beer Styles & Draught Service', 'Food Pairing Fundamentals']],
                    ['title' => 'Module 05: Mocktails & Non-Alcoholic Craft',              'lessons' => ['Zero-Proof Cocktail Design', 'Shrubs, Syrups & House-Made Mixers', 'Presentation & Garnishing']],
                    ['title' => 'Module 06: Responsible Alcohol Service',                  'lessons' => ['Signs of Intoxication', 'Refusing Service Professionally', 'Rwandan Alcohol Regulations']],
                    ['title' => 'Module 07: Bar Operations & Hygiene',                     'lessons' => ['Cleaning & Sanitation Protocols', 'Temperature Control for Perishables', 'Pest Control Awareness']],
                    ['title' => 'Module 08: Bar Menu Development',                         'lessons' => ['Signature Cocktail Ideation', 'Menu Costing & Pricing', 'Seasonal & Local Ingredient Menus']],
                    ['title' => 'Module 09: Guest Experience & Upselling',                 'lessons' => ['Reading Guest Preferences', 'Upselling Premiums & Specials', 'Handling Difficult Situations']],
                    ['title' => 'Module 10: Bar Management & Leadership',                  'lessons' => ['Stock Control & Wastage Reduction', 'Leading a Bar Team', 'Bar P&L Basics']],
                ],
            ],

            // ── SOMMELIER ────────────────────────────────────────────────────
            [
                'title'            => 'Sommelier Fundamentals & Wine Service',
                'slug'             => 'sommelier-fundamentals-wine-service',
                'category'         => 'sommelier-wine',
                'level'            => 'intermediate',
                'price'            => 70000.00,
                'is_free'          => 0,
                'is_featured'      => 0,
                'duration_hours'   => 14,
                'thumbnail'        => 'coffeshop.jpg',
                'short_description'=> 'Master wine regions, grape varietals, tasting methodology, and fine dining wine service protocols.',
                'description'      => 'A complete introductory-to-intermediate sommelier certification program. Covers Old World and New World wine regions, grape varietal profiles, systematic tasting methodology, food pairing theory, tableside service rituals, and decanting techniques for fine dining environments.',
                'requirements'     => ['18+ years of age', 'Interest in wine and fine dining hospitality'],
                'outcomes'         => [
                    'Identify major wine regions and their signature varietals',
                    'Apply the systematic approach to tasting (SAT)',
                    'Pair wines confidently with food menus',
                    'Execute professional tableside wine service and decanting',
                    'Read and recommend wine lists to restaurant guests'
                ],
                'modules' => [
                    ['title' => 'Module 01: Introduction to Wine & Viticulture',             'lessons' => ['How Wine is Made', 'The Vine, Terroir & Climate', 'Major Wine Producing Countries']],
                    ['title' => 'Module 02: Systematic Approach to Tasting',                 'lessons' => ['Sight, Nose, Palate Framework', 'Wine Faults & Defects', 'Describing Wine Professionally']],
                    ['title' => 'Module 03: Red Wine Varietals & Regions',                   'lessons' => ['Cabernet Sauvignon & Bordeaux', 'Pinot Noir & Burgundy', 'Malbec, Shiraz & New World Reds']],
                    ['title' => 'Module 04: White, Rosé & Sparkling Wines',                  'lessons' => ['Chardonnay & Burgundy Whites', 'Champagne Method & Prosecco', 'Rosé Styles & Skin Contact']],
                    ['title' => 'Module 05: Food & Wine Pairing Principles',                 'lessons' => ['Matching Weight, Texture & Flavour', 'Regional Pairing Logic', 'Building a Pairing Menu']],
                    ['title' => 'Module 06: Wine List Reading & Recommendations',            'lessons' => ['Navigating Restaurant Wine Lists', 'Value vs Premium Recommendations', 'Budget Dialogue with Guests']],
                    ['title' => 'Module 07: Tableside Service & Decanting',                  'lessons' => ['Presenting & Opening Wine', 'Decanting Protocols for Old World Reds', 'Managing Service Flow']],
                    ['title' => 'Module 08: Fortified Wines & Digestifs',                   'lessons' => ['Port, Sherry & Madeira', 'Cognac & Armagnac', 'After-Dinner Service Etiquette']],
                    ['title' => 'Module 09: Wine Cellar Management',                        'lessons' => ['Cellar Temperature & Humidity', 'Bin Management & FIFO', 'Stock Valuation & Cost Control']],
                    ['title' => 'Module 10: African Wine Landscape',                        'lessons' => ['South African Wine Regions', 'East African Emerging Wine Tourism', 'Rwandan Hospitality & Wine Culture']],
                ],
            ],

            // ── FOOD SAFETY ──────────────────────────────────────────────────
            [
                'title'            => 'Food Safety, HACCP & Hygiene Certification',
                'slug'             => 'food-safety-haccp-hygiene-certification',
                'category'         => 'food-safety-haccp',
                'level'            => 'beginner',
                'price'            => 25000.00,
                'is_free'          => 0,
                'is_featured'      => 0,
                'duration_hours'   => 6,
                'thumbnail'        => 'class.png',
                'short_description'=> 'Internationally aligned food safety training covering HACCP, allergen management, and Rwanda Bureau of Standards compliance.',
                'description'      => 'Comprehensive food safety program for kitchen, café, restaurant, and hospitality professionals. Covers Hazard Analysis Critical Control Points (HACCP) frameworks, food temperature monitoring, cross-contamination prevention, allergen management, personal hygiene standards, and Rwanda Bureau of Standards (RBS) regulatory requirements.',
                'requirements'     => ['No prerequisites', 'Suitable for all food handlers'],
                'outcomes'         => [
                    'Understand and implement HACCP critical control points',
                    'Identify and manage the 14 major food allergens',
                    'Apply correct food storage temperatures and FIFO rotation',
                    'Maintain personal hygiene to international standards',
                    'Pass the BBA Food Safety certification assessment'
                ],
                'modules' => [
                    ['title' => 'Module 01: Introduction to Food Safety',           'lessons' => ['Why Food Safety Matters', 'Foodborne Illnesses & Outbreaks', 'Rwanda Food Safety Regulations']],
                    ['title' => 'Module 02: Personal Hygiene',                      'lessons' => ['Handwashing Technique & Frequency', 'Protective Clothing Standards', 'When to Exclude from Food Handling']],
                    ['title' => 'Module 03: Temperature Control',                   'lessons' => ['Danger Zone: 5°C–63°C', 'Chilling, Freezing & Thawing', 'Calibrating Food Thermometers']],
                    ['title' => 'Module 04: Cross-Contamination Prevention',        'lessons' => ['Colour-Coded Cutting Board System', 'Raw vs Ready-to-Eat Separation', 'Cleaning vs Sanitizing']],
                    ['title' => 'Module 05: HACCP Principles & Implementation',     'lessons' => ['7 HACCP Principles Explained', 'Completing a CCP Monitoring Log', 'Corrective Actions & Verification']],
                ],
            ],

            // ── CULINARY ARTS ────────────────────────────────────────────────
            [
                'title'            => 'Culinary Arts Foundations & Modern Kitchen Skills',
                'slug'             => 'culinary-arts-foundations-modern-kitchen',
                'category'         => 'culinary-arts',
                'level'            => 'beginner',
                'price'            => 60000.00,
                'is_free'          => 0,
                'is_featured'      => 0,
                'duration_hours'   => 16,
                'thumbnail'        => 'best.jpg',
                'short_description'=> 'From knife skills to modern plating — build a complete culinary foundation aligned with professional kitchen standards.',
                'description'      => 'A professional culinary arts foundation program covering essential knife skills, stocks and sauces, protein cookery, vegetable preparation, modern plating techniques, pastry basics, and commercial kitchen safety standards aligned with international culinary institute benchmarks.',
                'requirements'     => ['Interest in cooking and hospitality', 'No prior experience required'],
                'outcomes'         => [
                    'Execute professional knife cuts with speed and precision',
                    'Prepare classical mother sauces and modern derivatives',
                    'Apply correct protein cookery temperatures',
                    'Plate dishes using modern restaurant presentation techniques',
                    'Work safely in a commercial kitchen environment'
                ],
                'modules' => [
                    ['title' => 'Module 01: Kitchen Orientation & Safety',          'lessons' => ['Kitchen Brigade & Hierarchy', 'Fire Safety & First Aid', 'PPE & Protective Equipment']],
                    ['title' => 'Module 02: Knife Skills & Mise en Place',          'lessons' => ['Knife Types & Selection', 'Classical Cuts: Julienne, Brunoise, Chiffonade', 'Mise en Place Philosophy']],
                    ['title' => 'Module 03: Stocks, Soups & Sauces',               'lessons' => ['Chicken, Veal & Fish Stock Production', 'Mother Sauces: Béchamel, Velouté, Espagnole', 'Pan Sauce & Emulsion Techniques']],
                    ['title' => 'Module 04: Protein Cookery',                       'lessons' => ['Beef & Lamb Cookery Temperatures', 'Poultry Handling & Fabrication', 'Fish & Seafood Cookery Methods']],
                    ['title' => 'Module 05: Vegetable & Starch Preparation',       'lessons' => ['Blanching & Shocking', 'Root Vegetable Glazing', 'Potato: Mashed, Roasted, Fondant']],
                    ['title' => 'Module 06: Pastry & Baking Fundamentals',         'lessons' => ['Short Crust, Puff & Choux Pastry', 'Sponge, Genoise & Mousse', 'Chocolate Tempering Basics']],
                    ['title' => 'Module 07: Modern Plating & Presentation',        'lessons' => ['Plating Tools & Techniques', 'Colour, Texture & Height in Plating', 'Food Photography for Menus']],
                    ['title' => 'Module 08: Rwandan Cuisine & Local Ingredients',  'lessons' => ['Traditional Rwandan Dishes & Context', 'Local Vegetables, Grains & Proteins', 'Modernizing Rwandan Recipes']],
                    ['title' => 'Module 09: Menu Engineering & Costing',           'lessons' => ['Food Cost Percentage Formula', 'Menu Profitability Matrix', 'Seasonal Menu Planning']],
                    ['title' => 'Module 10: Kitchen Management Essentials',        'lessons' => ['Prep Lists & Production Planning', 'Ordering & Receiving Procedures', 'Waste Reduction Strategies']],
                ],
            ],

            // ── HERBALISM ────────────────────────────────────────────────────
            [
                'title'            => 'Herbalism & Wellness Beverage Formulation',
                'slug'             => 'herbalism-wellness-beverage-formulation',
                'category'         => 'herbalism-wellness',
                'level'            => 'beginner',
                'price'            => 35000.00,
                'is_free'          => 0,
                'is_featured'      => 0,
                'duration_hours'   => 10,
                'thumbnail'        => 'wood1.jpg',
                'short_description'=> 'Learn to identify, process, and formulate wellness beverages from African medicinal herbs and plants.',
                'description'      => 'A unique BBA program exploring African herbalism and its application in modern wellness beverage formulation. Learn botanical identification, plant preparation techniques, decoction and infusion methods, tincture making, and how to develop herb-based beverages for café and retail markets.',
                'requirements'     => ['Curiosity about plants and natural wellness', 'No prior experience required'],
                'outcomes'         => [
                    'Identify 30+ common medicinal herbs used in East African wellness culture',
                    'Prepare herbal infusions, decoctions, and tinctures',
                    'Formulate and cost wellness tea blends for commercial sale',
                    'Apply safe handling and dosage guidelines for common herbs',
                    'Design a wellness beverage menu for café or retail'
                ],
                'modules' => [
                    ['title' => 'Module 01: Introduction to Herbalism',                    'lessons' => ['History of African Herbalism', 'Traditional Rwandan Plant Medicine', 'Modern Herbalism & Science']],
                    ['title' => 'Module 02: Botanical Identification & Sourcing',           'lessons' => ['Plant Families & Identification Keys', 'Wild Harvesting Ethics', 'Drying, Storing & Grading Herbs']],
                    ['title' => 'Module 03: Preparation Methods',                          'lessons' => ['Infusions vs Decoctions', 'Tincture & Glycerite Making', 'Cold Process Extracts']],
                    ['title' => 'Module 04: Wellness Beverage Development',                'lessons' => ['Flavour Balancing in Herbal Blends', 'Sweeteners: Honey, Stevia & Agave', 'Carbonated & Functional Drinks']],
                    ['title' => 'Module 05: Safety, Dosage & Contraindications',           'lessons' => ['Safe Herb-Drug Interactions', 'Dosage Guidelines & Cautions', 'Labelling & Regulatory Requirements']],
                    ['title' => 'Module 06: Commercial Herbalism & Entrepreneurship',      'lessons' => ['Costing a Wellness Beverage Product', 'Rwanda FDA Registration Basics', 'Selling at Markets & Online']],
                ],
            ],

            // ── DOMESTIC HOSPITALITY ─────────────────────────────────────────
            [
                'title'            => 'Domestic Hospitality & Professional Housekeeping',
                'slug'             => 'domestic-hospitality-professional-housekeeping',
                'category'         => 'domestic-hospitality',
                'level'            => 'beginner',
                'price'            => 30000.00,
                'is_free'          => 0,
                'is_featured'      => 0,
                'duration_hours'   => 8,
                'thumbnail'        => 'wood2.jpg',
                'short_description'=> 'Professional household management, hosting protocols, childcare essentials, and domestic service excellence.',
                'description'      => 'A professional training program for domestic workers, household managers, and hospitality support staff. Covers professional housekeeping standards, laundry and linen care, household management systems, hosting and entertainment protocols, childcare basics, and career development in domestic service.',
                'requirements'     => ['No prior experience required', 'Basic literacy'],
                'outcomes'         => [
                    'Apply hotel-standard room cleaning and inspection techniques',
                    'Manage laundry, linen care, and garment storage',
                    'Set a formal dining table and serve meals professionally',
                    'Provide basic childcare support and educational activities',
                    'Manage a household schedule and supply budget'
                ],
                'modules' => [
                    ['title' => 'Module 01: Introduction to Domestic Service',             'lessons' => ['History & Professionalism in Domestic Work', 'Role of Domestic Staff in Rwanda', 'Rights & Responsibilities']],
                    ['title' => 'Module 02: Professional Housekeeping',                    'lessons' => ['Cleaning Schedules & Priority Areas', 'Colour-Coded Cleaning System', 'High-Touch Point Disinfection']],
                    ['title' => 'Module 03: Laundry & Linen Care',                        'lessons' => ['Fabric Identification & Washing Temperatures', 'Ironing & Pressing Techniques', 'Wardrobe & Storage Organisation']],
                    ['title' => 'Module 04: Household & Kitchen Management',               'lessons' => ['Menu Planning & Shopping Lists', 'Kitchen Hygiene & Safety', 'Batch Cooking & Food Storage']],
                    ['title' => 'Module 05: Hosting & Table Service',                     'lessons' => ['Formal Table Setting Protocols', 'Serving & Clearing Courses', 'Event Preparation & Hosting Etiquette']],
                    ['title' => 'Module 06: Childcare Essentials',                        'lessons' => ['Child Development Milestones', 'Safe Play & Educational Activities', 'Emergency First Aid for Children']],
                ],
            ],

            // ── ORIENTATION ──────────────────────────────────────────────────
            [
                'title'            => 'BBA Student Orientation & Online Academy Guide',
                'slug'             => 'bba-student-orientation-online-academy',
                'category'         => 'bba-specialty',
                'level'            => 'beginner',
                'price'            => 0.00,
                'is_free'          => 1,
                'is_featured'      => 0,
                'duration_hours'   => 1.5,
                'thumbnail'        => 'teachers.jpg',
                'short_description'=> 'Welcome to BBA Online! Learn how the platform works, meet your trainers, and set yourself up for success — free for all students.',
                'description'      => 'Your starting point at Beyond Barista Academy. This free orientation walks you through the learning platform, explains the assessment system, introduces Coach Egide and your trainers, outlines the student code of conduct, and gives you the mindset frameworks to succeed in any BBA course.',
                'requirements'     => ['No requirements — open to all registered students'],
                'outcomes'         => [
                    'Navigate the BBA online learning platform confidently',
                    'Understand the assessment, quiz, and certificate system',
                    'Know who to contact for student support',
                    'Apply the BBA success mindset framework to your studies',
                    'Join the BBA student community and alumni network'
                ],
                'modules' => [
                    ['title' => 'Welcome & Platform Overview', 'lessons' => [
                        '0.1 Welcome Message from Coach Egide',
                        '0.2 About Beyond Barista Academy',
                        '0.3 Meet Your Trainer',
                        '0.4 How the Online Academy Works',
                        '0.5 Learning Rules & Student Code of Conduct',
                    ]],
                    ['title' => 'Getting Ready to Learn', 'lessons' => [
                        '0.6 How to Succeed at BBA',
                        '0.7 Equipment Needed for Your Course',
                        '0.8 Assessment System & Grading',
                        '0.9 Student Support & Community',
                        '0.10 Graduation & Digital Certificate',
                        '0.11 The BBA Success Mindset',
                        '0.12 Final Orientation Message',
                    ]],
                ],
            ],
        ];

        // ── 3. Insert courses, modules, and lessons ───────────────────────────
        foreach ($courses as $courseData) {
            $slug = $courseData['slug'];
            $existing = self::db()->fetchOne("SELECT id FROM courses WHERE slug = :s", ['s' => $slug]);
            if ($existing) {
                echo "  — Skipping existing course: {$courseData['title']}\n";
                continue;
            }

            $categoryId = $catId($courseData['category']);
            if (!$categoryId) {
                echo "  ✗ Category not found for: {$courseData['title']}\n";
                continue;
            }

            // Use real image if exists, else fallback to placeholder.svg
            $thumbFile = 'public/assets/img/' . $courseData['thumbnail'];
            $thumbnail = file_exists($thumbFile) ? $courseData['thumbnail'] : 'placeholder.svg';

            $courseId = self::db()->insert('courses', [
                'title'              => $courseData['title'],
                'slug'               => $slug,
                'short_description'  => $courseData['short_description'],
                'description'        => $courseData['description'],
                'category_id'        => $categoryId,
                'level'              => $courseData['level'],
                'price'              => $courseData['price'],
                'discount_price'     => null,
                'is_free'            => $courseData['is_free'],
                'is_featured'        => $courseData['is_featured'],
                'is_published'       => 1,
                'duration_hours'     => $courseData['duration_hours'],
                'thumbnail'          => $thumbnail,
                'requirements'       => json_encode($courseData['requirements']),
                'learning_outcomes'  => json_encode($courseData['outcomes']),
                'certificate_included' => 1,
                'passing_score'      => 70,
                'created_by'         => $instructorId,
            ]);

            self::db()->insert('course_instructors', [
                'course_id' => $courseId,
                'user_id'   => $instructorId,
            ]);

            // Insert modules + lessons
            foreach ($courseData['modules'] as $sortModule => $module) {
                $moduleId = self::db()->insert('modules', [
                    'course_id'  => $courseId,
                    'title'      => $module['title'],
                    'description'=> '',
                    'sort_order' => $sortModule + 1,
                ]);

                foreach ($module['lessons'] as $sortLesson => $lesson) {
                    $lessonTitle = is_string($lesson) ? $lesson : $lesson['title'];
                    $lessonSlug  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($lessonTitle)));
                    $lessonSlug .= '-' . $courseId . '-' . $moduleId . '-' . ($sortLesson + 1);

                    self::db()->insert('lessons', [
                        'module_id'       => $moduleId,
                        'course_id'       => $courseId,
                        'title'           => $lessonTitle,
                        'slug'            => substr($lessonSlug, 0, 200),
                        'lesson_type'     => 'text',
                        'content'         => 'Content for: ' . $lessonTitle . '. Full video and materials will be uploaded before course launch.',
                        'duration_minutes'=> 15,
                        'is_free_preview' => ($sortLesson === 0 && $sortModule === 0) ? 1 : 0,
                        'sort_order'      => $sortLesson + 1,
                    ]);
                }
            }

            echo "  ✓ Seeded: {$courseData['title']} ({$courseId})\n";
        }

        echo "✓ BBA Full Course Catalog seeded successfully.\n";
    }
}
