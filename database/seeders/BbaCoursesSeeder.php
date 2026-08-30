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
                'resync' => true,
                'modules' => [
                    ['title' => 'Module 11: Basic Cooking Skills for Baristas', 'lessons' => [
                        ['title' => 'Food Pairing with Coffee', 'content' => 'Pairing starts with matching intensity: light, floral roasts suit citrus pastries and fresh fruit, while dark, chocolatey roasts stand up to rich cakes and nutty bakes. Acidity in coffee cuts through fatty or creamy food the way a squeeze of lemon does, so a bright washed coffee pairs beautifully with buttery croissants or cheese. When building a café menu, taste each pastry against two or three roast profiles before deciding what goes on the counter together.'],
                        ['title' => 'Simple Patisserie for Café Display', 'content' => 'A café display case sells with the eyes before the mouth. Keep a rotating base of reliable bakes — croissants, muffins, banana bread, cookies — baked in small batches so nothing sits stale past its window. Label everything with a bake time and follow strict FIFO (first in, first out) rotation. Presentation matters: consistent spacing, a couple of hero items at eye level, and clean glass, restocked before the morning and afternoon rushes.'],
                        ['title' => 'Allergy Awareness in Food Prep', 'content' => 'The most common food allergens in a café kitchen are gluten, dairy, tree nuts, peanuts, soy, and eggs. Cross-contact happens easily — a shared toaster, an unwashed knife, or crumbs on a shared cutting board — so dedicate tools and prep zones for allergen-free orders whenever possible. Always tell a guest honestly if you cannot guarantee an allergen-free item; hospitality means protecting guests, not just pleasing them.'],
                    ]],
                    ['title' => 'Module 12: Prepare Ice Cream & Frozen Desserts', 'lessons' => [
                        ['title' => 'Gelato Bases & Overrun', 'content' => 'Gelato differs from ice cream mainly in fat content (typically 4-8% versus 14%+) and overrun — the amount of air churned into the mix. Ice cream is churned fast and cold, giving high overrun (often 50-100%) and a light, airy texture; gelato is churned slower and served slightly warmer, giving low overrun (20-30%) and a dense, intensely flavoured result. Get your base ratio of milk, cream, sugar, and stabiliser right before ever touching flavourings.'],
                        ['title' => 'Affogato & Coffee Desserts', 'content' => 'An affogato is simply a scoop of good vanilla gelato "drowned" under a freshly pulled hot espresso shot, served immediately so the contrast of hot and cold, bitter and sweet, is still active when it reaches the guest. Build variations by swapping in hazelnut or coffee gelato, adding a shot of liqueur, or finishing with crushed biscotti or cocoa nibs for texture and visual appeal on the plate.'],
                        ['title' => 'Freezing Curves & Storage', 'content' => 'Frozen desserts should be stored at -18°C or colder in a freezer that does not fluctuate — temperature swings cause ice crystals to grow, giving a grainy, icy texture instead of smooth and creamy. Never refreeze melted product. Label every tub with the production date and flavour, and rotate stock so nothing sits longer than its recommended shelf life (typically 2-3 weeks for house-made gelato).'],
                    ]],
                    ['title' => 'Module 13: Drinking Water Science', 'lessons' => [
                        ['title' => 'Water Hardness & TDS Impact on Espresso', 'content' => 'Water is over 98% of every cup you serve, so its mineral content directly shapes extraction and flavour. Total Dissolved Solids (TDS) that are too low under-extract and taste flat; too high over-extracts and tastes harsh, while also accelerating limescale build-up inside your espresso machine. The SCA target for brewing water is roughly 75-250 ppm TDS, with calcium and magnesium hardness kept moderate to protect both flavour and equipment.'],
                        ['title' => 'Filtration Systems', 'content' => 'Common café filtration includes carbon block filters (remove chlorine and odours), scale-inhibiting filters (reduce limescale without stripping all minerals), and reverse osmosis (RO) systems that strip nearly everything, requiring remineralisation before brewing. Match your filter to your local water hardness, and replace cartridges on the manufacturer schedule — an exhausted filter is worse than no filter, since it can shed built-up contaminants back into your water.'],
                        ['title' => 'Water Quality Standards', 'content' => 'The SCA Water Quality Standard gives baristas a practical target: zero chlorine, 75-250 ppm TDS (150 ppm ideal), pH between 6.5 and 7.5, and total alkalinity around 40 mg/L. Testing your water with a simple TDS meter and strips takes minutes and explains more inconsistent-tasting shots than most baristas realise. Re-test whenever your water source changes, such as after a filter swap or a move to a new location.'],
                    ]],
                    ['title' => 'Module 14: Welcoming Drinks & Food Pairing', 'lessons' => [
                        ['title' => 'Arrival Drink Concepts', 'content' => 'A welcome drink — offered the moment a guest arrives at a hotel, lounge, or event — sets the emotional tone before a single word of service happens. Keep it simple, seasonal, and quick to produce in volume: a chilled hibiscus cooler, a citrus-mint infusion, or a warm spiced tea in cooler months. The goal is warmth and speed, not complexity.'],
                        ['title' => 'Rwandan Botanical Infusions', 'content' => 'Local botanicals make distinctive, low-cost welcome drinks: dried hibiscus (sorrel) for a tart ruby-red cooler, fresh ginger and lemongrass for a warming infusion, and tree tomato (ibitangatunda/tamarillo) for a tangy fruit base. Steep dried botanicals in just-boiled water for 5-10 minutes, strain, sweeten lightly, and serve hot or over ice depending on season — a simple way to give a venue a distinctly local identity.'],
                        ['title' => 'Pairing Theory: Texture, Weight, Flavour', 'content' => 'Good pairing matches or deliberately contrasts three things: texture (a fizzy drink cuts through a creamy dish), weight (a light drink is overwhelmed by a heavy, rich meal), and flavour (complementary notes reinforce each other, contrasting notes create interest). Decide intentionally whether you want harmony or contrast before choosing what to pour alongside a dish.'],
                    ]],
                    ['title' => 'Module 15: Hot & Cold Beverage Preparation', 'lessons' => [
                        ['title' => 'Hot Chocolate Masterclass', 'content' => 'Real hot chocolate starts with couverture chocolate or high-cocoa-content powder, not a sugary instant mix. Warm milk gently (avoid boiling, which scorches proteins), whisk in the chocolate off the heat until fully emulsified and glossy, and finish with a light froth for texture. Garnish with cocoa dust, a cinnamon stick, or whipped cream for a premium presentation.'],
                        ['title' => 'Matcha & Specialty Teas', 'content' => 'Matcha is whisked, not steeped: sift the powder to remove clumps, add a small amount of water just off the boil (around 80°C, never boiling, which turns matcha bitter), and whisk briskly in a zig-zag motion until frothy. For loose-leaf teas, water temperature and steep time both matter — delicate green teas need cooler water and a shorter steep than robust black teas.'],
                        ['title' => 'Batch Brew & Dispense Systems', 'content' => 'Batch brewing lets a café serve consistent filter coffee at volume without pulling individual pour-overs all day. Dial in one recipe (grind size, coffee-to-water ratio, brew time) for your batch brewer, then hold it in an insulated dispenser for no more than 30-45 minutes before quality drops noticeably — label the brew time so staff know when to dump and rebrew.'],
                    ]],
                    ['title' => 'Module 16: Machine Cleaning & Maintenance', 'lessons' => [
                        ['title' => 'Daily Backflushing Protocols', 'content' => 'At the end of each service day, backflush every espresso group head using a blind filter basket and espresso machine detergent, running several short flush cycles to push cleaning solution through the group\'s internal valve and shower screen. This removes built-up coffee oils that would otherwise turn rancid overnight and taint tomorrow\'s first shots.'],
                        ['title' => 'Group Head & Steam Wand Deep Clean', 'content' => 'Weekly, remove and soak shower screens and dispersion plates in a cleaning solution to dissolve stubborn coffee residue that daily backflushing cannot reach. The steam wand tip should be wiped immediately after every use and purged before and after texturing milk — leftover milk residue inside the tip is a genuine bacteria risk, not just a taste issue.'],
                        ['title' => 'Grinder Burr Maintenance Schedule', 'content' => 'Burrs dull gradually and silently — output can drift for weeks before a barista notices the shots tasting duller. Brush out grounds and chaff daily, deep-clean the hopper and chute weekly, and inspect or replace burrs on the manufacturer\'s recommended cycle (often every 600-1000kg of coffee for commercial grinders). Recalibrate your dial-in immediately after any burr change.'],
                    ]],
                    ['title' => 'Module 17: Tobacco & Cigar Service', 'lessons' => [
                        ['title' => 'Pairing Cigars with Coffee & Spirits', 'content' => 'In upscale lounges, cigar pairing follows the same intensity-matching logic as coffee and food: a full-bodied cigar overwhelms a delicate drink, so pair bold, dark-roasted coffee or aged dark spirits (rum, whisky, cognac) with medium-to-full cigars, and lighter cigars with milder drinks. The goal is that neither the smoke nor the drink cancels the other out.'],
                        ['title' => 'Proper Cutting & Lighting Technique', 'content' => 'Cut a cigar just above the cap line using a guillotine or punch cutter — cutting too much unravels the wrapper. Toast the foot evenly over a flame without touching it directly, rotating the cigar, then draw gently while lighting the rest of the foot. An uneven light causes an uneven, harsh burn for the rest of the smoke.'],
                        ['title' => 'Storage & Humidor Management', 'content' => 'Cigars need stable humidity (68-72%) and temperature (around 18-20°C) to age and smoke properly — too dry and they crack and burn hot, too humid and they mould. Use a calibrated hygrometer, rotate stock so older cigars are offered first, and never store cigars near strong-smelling items, since tobacco leaf absorbs odours easily.'],
                    ]],
                    ['title' => 'Module 18: POS & Point of Sales Operations', 'lessons' => [
                        ['title' => 'POS Navigation & Menu Setup', 'content' => 'A well-built POS menu groups items logically (hot drinks, cold drinks, food, retail), uses modifiers for size and milk options instead of duplicating items, and keeps pricing tiers consistent across the system. Spend time getting this structure right before opening — a messy POS setup slows down every single transaction for the life of the business.'],
                        ['title' => 'Cash Handling & Reconciliation', 'content' => 'Start every shift by counting and recording the till float, and count again at close, reconciling actual cash against what the POS system reports was sold in cash. Any discrepancy should be logged and reported immediately, not adjusted quietly — consistent, transparent cash handling protects both the business and the staff member handling the drawer.'],
                        ['title' => 'Digital Payments & MTN Mobile Money', 'content' => 'Mobile money (MTN MoMo, Airtel Money) is often the fastest and most trusted payment method for many guests in Rwanda. Learn your venue\'s specific process for confirming a mobile money payment before releasing an order — typically a merchant code, a confirmation SMS, or a POS-integrated prompt — and never hand over an order on a promised payment you have not personally verified.'],
                    ]],
                    ['title' => 'Module 19: Coffee Mixology', 'lessons' => [
                        ['title' => 'Espresso Martini & Coffee Cocktails', 'content' => 'The classic espresso martini is vodka, coffee liqueur, and a freshly pulled espresso shot, shaken hard with ice for a thick, lasting foam cap — the shot must be hot and fresh, never a leftover cold one, or the foam and aroma both fall flat. Build variations by swapping the spirit base (rum, whisky) or using flavoured syrups to create a house signature.'],
                        ['title' => 'Cold Brew Infusions & Tinctures', 'content' => 'Cold brew concentrate is a versatile cocktail base: steep coarse-ground coffee in room-temperature water for 12-18 hours, strain, and use the concentrate in place of espresso for a smoother, less acidic coffee flavour in mixed drinks. Fat-washing (infusing a spirit with butter or cream, then freezing and straining) is a more advanced technique for adding a coffee-cream mouthfeel to a spirit.'],
                        ['title' => 'Non-Alcoholic Coffee Mixology', 'content' => 'A great coffee mocktail relies on the same balance principles as a cocktail: acidity, sweetness, and body. Try a coffee-tonic (cold brew topped with tonic water and citrus), a coffee shrub (cold brew with a fruit vinegar syrup), or a spiced coffee cordial mixed with soda — all deliver complexity and theatre without alcohol.'],
                    ]],
                    ['title' => 'Module 20: Home Barista Mastery', 'lessons' => [
                        ['title' => 'Best Home Espresso Machines 2026', 'content' => 'Home machines fall into three categories: manual lever machines (full control, steep learning curve), semi-automatic machines with a separate grinder (the best balance of control and price for serious hobbyists), and super-automatic all-in-one machines (convenient, less control). Look for PID temperature control and a portafilter that matches commercially available baskets when advising students on what to buy.'],
                        ['title' => 'Dialing In for Home Gear', 'content' => 'The same 1:2 ratio and 25-30 second target used commercially still applies at home, but home grinders often have a narrower usable range and less consistent particle size, so patience and small adjustments matter more. Change one variable at a time — grind size first, then dose — and taste after every change rather than guessing multiple adjustments at once.'],
                        ['title' => 'Home Roasting Fundamentals', 'content' => 'Home roasting is possible with simple tools like a modified popcorn popper or a dedicated small-batch home roaster. Track first crack (audible popping, signalling the start of a light roast) and second crack (a sign of a darker roast) by ear and sound, and always rest roasted beans for at least 24-48 hours before brewing to let trapped CO2 degas.'],
                    ]],
                    ['title' => 'Module 21: Interview Performance & Public Speaking', 'lessons' => [
                        ['title' => 'CV & Portfolio Building', 'content' => 'A hospitality CV should lead with concrete, measurable experience — volume handled, certifications earned, competitions entered — rather than vague duties. For baristas, attach a short visual portfolio: a few strong latte art photos, a competition scoresheet if you have one, and any certificates, including this Academy\'s. Keep the whole document to one page for entry to mid-level roles.'],
                        ['title' => 'Mock Interview Techniques', 'content' => 'Use the STAR method (Situation, Task, Action, Result) to answer behavioural questions like "tell me about a time you handled a difficult guest." Practise out loud, not just in your head — the words that sound fine internally often come out clumsy the first time you actually speak them, so rehearsal matters as much as content.'],
                        ['title' => 'Presenting on Stage & Media Presence', 'content' => 'Whether on a competition stage or in front of a camera, slow down more than feels natural — nerves speed up speech and shrink posture. Plant your feet, breathe before you start, and make eye contact with one person at a time rather than scanning the whole room, which reads as more confident and grounded.'],
                    ]],
                    ['title' => 'Module 22: Personal Branding', 'lessons' => [
                        ['title' => 'Defining Your Barista Identity', 'content' => 'A personal brand is simply a consistent answer to "what do you want to be known for?" — technical precision, creative latte art, community teaching, or entrepreneurship. Write down three words that describe the barista you want to become, and let them guide the content, competitions, and opportunities you choose to pursue.'],
                        ['title' => 'Social Media & Content Strategy', 'content' => 'Consistency beats perfection: posting your process — practice pours, failed attempts, small wins — builds a more relatable following than only posting polished final shots. Pick two or three platforms you can maintain regularly rather than spreading thin across every app, and engage genuinely with other creators in the coffee community rather than just broadcasting.'],
                        ['title' => 'Building an Online Coffee Portfolio', 'content' => 'A simple portfolio — even a well-organised Instagram highlight or a one-page website — should show your best latte art, any competition results, certifications, and a short bio. Update it every few months as your skills grow; an outdated portfolio undersells a barista who has improved significantly since it was last touched.'],
                    ]],
                    ['title' => 'Module 23: Rwandan Barista & Coffee Culture', 'lessons' => [
                        ['title' => 'Rwanda in the Specialty Coffee World', 'content' => 'Rwanda is internationally recognised for high-altitude, fully washed Red Bourbon Arabica, grown across regions such as Huye, Nyamasheke, Gakenke, and the shores of Lake Kivu. This reputation for quality and traceability has opened export relationships with specialty roasters worldwide, and gives Rwandan baristas a genuine, marketable origin story to share with guests.'],
                        ['title' => 'Farm-to-Cup Story Telling', 'content' => 'Guests increasingly want to know where their coffee comes from — the washing station, the altitude, the cooperative of smallholder farmers behind it. Learning a short, honest version of your coffee\'s journey from cherry to cup gives every pour-over or espresso extra meaning, and is one of the simplest ways a barista adds value beyond the drink itself.'],
                        ['title' => 'Cultural Etiquette in Coffee Service', 'content' => 'Warm, personal, and respectful service is at the heart of Rwandan hospitality — greeting guests properly, showing patience, and treating every guest as welcome regardless of what they order. Carrying that same warmth into a fast-paced specialty café, without letting speed erode courtesy, is a skill every advanced barista should actively practise.'],
                    ]],
                    ['title' => 'Module 24: Beverage Entrepreneurship', 'lessons' => [
                        ['title' => 'Business Plan for a Coffee Shop', 'content' => 'A workable coffee shop business plan covers five things at minimum: the concept and menu, the target guest and location analysis, a realistic startup and monthly budget, a staffing plan, and a simple 12-month financial projection. Keep the first draft honest and conservative — most new food and beverage businesses take longer to become profitable than founders initially expect.'],
                        ['title' => 'Cost Control & Profitability', 'content' => 'Two numbers drive profitability in any café: food/beverage cost percentage (ingredient cost divided by selling price, typically targeted under 30%) and labour cost percentage. Track both weekly, not just at month end, so a supplier price increase or a scheduling overrun gets caught and corrected before it erodes an entire month\'s margin.'],
                        ['title' => 'Licensing & Regulatory Requirements in Rwanda', 'content' => 'Opening a food and beverage business in Rwanda generally involves business registration (via the Rwanda Development Board), a trading licence from your local sector office, and food safety/health compliance for the premises. Requirements and processes can change, so always confirm the current, exact steps with RDB and your local authority before opening rather than relying on older information.'],
                    ]],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Advanced Barista Certification Exam',
                        'description'        => 'Covers water science, machine maintenance, coffee mixology, personal branding, and beverage entrepreneurship. Passing score is 75%.',
                        'module_index'       => 2,
                        'time_limit_minutes' => 25,
                        'passing_score'      => 75,
                        'max_attempts'       => 3,
                        'questions' => [
                            ['text' => 'What is the SCA-recommended target TDS range for espresso brewing water?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'The SCA Water Quality Standard targets 75-250 ppm TDS, with 150 ppm as an ideal midpoint.', 'options' => [
                                ['text' => '0-25 ppm', 'correct' => false],
                                ['text' => '75-250 ppm', 'correct' => true],
                                ['text' => '500-750 ppm', 'correct' => false],
                                ['text' => '1000+ ppm', 'correct' => false],
                            ]],
                            ['text' => 'Why should a steam wand always be purged before and after texturing milk?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Purging clears condensation before use and removes milk residue after use, which is both a hygiene and bacteria-risk issue.', 'options' => [
                                ['text' => 'To make a louder hissing sound for guests', 'correct' => false],
                                ['text' => 'To clear condensation and remove milk residue that can harbour bacteria', 'correct' => true],
                                ['text' => 'It is only cosmetic and has no hygiene purpose', 'correct' => false],
                                ['text' => 'To cool the wand down faster', 'correct' => false],
                            ]],
                            ['text' => 'True or False: Gelato typically has less overrun (air) and lower fat content than traditional ice cream.', 'type' => 'true_false', 'points' => 12, 'explanation' => 'Gelato is churned slower with less air incorporated and uses less fat, giving a denser, more intense result.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'In an espresso martini, why must the espresso shot be freshly pulled and hot?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'A hot, fresh shot produces the thick, lasting crema/foam cap and full aroma the drink depends on; a cold or old shot falls flat.', 'options' => [
                                ['text' => 'It has no real effect on the drink', 'correct' => false],
                                ['text' => 'It produces the signature foam cap and aroma', 'correct' => true],
                                ['text' => 'It only matters for temperature of the glass', 'correct' => false],
                                ['text' => 'Cold shots actually work better', 'correct' => false],
                            ]],
                            ['text' => 'Which Rwandan coffee variety is most associated with the country\'s specialty coffee reputation?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Rwanda is internationally known for fully washed Red Bourbon Arabica grown at high altitude.', 'options' => [
                                ['text' => 'Robusta', 'correct' => false],
                                ['text' => 'Red Bourbon Arabica', 'correct' => true],
                                ['text' => 'Liberica', 'correct' => false],
                                ['text' => 'Excelsa', 'correct' => false],
                            ]],
                            ['text' => 'What method helps structure a strong answer to a behavioural interview question?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'STAR (Situation, Task, Action, Result) gives interview answers a clear, complete structure.', 'options' => [
                                ['text' => 'The STAR method (Situation, Task, Action, Result)', 'correct' => true],
                                ['text' => 'Speaking as fast as possible', 'correct' => false],
                                ['text' => 'Avoiding eye contact to appear humble', 'correct' => false],
                                ['text' => 'Memorising a script word for word', 'correct' => false],
                            ]],
                            ['text' => 'True or False: A café\'s food cost percentage should generally be tracked only once a month.', 'type' => 'true_false', 'points' => 10, 'explanation' => 'Cost percentages should be tracked weekly so price or waste problems are caught before they erode a full month\'s margin.', 'options' => [
                                ['text' => 'True', 'correct' => false],
                                ['text' => 'False', 'correct' => true],
                            ]],
                            ['text' => 'What is the main risk of using an exhausted (overdue) water filter cartridge?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'A spent filter can shed previously absorbed contaminants back into the water, making it worse than having no filter at all.', 'options' => [
                                ['text' => 'It has no risk beyond slightly worse flavour', 'correct' => false],
                                ['text' => 'It can release previously trapped contaminants back into the water', 'correct' => true],
                                ['text' => 'It makes water taste too clean', 'correct' => false],
                                ['text' => 'It only affects hot water, not cold', 'correct' => false],
                            ]],
                            ['text' => 'What is the single most important habit for building a personal brand on social media as a barista?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Consistency, including sharing process and not just polished results, builds a more relatable and durable following.', 'options' => [
                                ['text' => 'Posting only perfectly polished final results', 'correct' => false],
                                ['text' => 'Posting consistently, including process and progress', 'correct' => true],
                                ['text' => 'Posting as rarely as possible to seem exclusive', 'correct' => false],
                                ['text' => 'Copying another barista\'s exact content', 'correct' => false],
                            ]],
                        ],
                    ],
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
                'resync' => true,
                'modules' => [
                    ['title' => 'Module 01: Introduction to Bartending & Bar Tools', 'lessons' => [
                        ['title' => 'Essential Bar Tools & Equipment', 'content' => 'Every bartender station needs a core toolkit: a Boston or cobbler shaker, a jigger for accurate measuring, a bar spoon for stirring and layering, a Hawthorne strainer, a muddler, a citrus juicer, and a speed pourer for each open bottle. Learn each tool by feel, not just by name — a confident bartender can grab the right tool without looking while keeping eye contact with the guest.'],
                        ['title' => 'Glassware Guide', 'content' => 'Glassware signals the drink before a sip is taken: a coupe or martini glass for stirred, spirit-forward drinks; a rocks glass for drinks served over ice; a highball or Collins glass for long, fizzy drinks; and a wine glass or flute for wine and sparkling service. Serving a drink in the wrong glass is a small but noticeable professionalism gap.'],
                        ['title' => 'Station Setup & Mise en Place', 'content' => 'Before service, stock your well with the most-used spirits and mixers, pre-cut standard garnishes, fill ice bins, and check that juices and syrups are fresh and labelled with a date. A bartender who has to leave the station mid-rush to fetch a missing ingredient loses both time and guest confidence.'],
                    ]],
                    ['title' => 'Module 02: Spirits Knowledge', 'lessons' => [
                        ['title' => 'Whisky, Rum, Gin, Vodka, Tequila Origins', 'content' => 'Whisky is grain-based and aged in wood (Scotch, Bourbon, Irish, Japanese styles all differ by grain and rules); rum comes from sugarcane juice or molasses; gin is a neutral spirit redistilled with botanicals, chiefly juniper; vodka is a highly filtered neutral spirit valued for purity; and tequila is distilled specifically from blue agave grown in defined regions of Mexico. Knowing the base ingredient of each spirit explains most of their flavour differences.'],
                        ['title' => 'Distillation & Aging Processes', 'content' => 'Distillation concentrates alcohol by heating a fermented liquid and capturing the vapour, which is why the still shape and number of distillation runs affect a spirit\'s character. Aging in wooden barrels (usually oak) adds colour, vanilla and caramel notes, and softens harshness over time — the longer and more active the barrel, the more pronounced these effects, up to a point of diminishing returns.'],
                        ['title' => 'Reading Spirit Labels & ABV', 'content' => 'ABV (Alcohol By Volume) tells you the strength of a spirit and directly affects how much a drink recipe needs adjusting for balance. Labels also disclose age statements (the youngest spirit in the blend), region of origin, and sometimes cask type — all of which help you set guest expectations and recommend substitutions confidently when a requested brand is unavailable.'],
                    ]],
                    ['title' => 'Module 03: Cocktail Foundations', 'lessons' => [
                        ['title' => 'Mixing Techniques: Stir, Shake, Build, Muddle', 'content' => 'Stir spirit-only drinks (like a Martini or Manhattan) to chill and dilute without clouding them; shake drinks containing juice, dairy, or egg to fully integrate and aerate; build drinks directly in the serving glass when layering or simplicity matters (like a Gin & Tonic); and muddle fresh ingredients like mint or fruit to release oils and juice without pulverising them into bitterness.'],
                        ['title' => 'Classic Cocktail Families', 'content' => 'Most classic cocktails fall into a handful of families: sours (spirit, citrus, sweetener — e.g. Whiskey Sour), old fashioneds (spirit, sugar, bitters), highballs (spirit plus a longer mixer, e.g. Gin & Tonic), and Martinis/Manhattans (spirit plus vermouth). Learning the family structure lets you build dozens of variations from a handful of ratios instead of memorising every recipe individually.'],
                        ['title' => 'Balance: Sweet, Sour, Strong, Weak', 'content' => 'The classic sour formula — 2 parts strong (spirit), 1 part sour (citrus), 1 part sweet (syrup) — is the starting ratio behind most well-balanced cocktails. Taste as you build: if a drink feels flat, it usually needs more acid or spirit; if it is harsh, it usually needs more sweetness or dilution.'],
                    ]],
                    ['title' => 'Module 04: Wine & Beer Basics for Bartenders', 'lessons' => [
                        ['title' => 'Wine Service by the Glass', 'content' => 'Store opened wine bottles properly (reds cool and dark, whites and sparkling chilled), pour standard glass measures consistently (typically 125-150ml), and know your by-the-glass list well enough to describe body, sweetness, and a simple food pairing for each option without hesitation.'],
                        ['title' => 'Beer Styles & Draught Service', 'content' => 'Lagers are crisp and clean-fermented cold; ales are fruitier and fermented warmer; stouts and porters are dark and roasted. For draught service, pour at the correct angle to build a proper head (roughly 2-3 fingers), keep lines clean to avoid off-flavours, and serve in the glass style suited to that beer.'],
                        ['title' => 'Food Pairing Fundamentals', 'content' => 'As with coffee, pairing wine or beer with food is about matching or contrasting weight and intensity: a light beer or crisp white wine suits delicate dishes, while a bold red or a rich stout stands up to heavier, fattier food. High-acid wines and beers cut through fat particularly well.'],
                    ]],
                    ['title' => 'Module 05: Mocktails & Non-Alcoholic Craft', 'lessons' => [
                        ['title' => 'Zero-Proof Cocktail Design', 'content' => 'A good mocktail is not just soda with fruit — it needs the same structural balance as a real cocktail: acid, sweetness, and something bitter or complex to replace the depth alcohol normally provides, such as a shrub, bitters, or a strong tea reduction.'],
                        ['title' => 'Shrubs, Syrups & House-Made Mixers', 'content' => 'A shrub (fruit, sugar, and vinegar, steeped and strained) adds tang and complexity that plain juice cannot. House-made syrups — simple, flavoured, or spiced — let you control sweetness precisely and create a signature flavour guests cannot get anywhere else.'],
                        ['title' => 'Presentation & Garnishing', 'content' => 'A mocktail is often judged more on presentation than a cocktail, since guests may expect it to feel like "less". Invest in the same glassware, ice quality, and garnish care (fresh herbs, citrus twists, edible flowers) as your alcoholic menu so non-drinking guests feel equally considered.'],
                    ]],
                    ['title' => 'Module 06: Responsible Alcohol Service', 'lessons' => [
                        ['title' => 'Signs of Intoxication', 'content' => 'Watch for slurred speech, unsteady movement, overly loud or argumentative behaviour, and slowed reactions. Catching early signs lets you slow service (space out drinks, offer water and food) before a guest becomes a safety risk to themselves or others.'],
                        ['title' => 'Refusing Service Professionally', 'content' => 'Refusing service is a safety duty, not a punishment. Stay calm, be direct without being confrontational, offer water or food as an alternative, and involve a manager or security if a guest becomes difficult — never argue publicly or make a guest feel humiliated in front of others.'],
                        ['title' => 'Rwandan Alcohol Regulations', 'content' => 'Rwanda regulates the sale and service of alcohol, including licensing requirements for venues and age restrictions for purchase. Requirements can be updated by local authorities, so always confirm your venue\'s current licence conditions and legal serving age with management or the relevant local authority rather than relying on general assumptions.'],
                    ]],
                    ['title' => 'Module 07: Bar Operations & Hygiene', 'lessons' => [
                        ['title' => 'Cleaning & Sanitation Protocols', 'content' => 'Wipe down and sanitise surfaces, ice wells, and tool caddies throughout service, not just at close. Rotate bar mats and glass-washing water regularly, and never let dirty glassware or spent garnish accumulate at the station during a rush — it slows you down and looks unprofessional to guests seated at the bar.'],
                        ['title' => 'Temperature Control for Perishables', 'content' => 'Fresh juices, dairy, and garnishes belong in refrigeration below 5°C whenever not actively in use at the station, with clear labelling of prep dates. Perishables left warm at a busy bar top for hours are a genuine food-safety hazard, not just a quality issue.'],
                        ['title' => 'Pest Control Awareness', 'content' => 'Sugary spills, fruit peels, and standing liquid attract pests quickly in a bar environment. Clean spills immediately, store fruit and garnish properly sealed, and report any signs of pest activity to management right away rather than treating it as a minor issue.'],
                    ]],
                    ['title' => 'Module 08: Bar Menu Development', 'lessons' => [
                        ['title' => 'Signature Cocktail Ideation', 'content' => 'Start signature drinks from a flavour concept (a local fruit, a spice, a theme) rather than starting from a spirit — this produces more memorable, marketable drinks. Test recipes with real guests before finalising them on a printed menu.'],
                        ['title' => 'Menu Costing & Pricing', 'content' => 'Cost every ingredient in a recipe down to the millilitre and gram, then apply your venue\'s target pour cost percentage (commonly 18-24% for cocktails) to set a price that protects margin while staying competitive for your market.'],
                        ['title' => 'Seasonal & Local Ingredient Menus', 'content' => 'Rotating a portion of the menu seasonally keeps it fresh, controls cost by using ingredients at their cheapest and best, and gives regular guests a reason to keep coming back to see what is new.'],
                    ]],
                    ['title' => 'Module 09: Guest Experience & Upselling', 'lessons' => [
                        ['title' => 'Reading Guest Preferences', 'content' => 'Listen for cues — a guest ordering their usual brand, hesitating over the menu, or asking "what do you recommend" — and tailor suggestions accordingly. A confident, specific recommendation ("try our smoked Old Fashioned, it is a house favourite") converts better than a vague "anything you\'d like."'],
                        ['title' => 'Upselling Premiums & Specials', 'content' => 'Upselling works best when it is framed as adding value, not just adding cost — suggesting a premium spirit upgrade or a food pairing because it genuinely improves the experience. Never push an upsell so hard it feels like pressure; that damages trust and repeat business.'],
                        ['title' => 'Handling Difficult Situations', 'content' => 'Stay calm, listen fully before responding, and involve a manager early for anything beyond a simple complaint. Most difficult guest situations de-escalate simply because the guest feels heard, even before a solution is offered.'],
                    ]],
                    ['title' => 'Module 10: Bar Management & Leadership', 'lessons' => [
                        ['title' => 'Stock Control & Wastage Reduction', 'content' => 'Run regular stock counts against POS sales data to catch over-pouring, spillage, or theft early. Track high-wastage items (fresh juices, garnishes) closely, since they expire fastest and are the easiest source of hidden cost.'],
                        ['title' => 'Leading a Bar Team', 'content' => 'A good bar lead sets the pace during a rush, checks in on struggling team members without micromanaging, and gives feedback immediately after service while it is still specific and useful, not days later when the details are forgotten.'],
                        ['title' => 'Bar P&L Basics', 'content' => 'A bar\'s profit and loss statement is driven by three levers: revenue (volume and pricing), cost of goods (pour cost, wastage), and labour cost. Reviewing all three together weekly, rather than any one in isolation, gives an accurate picture of whether the bar is actually profitable.'],
                    ]],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Professional Bartender Certification Exam',
                        'description'        => 'Covers spirits knowledge, cocktail balance, responsible alcohol service, and bar management. Passing score is 75%.',
                        'module_index'       => 9,
                        'time_limit_minutes' => 25,
                        'passing_score'      => 75,
                        'max_attempts'       => 3,
                        'questions' => [
                            ['text' => 'What is the classic sour-formula ratio of strong : sour : sweet used as a starting point for many cocktails?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'The classic sour formula is 2 parts spirit, 1 part citrus, 1 part sweetener.', 'options' => [
                                ['text' => '2 : 1 : 1', 'correct' => true],
                                ['text' => '1 : 1 : 1', 'correct' => false],
                                ['text' => '4 : 1 : 1', 'correct' => false],
                                ['text' => '1 : 2 : 2', 'correct' => false],
                            ]],
                            ['text' => 'Which mixing technique is correct for a drink containing fresh juice or dairy?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Shaking fully integrates and aerates juice, dairy, or egg-based ingredients.', 'options' => [
                                ['text' => 'Stir', 'correct' => false],
                                ['text' => 'Shake', 'correct' => true],
                                ['text' => 'Build only', 'correct' => false],
                                ['text' => 'Flame', 'correct' => false],
                            ]],
                            ['text' => 'True or False: Spirit-only drinks like a Martini or Manhattan are typically stirred, not shaken, to avoid clouding and over-dilution.', 'type' => 'true_false', 'points' => 12, 'explanation' => 'Stirring chills and dilutes spirit-forward drinks without the excess aeration and ice shards shaking would cause.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'Tequila must be distilled specifically from which plant?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Tequila is distilled from blue agave grown in defined regions of Mexico.', 'options' => [
                                ['text' => 'Sugarcane', 'correct' => false],
                                ['text' => 'Blue agave', 'correct' => true],
                                ['text' => 'Juniper', 'correct' => false],
                                ['text' => 'Barley', 'correct' => false],
                            ]],
                            ['text' => 'What should a bartender do first when a guest shows early signs of intoxication?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Slowing service and offering water/food addresses the issue early, before it becomes a safety risk.', 'options' => [
                                ['text' => 'Serve a stronger drink to finish the visit faster', 'correct' => false],
                                ['text' => 'Slow down service and offer water or food', 'correct' => true],
                                ['text' => 'Ignore it unless the guest asks for help', 'correct' => false],
                                ['text' => 'Immediately call the police without warning', 'correct' => false],
                            ]],
                            ['text' => 'True or False: Upselling should feel like pressure to maximise revenue on every check.', 'type' => 'true_false', 'points' => 10, 'explanation' => 'Effective upselling adds genuine value; pushing too hard damages guest trust and repeat business.', 'options' => [
                                ['text' => 'True', 'correct' => false],
                                ['text' => 'False', 'correct' => true],
                            ]],
                            ['text' => 'What is "pour cost percentage" used for in bar menu pricing?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Pour cost percentage (ingredient cost vs. selling price) is used to set prices that protect margin.', 'options' => [
                                ['text' => 'Measuring how fast a bartender pours', 'correct' => false],
                                ['text' => 'Setting drink prices based on ingredient cost vs. selling price', 'correct' => true],
                                ['text' => 'Tracking how many guests order beer vs. cocktails', 'correct' => false],
                                ['text' => 'Measuring glass breakage rates', 'correct' => false],
                            ]],
                            ['text' => 'Why should perishables like fresh juice and dairy be kept refrigerated at the bar station?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Perishables left warm for extended periods are a genuine food-safety hazard, not just a quality concern.', 'options' => [
                                ['text' => 'It only affects taste, not safety', 'correct' => false],
                                ['text' => 'Warm perishables are a food-safety hazard', 'correct' => true],
                                ['text' => 'Refrigeration is only needed overnight', 'correct' => false],
                                ['text' => 'It has no real effect either way', 'correct' => false],
                            ]],
                            ['text' => 'A bar\'s profit and loss is primarily driven by which three factors?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Revenue, cost of goods (pour cost/wastage), and labour cost together determine bar profitability.', 'options' => [
                                ['text' => 'Music, lighting, and décor', 'correct' => false],
                                ['text' => 'Revenue, cost of goods, and labour cost', 'correct' => true],
                                ['text' => 'Number of glass types used', 'correct' => false],
                                ['text' => 'Number of social media posts', 'correct' => false],
                            ]],
                        ],
                    ],
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
                'resync' => true,
                'modules' => [
                    ['title' => 'Module 01: Introduction to Wine & Viticulture', 'lessons' => [
                        ['title' => 'How Wine is Made', 'content' => 'Wine is grape juice fermented by yeast, which converts sugar into alcohol and carbon dioxide. Red wine ferments with grape skins to extract colour and tannin; white wine is typically pressed and fermented without skin contact. From there, ageing (in steel, oak, or bottle), fining, and filtering shape the final style before bottling.'],
                        ['title' => 'The Vine, Terroir & Climate', 'content' => '"Terroir" describes how soil, climate, altitude, and slope together shape a wine\'s character — the same grape variety can taste dramatically different grown in a cool, high-altitude site versus a hot, low-altitude one. Cooler climates generally give higher acidity and lighter body; warmer climates give riper fruit and higher alcohol.'],
                        ['title' => 'Major Wine Producing Countries', 'content' => 'Old World producers (France, Italy, Spain, Portugal, Germany) built the classic regional naming system, where a wine is often named for its place, not its grape. New World producers (USA, Australia, Chile, Argentina, South Africa) typically label by grape variety and tend toward riper, fruit-forward styles, though both worlds now overlap significantly in technique.'],
                    ]],
                    ['title' => 'Module 02: Systematic Approach to Tasting', 'lessons' => [
                        ['title' => 'Sight, Nose, Palate Framework', 'content' => 'The Systematic Approach to Tasting (SAT) moves through three stages: sight (colour and clarity, which hint at age and grape), nose (aroma intensity and character, assessed before and after swirling), and palate (sweetness, acidity, tannin, body, flavour, and finish length). Working through the stages in order trains consistent, comparable tasting notes.'],
                        ['title' => 'Wine Faults & Defects', 'content' => 'Common faults include cork taint (a damp cardboard smell from TCA contamination), oxidation (a flat, sherry-like character in a wine that should be fresh), and volatile acidity (a nail-polish-remover smell from excess acetic acid). Learning to identify faults quickly lets a sommelier confidently and professionally return a bad bottle.'],
                        ['title' => 'Describing Wine Professionally', 'content' => 'Professional tasting notes are structured, not just a list of adjectives: describe appearance, then aroma categories (fruit, floral, earthy, oak-derived), then structure (acidity, tannin, body, alcohol, finish). This structure lets another sommelier understand a wine from your notes alone, without tasting it themselves.'],
                    ]],
                    ['title' => 'Module 03: Red Wine Varietals & Regions', 'lessons' => [
                        ['title' => 'Cabernet Sauvignon & Bordeaux', 'content' => 'Cabernet Sauvignon is a thick-skinned, high-tannin grape giving structured, age-worthy wines with blackcurrant and cedar notes. In Bordeaux, it is typically blended with Merlot and other varieties, with the exact blend and dominant grape shifting by bank (Left Bank favours Cabernet, Right Bank favours Merlot).'],
                        ['title' => 'Pinot Noir & Burgundy', 'content' => 'Pinot Noir is thin-skinned and notoriously difficult to grow, producing light-to-medium bodied, high-acid wines with red fruit and earthy notes. Burgundy is its spiritual home, where tiny differences in vineyard plot (climat) are prized enough to be individually classified and priced.'],
                        ['title' => 'Malbec, Shiraz & New World Reds', 'content' => 'Malbec, largely associated with Argentina\'s Mendoza region, gives deep colour and plush dark-fruit character. Shiraz (Syrah in the Old World) ranges from peppery and medium-bodied in cooler France to bold, jammy, and full-bodied in warmer Australia — the same grape, dramatically shaped by climate.'],
                    ]],
                    ['title' => 'Module 04: White, Rosé & Sparkling Wines', 'lessons' => [
                        ['title' => 'Chardonnay & Burgundy Whites', 'content' => 'Chardonnay is a flexible grape that reflects winemaking choices heavily: unoaked versions are crisp and citrus-driven, while oaked, malolactic-fermented versions (classic White Burgundy style) are richer, creamier, and show butter and vanilla notes.'],
                        ['title' => 'Champagne Method & Prosecco', 'content' => 'Champagne undergoes a second fermentation inside the bottle (méthode traditionnelle), creating fine, long-lasting bubbles and complex, often bread-like aromas from lees ageing. Prosecco is typically made with a second fermentation in a pressurised tank instead (Charmat method), giving fresher, fruitier, simpler bubbles at a lower cost.'],
                        ['title' => 'Rosé Styles & Skin Contact', 'content' => 'Rosé gets its colour from brief contact with red grape skins before the juice is drained off — a few hours for a pale, delicate rosé versus longer for a deeper, more structured style. It is a genuine winemaking category, not simply a blend of red and white wine (which is only standard practice for rosé Champagne).'],
                    ]],
                    ['title' => 'Module 05: Food & Wine Pairing Principles', 'lessons' => [
                        ['title' => 'Matching Weight, Texture & Flavour', 'content' => 'As with any beverage pairing, match the weight of the wine to the weight of the dish — a delicate white overwhelmed by a heavy stew, or a light dish lost next to a bold red. High-acid wines cut through fat and richness; tannic reds pair well with protein and fat, which soften the tannin\'s grip.'],
                        ['title' => 'Regional Pairing Logic', 'content' => 'A reliable starting rule: "what grows together, goes together." Regional dishes and regional wines evolved side by side for generations, which is why a Chianti with Tuscan tomato-based dishes, or a crisp Loire white with fresh seafood, so often works effortlessly.'],
                        ['title' => 'Building a Pairing Menu', 'content' => 'When building a pairing menu, plan the wine progression across a meal from lighter to fuller-bodied, and whites before reds where the menu allows, so each pairing does not get overwhelmed by what came before it.'],
                    ]],
                    ['title' => 'Module 06: Wine List Reading & Recommendations', 'lessons' => [
                        ['title' => 'Navigating Restaurant Wine Lists', 'content' => 'Wine lists are often organised by style (sparkling, white, red, dessert) and then by region or body within each category. Learn to scan quickly for familiar regions and grapes so you can guide a guest confidently even under time pressure during service.'],
                        ['title' => 'Value vs Premium Recommendations', 'content' => 'Every wine list has "sweet spot" bottles — quality wines priced modestly because they are from a lesser-known region or producer. Knowing these lets you serve guests genuinely well at any budget, not just push the most expensive bottle.'],
                        ['title' => 'Budget Dialogue with Guests', 'content' => 'Ask about budget indirectly and respectfully — for example, pointing to a mid-list price range and asking if that is the right area — rather than asking a guest to state a number outright, which can feel awkward in front of company.'],
                    ]],
                    ['title' => 'Module 07: Tableside Service & Decanting', 'lessons' => [
                        ['title' => 'Presenting & Opening Wine', 'content' => 'Present the bottle label-out for guest confirmation before opening, cut the foil cleanly below the lip, remove the cork without excess noise or force, and offer a small taste to the host before pouring for the table — this is a check for fault, not a formality to skip.'],
                        ['title' => 'Decanting Protocols for Old World Reds', 'content' => 'Decant older, tannic reds gently to separate sediment and allow careful aeration, and decant young, closed wines more vigorously to open up aroma faster. Always check for sediment beforehand and pour slowly against the light so it stays behind in the bottle.'],
                        ['title' => 'Managing Service Flow', 'content' => 'Keep glasses topped appropriately (not overfilled, which limits aroma and swirling room), track which guests are on which wine during multi-course pairings, and time pours so a course does not run out of wine before the next pairing arrives.'],
                    ]],
                    ['title' => 'Module 08: Fortified Wines & Digestifs', 'lessons' => [
                        ['title' => 'Port, Sherry & Madeira', 'content' => 'Fortified wines have a spirit added during or after fermentation, raising alcohol and often stopping fermentation early to retain sweetness. Port (Portugal) is typically sweet and rich; Sherry (Spain) ranges from bone-dry to lusciously sweet; Madeira is deliberately heat-aged, giving it remarkable stability even once opened.'],
                        ['title' => 'Cognac & Armagnac', 'content' => 'Both are grape brandies from France, aged in oak. Cognac undergoes double distillation and comes from a defined region near Bordeaux; Armagnac is typically distilled once, giving a slightly more rustic, intense character, and comes from Gascony.'],
                        ['title' => 'After-Dinner Service Etiquette', 'content' => 'Serve digestifs in appropriately small glassware, at the correct temperature (many fortified wines and brandies are best slightly below room temperature or gently warmed by hand), and pace the offer so it feels like a natural close to the meal rather than a pushed upsell.'],
                    ]],
                    ['title' => 'Module 09: Wine Cellar Management', 'lessons' => [
                        ['title' => 'Cellar Temperature & Humidity', 'content' => 'Wine ages best in stable conditions: around 12-14°C with 60-70% humidity, away from vibration and direct light. Fluctuating temperature is more damaging over time than a slightly imperfect but stable one.'],
                        ['title' => 'Bin Management & FIFO', 'content' => 'Organise cellar bins by region or style, and rotate stock so older vintages are tracked and used or sold before newer ones, especially for wines with a shorter drinking window.'],
                        ['title' => 'Stock Valuation & Cost Control', 'content' => 'Track cellar value regularly against your POS wine sales to catch shrinkage (breakage, theft, over-pour) early, and set by-the-glass pricing based on actual bottle cost plus wastage risk from an opened bottle not being fully sold.'],
                    ]],
                    ['title' => 'Module 10: African Wine Landscape', 'lessons' => [
                        ['title' => 'South African Wine Regions', 'content' => 'South Africa, centred on the Western Cape (Stellenbosch, Franschhoek, Constantia), is Africa\'s dominant wine-producing country, known for Chenin Blanc, Pinotage (a local cross of Pinot Noir and Cinsault), and increasingly acclaimed Cabernet and Syrah.'],
                        ['title' => 'East African Emerging Wine Tourism', 'content' => 'While East Africa is not a traditional wine-producing region, growing hospitality and tourism demand is expanding wine list sophistication in hotels and restaurants across the region, creating real opportunity for sommeliers trained to guide guests through imported selections confidently.'],
                        ['title' => 'Rwandan Hospitality & Wine Culture', 'content' => 'As Rwanda\'s hospitality and fine-dining sector grows, sommeliers who combine international wine knowledge with the country\'s renowned standard of warm, attentive service are well positioned to lead wine programmes in hotels, lodges, and upscale restaurants.'],
                    ]],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Sommelier Fundamentals Certification Exam',
                        'description'        => 'Covers winemaking, tasting methodology, varietals, tableside service, and cellar management. Passing score is 75%.',
                        'module_index'       => 9,
                        'time_limit_minutes' => 25,
                        'passing_score'      => 75,
                        'max_attempts'       => 3,
                        'questions' => [
                            ['text' => 'What is the main difference in how red and white wine are typically fermented?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Red wine ferments with grape skins to extract colour and tannin; white wine is usually fermented without skin contact.', 'options' => [
                                ['text' => 'Red ferments with skins, white typically without', 'correct' => true],
                                ['text' => 'White ferments hotter than red', 'correct' => false],
                                ['text' => 'There is no meaningful difference', 'correct' => false],
                                ['text' => 'Red is never fermented, only white', 'correct' => false],
                            ]],
                            ['text' => 'In the Systematic Approach to Tasting (SAT), what is assessed after sight and before palate?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'The SAT framework moves through sight, then nose (aroma), then palate.', 'options' => [
                                ['text' => 'Price', 'correct' => false],
                                ['text' => 'Nose (aroma)', 'correct' => true],
                                ['text' => 'Label design', 'correct' => false],
                                ['text' => 'Bottle weight', 'correct' => false],
                            ]],
                            ['text' => 'True or False: A damp cardboard smell in a glass of wine is a classic sign of cork taint (TCA).', 'type' => 'true_false', 'points' => 12, 'explanation' => 'Cork taint (TCA contamination) is classically described as a damp cardboard or wet newspaper smell.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'Which grape is Burgundy most famous for producing as a red wine?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Pinot Noir is Burgundy\'s signature red grape.', 'options' => [
                                ['text' => 'Cabernet Sauvignon', 'correct' => false],
                                ['text' => 'Pinot Noir', 'correct' => true],
                                ['text' => 'Malbec', 'correct' => false],
                                ['text' => 'Tempranillo', 'correct' => false],
                            ]],
                            ['text' => 'What gives Champagne its fine, long-lasting bubbles?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Champagne undergoes a second fermentation inside the sealed bottle (méthode traditionnelle).', 'options' => [
                                ['text' => 'A second fermentation inside the bottle', 'correct' => true],
                                ['text' => 'Added carbon dioxide from a machine', 'correct' => false],
                                ['text' => 'Shaking the bottle before service', 'correct' => false],
                                ['text' => 'Serving it colder than other wines', 'correct' => false],
                            ]],
                            ['text' => 'When presenting a bottle tableside, what should happen before pouring for the whole table?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'A small taste is offered to the host first as a check for fault, not just a formality.', 'options' => [
                                ['text' => 'Pour everyone a full glass immediately', 'correct' => false],
                                ['text' => 'Offer a small taste to the host to check for fault', 'correct' => true],
                                ['text' => 'Remove the label so guests cannot see the price', 'correct' => false],
                                ['text' => 'Chill the bottle further regardless of style', 'correct' => false],
                            ]],
                            ['text' => 'True or False: "What grows together, goes together" is a useful starting rule for food and wine pairing.', 'type' => 'true_false', 'points' => 10, 'explanation' => 'Regional dishes and regional wines evolved together, which is why they so often pair naturally.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'What are the recommended stable conditions for a wine cellar?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Around 12-14°C with 60-70% humidity, away from vibration and light, kept stable over time.', 'options' => [
                                ['text' => 'As cold as possible, below freezing', 'correct' => false],
                                ['text' => 'Around 12-14°C with 60-70% humidity, kept stable', 'correct' => true],
                                ['text' => 'Room temperature with direct sunlight', 'correct' => false],
                                ['text' => 'Any temperature, as long as it is dark', 'correct' => false],
                            ]],
                            ['text' => 'Which South African grape is a local cross of Pinot Noir and Cinsault?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Pinotage is South Africa\'s signature grape, a cross of Pinot Noir and Cinsault.', 'options' => [
                                ['text' => 'Pinotage', 'correct' => true],
                                ['text' => 'Chenin Blanc', 'correct' => false],
                                ['text' => 'Zinfandel', 'correct' => false],
                                ['text' => 'Grenache', 'correct' => false],
                            ]],
                        ],
                    ],
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
                'resync' => true,
                'modules' => [
                    ['title' => 'Module 01: Introduction to Food Safety', 'lessons' => [
                        ['title' => 'Why Food Safety Matters', 'content' => 'A single food safety failure can make guests seriously ill, close a business, and destroy a reputation built over years in a single review or headline. Food safety is not a bureaucratic formality — it is the baseline promise every hospitality business makes to every guest who trusts them with a meal.'],
                        ['title' => 'Foodborne Illnesses & Outbreaks', 'content' => 'Common foodborne pathogens include Salmonella (poultry, eggs), E. coli (undercooked beef, contaminated produce), and Norovirus (spread by infected food handlers). Symptoms often appear hours to days after exposure, which is why tracing an outbreak back to its source requires careful, honest record-keeping of what was served and when.'],
                        ['title' => 'Rwanda Food Safety Regulations', 'content' => 'Food businesses in Rwanda are expected to meet hygiene and safety standards set and enforced by relevant national bodies, including the Rwanda Standards Board (RSB) and local health authorities. Regulations and enforcement processes can be updated, so confirm current requirements directly with RSB or your local authority rather than relying on outdated information.'],
                    ]],
                    ['title' => 'Module 02: Personal Hygiene', 'lessons' => [
                        ['title' => 'Handwashing Technique & Frequency', 'content' => 'Wash hands with soap and warm water for at least 20 seconds, covering between fingers, thumbs, and under nails, and dry with a clean or disposable towel. Wash before starting work, after handling raw food, after using the toilet, after touching your face or hair, and after handling waste or money.'],
                        ['title' => 'Protective Clothing Standards', 'content' => 'Clean uniforms, tied-back hair or a hairnet/hat, and closed, non-slip footwear are minimum standards in any food environment. Remove aprons before leaving the food prep area (such as visiting the toilet) and change them if visibly soiled during a shift.'],
                        ['title' => 'When to Exclude from Food Handling', 'content' => 'Staff experiencing vomiting, diarrhoea, an infected wound, or a contagious illness should be excluded from food handling duties until fully recovered (commonly 48 hours symptom-free for gastrointestinal illness), to prevent contaminating food or surfaces.'],
                    ]],
                    ['title' => 'Module 03: Temperature Control', 'lessons' => [
                        ['title' => 'Danger Zone: 5°C–63°C', 'content' => 'Bacteria multiply fastest between roughly 5°C and 63°C — the "danger zone." Food should spend as little time as possible in this range: keep cold food at or below 5°C, hot food at or above 63°C, and never leave perishable food out at room temperature for more than about two hours.'],
                        ['title' => 'Chilling, Freezing & Thawing', 'content' => 'Cool hot food quickly before refrigerating (within about 2 hours) to avoid it lingering in the danger zone, freeze at -18°C or below, and always thaw frozen food in the refrigerator, under cold running water, or in a microwave as part of continuous cooking — never at room temperature on a counter.'],
                        ['title' => 'Calibrating Food Thermometers', 'content' => 'A probe thermometer is only useful if it is accurate — check calibration regularly using an ice-water bath (should read 0°C) or boiling water (should read 100°C at sea level), and clean and sanitise the probe between uses to avoid cross-contaminating different foods.'],
                    ]],
                    ['title' => 'Module 04: Cross-Contamination Prevention', 'lessons' => [
                        ['title' => 'Colour-Coded Cutting Board System', 'content' => 'A standard colour-coded system assigns separate boards for raw meat (red), raw poultry (yellow), seafood (blue), fruit and vegetables (green), dairy (white), and cooked/ready-to-eat food (brown or another dedicated colour) — preventing pathogens from raw food transferring to food that will not be cooked further.'],
                        ['title' => 'Raw vs Ready-to-Eat Separation', 'content' => 'Store raw meat and poultry below and separate from ready-to-eat food in refrigeration to prevent drips contaminating food that will not be cooked again, and use separate utensils, plates, and prep surfaces for raw and ready-to-eat items throughout service.'],
                        ['title' => 'Cleaning vs Sanitizing', 'content' => 'Cleaning removes visible dirt and food debris using detergent and water; sanitising then reduces germs on that already-clean surface to a safe level using heat or a chemical sanitiser. Skipping straight to sanitising a dirty surface does not work — cleaning must always happen first.'],
                    ]],
                    ['title' => 'Module 05: HACCP Principles & Implementation', 'lessons' => [
                        ['title' => '7 HACCP Principles Explained', 'content' => 'HACCP\'s seven principles are: conduct a hazard analysis, identify critical control points (CCPs), establish critical limits, establish monitoring procedures, establish corrective actions, establish verification procedures, and establish record-keeping. Together they turn food safety from a vague intention into a specific, checkable system.'],
                        ['title' => 'Completing a CCP Monitoring Log', 'content' => 'A CCP log records what was checked (e.g. fridge temperature), the reading, the time, who checked it, and any action taken if the reading was out of range. Consistent, honest logging is what allows a business to prove due diligence if a food safety incident is ever investigated.'],
                        ['title' => 'Corrective Actions & Verification', 'content' => 'When a critical limit is breached (for example, a fridge reads above 5°C), a corrective action defines exactly what happens next — move stock, call a technician, discard affected food — and verification confirms the fix actually worked, closing the loop rather than just noting the problem and moving on.'],
                    ]],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Food Safety & HACCP Certification Exam',
                        'description'        => 'Covers personal hygiene, temperature control, cross-contamination prevention, and HACCP principles. Passing score is 80%.',
                        'module_index'       => 4,
                        'time_limit_minutes' => 20,
                        'passing_score'      => 80,
                        'max_attempts'       => 3,
                        'questions' => [
                            ['text' => 'What is the temperature "danger zone" in which bacteria multiply fastest?', 'type' => 'single_choice', 'points' => 15, 'explanation' => 'The danger zone is roughly 5°C to 63°C.', 'options' => [
                                ['text' => '5°C – 63°C', 'correct' => true],
                                ['text' => '-18°C – 0°C', 'correct' => false],
                                ['text' => '63°C – 100°C', 'correct' => false],
                                ['text' => '0°C – 5°C', 'correct' => false],
                            ]],
                            ['text' => 'How long should hands be washed with soap and water to be effective?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'At least 20 seconds, covering between fingers, thumbs, and under nails.', 'options' => [
                                ['text' => 'At least 20 seconds', 'correct' => true],
                                ['text' => 'A quick 2-3 second rinse', 'correct' => false],
                                ['text' => 'Only when visibly dirty', 'correct' => false],
                                ['text' => 'Once at the start of each day only', 'correct' => false],
                            ]],
                            ['text' => 'True or False: A staff member with active vomiting or diarrhoea should continue food handling duties if they feel well enough to work.', 'type' => 'true_false', 'points' => 12, 'explanation' => 'Staff with gastrointestinal illness should be excluded from food handling until fully recovered, commonly 48 hours symptom-free.', 'options' => [
                                ['text' => 'True', 'correct' => false],
                                ['text' => 'False', 'correct' => true],
                            ]],
                            ['text' => 'What must always happen to a surface before it is sanitised?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Cleaning (removing visible dirt/debris) must happen before sanitising, which reduces germs on an already-clean surface.', 'options' => [
                                ['text' => 'It must be cleaned first', 'correct' => true],
                                ['text' => 'It must be left wet', 'correct' => false],
                                ['text' => 'Nothing, sanitising alone is always enough', 'correct' => false],
                                ['text' => 'It must be heated to boiling first', 'correct' => false],
                            ]],
                            ['text' => 'In the colour-coded cutting board system, which colour is typically used for raw poultry?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Yellow is the standard colour for raw poultry in most colour-coded systems.', 'options' => [
                                ['text' => 'Red', 'correct' => false],
                                ['text' => 'Yellow', 'correct' => true],
                                ['text' => 'Green', 'correct' => false],
                                ['text' => 'Blue', 'correct' => false],
                            ]],
                            ['text' => 'Which of the following is one of the seven HACCP principles?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Establishing critical limits is one of the seven HACCP principles.', 'options' => [
                                ['text' => 'Establish critical limits', 'correct' => true],
                                ['text' => 'Maximise portion sizes', 'correct' => false],
                                ['text' => 'Minimise staff training', 'correct' => false],
                                ['text' => 'Increase menu variety', 'correct' => false],
                            ]],
                            ['text' => 'True or False: Frozen food should always be thawed on a counter at room temperature for convenience.', 'type' => 'true_false', 'points' => 12, 'explanation' => 'Food should be thawed in the refrigerator, under cold running water, or as part of continuous cooking — never at room temperature.', 'options' => [
                                ['text' => 'True', 'correct' => false],
                                ['text' => 'False', 'correct' => true],
                            ]],
                            ['text' => 'What should a CCP monitoring log always record when a critical limit is breached?', 'type' => 'single_choice', 'points' => 13, 'explanation' => 'A corrective action defines exactly what happens next, and verification confirms it worked — both should be recorded.', 'options' => [
                                ['text' => 'Nothing, breaches do not need to be logged', 'correct' => false],
                                ['text' => 'The corrective action taken and verification it worked', 'correct' => true],
                                ['text' => 'Only the name of the manager on duty', 'correct' => false],
                                ['text' => 'The weather that day', 'correct' => false],
                            ]],
                        ],
                    ],
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
                'resync' => true,
                'modules' => [
                    ['title' => 'Module 01: Kitchen Orientation & Safety', 'lessons' => [
                        ['title' => 'Kitchen Brigade & Hierarchy', 'content' => 'The classical kitchen brigade runs from the Executive Chef (overall responsibility) down through the Sous Chef (second in command), Chefs de Partie (station leads — sauce, grill, pastry, etc.), Commis Chefs (junior cooks), and Kitchen Porters. Knowing this hierarchy tells you exactly who to escalate a problem to during a busy service.'],
                        ['title' => 'Fire Safety & First Aid', 'content' => 'Know the location and correct use of your kitchen\'s fire extinguisher and fire blanket before you ever need them — never throw water on an oil or fat fire, as it causes violent flare-ups. Keep a stocked first aid kit accessible, and know the basic response for burns (cool running water, do not apply ice or butter) and cuts (apply pressure, clean, dress).'],
                        ['title' => 'PPE & Protective Equipment', 'content' => 'Non-slip shoes, cut-resistant gloves for high-risk tasks, heat-resistant gloves near ovens and fryers, and a proper chef\'s jacket (which protects arms from splashes) are minimum kitchen PPE. Comfort should never override safety — the "just this once" moment is when most kitchen injuries happen.'],
                    ]],
                    ['title' => 'Module 02: Knife Skills & Mise en Place', 'lessons' => [
                        ['title' => 'Knife Types & Selection', 'content' => 'A chef\'s knife (all-purpose chopping and slicing), a paring knife (small, precise trimming), and a serrated bread knife cover most kitchen tasks. Choose a knife that feels balanced in your hand and keep it sharp — a dull knife requires more force and is actually more dangerous than a sharp one, since it slips instead of cutting cleanly.'],
                        ['title' => 'Classical Cuts: Julienne, Brunoise, Chiffonade', 'content' => 'Julienne is a thin matchstick cut (roughly 3mm x 3mm x 5cm); brunoise is julienne diced further into tiny, even cubes; chiffonade is a fine ribbon cut used for leafy herbs and greens, made by stacking and rolling leaves before slicing. Consistent cut sizes matter because they ensure even cooking, not just neat presentation.'],
                        ['title' => 'Mise en Place Philosophy', 'content' => '"Mise en place" — everything in its place — means all ingredients are washed, cut, measured, and positioned before cooking starts. A kitchen that skips proper mise en place looks busy but is actually working inefficiently, scrambling mid-service for things that should already be ready.'],
                    ]],
                    ['title' => 'Module 03: Stocks, Soups & Sauces', 'lessons' => [
                        ['title' => 'Chicken, Veal & Fish Stock Production', 'content' => 'A good stock starts with bones (roasted for brown stock, raw for white stock), aromatics (onion, carrot, celery — the classic mirepoix), and a long, gentle simmer that extracts flavour and gelatin without boiling, which would make the stock cloudy. Skim impurities regularly for a clean, clear result.'],
                        ['title' => 'Mother Sauces: Béchamel, Velouté, Espagnole', 'content' => 'The classical French mother sauces are the foundation for dozens of derivative sauces: Béchamel (milk thickened with a butter-flour roux), Velouté (white stock thickened with roux), and Espagnole (brown stock, roux, and tomato). Learning these five foundational sauces unlocks an enormous range of finished sauces built from them.'],
                        ['title' => 'Pan Sauce & Emulsion Techniques', 'content' => 'A pan sauce is built by deglazing the browned bits (fond) left in a pan after searing meat with wine or stock, then reducing and finishing with butter (monter au beurre) for richness and shine. Emulsions like hollandaise depend on slowly incorporating fat into egg yolk while whisking constantly, so it does not split.'],
                    ]],
                    ['title' => 'Module 04: Protein Cookery', 'lessons' => [
                        ['title' => 'Beef & Lamb Cookery Temperatures', 'content' => 'Internal temperature guides doneness: rare beef around 50-52°C, medium around 60-63°C, well-done above 71°C — always rest meat for several minutes after cooking so juices redistribute instead of running out onto the plate when cut.'],
                        ['title' => 'Poultry Handling & Fabrication', 'content' => 'Poultry must reach a safe internal temperature of 74°C to eliminate Salmonella risk, with no exceptions for "rare" chicken. Fabrication (breaking down a whole bird into breasts, thighs, wings) should follow the joints, not force through bone, using a sharp boning knife for clean cuts.'],
                        ['title' => 'Fish & Seafood Cookery Methods', 'content' => 'Fish cooks fast and overcooks even faster — look for opaque, easily-flaking flesh rather than relying on time alone. Shellfish should be cooked until shells open (discard any that remain closed after cooking) and handled with strict temperature control given their higher spoilage risk.'],
                    ]],
                    ['title' => 'Module 05: Vegetable & Starch Preparation', 'lessons' => [
                        ['title' => 'Blanching & Shocking', 'content' => 'Blanching (briefly boiling, often 1-3 minutes) followed by shocking in ice water stops the cooking instantly, locking in colour and crisp texture — essential for green vegetables that would otherwise turn dull and overcooked between prep and service.'],
                        ['title' => 'Root Vegetable Glazing', 'content' => 'Glazing root vegetables (carrots, turnips) in butter, a little sugar, and stock, reduced until it coats the vegetable in a glossy layer, is a classic technique that adds both flavour and visual shine to a plate with minimal extra work.'],
                        ['title' => 'Potato: Mashed, Roasted, Fondant', 'content' => 'Mashed potato benefits from starchy varieties and gentle ricing rather than aggressive blending, which turns it gluey. Roasted potatoes crisp best when par-boiled first, then roughed up before roasting in hot fat. Fondant potatoes are pan-seared then braised in stock and butter for a crisp top and creamy centre.'],
                    ]],
                    ['title' => 'Module 06: Pastry & Baking Fundamentals', 'lessons' => [
                        ['title' => 'Short Crust, Puff & Choux Pastry', 'content' => 'Short crust (flour, fat, minimal water, worked briefly) gives a crumbly, tender base for tarts. Puff pastry uses repeated folding and rolling of butter into dough to create hundreds of flaky layers. Choux pastry is cooked on the stovetop before baking, and puffs from steam, forming a hollow shell used for éclairs and profiteroles.'],
                        ['title' => 'Sponge, Genoise & Mousse', 'content' => 'A genoise sponge relies on whipping whole eggs with sugar to incorporate air before folding in flour, giving a light, dry-ish crumb ideal for soaking with syrup. Mousse gets its light texture from folding in whipped cream or egg whites without deflating them — overmixing at this stage is the most common mistake.'],
                        ['title' => 'Chocolate Tempering Basics', 'content' => 'Tempering chocolate (carefully heating and cooling it through specific temperature stages) aligns its cocoa butter crystals so it sets with a glossy finish and a clean snap instead of a dull, soft, or streaky result. Untempered chocolate is still edible, but it will not perform or look professional on a finished dessert.'],
                    ]],
                    ['title' => 'Module 07: Modern Plating & Presentation', 'lessons' => [
                        ['title' => 'Plating Tools & Techniques', 'content' => 'Squeeze bottles for precise sauce dots and lines, offset spatulas for clean spreading, and small brushes for smearing are standard modern plating tools. Wipe plate rims clean before service — a smudged edge undermines even a beautifully composed dish.'],
                        ['title' => 'Colour, Texture & Height in Plating', 'content' => 'Strong plates combine contrasting colours (not everything beige), varied textures (something crisp against something soft), and a touch of height or negative space rather than spreading everything flat across the plate — the eye reads variety as quality.'],
                        ['title' => 'Food Photography for Menus', 'content' => 'Natural, diffused light (near a window, avoiding harsh direct sun) and a simple, uncluttered background make menu photography look far more professional than heavy filters or artificial lighting. Shoot the dish as it would actually be served — guests notice when the photo does not match the plate.'],
                    ]],
                    ['title' => 'Module 08: Rwandan Cuisine & Local Ingredients', 'lessons' => [
                        ['title' => 'Traditional Rwandan Dishes & Context', 'content' => 'Rwandan cuisine centres on hearty, plant-forward staples such as ubugali (a firm cassava or maize porridge), isombe (cassava leaves cooked with eggplant and often peanut), and ibiharage (beans, frequently stewed with vegetables) — dishes built around communal, filling meals rather than elaborate presentation.'],
                        ['title' => 'Local Vegetables, Grains & Proteins', 'content' => 'Sweet potato, cassava, plantain (ibitoke), sorghum, beans, and leafy greens form the backbone of the local pantry, alongside goat, beef, and freshwater fish from Rwanda\'s many lakes. A modern Rwandan kitchen should treat these as premium ingredients worth showcasing, not fallback substitutes for imported items.'],
                        ['title' => 'Modernizing Rwandan Recipes', 'content' => 'Modernising a traditional dish means respecting its core flavour while updating technique or presentation — for example, refining isombe\'s texture, or plating ibiharage with modern composition instead of a single mound. The goal is elevation, not erasure of the dish\'s identity.'],
                    ]],
                    ['title' => 'Module 09: Menu Engineering & Costing', 'lessons' => [
                        ['title' => 'Food Cost Percentage Formula', 'content' => 'Food cost percentage = (cost of ingredients for a dish ÷ menu selling price) × 100. Most full-service restaurants target roughly 28-35%, though this varies by concept — a costing error here quietly erodes profit on every single plate sold.'],
                        ['title' => 'Menu Profitability Matrix', 'content' => 'Classic menu engineering sorts dishes into four categories by popularity and profitability: "stars" (popular and profitable — promote these), "plow-horses" (popular but low-margin — consider re-costing), "puzzles" (profitable but unpopular — consider repositioning or removing), and "dogs" (neither — usually cut).'],
                        ['title' => 'Seasonal Menu Planning', 'content' => 'Building menus around what is seasonally available keeps ingredient costs lower, quality higher, and gives guests a reason to return as the menu evolves through the year rather than staying static indefinitely.'],
                    ]],
                    ['title' => 'Module 10: Kitchen Management Essentials', 'lessons' => [
                        ['title' => 'Prep Lists & Production Planning', 'content' => 'A written prep list, prioritised by what is needed earliest in service and by shelf life, keeps a kitchen organised and prevents both last-minute scrambling and unnecessary over-production that leads to waste.'],
                        ['title' => 'Ordering & Receiving Procedures', 'content' => 'Order against actual par levels and upcoming bookings rather than habit, and inspect deliveries on arrival — checking temperature for perishables, quality, and quantity against the invoice — before signing for anything.'],
                        ['title' => 'Waste Reduction Strategies', 'content' => 'Track why food is thrown away (over-production, spoilage, trim, plate waste) rather than just how much, since the cause determines the fix — better portioning, better rotation, or better butchery/trim technique each solve a different kind of waste.'],
                    ]],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Culinary Arts Foundations Certification Exam',
                        'description'        => 'Covers kitchen safety, knife skills, mother sauces, protein cookery, and menu costing. Passing score is 75%.',
                        'module_index'       => 9,
                        'time_limit_minutes' => 25,
                        'passing_score'      => 75,
                        'max_attempts'       => 3,
                        'questions' => [
                            ['text' => 'Why should water never be used on an oil or fat fire?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Water causes violent flare-ups on oil/fat fires; a fire blanket or the correct extinguisher must be used instead.', 'options' => [
                                ['text' => 'It has no effect either way', 'correct' => false],
                                ['text' => 'It causes violent flare-ups', 'correct' => true],
                                ['text' => 'It makes the fire burn slower safely', 'correct' => false],
                                ['text' => 'It is actually the recommended method', 'correct' => false],
                            ]],
                            ['text' => 'What is the approximate size of a julienne cut?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Julienne is roughly 3mm x 3mm x 5cm matchstick-sized.', 'options' => [
                                ['text' => 'Roughly 3mm x 3mm x 5cm', 'correct' => true],
                                ['text' => 'Roughly 2cm cubes', 'correct' => false],
                                ['text' => 'Paper-thin shavings only', 'correct' => false],
                                ['text' => 'Whole, uncut pieces', 'correct' => false],
                            ]],
                            ['text' => 'True or False: A dull knife is actually more dangerous than a sharp one.', 'type' => 'true_false', 'points' => 12, 'explanation' => 'A dull knife requires more force and is more likely to slip, making it more dangerous than a sharp, controlled blade.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'Which of these is one of the classical French mother sauces?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Béchamel is one of the five classical mother sauces.', 'options' => [
                                ['text' => 'Béchamel', 'correct' => true],
                                ['text' => 'Sriracha mayo', 'correct' => false],
                                ['text' => 'Ranch dressing', 'correct' => false],
                                ['text' => 'Teriyaki glaze', 'correct' => false],
                            ]],
                            ['text' => 'What internal temperature must poultry reach to be considered safe?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Poultry must reach 74°C internally to eliminate Salmonella risk.', 'options' => [
                                ['text' => '50°C', 'correct' => false],
                                ['text' => '63°C', 'correct' => false],
                                ['text' => '74°C', 'correct' => true],
                                ['text' => '35°C', 'correct' => false],
                            ]],
                            ['text' => 'What is the purpose of "shocking" a blanched vegetable in ice water?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Shocking stops the cooking process instantly, preserving colour and crisp texture.', 'options' => [
                                ['text' => 'To add more flavour', 'correct' => false],
                                ['text' => 'To stop the cooking process and preserve colour/texture', 'correct' => true],
                                ['text' => 'To make the vegetable softer', 'correct' => false],
                                ['text' => 'It is purely a presentation step with no functional purpose', 'correct' => false],
                            ]],
                            ['text' => 'What does tempering chocolate correctly achieve?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'Tempering aligns cocoa butter crystals for a glossy finish and clean snap when the chocolate sets.', 'options' => [
                                ['text' => 'A glossy finish and clean snap when set', 'correct' => true],
                                ['text' => 'A stronger chocolate flavour', 'correct' => false],
                                ['text' => 'A longer shelf life only', 'correct' => false],
                                ['text' => 'No real difference from untempered chocolate', 'correct' => false],
                            ]],
                            ['text' => 'Which menu engineering category describes a dish that is popular but low-margin?', 'type' => 'single_choice', 'points' => 10, 'explanation' => 'A "plow-horse" is popular but low-profitability, and is a candidate for re-costing.', 'options' => [
                                ['text' => 'Star', 'correct' => false],
                                ['text' => 'Plow-horse', 'correct' => true],
                                ['text' => 'Puzzle', 'correct' => false],
                                ['text' => 'Dog', 'correct' => false],
                            ]],
                            ['text' => 'True or False: Deliveries should always be signed for immediately without checking temperature or quality first.', 'type' => 'true_false', 'points' => 10, 'explanation' => 'Deliveries must be inspected for temperature, quality, and quantity against the invoice before being signed for.', 'options' => [
                                ['text' => 'True', 'correct' => false],
                                ['text' => 'False', 'correct' => true],
                            ]],
                        ],
                    ],
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
                'resync' => true,
                'modules' => [
                    ['title' => 'Module 01: Introduction to Herbalism', 'lessons' => [
                        ['title' => 'History of African Herbalism', 'content' => 'Across Africa, herbal knowledge has been passed down through generations of community healers and household practice, long before it was documented in written form. This course treats that knowledge with respect while applying modern food-safety and formulation standards, so traditional plants can be turned into consistent, safe, commercially viable beverages. Nothing taught here should be understood as medical treatment or a substitute for professional healthcare.'],
                        ['title' => 'Traditional Rwandan Plant Medicine', 'content' => 'Rwandan households have long used common local plants — ginger, garlic, lemongrass, eucalyptus, and various local leaves — in home remedies and everyday wellness drinks. This course focuses on the beverage and flavour applications of well-known plants, not clinical claims, and always encourages sourcing from reputable growers and consulting a health professional for any therapeutic use.'],
                        ['title' => 'Modern Herbalism & Science', 'content' => 'Modern herbalism blends traditional plant use with food science: understanding active compounds, extraction methods, shelf stability, and safe concentration levels. This course teaches herbalism as a craft for flavour, aroma, and general wellness beverages — not as a substitute for diagnosis or medical treatment, which should always be left to qualified healthcare providers.'],
                    ]],
                    ['title' => 'Module 02: Botanical Identification & Sourcing', 'lessons' => [
                        ['title' => 'Plant Families & Identification Keys', 'content' => 'Learning basic plant families (mints, citrus, composites like chamomile) helps you recognise related plants by shared features — leaf shape, stem structure, flower form — and understand why related plants often share similar aromatic compounds and uses.'],
                        ['title' => 'Wild Harvesting Ethics', 'content' => 'If sourcing wild plants, harvest only what you can positively identify with certainty, take no more than a small portion of any wild population so it can regenerate, and always get permission before harvesting on land you do not own. When in doubt about identification, do not use the plant — misidentification is a real safety risk.'],
                        ['title' => 'Drying, Storing & Grading Herbs', 'content' => 'Dry herbs in a well-ventilated, shaded space (direct sun degrades aroma and colour) until fully crisp, then store in airtight, light-blocking containers labelled with the harvest date. Grade by appearance, aroma strength, and freedom from mould or discoloration before using herbs in any commercial blend.'],
                    ]],
                    ['title' => 'Module 03: Preparation Methods', 'lessons' => [
                        ['title' => 'Infusions vs Decoctions', 'content' => 'An infusion steeps delicate plant parts (leaves, flowers) in hot water for a short time, similar to brewing tea. A decoction simmers tougher plant parts (roots, bark, seeds) for a longer period to extract their compounds fully, since these dense parts need more heat and time than a quick steep can provide.'],
                        ['title' => 'Tincture & Glycerite Making', 'content' => 'A tincture extracts plant compounds using alcohol as a solvent, typically macerated for several weeks and then strained. A glycerite uses food-grade vegetable glycerine instead, giving an alcohol-free, sweeter extract — useful for beverage applications where alcohol is not desired.'],
                        ['title' => 'Cold Process Extracts', 'content' => 'Cold infusion (steeping plant material in cool or room-temperature water over several hours) suits delicate, heat-sensitive aromatics and produces a smoother, less bitter extraction than hot methods — commonly used for delicate flowers or mucilaginous plants that turn gluey when heated.'],
                    ]],
                    ['title' => 'Module 04: Wellness Beverage Development', 'lessons' => [
                        ['title' => 'Flavour Balancing in Herbal Blends', 'content' => 'A balanced herbal blend usually combines a base note (a mellow, larger-quantity herb), a bright top note (citrus, mint), and an accent (a small amount of something distinctive like ginger or hibiscus). Build blends in small test batches, adjusting ratios by taste before scaling up production.'],
                        ['title' => 'Sweeteners: Honey, Stevia & Agave', 'content' => 'Honey adds floral sweetness and body but changes flavour by source; stevia is intensely sweet with zero calories but can carry a mild bitter aftertaste at high concentrations; agave syrup is neutral-tasting and dissolves easily in cold drinks. Choose based on the flavour profile and dietary claims (sugar-free, natural) you want for the finished product.'],
                        ['title' => 'Carbonated & Functional Drinks', 'content' => 'Herbal infusions can be carbonated for a modern "botanical soda" format, or blended with functional ingredients like ginger for digestive comfort or hibiscus for its tart, antioxidant-rich flavour profile — always describing benefits in general wellness terms, not medical claims, unless backed by regulatory-approved evidence.'],
                    ]],
                    ['title' => 'Module 05: Safety, Dosage & Contraindications', 'lessons' => [
                        ['title' => 'Safe Herb-Drug Interactions', 'content' => 'Some common herbs can interact with medications — for example, certain herbs affect blood clotting or blood pressure medication. This is a genuinely important safety area: this course teaches awareness, not clinical guidance, and any customer with existing health conditions or medications should be encouraged to consult a doctor or pharmacist before regularly consuming a new herbal product.'],
                        ['title' => 'Dosage Guidelines & Cautions', 'content' => 'For beverage-strength preparations (as opposed to concentrated medicinal doses), moderation and variety are the safest general approach — rotating herbs rather than consuming the same concentrated blend daily in large quantities. This course does not provide medical dosage guidance; any therapeutic use should be directed by a qualified health professional.'],
                        ['title' => 'Labelling & Regulatory Requirements', 'content' => 'Any wellness beverage sold commercially should be clearly labelled with ingredients, allergen information, and honest, non-medical language about its intended use. Avoid disease-treatment or cure claims on packaging, since these typically require regulatory approval and can mislead or harm customers if unsubstantiated.'],
                    ]],
                    ['title' => 'Module 06: Commercial Herbalism & Entrepreneurship', 'lessons' => [
                        ['title' => 'Costing a Wellness Beverage Product', 'content' => 'Cost every ingredient (herbs, sweetener, packaging, labour) per unit produced, and price to cover that cost plus your target margin — small-batch herbal products often justify a premium price when the quality, sourcing story, and craftsmanship are genuinely communicated to the customer.'],
                        ['title' => 'Rwanda FDA Registration Basics', 'content' => 'Selling food and beverage products commercially in Rwanda generally requires registration and compliance with the Rwanda Food and Drugs Authority (Rwanda FDA) and related food-safety standards. Requirements can change, so confirm the current registration process directly with Rwanda FDA before selling any product at scale.'],
                        ['title' => 'Selling at Markets & Online', 'content' => 'Start small — local markets, pop-up stalls, or direct social media sales — to test flavour reception and refine recipes before investing in larger production. Clear photos, an honest ingredient list, and a simple brand story about sourcing and craftsmanship help a small wellness beverage brand stand out.'],
                    ]],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Herbalism & Wellness Beverage Certification Exam',
                        'description'        => 'Covers botanical sourcing, preparation methods, safety practices, and commercial basics. Passing score is 75%.',
                        'module_index'       => 5,
                        'time_limit_minutes' => 20,
                        'passing_score'      => 75,
                        'max_attempts'       => 3,
                        'questions' => [
                            ['text' => 'What is the main difference between an infusion and a decoction?', 'type' => 'single_choice', 'points' => 14, 'explanation' => 'Infusions steep delicate parts briefly; decoctions simmer tougher plant parts longer to extract their compounds.', 'options' => [
                                ['text' => 'There is no real difference', 'correct' => false],
                                ['text' => 'Infusions steep delicate parts briefly; decoctions simmer tougher parts longer', 'correct' => true],
                                ['text' => 'Decoctions always use alcohol', 'correct' => false],
                                ['text' => 'Infusions are always cold, decoctions always hot', 'correct' => false],
                            ]],
                            ['text' => 'True or False: If you cannot positively identify a wild plant, it is safe to use it anyway as long as it looks similar to a known herb.', 'type' => 'true_false', 'points' => 14, 'explanation' => 'Misidentification is a real safety risk — when in doubt, do not use the plant.', 'options' => [
                                ['text' => 'True', 'correct' => false],
                                ['text' => 'False', 'correct' => true],
                            ]],
                            ['text' => 'What should a customer with existing health conditions or medications be advised to do before regularly consuming a new herbal product?', 'type' => 'single_choice', 'points' => 14, 'explanation' => 'Herb-drug interactions are a real concern; customers should consult a doctor or pharmacist.', 'options' => [
                                ['text' => 'Nothing, herbal products are always safe to combine with medication', 'correct' => false],
                                ['text' => 'Consult a doctor or pharmacist first', 'correct' => true],
                                ['text' => 'Double the recommended amount for faster results', 'correct' => false],
                                ['text' => 'Stop taking their prescribed medication instead', 'correct' => false],
                            ]],
                            ['text' => 'What solvent does a glycerite use instead of alcohol?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'A glycerite uses food-grade vegetable glycerine as the extraction solvent.', 'options' => [
                                ['text' => 'Vegetable glycerine', 'correct' => true],
                                ['text' => 'Vinegar', 'correct' => false],
                                ['text' => 'Vegetable oil', 'correct' => false],
                                ['text' => 'Distilled water only', 'correct' => false],
                            ]],
                            ['text' => 'Why should dried herbs be stored away from direct sunlight?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Direct sun degrades the aroma and colour of dried herbs.', 'options' => [
                                ['text' => 'Sunlight has no effect on dried herbs', 'correct' => false],
                                ['text' => 'It degrades aroma and colour', 'correct' => true],
                                ['text' => 'It makes herbs too strong', 'correct' => false],
                                ['text' => 'It is only a cosmetic preference, not a quality issue', 'correct' => false],
                            ]],
                            ['text' => 'True or False: Wellness beverage packaging should avoid disease-treatment or cure claims unless backed by regulatory-approved evidence.', 'type' => 'true_false', 'points' => 12, 'explanation' => 'Unsubstantiated medical claims can mislead and harm customers, and typically require regulatory approval.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'Which body generally oversees food and beverage product registration in Rwanda?', 'type' => 'single_choice', 'points' => 11, 'explanation' => 'The Rwanda Food and Drugs Authority (Rwanda FDA) is the relevant body for food/beverage product compliance.', 'options' => [
                                ['text' => 'Rwanda Food and Drugs Authority (Rwanda FDA)', 'correct' => true],
                                ['text' => 'The local coffee cooperative', 'correct' => false],
                                ['text' => 'Any hotel association', 'correct' => false],
                                ['text' => 'No registration is ever required', 'correct' => false],
                            ]],
                            ['text' => 'What is a sensible way to test a new herbal product before large-scale production?', 'type' => 'single_choice', 'points' => 11, 'explanation' => 'Starting small (markets, pop-ups, direct social sales) lets you test reception and refine recipes before scaling.', 'options' => [
                                ['text' => 'Immediately mass-produce and distribute nationally', 'correct' => false],
                                ['text' => 'Start small at local markets or via direct sales to test reception', 'correct' => true],
                                ['text' => 'Skip testing and rely on packaging design alone', 'correct' => false],
                                ['text' => 'Only sell through international exporters first', 'correct' => false],
                            ]],
                        ],
                    ],
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
                'resync' => true,
                'modules' => [
                    ['title' => 'Module 01: Introduction to Domestic Service', 'lessons' => [
                        ['title' => 'History & Professionalism in Domestic Work', 'content' => 'Domestic service is a skilled hospitality profession, not unskilled labour — it demands organisation, discretion, food safety knowledge, and genuine care. Treating the role with professional pride, the same way a hotel housekeeper or butler would, changes both how you perform the work and how you are treated by employers.'],
                        ['title' => 'Role of Domestic Staff in Rwanda', 'content' => 'Domestic staff in Rwandan households often take on a wide range of responsibilities — cleaning, cooking, laundry, and sometimes childcare — making them a central part of how a household functions day to day. Clarity on which specific duties are expected, agreed in advance with the employer, prevents confusion and builds a healthier working relationship.'],
                        ['title' => 'Rights & Responsibilities', 'content' => 'A fair working arrangement includes a clear agreement on duties, working hours, rest days, and pay, discussed and ideally documented at the start of employment. Rwandan labour regulations continue to evolve, so both domestic workers and employers should confirm current requirements with Rwanda\'s Ministry of Labour or a legal advisor rather than relying on informal custom alone.'],
                    ]],
                    ['title' => 'Module 02: Professional Housekeeping', 'lessons' => [
                        ['title' => 'Cleaning Schedules & Priority Areas', 'content' => 'A written cleaning schedule (daily, weekly, monthly tasks) ensures nothing is forgotten and prevents last-minute scrambling before guests arrive. Prioritise high-visibility and high-touch areas — entryways, bathrooms, kitchen surfaces — since these shape a visitor\'s first impression of the whole home.'],
                        ['title' => 'Colour-Coded Cleaning System', 'content' => 'Just as in professional kitchens, assigning different coloured cloths to different areas (e.g. one colour for bathrooms, another for kitchen surfaces, another for general dusting) prevents cross-contamination — you never want a cloth used on a toilet then used on a dining table.'],
                        ['title' => 'High-Touch Point Disinfection', 'content' => 'Door handles, light switches, remote controls, and taps are touched constantly and should be disinfected more frequently than general surfaces, particularly during illness season or after guests visit, since these points are a primary way germs spread through a household.'],
                    ]],
                    ['title' => 'Module 03: Laundry & Linen Care', 'lessons' => [
                        ['title' => 'Fabric Identification & Washing Temperatures', 'content' => 'Check garment care labels before washing: delicate fabrics (silk, wool, some synthetics) generally need cold water and gentle cycles, while sturdy cottons tolerate warmer water. Washing an item at the wrong temperature is one of the most common causes of shrinking, fading, or damaging good clothing.'],
                        ['title' => 'Ironing & Pressing Techniques', 'content' => 'Iron on the appropriate heat setting for the fabric (checking the care label), iron dark or delicate fabrics inside-out to avoid shine marks, and press linens and shirts in a logical order (collars and cuffs before large flat areas) for efficient, crease-free results.'],
                        ['title' => 'Wardrobe & Storage Organisation', 'content' => 'Store garments by category and season, use padded or appropriate hangers for structured clothing to preserve shape, and keep a moisture-control measure (like silica packets) in storage areas to prevent mildew, especially during humid periods.'],
                    ]],
                    ['title' => 'Module 04: Household & Kitchen Management', 'lessons' => [
                        ['title' => 'Menu Planning & Shopping Lists', 'content' => 'Planning meals for the week before shopping reduces waste, controls the household budget, and avoids the last-minute stress of deciding what to cook. Build the shopping list around the planned menu, not the other way around, to avoid impulse purchases that go unused.'],
                        ['title' => 'Kitchen Hygiene & Safety', 'content' => 'The same core food safety principles used in professional kitchens apply at home: separate raw and cooked food, wash hands frequently, keep perishables refrigerated, and clean surfaces after handling raw meat or poultry before they touch anything else.'],
                        ['title' => 'Batch Cooking & Food Storage', 'content' => 'Preparing larger batches of staple items (grains, beans, sauces) and portioning them into labelled, dated containers for refrigeration or freezing saves time on busy days and reduces the temptation to let food go to waste.'],
                    ]],
                    ['title' => 'Module 05: Hosting & Table Service', 'lessons' => [
                        ['title' => 'Formal Table Setting Protocols', 'content' => 'A formal table setting places cutlery in the order it will be used, working from the outside in, with the napkin to the left of the forks or on the plate, and glassware positioned above the knife. Consistency across every place setting is what makes a table look genuinely polished.'],
                        ['title' => 'Serving & Clearing Courses', 'content' => 'Serve from the guest\'s left and clear from their right where possible, moving efficiently but never rushing a guest who is still eating. Clear plates only once everyone at the table has finished a course, not as soon as the first person is done.'],
                        ['title' => 'Event Preparation & Hosting Etiquette', 'content' => 'For hosted events, prepare a checklist covering the menu, table settings, seating arrangement, and timing well in advance, and do a final walkthrough of the space shortly before guests arrive to catch anything overlooked.'],
                    ]],
                    ['title' => 'Module 06: Childcare Essentials', 'lessons' => [
                        ['title' => 'Child Development Milestones', 'content' => 'Understanding broad developmental stages — language, motor skills, and social development typical for different ages — helps a caregiver set age-appropriate expectations and choose suitable activities, while remembering that every child develops at their own pace.'],
                        ['title' => 'Safe Play & Educational Activities', 'content' => 'Choose age-appropriate toys and activities, supervise play near stairs, water, or small objects that pose choking hazards, and balance free play with simple educational activities like reading, counting games, or drawing.'],
                        ['title' => 'Emergency First Aid for Children', 'content' => 'Know the location of a stocked first aid kit and emergency contact numbers before you ever need them, and recognise the signs that require immediate professional medical help (difficulty breathing, unresponsiveness, severe bleeding). This lesson is an awareness overview only — anyone caring for children should pursue accredited, hands-on first aid and CPR certification from a recognised provider.'],
                    ]],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Domestic Hospitality Certification Exam',
                        'description'        => 'Covers housekeeping standards, laundry care, hosting etiquette, and childcare safety basics. Passing score is 75%.',
                        'module_index'       => 5,
                        'time_limit_minutes' => 20,
                        'passing_score'      => 75,
                        'max_attempts'       => 3,
                        'questions' => [
                            ['text' => 'Why should cleaning cloths be colour-coded for different areas of a home?', 'type' => 'single_choice', 'points' => 14, 'explanation' => 'Colour-coding prevents cross-contamination, such as using a bathroom cloth on kitchen or dining surfaces.', 'options' => [
                                ['text' => 'To prevent cross-contamination between areas', 'correct' => true],
                                ['text' => 'Purely for decoration', 'correct' => false],
                                ['text' => 'To make cloths more expensive', 'correct' => false],
                                ['text' => 'It has no real functional purpose', 'correct' => false],
                            ]],
                            ['text' => 'Which household surfaces should be disinfected more frequently as "high-touch points"?', 'type' => 'single_choice', 'points' => 14, 'explanation' => 'Door handles, light switches, and taps are touched constantly and are a primary way germs spread.', 'options' => [
                                ['text' => 'Door handles, light switches, and taps', 'correct' => true],
                                ['text' => 'Ceiling corners only', 'correct' => false],
                                ['text' => 'Outdoor garden furniture only', 'correct' => false],
                                ['text' => 'None, all surfaces need equal frequency', 'correct' => false],
                            ]],
                            ['text' => 'True or False: Garment care labels should be checked before selecting a wash temperature.', 'type' => 'true_false', 'points' => 14, 'explanation' => 'Washing at the wrong temperature is a common cause of shrinking, fading, or damaging clothing.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'In formal table service, from which side should plates typically be served?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'The convention is to serve from the guest\'s left and clear from their right where possible.', 'options' => [
                                ['text' => 'From the guest\'s left', 'correct' => true],
                                ['text' => 'From directly behind, reaching over the guest', 'correct' => false],
                                ['text' => 'It never matters which side', 'correct' => false],
                                ['text' => 'Always from the right for both serving and clearing', 'correct' => false],
                            ]],
                            ['text' => 'What is the recommended approach to planning household meals?', 'type' => 'single_choice', 'points' => 12, 'explanation' => 'Planning the menu first, then building the shopping list around it, reduces waste and impulse purchases.', 'options' => [
                                ['text' => 'Shop first, then decide what to cook from what was bought', 'correct' => false],
                                ['text' => 'Plan the menu first, then shop based on that plan', 'correct' => true],
                                ['text' => 'Buy in bulk with no plan at all', 'correct' => false],
                                ['text' => 'Cook only what is already in the pantry, indefinitely', 'correct' => false],
                            ]],
                            ['text' => 'True or False: A fair domestic work arrangement should include a clear agreement on duties, hours, rest days, and pay.', 'type' => 'true_false', 'points' => 12, 'explanation' => 'Clarity on these terms, ideally documented, prevents confusion and builds a healthier working relationship.', 'options' => [
                                ['text' => 'True', 'correct' => true],
                                ['text' => 'False', 'correct' => false],
                            ]],
                            ['text' => 'What should a caregiver do before supervising children around stairs, water, or small objects?', 'type' => 'single_choice', 'points' => 11, 'explanation' => 'Active supervision near these hazards is essential, along with choosing age-appropriate toys and activities.', 'options' => [
                                ['text' => 'Assume children will be careful on their own', 'correct' => false],
                                ['text' => 'Actively supervise and remove obvious hazards', 'correct' => true],
                                ['text' => 'Leave the room once play has started', 'correct' => false],
                                ['text' => 'Only intervene if a child cries', 'correct' => false],
                            ]],
                            ['text' => 'What should anyone responsible for childcare pursue beyond this course\'s overview lesson on emergencies?', 'type' => 'single_choice', 'points' => 11, 'explanation' => 'This course provides an awareness overview only; accredited, hands-on first aid and CPR certification is recommended.', 'options' => [
                                ['text' => 'Nothing further is needed', 'correct' => false],
                                ['text' => 'Accredited, hands-on first aid and CPR certification', 'correct' => true],
                                ['text' => 'A general cooking course', 'correct' => false],
                                ['text' => 'An unrelated business certificate', 'correct' => false],
                            ]],
                        ],
                    ],
                ],
            ],

            // ── ORIENTATION (Module 0 — mandatory before any course) ──────────
            [
                'title'            => 'BBA Student Orientation & Online Academy Guide',
                'slug'             => 'bba-student-orientation-online-academy',
                'category'         => 'bba-specialty',
                'level'            => 'beginner',
                'price'            => 0.00,
                'is_free'          => 1,
                'is_featured'      => 0,
                'duration_hours'   => 1.5,
                'passing_score'    => 80,
                'thumbnail'        => 'teachers.jpg',
                'resync'           => true, // rebuild curriculum in place if this course already exists
                'short_description'=> 'Module 0 — mandatory before any course. Meet Coach Egide, learn how the platform works, and set yourself up for success.',
                'description'      => 'Your starting point at Beyond Barista Academy. This free, mandatory orientation walks you through the learning platform, the assessment and certificate system, the Student Code of Conduct, and the BBA success mindset — everything you need before beginning any BBA course.',
                'requirements'     => ['No requirements — open to all registered students', 'Must be completed before accessing other BBA courses'],
                'outcomes'         => [
                    'Navigate the BBA online learning platform confidently',
                    'Understand the assessment, quiz, and certificate system',
                    'Follow the BBA Student Code of Conduct and Learning Rules',
                    'Know who to contact for student support and how to join the BBA community',
                    'Apply the BBA success mindset to every course you take',
                ],
                'modules' => [
                    [
                        'title' => 'Module 0.1 – Welcome to Beyond Barista Academy',
                        'description' => 'Meet Coach Egide, learn what BBA stands for, and meet your trainer.',
                        'lessons' => [
                            [
                                'title' => '0.1 Welcome Message from Coach Egide',
                                'lesson_type' => 'video',
                                'duration_minutes' => 5,
                                'content' => <<<TXT
[Video placeholder — 3 to 5 minutes. Once Coach Egide's welcome video is recorded, switch this lesson's Content Type to Video and paste the link. Until then, please read the welcome message below.]

Dear Student,

On behalf of Beyond Barista Academy (BBA), I am delighted to welcome you to the Barista & Beverage Mastery Program.

Thank you for choosing BBA as your partner in your professional development. By enrolling in this program, you have taken an important step toward building a successful career in the coffee and beverage industry.

At BBA, we believe that preparing an excellent cup of coffee or creating an outstanding beverage is more than learning techniques. It requires knowledge, discipline, professionalism, creativity, consistency, and a genuine passion for serving others.

Our goal is not simply to train baristas — we aim to develop hospitality professionals, entrepreneurs, and future industry leaders who are capable of creating opportunities for themselves and others.

I encourage you to approach every lesson with curiosity, commitment, and a willingness to practice continuously. Ask questions, complete every activity, and challenge yourself to improve each day. Remember that excellence is achieved through consistency and dedication.

Thank you once again for becoming part of the BBA family. We are honoured to be part of your learning journey and look forward to celebrating your success.

Welcome to Beyond Barista Academy, where attitude and skills create opportunities, opportunities create careers, our courses changing lives.

I wish you every success.

Coach Egide
Founder & Lead Trainer | Food & Beverage Consultant | Hospitality Business Coach | Curriculum Developer | Opportunity & Talent Developer | Alcohol Service Specialist

"Welcome to Beyond Barista Academy. I believe you have the potential to become a world-class hospitality professional. Let us begin this exciting journey together."
TXT,
                            ],
                            [
                                'title' => '0.2 About Beyond Barista Academy',
                                'duration_minutes' => 8,
                                'content' => <<<TXT
Who is BBA?
Beyond Barista Academy (BBA) is an online hospitality training academy built to train baristas, bartenders, sommeliers, cooks, and hospitality professionals to an internationally aligned standard, through accessible, mobile-friendly online learning.

History
[Admin/Coach Egide: add BBA's founding story here — the year it started, the motivation behind it, and key milestones so far. This section is a placeholder until the exact history is supplied.]

Vision
To become a leading hospitality and beverage training academy, known for producing skilled, disciplined, opportunity-ready professionals who elevate the coffee and hospitality industry.

Mission
To equip every student — regardless of background or prior experience — with the technical skills, professional attitude, and business knowledge needed to build a real career or business in coffee, beverage, and hospitality.

Core Values
- Knowledge: we teach the science and theory behind every technique, not just the motion.
- Discipline: consistency in practice is what turns a skill into a craft.
- Professionalism: how you show up matters as much as what you know.
- Creativity: great hospitality solves problems and delights guests in ways no manual can fully script.
- Consistency: one perfect cup means nothing if you cannot repeat it a hundred times.
- Passion for Serving Others: hospitality, at its core, is about people.

Why BBA Exists
Many talented people in the coffee and hospitality industry never get a real chance to prove themselves, simply because they lack certified, structured training. BBA exists to close that gap — turning passion and potential into recognized skill, and recognized skill into real careers, businesses, and opportunities.
TXT,
                            ],
                            [
                                'title' => '0.3 Meet Your Trainer',
                                'duration_minutes' => 5,
                                'content' => <<<TXT
Students learn best when they know who is teaching them — so meet the founder and lead trainer of Beyond Barista Academy.

Coach Egide
Founder & Lead Trainer at Beyond Barista Academy

- Food & Beverage Consultant
- Hospitality Business Coach
- Curriculum Developer
- Opportunity & Talent Developer
- Alcohol Service Specialist

Coach Egide built the BBA curriculum from real, hands-on experience in the coffee and hospitality industry — not just theory. Every module in this academy, from espresso mechanics to bar management to personal branding, reflects lessons learned from actually running bars, training teams, and building hospitality businesses.

As you progress through your chosen course, you will also be introduced to additional instructors and guest trainers for specialized topics such as wine service, culinary arts, and food safety. Each instructor's profile is available on their course page.
TXT,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Module 0.2 – How the Online Academy Works',
                        'description' => 'Platform walkthrough, learning rules, and the Student Code of Conduct.',
                        'lessons' => [
                            [
                                'title' => '0.4 How the Online Academy Works',
                                'duration_minutes' => 10,
                                'content' => <<<TXT
This lesson explains exactly how to use the BBA online platform, step by step.

1. Watch Videos
Each lesson may include a video player right in the classroom. Use Next/Previous to move through lessons in order, and Mark Lesson as Complete once you have finished, so your progress bar updates automatically.

2. Download Notes & Read Materials
Some lessons are text-based reading material (like this one), and others include downloadable PDFs — handbooks, dial-in charts, recipe sheets, and reference guides — available directly on the lesson page.

3. Submit Assignments
Practical and written assignments are shared by your instructor per course. Follow the instructions on each assignment lesson and submit your work through the platform (or the channel your instructor specifies) before the deadline.

4. Ask Questions
If you are stuck or unsure about anything, use the Student Support contact channels covered in lesson 0.9, or the course discussion area where available. No question is a bad question — asking early is part of succeeding at BBA.

5. Take Quizzes
Each course includes one or more quizzes, usually at the end of a module. Quizzes have a time limit, a required passing score, and a limited number of attempts — all shown before you start. Your questions may be single choice, multiple choice, true/false, or short answer.

6. Receive Certificates
Once you complete all lessons, pass all required quizzes, and meet the course's overall passing score, a digital certificate is generated for you automatically, complete with a verification code so employers can confirm it is genuine.

Your Student Dashboard is your home base — from there you can see all your enrolled courses, resume any lesson exactly where you left off, track your progress percentage, and access your certificates once earned.
TXT,
                            ],
                            [
                                'title' => '0.5 Learning Rules',
                                'duration_minutes' => 6,
                                'content' => <<<TXT
To keep your learning effective and fair to every student, please follow these rules:

1. Watch lessons in order.
Courses are structured to build on each other. Skipping ahead often means missing the foundation a later lesson assumes you already have.

2. Complete quizzes.
Quizzes are not optional extras — they confirm you have actually absorbed the material, and they count toward your certificate.

3. Respect copyrights.
All videos, PDFs, recipes, and slides belong to Beyond Barista Academy and its instructors. Do not re-record, re-upload, or redistribute them.

4. Do not share course materials.
Sharing your login, downloaded notes, or paid content with people who have not enrolled undermines the academy and is a violation of your student agreement.

5. Participate actively.
Ask questions, engage with feedback, and treat every lesson as practice for a real work environment.

6. Complete practical assignments.
Hospitality is a hands-on craft. Reading about milk texturing is not the same as texturing milk — practical assignments are where real skill is built.
TXT,
                            ],
                            [
                                'title' => '0.6 Student Code of Conduct',
                                'duration_minutes' => 6,
                                'content' => <<<TXT
Every BBA student is expected to uphold the following standards, both on the platform and in any practical or in-person sessions:

Professionalism
Treat every lesson, assignment, and interaction the way you would treat a real hospitality job — because that is exactly what it is preparing you for.

Respect
Respect your instructors, fellow students, and support staff. Hospitality is a people industry; how you treat people here reflects how you will treat guests later.

Integrity
Do your own work. Submit your own quiz answers and your own assignments — certificates only mean something if they represent real, personal effort.

Positive Attitude
Approach challenges, feedback, and mistakes with openness rather than defensiveness. As Coach Egide says, "Without attitude, everything is nothing."

Time Management
Meet assignment deadlines and quiz attempt windows. Treat your study schedule with the same discipline you would bring to a paid shift.

Academic Honesty
No plagiarism, no impersonation during quizzes or assessments, and no falsifying practical assignment evidence (photos/videos of work you did not actually do).

Violations of this Code of Conduct may result in a warning, suspension of platform access, or revocation of a certificate already issued.
TXT,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Module 0.3 – Getting Ready to Learn',
                        'description' => 'How to succeed, equipment needed, and how you will be assessed.',
                        'lessons' => [
                            [
                                'title' => '0.7 How to Succeed at BBA',
                                'duration_minutes' => 6,
                                'content' => <<<TXT
Students who get the most out of BBA consistently do these six things:

1. Take notes.
Writing things down in your own words helps you remember them under pressure, at a real bar or espresso station.

2. Practice every day.
Hospitality skills are motor skills as much as knowledge — a small amount of daily practice beats one long session a week.

3. Ask questions.
If a concept, ratio, or technique is unclear, ask before moving on. Confusion compounds if it is left unresolved.

4. Learn from mistakes.
Every experienced barista, bartender, or chef has ruined a batch, over-extracted a shot, or over-poured a drink. What matters is what you adjust next time.

5. Complete every assignment.
Assignments are designed to close the gap between "I understood the video" and "I can actually do this."

6. Never skip practical sessions.
Theory tells you why; practice teaches you how. Skipping the hands-on component is the single biggest predictor of struggling later in your career.
TXT,
                            ],
                            [
                                'title' => '0.8 Equipment Needed',
                                'duration_minutes' => 5,
                                'content' => <<<TXT
The exact equipment you need depends on the course you are enrolled in — your course's Requirements section and instructor will confirm the full list. As an example, here is what a Barista course typically requires for hands-on practice:

- Espresso machine
- Grinder
- Milk pitcher
- Scale
- Thermometer
- Coffee beans

If you do not have access to professional equipment yet, do not worry — most foundation lessons can be studied and understood through video and notes first, with practical assignments designed around whatever setup (home or workplace) you have available. Talk to your instructor via Student Support if you are unsure what you need for your specific course.
TXT,
                            ],
                            [
                                'title' => '0.9 Assessment System',
                                'duration_minutes' => 8,
                                'content' => <<<TXT
Your progress and final certificate at BBA are based on four components:

1. Quizzes
Short knowledge checks tied to a module. Each quiz shows its time limit, required passing score, and maximum number of attempts before you start, so there are no surprises.

2. Assignments
Written or practical tasks set by your instructor to apply what you just learned — for example, submitting a dial-in log, a recipe costing sheet, or a short written reflection.

3. Practical Assessment
For hands-on courses, you will be asked to demonstrate a skill directly (e.g. pouring a rosetta, executing a cocktail build, plating a dish) — either in person at a partner location or via a submitted video, depending on how your course is delivered.

4. Final Examination
A comprehensive assessment covering the full course, taken after all modules and practical assignments are complete.

Certificate Requirements
To receive your Beyond Barista Academy certificate, you must complete all lessons, meet the passing score on every quiz and the final examination, and submit all required assignments and practical assessments. Once these are met, your digital certificate is generated automatically and appears on your Student Dashboard, ready to download or share with a verification code employers can check.
TXT,
                            ],
                        ],
                    ],
                    [
                        'title' => 'Module 0.4 – Community, Graduation & Mindset',
                        'description' => 'Support channels, community, graduation, and the BBA success mindset.',
                        'lessons' => [
                            [
                                'title' => '0.10 Student Support',
                                'duration_minutes' => 4,
                                'content' => <<<TXT
If you ever get stuck — technically or academically — reach out. Here is how to contact Beyond Barista Academy:

- Email: info@beyondbarista.rw
- Phone / WhatsApp: +250 788 000 111
- Office Hours: Monday to Friday, 8:00 AM to 5:00 PM (Kigali time)

[Admin: confirm the final WhatsApp number, email, and office hours before publishing — these are placeholders drawn from the current site settings.]

For platform issues (login problems, a video not loading, a certificate not generating), contact support with your registered email and a short description of the problem so we can help you quickly.
TXT,
                            ],
                            [
                                'title' => '0.11 Community',
                                'duration_minutes' => 4,
                                'content' => <<<TXT
Learning does not stop when the video ends. You are invited to join the wider BBA community:

- Facebook Group — connect with fellow students and share your progress
- WhatsApp Group — quick updates and peer support
- Telegram Channel — announcements and resources
- LinkedIn — follow BBA's company page and grow your professional network
- Alumni Community — for graduates to stay connected, mentor new students, and hear about job opportunities first

Current invite links for these groups are posted on your Student Dashboard announcements and the BBA website footer — check there for the most up-to-date links, since group links are refreshed periodically.
TXT,
                            ],
                            [
                                'title' => '0.12 Graduation',
                                'duration_minutes' => 5,
                                'content' => <<<TXT
Requirements
To graduate from a course, you must complete every lesson, pass every quiz and the final examination at the required score, and submit all required assignments and practical assessments.

Certificates
Graduates receive a verified digital certificate with a unique certificate number and QR code, so anyone — an employer, a client, a hiring manager — can confirm it is genuine.

Awards
Outstanding students may be recognized publicly (for example, Top Graduate of a cohort) and highlighted in BBA's community channels.

Career Support
Beyond Barista Academy maintains a hospitality Job Board where graduates can find openings from partner cafes, hotels, and hospitality businesses. Completing your certificate is often the first requirement employers look for when reviewing BBA applicants.
TXT,
                            ],
                            [
                                'title' => '0.13 The BBA Success Mindset',
                                'duration_minutes' => 6,
                                'content' => <<<TXT
Before you learn how to make an espresso, mix a cocktail, or serve wine, you should first understand what it means to show a positive attitude in the hospitality industry. This is what sets BBA apart from academies that teach technique but overlook character.

Why Attitude Is More Important Than Talent
Talent gets you noticed; attitude is what gets you hired, kept, and promoted. Guests remember how a barista or server made them feel far more than the technical precision of the drink.

Professional Image and Grooming
In hospitality, you are part of the product. Neat appearance, punctuality, and composed body language build guest trust before you have said a single word.

Keep Learning and Work Smarter, Not Harder
The best hospitality professionals never stop studying their craft — but they also learn to systemize repetitive work (checklists, par levels, prep lists) so their energy goes toward guest experience, not chaos.

Turning Skills and Problems into Opportunities
Every complaint, shortage, or mistake is a chance to demonstrate problem-solving. Professionals who can calmly fix a problem in front of a guest are the ones who get promoted into leadership.

Coach Egide's reminder:
"Without attitude, everything is nothing."
TXT,
                            ],
                            [
                                'title' => '0.14 Final Orientation Message',
                                'lesson_type' => 'video',
                                'duration_minutes' => 3,
                                'content' => <<<TXT
[Video placeholder — once recorded, switch this lesson's Content Type to Video and add the link.]

"At Beyond Barista Academy, we do not simply teach beverage skills — we develop professionals, entrepreneurs, and future industry leaders. Your commitment to learning, discipline, and continuous improvement will determine your success.

Welcome to the BBA school of barista and beverage mastery, the family where attitude + skills create opportunities and opportunities create success."

You have now completed Module 0. Take the short quiz below to confirm what you have learned, then head to My Courses to begin your first BBA course.
TXT,
                            ],
                        ],
                    ],
                ],
                'quizzes' => [
                    [
                        'title'              => 'Module 0 Orientation Completion Check',
                        'description'        => 'A short check to confirm you understand how BBA works, the Student Code of Conduct, and the BBA success mindset before you begin your first course. Passing score is 80%.',
                        'module_index'       => 3,
                        'time_limit_minutes' => 15,
                        'passing_score'      => 80,
                        'max_attempts'       => 5,
                        'questions' => [
                            [
                                'text' => 'According to this orientation, why is Module 0 required for every student?',
                                'type' => 'single_choice',
                                'points' => 10,
                                'explanation' => 'The orientation is mandatory before a student can access any other BBA course.',
                                'options' => [
                                    ['text' => 'It is optional, but recommended for beginners', 'correct' => false],
                                    ['text' => 'It is mandatory before accessing any other BBA course', 'correct' => true],
                                    ['text' => 'It is only required for the free courses', 'correct' => false],
                                    ['text' => 'It is only required for students under 18', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Coach Egide teaches: "Without ___, everything is nothing."',
                                'type' => 'single_choice',
                                'points' => 10,
                                'explanation' => 'The BBA Success Mindset is built on the idea that attitude matters more than raw talent.',
                                'options' => [
                                    ['text' => 'money', 'correct' => false],
                                    ['text' => 'talent', 'correct' => false],
                                    ['text' => 'attitude', 'correct' => true],
                                    ['text' => 'equipment', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'True or False: It is acceptable to share your paid course login or downloaded materials with a friend who has not enrolled.',
                                'type' => 'true_false',
                                'points' => 10,
                                'explanation' => 'The Learning Rules explicitly prohibit sharing course materials with non-enrolled students.',
                                'options' => [
                                    ['text' => 'True', 'correct' => false],
                                    ['text' => 'False', 'correct' => true],
                                ],
                            ],
                            [
                                'text' => 'Complete the BBA philosophy: "Attitude and skills create ___, ___ create careers."',
                                'type' => 'single_choice',
                                'points' => 10,
                                'explanation' => 'From Coach Egide\'s welcome message: attitude and skills create opportunities, opportunities create careers.',
                                'options' => [
                                    ['text' => 'opportunities', 'correct' => true],
                                    ['text' => 'certificates', 'correct' => false],
                                    ['text' => 'profits', 'correct' => false],
                                    ['text' => 'discounts', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Which of these is required before a certificate is issued for a course?',
                                'type' => 'single_choice',
                                'points' => 10,
                                'explanation' => 'Certificates require completing all lessons and meeting the passing score on quizzes, the final exam, and required assignments/practicals.',
                                'options' => [
                                    ['text' => 'Watching the welcome video only', 'correct' => false],
                                    ['text' => 'Paying an extra certificate fee', 'correct' => false],
                                    ['text' => 'Completing all lessons and meeting the required passing score', 'correct' => true],
                                    ['text' => 'Being enrolled for at least 6 months', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'True or False: Every quiz on the platform shows its time limit, passing score, and maximum attempts before you start.',
                                'type' => 'true_false',
                                'points' => 10,
                                'explanation' => 'Quiz rules are always shown up front so there are no surprises.',
                                'options' => [
                                    ['text' => 'True', 'correct' => true],
                                    ['text' => 'False', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Which of the following is NOT part of the BBA Student Code of Conduct?',
                                'type' => 'single_choice',
                                'points' => 10,
                                'explanation' => 'The Code of Conduct covers Professionalism, Respect, Integrity, Positive Attitude, Time Management, and Academic Honesty — not "working alone without asking for help."',
                                'options' => [
                                    ['text' => 'Professionalism', 'correct' => false],
                                    ['text' => 'Academic Honesty', 'correct' => false],
                                    ['text' => 'Always work alone and never ask for help', 'correct' => true],
                                    ['text' => 'Time Management', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'If you are unsure about a technique or concept in a lesson, what should you do?',
                                'type' => 'single_choice',
                                'points' => 10,
                                'explanation' => 'Asking questions early — via Student Support or your course discussion area — prevents confusion from compounding.',
                                'options' => [
                                    ['text' => 'Skip ahead and hope it becomes clear later', 'correct' => false],
                                    ['text' => 'Ask a question through Student Support or the course discussion area', 'correct' => true],
                                    ['text' => 'Wait until the final exam to bring it up', 'correct' => false],
                                    ['text' => 'Guess on the related quiz question', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'Where can you find current invite links for the BBA WhatsApp Group and Telegram channel?',
                                'type' => 'single_choice',
                                'points' => 10,
                                'explanation' => 'Community invite links are posted on the Student Dashboard announcements and the website footer, since they are refreshed periodically.',
                                'options' => [
                                    ['text' => 'They are emailed once at registration and never change', 'correct' => false],
                                    ['text' => 'Student Dashboard announcements and the BBA website footer', 'correct' => true],
                                    ['text' => 'They are not available to students', 'correct' => false],
                                    ['text' => 'Only instructors have access to them', 'correct' => false],
                                ],
                            ],
                            [
                                'text' => 'True or False: Practical, hands-on assignments can be skipped as long as you pass the written quizzes.',
                                'type' => 'true_false',
                                'points' => 10,
                                'explanation' => 'Practical sessions and assignments are required — theory alone does not build real hospitality skill or satisfy certificate requirements.',
                                'options' => [
                                    ['text' => 'True', 'correct' => false],
                                    ['text' => 'False', 'correct' => true],
                                ],
                            ],
                        ],
                    ],
                ],
            ],
        ];

        // ── 3. Insert courses, modules, lessons, and quizzes ──────────────────
        foreach ($courses as $courseData) {
            $slug = $courseData['slug'];
            $existing = self::db()->fetchOne("SELECT id FROM courses WHERE slug = :s", ['s' => $slug]);

            if ($existing && empty($courseData['resync'])) {
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

            $courseFields = [
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
                'passing_score'      => $courseData['passing_score'] ?? 70,
            ];

            if ($existing) {
                // Resync: refresh the course record and rebuild its curriculum in place,
                // without touching enrollments/certificates that reference the course_id.
                $courseId = (int)$existing['id'];
                self::db()->update('courses', $courseFields, ['id' => $courseId]);

                $oldQuizIds = array_column(self::db()->fetchAll("SELECT id FROM quizzes WHERE course_id = :c", ['c' => $courseId]), 'id');
                foreach ($oldQuizIds as $qid) {
                    self::db()->delete('quizzes', ['id' => $qid]);
                }
                $oldModuleIds = array_column(self::db()->fetchAll("SELECT id FROM modules WHERE course_id = :c", ['c' => $courseId]), 'id');
                foreach ($oldModuleIds as $mid) {
                    self::db()->delete('modules', ['id' => $mid]);
                }
                echo "  ↻ Resyncing curriculum for: {$courseData['title']}\n";
            } else {
                $courseId = self::db()->insert('courses', $courseFields + ['created_by' => $instructorId]);
                self::db()->insert('course_instructors', [
                    'course_id' => $courseId,
                    'user_id'   => $instructorId,
                ]);
            }

            // Insert modules + lessons
            $moduleIdsByIndex = [];
            foreach ($courseData['modules'] as $sortModule => $module) {
                $moduleId = self::db()->insert('modules', [
                    'course_id'  => $courseId,
                    'title'      => $module['title'],
                    'description'=> $module['description'] ?? '',
                    'sort_order' => $sortModule + 1,
                ]);
                $moduleIdsByIndex[$sortModule] = $moduleId;

                foreach ($module['lessons'] as $sortLesson => $lesson) {
                    $isArray     = is_array($lesson);
                    $lessonTitle = $isArray ? $lesson['title'] : $lesson;
                    $lessonSlug  = strtolower(preg_replace('/[^a-z0-9]+/i', '-', trim($lessonTitle)));
                    $lessonSlug .= '-' . $courseId . '-' . $moduleId . '-' . ($sortLesson + 1);

                    self::db()->insert('lessons', [
                        'module_id'       => $moduleId,
                        'course_id'       => $courseId,
                        'title'           => $lessonTitle,
                        'slug'            => substr($lessonSlug, 0, 200),
                        'lesson_type'     => $isArray ? ($lesson['lesson_type'] ?? 'text') : 'text',
                        'content'         => $isArray && isset($lesson['content'])
                                                ? $lesson['content']
                                                : 'Content for: ' . $lessonTitle . '. Full video and materials will be uploaded before course launch.',
                        'duration_minutes'=> $isArray ? ($lesson['duration_minutes'] ?? 15) : 15,
                        'is_free_preview' => ($sortLesson === 0 && $sortModule === 0) ? 1 : 0,
                        'sort_order'      => $sortLesson + 1,
                    ]);
                }
            }

            // Insert quizzes (optional, keyed to a module by index)
            foreach ($courseData['quizzes'] ?? [] as $quizData) {
                $quizId = self::db()->insert('quizzes', [
                    'course_id'          => $courseId,
                    'module_id'          => $moduleIdsByIndex[$quizData['module_index']] ?? null,
                    'title'              => $quizData['title'],
                    'description'        => $quizData['description'] ?? '',
                    'time_limit_minutes' => $quizData['time_limit_minutes'] ?? 20,
                    'passing_score'      => $quizData['passing_score'] ?? 75,
                    'max_attempts'       => $quizData['max_attempts'] ?? 3,
                    'is_published'       => 1,
                ]);

                foreach ($quizData['questions'] as $sortQ => $question) {
                    $questionId = self::db()->insert('quiz_questions', [
                        'quiz_id'       => $quizId,
                        'question_text' => $question['text'],
                        'question_type' => $question['type'] ?? 'single_choice',
                        'points'        => $question['points'] ?? 10,
                        'explanation'   => $question['explanation'] ?? '',
                        'sort_order'    => $sortQ + 1,
                    ]);

                    foreach ($question['options'] as $sortO => $option) {
                        self::db()->insert('quiz_options', [
                            'question_id' => $questionId,
                            'option_text' => $option['text'],
                            'is_correct'  => !empty($option['correct']) ? 1 : 0,
                            'sort_order'  => $sortO + 1,
                        ]);
                    }
                }
            }

            echo "  ✓ Seeded: {$courseData['title']} ({$courseId})\n";
        }

        echo "✓ BBA Full Course Catalog seeded successfully.\n";
    }
}
