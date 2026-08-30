<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\FinanceService;

class AdminFinanceController extends Controller {

    /**
     * Executive Finance Dashboard with KPI metrics, charts, and recent sales.
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
        } elseif ($range === 'last_30_days') {
            $startDate = date('Y-m-d', strtotime('-30 days'));
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

        $kpis = FinanceService::getDashboardKpis($startDate, $endDate);
        $chartData = FinanceService::getChartData();
        $recentOrders = FinanceService::getOrders([], 1, 8)['data'];

        $this->render('admin/finance/dashboard', [
            'pageTitle' => 'Finance & Revenue Dashboard',
            'kpis' => $kpis,
            'chartData' => $chartData,
            'recentOrders' => $recentOrders,
            'filters' => [
                'range' => $range,
                'start_date' => $startDate,
                'end_date' => $endDate
            ]
        ], 'dashboard');
    }

    /**
     * Financial Reports & Sales breakdowns.
     */
    public function reports(Request $request): void {
        $chartData = FinanceService::getChartData();
        $kpis = FinanceService::getDashboardKpis();

        $this->render('admin/finance/reports', [
            'pageTitle' => 'Financial Reports & Analytics',
            'kpis' => $kpis,
            'chartData' => $chartData
        ], 'dashboard');
    }

    /**
     * Financial Transaction Ledger.
     */
    public function ledger(Request $request): void {
        $page = (int)$request->input('page', 1);
        $ledger = FinanceService::getLedgerTransactions([], $page, 25);

        $this->render('admin/finance/ledger', [
            'pageTitle' => 'Financial Ledger & Cash Flow',
            'ledger' => $ledger
        ], 'dashboard');
    }
}
