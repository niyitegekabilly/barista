<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Coupon;
use App\Models\CouponCampaign;
use App\Models\Course;
use App\Models\Category;
use App\Models\User;
use App\Services\CouponService;

class AdminCouponController extends Controller {

    /**
     * Promotions & Discounts Executive Dashboard.
     */
    public function dashboard(Request $request): void {
        $startDate = $request->input('start_date');
        $endDate = $request->input('end_date');
        $range = $request->input('range', 'this_month');

        if ($range === 'today') {
            $startDate = date('Y-m-d');
            $endDate = date('Y-m-d');
        } elseif ($range === 'yesterday') {
            $startDate = date('Y-m-d', strtotime('-1 day'));
            $endDate = date('Y-m-d', strtotime('-1 day'));
        } elseif ($range === 'last_7_days') {
            $startDate = date('Y-m-d', strtotime('-7 days'));
            $endDate = date('Y-m-d');
        } elseif ($range === 'this_month') {
            $startDate = date('Y-m-01');
            $endDate = date('Y-m-t');
        } elseif ($range === 'last_month') {
            $startDate = date('Y-m-01', strtotime('first day of last month'));
            $endDate = date('Y-m-t', strtotime('last day of last month'));
        } elseif ($range === 'this_year') {
            $startDate = date('Y-01-01');
            $endDate = date('Y-12-31');
        }

        $kpis = CouponService::getDashboardKpis($startDate, $endDate);
        $chartData = CouponService::getChartData();
        $recentRedemptions = CouponService::getRedemptions([], 1, 8)['data'];

        $this->render('admin/coupons/dashboard', [
            'pageTitle' => 'Promotions & Discounts Dashboard',
            'kpis' => $kpis,
            'chartData' => $chartData,
            'recentRedemptions' => $recentRedemptions,
            'filters' => [
                'range' => $range,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ], 'dashboard');
    }

    /**
     * Coupons List Table with search and filters.
     */
    public function index(Request $request): void {
        $filters = [
            'q'             => $request->input('q', ''),
            'status'        => $request->input('status', 'all'),
            'campaign_id'   => $request->input('campaign_id', 'all'),
            'discount_type' => $request->input('discount_type', 'all')
        ];

        $page = (int)$request->input('page', 1);
        $couponsData = CouponService::getCoupons($filters, $page, 20);
        $campaigns = Database::fetchAll("SELECT id, name FROM coupon_campaigns ORDER BY name ASC");

        $this->render('admin/coupons/index', [
            'pageTitle' => 'Coupons & Discount Codes',
            'coupons' => $couponsData['data'],
            'pagination' => $couponsData,
            'campaigns' => $campaigns,
            'filters' => $filters
        ], 'dashboard');
    }

    /**
     * Create Coupon Form.
     */
    public function create(Request $request): void {
        $campaigns = Database::fetchAll("SELECT id, name FROM coupon_campaigns WHERE status = 'active' ORDER BY name ASC");
        $courses = Database::fetchAll("SELECT id, title, price FROM courses WHERE is_published = 1 ORDER BY title ASC");
        $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");
        $users = Database::fetchAll("SELECT id, name, email FROM users ORDER BY name ASC LIMIT 100");

        $this->render('admin/coupons/create', [
            'pageTitle' => 'Create Promotional Coupon',
            'campaigns' => $campaigns,
            'courses' => $courses,
            'categories' => $categories,
            'users' => $users,
            'suggestedCode' => Coupon::generateCode('BBA', 6)
        ], 'dashboard');
    }

    /**
     * Store new Coupon.
     */
    public function store(Request $request): void {
        $code = strtoupper(trim($request->input('code', '')));
        if (empty($code)) {
            $code = Coupon::generateCode($request->input('prefix', 'BBA'), 6);
        }

        // Uniqueness check
        $existing = Database::fetchOne("SELECT id FROM coupons WHERE code = :c", ['c' => $code]);
        if ($existing) {
            $this->flash('danger', "A coupon code '{$code}' already exists. Please use a unique code.");
            $this->redirect('admin/coupons/create');
            return;
        }

        $couponId = Database::insert('coupons', [
            'code'                => $code,
            'name'                => trim($request->input('name', '')) ?: $code,
            'description'         => trim($request->input('description', '')),
            'campaign_id'         => !empty($request->input('campaign_id')) ? (int)$request->input('campaign_id') : null,
            'discount_type'       => $request->input('discount_type', 'percentage'),
            'discount_value'      => (float)$request->input('discount_value', 10.0),
            'currency'            => $request->input('currency', 'RWF'),
            'max_discount_amount' => (float)$request->input('max_discount_amount', 0.0),
            'min_spend'           => (float)$request->input('min_spend', 0.0),
            'max_uses'            => (int)$request->input('max_uses', 100),
            'per_user_limit'      => (int)$request->input('per_user_limit', 1),
            'user_eligibility'    => $request->input('user_eligibility', 'all'),
            'is_stackable'        => !empty($request->input('is_stackable')) ? 1 : 0,
            'sale_price_rule'     => $request->input('sale_price_rule', 'apply_to_sale_price'),
            'start_date'          => !empty($request->input('start_date')) ? $request->input('start_date') : null,
            'expires_at'          => !empty($request->input('expires_at')) ? $request->input('expires_at') : null,
            'is_active'           => 1,
            'status'              => 'active',
            'created_by'          => auth_id(),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s')
        ]);

        // Sync Included & Excluded Courses
        $includedCourses = (array)$request->input('included_courses', []);
        foreach ($includedCourses as $crsId) {
            if (!empty($crsId)) {
                Database::insert('coupon_courses', ['coupon_id' => $couponId, 'course_id' => (int)$crsId, 'type' => 'include']);
            }
        }

        $excludedCourses = (array)$request->input('excluded_courses', []);
        foreach ($excludedCourses as $crsId) {
            if (!empty($crsId)) {
                Database::insert('coupon_courses', ['coupon_id' => $couponId, 'course_id' => (int)$crsId, 'type' => 'exclude']);
            }
        }

        // Sync Included & Excluded Categories
        $includedCategories = (array)$request->input('included_categories', []);
        foreach ($includedCategories as $catId) {
            if (!empty($catId)) {
                Database::insert('coupon_categories', ['coupon_id' => $couponId, 'category_id' => (int)$catId, 'type' => 'include']);
            }
        }

        $excludedCategories = (array)$request->input('excluded_categories', []);
        foreach ($excludedCategories as $catId) {
            if (!empty($catId)) {
                Database::insert('coupon_categories', ['coupon_id' => $couponId, 'category_id' => (int)$catId, 'type' => 'exclude']);
            }
        }

        // Sync Restricted Users
        $restrictedUsers = (array)$request->input('restricted_users', []);
        foreach ($restrictedUsers as $uId) {
            if (!empty($uId)) {
                Database::insert('coupon_users', ['coupon_id' => $couponId, 'user_id' => (int)$uId]);
            }
        }

        Coupon::logActivity($couponId, !empty($request->input('campaign_id')) ? (int)$request->input('campaign_id') : null, 'created', [
            'code' => $code,
            'discount' => $request->input('discount_value')
        ], auth_id());

        $this->flash('success', "Coupon '{$code}' created successfully!");
        $this->redirect('admin/coupons/' . $couponId);
    }

    /**
     * 360° Coupon Detail Workspace (7 Comprehensive Tabs).
     */
    public function show(Request $request, int $id): void {
        $coupon = Coupon::findWithRelations($id);
        if (!$coupon) {
            $this->flash('danger', 'Coupon not found.');
            $this->redirect('admin/coupons');
            return;
        }

        $redemptions = CouponService::getRedemptions(['coupon_id' => $id], 1, 50)['data'];
        $activityLogs = Database::fetchAll(
            "SELECT cal.*, u.name as user_name FROM coupon_activity_logs cal LEFT JOIN users u ON cal.user_id = u.id WHERE cal.coupon_id = :id ORDER BY cal.created_at DESC",
            ['id' => $id]
        );

        $this->render('admin/coupons/show', [
            'pageTitle' => 'Coupon: ' . $coupon['code'],
            'coupon' => $coupon,
            'redemptions' => $redemptions,
            'activityLogs' => $activityLogs
        ], 'dashboard');
    }

    /**
     * Edit Coupon Form.
     */
    public function edit(Request $request, int $id): void {
        $coupon = Coupon::findWithRelations($id);
        if (!$coupon) {
            $this->abort(404);
            return;
        }

        $campaigns = Database::fetchAll("SELECT id, name FROM coupon_campaigns ORDER BY name ASC");
        $courses = Database::fetchAll("SELECT id, title, price FROM courses WHERE is_published = 1 ORDER BY title ASC");
        $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");

        $this->render('admin/coupons/edit', [
            'pageTitle' => 'Edit Coupon: ' . $coupon['code'],
            'coupon' => $coupon,
            'campaigns' => $campaigns,
            'courses' => $courses,
            'categories' => $categories
        ], 'dashboard');
    }

    /**
     * Update Coupon.
     */
    public function update(Request $request, int $id): void {
        $coupon = Coupon::find($id);
        if (!$coupon) {
            $this->abort(404);
            return;
        }

        Database::update('coupons', [
            'name'                => trim($request->input('name', '')) ?: $coupon['code'],
            'description'         => trim($request->input('description', '')),
            'campaign_id'         => !empty($request->input('campaign_id')) ? (int)$request->input('campaign_id') : null,
            'discount_type'       => $request->input('discount_type', $coupon['discount_type']),
            'discount_value'      => (float)$request->input('discount_value', $coupon['discount_value']),
            'max_discount_amount' => (float)$request->input('max_discount_amount', 0.0),
            'min_spend'           => (float)$request->input('min_spend', 0.0),
            'max_uses'            => (int)$request->input('max_uses', 100),
            'per_user_limit'      => (int)$request->input('per_user_limit', 1),
            'user_eligibility'    => $request->input('user_eligibility', 'all'),
            'is_stackable'        => !empty($request->input('is_stackable')) ? 1 : 0,
            'sale_price_rule'     => $request->input('sale_price_rule', 'apply_to_sale_price'),
            'start_date'          => !empty($request->input('start_date')) ? $request->input('start_date') : null,
            'expires_at'          => !empty($request->input('expires_at')) ? $request->input('expires_at') : null,
            'status'              => $request->input('status', 'active'),
            'updated_at'          => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        // Sync Courses
        Database::query("DELETE FROM coupon_courses WHERE coupon_id = :id", ['id' => $id]);
        $includedCourses = (array)$request->input('included_courses', []);
        foreach ($includedCourses as $crsId) {
            if (!empty($crsId)) {
                Database::insert('coupon_courses', ['coupon_id' => $id, 'course_id' => (int)$crsId, 'type' => 'include']);
            }
        }
        $excludedCourses = (array)$request->input('excluded_courses', []);
        foreach ($excludedCourses as $crsId) {
            if (!empty($crsId)) {
                Database::insert('coupon_courses', ['coupon_id' => $id, 'course_id' => (int)$crsId, 'type' => 'exclude']);
            }
        }

        // Sync Categories
        Database::query("DELETE FROM coupon_categories WHERE coupon_id = :id", ['id' => $id]);
        $includedCategories = (array)$request->input('included_categories', []);
        foreach ($includedCategories as $catId) {
            if (!empty($catId)) {
                Database::insert('coupon_categories', ['coupon_id' => $id, 'category_id' => (int)$catId, 'type' => 'include']);
            }
        }
        $excludedCategories = (array)$request->input('excluded_categories', []);
        foreach ($excludedCategories as $catId) {
            if (!empty($catId)) {
                Database::insert('coupon_categories', ['coupon_id' => $id, 'category_id' => (int)$catId, 'type' => 'exclude']);
            }
        }

        Coupon::logActivity($id, !empty($request->input('campaign_id')) ? (int)$request->input('campaign_id') : null, 'updated', [
            'discount' => $request->input('discount_value'),
            'status' => $request->input('status')
        ], auth_id());

        $this->flash('success', "Coupon '{$coupon['code']}' updated successfully!");
        $this->redirect('admin/coupons/' . $id);
    }

    /**
     * Duplicate Coupon.
     */
    public function duplicate(Request $request, int $id): void {
        $source = Coupon::find($id);
        if (!$source) {
            $this->abort(404);
            return;
        }

        $newCode = Coupon::generateCode('BBA', 6);
        $newCouponId = Database::insert('coupons', [
            'code'                => $newCode,
            'name'                => 'Copy of ' . $source['name'],
            'description'         => $source['description'],
            'campaign_id'         => $source['campaign_id'],
            'discount_type'       => $source['discount_type'],
            'discount_value'      => $source['discount_value'],
            'currency'            => $source['currency'],
            'max_discount_amount' => $source['max_discount_amount'],
            'min_spend'           => $source['min_spend'],
            'max_uses'            => $source['max_uses'],
            'uses_count'          => 0,
            'per_user_limit'      => $source['per_user_limit'],
            'user_eligibility'    => $source['user_eligibility'],
            'is_stackable'        => $source['is_stackable'],
            'sale_price_rule'     => $source['sale_price_rule'],
            'start_date'          => $source['start_date'],
            'expires_at'          => $source['expires_at'],
            'is_active'           => 1,
            'status'              => 'active',
            'created_by'          => auth_id(),
            'created_at'          => date('Y-m-d H:i:s'),
            'updated_at'          => date('Y-m-d H:i:s')
        ]);

        Coupon::logActivity($newCouponId, $source['campaign_id'], 'duplicated', ['source_code' => $source['code']], auth_id());

        $this->flash('success', "Coupon duplicated as new code '{$newCode}'!");
        $this->redirect('admin/coupons/' . $newCouponId);
    }

    /**
     * Toggle Active / Disabled Status.
     */
    public function toggle(Request $request, int $id): void {
        $coupon = Coupon::find($id);
        if ($coupon) {
            $newActive = $coupon['is_active'] ? 0 : 1;
            $newStatus = $newActive ? 'active' : 'disabled';
            Database::update('coupons', ['is_active' => $newActive, 'status' => $newStatus], ['id' => $id]);
            Coupon::logActivity($id, $coupon['campaign_id'], 'status_toggled', ['is_active' => $newActive], auth_id());
            $this->flash('success', "Coupon status updated to " . strtoupper($newStatus));
        }

        $this->redirect('admin/coupons');
    }

    /**
     * Archive Coupon.
     */
    public function archive(Request $request, int $id): void {
        $coupon = Coupon::find($id);
        if ($coupon) {
            Database::update('coupons', ['status' => 'archived', 'is_active' => 0], ['id' => $id]);
            Coupon::logActivity($id, $coupon['campaign_id'], 'archived', [], auth_id());
            $this->flash('success', "Coupon '{$coupon['code']}' has been archived.");
        }

        $this->redirect('admin/coupons');
    }

    /**
     * Delete Coupon (Safely guarded against coupons with redemptions).
     */
    public function delete(Request $request, int $id): void {
        $redemptionsCount = (int)(Database::fetchValue("SELECT COUNT(*) FROM coupon_redemptions WHERE coupon_id = :id", ['id' => $id]) ?: 0);
        if ($redemptionsCount > 0) {
            $this->flash('danger', "This coupon has {$redemptionsCount} recorded financial redemption(s) and cannot be deleted. You may Archive it instead.");
            $this->redirect('admin/coupons/' . $id);
            return;
        }

        Database::query("DELETE FROM coupons WHERE id = :id", ['id' => $id]);
        $this->flash('success', 'Coupon deleted successfully.');
        $this->redirect('admin/coupons');
    }

    /**
     * Dedicated Redemptions History Ledger.
     */
    public function redemptions(Request $request): void {
        $filters = [
            'q'           => $request->input('q', ''),
            'coupon_id'   => $request->input('coupon_id', ''),
            'campaign_id' => $request->input('campaign_id', ''),
            'start_date'  => $request->input('start_date', ''),
            'end_date'    => $request->input('end_date', '')
        ];

        $page = (int)$request->input('page', 1);
        $redemptionsData = CouponService::getRedemptions($filters, $page, 25);

        $this->render('admin/coupons/redemptions', [
            'pageTitle' => 'Coupon Redemptions History',
            'redemptions' => $redemptionsData['data'],
            'pagination' => $redemptionsData,
            'filters' => $filters
        ], 'dashboard');
    }

    /**
     * Bulk Coupon Code Generator.
     */
    public function bulkGenerate(Request $request): void {
        if ($request->method() === 'POST') {
            $params = $request->all();
            $res = CouponService::bulkGenerate($params, auth_id());

            $this->flash('success', "Successfully generated {$res['count']} unique promotional coupons!");
            $this->redirect('admin/coupons');
            return;
        }

        $campaigns = Database::fetchAll("SELECT id, name FROM coupon_campaigns WHERE status = 'active' ORDER BY name ASC");
        $this->render('admin/coupons/bulk_generate', [
            'pageTitle' => 'Bulk Coupon Generator',
            'campaigns' => $campaigns
        ], 'dashboard');
    }

    /**
     * Export Coupons to CSV.
     */
    public function export(Request $request): void {
        $csv = CouponService::exportCouponsCsv($request->all());
        $filename = 'bba_coupons_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
        exit;
    }

    /**
     * Export Redemptions to CSV.
     */
    public function exportRedemptions(Request $request): void {
        $csv = CouponService::exportRedemptionsCsv($request->all());
        $filename = 'bba_coupon_redemptions_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        echo $csv;
        exit;
    }
}
