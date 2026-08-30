<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\Membership;
use App\Models\MembershipPlan;
use App\Models\Course;
use App\Models\Category;
use App\Services\MembershipService;

class AdminMembershipController extends Controller {

    /**
     * Executive MRR & Subscription Analytics Dashboard.
     */
    public function dashboard(Request $request): void {
        $kpis = MembershipService::getDashboardKpis();
        $chartData = MembershipService::getChartData();

        $recentSubscriptions = Database::fetchAll(
            "SELECT m.*, u.name as user_name, u.email as user_email, p.name as plan_name, p.billing_interval, p.price as plan_price
             FROM memberships m
             JOIN users u ON m.user_id = u.id
             JOIN membership_plans p ON m.plan_id = p.id
             ORDER BY m.created_at DESC
             LIMIT 8"
        );

        $this->render('admin/memberships/dashboard', [
            'pageTitle' => 'Memberships & Recurring Revenue Hub',
            'kpis' => $kpis,
            'chartData' => $chartData,
            'recentSubscriptions' => $recentSubscriptions
        ], 'dashboard');
    }

    /**
     * Active & Historical Subscriptions List.
     */
    public function subscriptions(Request $request): void {
        $filters = [
            'q'       => $request->input('q', ''),
            'status'  => $request->input('status', 'all'),
            'plan_id' => $request->input('plan_id', 'all')
        ];

        $page = max(1, (int)$request->input('page', 1));
        $perPage = 20;

        $result = MembershipService::getSubscriptions($filters, $page, $perPage);
        $plans = Database::fetchAll("SELECT id, name FROM membership_plans WHERE status != 'archived' ORDER BY sort_order ASC, price ASC");

        $this->render('admin/memberships/subscriptions', [
            'pageTitle' => 'Student Subscriptions Hub',
            'subscriptions' => $result['data'],
            'pagination' => $result,
            'filters' => $filters,
            'plans' => $plans
        ], 'dashboard');
    }

    /**
     * 360° Subscription Detail Workspace.
     */
    public function showSubscription(Request $request, int $id): void {
        $sub = Membership::findWithRelations($id);
        if (!$sub) {
            $this->abort(404);
            return;
        }

        $this->render('admin/memberships/show', [
            'pageTitle' => 'Subscription: ' . $sub['subscription_number'],
            'subscription' => $sub
        ], 'dashboard');
    }

    /**
     * Manually Extend Subscription.
     */
    public function extend(Request $request, int $id): void {
        $days = (int)$request->input('days', 30);
        $reason = trim($request->input('reason', 'Admin complimentary extension'));

        if ($days <= 0) {
            $this->flash('danger', 'Number of days to extend must be at least 1.');
            $this->redirect('admin/memberships/' . $id);
            return;
        }

        $res = MembershipService::extendSubscription($id, $days, $reason, auth_id());

        if ($res['success']) {
            $this->flash('success', $res['message']);
        } else {
            $this->flash('danger', $res['message']);
        }

        $this->redirect('admin/memberships/' . $id);
    }

    /**
     * Cancel Subscription.
     */
    public function cancel(Request $request, int $id): void {
        $immediate = (bool)$request->input('immediate', 0);
        $reason = trim($request->input('reason', 'Admin requested cancellation'));

        $res = MembershipService::cancelSubscription($id, $reason, $immediate, auth_id());

        if ($res['success']) {
            $this->flash('success', $res['message']);
        } else {
            $this->flash('danger', $res['message']);
        }

        $this->redirect('admin/memberships/' . $id);
    }

    /**
     * Membership Plans Management Hub.
     */
    public function plans(Request $request): void {
        $plans = Database::fetchAll(
            "SELECT p.*,
                    (SELECT COUNT(*) FROM memberships WHERE plan_id = p.id AND status IN ('active', 'trialing', 'grace_period')) as active_subscribers_count
             FROM membership_plans p
             ORDER BY p.sort_order ASC, p.price ASC"
        );

        foreach ($plans as &$p) {
            $p['mrr_contribution'] = MembershipPlan::calculateMrr((float)$p['price'], $p['billing_interval'], (int)$p['active_subscribers_count']);
        }

        $this->render('admin/memberships/plans', [
            'pageTitle' => 'Membership Plans & Tiers',
            'plans' => $plans
        ], 'dashboard');
    }

