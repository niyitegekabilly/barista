<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Database;
use App\Models\CouponCampaign;
use App\Models\Coupon;
use App\Services\CouponService;

class AdminCampaignController extends Controller {

    /**
     * Marketing Campaigns Index.
     */
    public function index(Request $request): void {
        $campaigns = Database::fetchAll(
            "SELECT c.*, u.name as creator_name,
                    (SELECT COUNT(*) FROM coupons WHERE campaign_id = c.id) as coupons_count,
                    (SELECT COUNT(*) FROM coupon_redemptions WHERE campaign_id = c.id) as redemptions_count,
                    (SELECT COALESCE(SUM(final_amount), 0) FROM coupon_redemptions WHERE campaign_id = c.id) as total_revenue
             FROM coupon_campaigns c
             LEFT JOIN users u ON c.created_by = u.id
             ORDER BY c.created_at DESC"
        );

        $this->render('admin/campaigns/index', [
            'pageTitle' => 'Marketing Promotion Campaigns',
            'campaigns' => $campaigns
        ], 'dashboard');
    }

    /**
     * Store new Campaign.
     */
    public function store(Request $request): void {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            $this->flash('danger', 'Campaign name is required.');
            $this->redirect('admin/campaigns');
            return;
        }

        $slug = slugify($name);
        $baseSlug = $slug;
        $i = 1;
        while (Database::fetchOne("SELECT id FROM coupon_campaigns WHERE slug = :s", ['s' => $slug])) {
            $slug = $baseSlug . '-' . $i++;
        }

        $campaignId = Database::insert('coupon_campaigns', [
            'name'           => $name,
            'slug'           => $slug,
            'description'    => trim($request->input('description', '')),
            'budget_limit'   => (float)$request->input('budget_limit', 0.0),
            'discount_spent' => 0.00,
            'start_date'     => !empty($request->input('start_date')) ? $request->input('start_date') : null,
            'end_date'       => !empty($request->input('end_date')) ? $request->input('end_date') : null,
            'status'         => $request->input('status', 'active'),
            'created_by'     => auth_id(),
            'created_at'     => date('Y-m-d H:i:s'),
            'updated_at'     => date('Y-m-d H:i:s')
        ]);

        Coupon::logActivity(null, $campaignId, 'campaign_created', ['name' => $name], auth_id());

        $this->flash('success', "Campaign '{$name}' created successfully!");
        $this->redirect('admin/campaigns/' . $campaignId);
    }

    /**
     * 360° Campaign Performance Workspace.
     */
    public function show(Request $request, int $id): void {
        $campaign = CouponCampaign::findWithMetrics($id);
        if (!$campaign) {
            $this->abort(404);
            return;
        }

        $redemptions = CouponService::getRedemptions(['campaign_id' => $id], 1, 30)['data'];

        $this->render('admin/campaigns/show', [
            'pageTitle' => 'Campaign: ' . $campaign['name'],
            'campaign' => $campaign,
            'redemptions' => $redemptions
        ], 'dashboard');
    }

    /**
     * Update Campaign.
     */
    public function update(Request $request, int $id): void {
        $name = trim($request->input('name', ''));
        if (empty($name)) {
            $this->flash('danger', 'Campaign name is required.');
            $this->redirect('admin/campaigns/' . $id);
            return;
        }

        Database::update('coupon_campaigns', [
            'name'         => $name,
            'description'  => trim($request->input('description', '')),
            'budget_limit' => (float)$request->input('budget_limit', 0.0),
            'start_date'   => !empty($request->input('start_date')) ? $request->input('start_date') : null,
            'end_date'     => !empty($request->input('end_date')) ? $request->input('end_date') : null,
            'status'       => $request->input('status', 'active'),
            'updated_at'   => date('Y-m-d H:i:s')
        ], ['id' => $id]);

        Coupon::logActivity(null, $id, 'campaign_updated', ['name' => $name], auth_id());

        $this->flash('success', "Campaign updated successfully.");
        $this->redirect('admin/campaigns/' . $id);
    }

    /**
     * Delete Campaign.
     */
    public function delete(Request $request, int $id): void {
        Database::query("UPDATE coupons SET campaign_id = NULL WHERE campaign_id = :id", ['id' => $id]);
        Database::query("DELETE FROM coupon_campaigns WHERE id = :id", ['id' => $id]);

        $this->flash('success', "Campaign removed.");
        $this->redirect('admin/campaigns');
    }
}