    /**
     * Create Plan Builder View.
     */
    public function createPlan(Request $request): void {
        $courses = Database::fetchAll("SELECT id, title, price FROM courses WHERE is_published = 1 ORDER BY title ASC");
        $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");

        $this->render('admin/memberships/create_plan', [
            'pageTitle' => 'Create Membership Plan',
            'courses' => $courses,
            'categories' => $categories
        ], 'dashboard');
    }

    /**
     * Store New Membership Plan.
     */
    public function storePlan(Request $request): void {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            $this->flash('danger', 'Plan name is required.');
            $this->redirect('admin/membership-plans/create');
            return;
        }

        $slug = slugify($name);
        $baseSlug = $slug;
        $i = 1;
        while (Database::fetchOne("SELECT id FROM membership_plans WHERE slug = :s", ['s' => $slug])) {
            $slug = $baseSlug . '-' . $i++;
        }

        $featuresRaw = $request->input('features', '');
        $featuresList = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $featuresRaw)))));

        $planId = Database::insert('membership_plans', [
            'name'                   => $name,
            'slug'                   => $slug,
            'description'            => trim($request->input('description', '')),
            'price'                  => (float)$request->input('price', 0.0),
            'currency'               => 'RWF',
            'billing_interval'       => $request->input('billing_interval', 'month'),
            'tier_level'             => (int)$request->input('tier_level', 1),
            'trial_period_days'      => (int)$request->input('trial_period_days', 0),
            'grace_period_days'      => (int)$request->input('grace_period_days', 3),
            'course_access_type'     => $request->input('course_access_type', 'all_courses'),
            'course_limit'           => (int)$request->input('course_limit', 0),
            'discount_percentage'    => (float)$request->input('discount_percentage', 0.0),
            'has_certificate_access' => (int)$request->input('has_certificate_access', 1),
            'has_live_workshops'     => (int)$request->input('has_live_workshops', 0),
            'has_job_board_priority' => (int)$request->input('has_job_board_priority', 0),
            'is_featured'            => (int)$request->input('is_featured', 0),
            'is_active'              => (int)$request->input('is_active', 1),
            'sort_order'             => (int)$request->input('sort_order', 0),
            'features'               => json_encode($featuresList),
            'created_at'             => date('Y-m-d H:i:s'),
            'updated_at'             => date('Y-m-d H:i:s')
        ]);

        // Sync mapped courses
        $courses = $request->input('courses', []);
        if (is_array($courses)) {
            foreach ($courses as $cid) {
                Database::insert('membership_plan_courses', ['plan_id' => $planId, 'course_id' => (int)$cid]);
            }
        }

        // Sync mapped categories
        $categories = $request->input('categories', []);
        if (is_array($categories)) {
            foreach ($categories as $catId) {
                Database::insert('membership_plan_categories', ['plan_id' => $planId, 'category_id' => (int)$catId]);
            }
        }

        $this->flash('success', "Membership plan '{$name}' created successfully!");
        $this->redirect('admin/membership-plans');
    }

    /**
     * Edit Plan Builder View.
     */
    public function editPlan(Request $request, int $id): void {
        $plan = MembershipPlan::findWithRelations($id);
        if (!$plan) {
            $this->abort(404);
            return;
        }

        $courses = Database::fetchAll("SELECT id, title, price FROM courses WHERE is_published = 1 ORDER BY title ASC");
        $categories = Database::fetchAll("SELECT id, name FROM categories ORDER BY name ASC");

        $this->render('admin/memberships/edit_plan', [
            'pageTitle' => 'Edit Plan: ' . $plan['name'],
            'plan' => $plan,
            'courses' => $courses,
            'categories' => $categories
        ], 'dashboard');
    }

    /**
     * Update Membership Plan.
     */
    public function updatePlan(Request $request, int $id): void {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            $this->flash('danger', 'Plan name is required.');
            $this->redirect('admin/membership-plans/' . $id . '/edit');
            return;
        }

        $featuresRaw = $request->input('features', '');
        $featuresList = array_values(array_filter(array_map('trim', explode("\n", str_replace("\r", "", $featuresRaw)))));

        Database::update('membership_plans', [
            'name'                   => $name,
            'description'            => trim($request->input('description', '')),
            'price'                  => (float)$request->input('price', 0.0),
            'billing_interval'       => $request->input('billing_interval', 'month'),
            'tier_level'             => (int)$request->input('tier_level', 1),
            'trial_period_days'      => (int)$request->input('trial_period_days', 0),
            'grace_period_days'      => (int)$request->input('grace_period_days', 3),
            'course_access_type'     => $request->input('course_access_type', 'all_courses'),
            'course_limit'           => (int)$request->input('course_limit', 0),
            'discount_percentage'    => (float)$request->input('discount_percentage', 0.0),
            'has_certificate_access' => (int)$request->input('has_certificate_access', 1),
            'has_live_workshops'     => (int)$request->input('has_live_workshops', 0),
            'has_job_board_priority' => (int)$request->input('has_job_board_priority', 0),
            'is_featured'            => (int)$request->input('is_featured', 0),
            'is_active'              => (int)$request->input('is_active', 1),
            'sort_order'             => (int)$request->input('sort_order', 0),
            'features'               => json_encode($featuresList),
            'updated_at'             => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        // Resync mapped courses
        Database::query("DELETE FROM membership_plan_courses WHERE plan_id = :id", ['id' => $id]);
        $courses = $request->input('courses', []);
        if (is_array($courses)) {
            foreach ($courses as $cid) {
                Database::insert('membership_plan_courses', ['plan_id' => $id, 'course_id' => (int)$cid]);
            }
        }

        // Resync mapped categories
        Database::query("DELETE FROM membership_plan_categories WHERE plan_id = :id", ['id' => $id]);
        $categories = $request->input('categories', []);
        if (is_array($categories)) {
            foreach ($categories as $catId) {
                Database::insert('membership_plan_categories', ['plan_id' => $id, 'category_id' => (int)$catId]);
            }
        }

        $this->flash('success', "Plan '{$name}' updated successfully.");
        $this->redirect('admin/membership-plans');
    }

    /**
     * Safe Delete / Archive Plan.
     */
    public function deletePlan(Request $request, int $id): void {
        $hasActiveSubs = (int)(Database::fetchValue("SELECT COUNT(*) FROM memberships WHERE plan_id = :id AND status IN ('active', 'trialing', 'grace_period')", ['id' => $id]) ?: 0);

        if ($hasActiveSubs > 0) {
            Database::update('membership_plans', ['is_active' => 0, 'status' => 'archived'], ['id' => $id]);
            $this->flash('warning', "Plan has active subscribers. It has been archived and hidden rather than deleted.");
        } else {
            Database::query("DELETE FROM membership_plan_courses WHERE plan_id = :id", ['id' => $id]);
            Database::query("DELETE FROM membership_plan_categories WHERE plan_id = :id", ['id' => $id]);
            Database::query("DELETE FROM membership_plans WHERE id = :id", ['id' => $id]);
            $this->flash('success', "Plan deleted permanently.");
        }

        $this->redirect('admin/membership-plans');
    }

    /**
     * Export Subscriptions to CSV.
     */
    public function exportSubscriptions(Request $request): void {
        $filters = [
            'q'       => $request->input('q', ''),
            'status'  => $request->input('status', 'all'),
            'plan_id' => $request->input('plan_id', 'all')
        ];

        $csv = MembershipService::exportSubscriptionsCsv($filters);
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="beyond_barista_subscriptions_' . date('Ymd_His') . '.csv"');
        echo $csv;
        exit;
    }
}
