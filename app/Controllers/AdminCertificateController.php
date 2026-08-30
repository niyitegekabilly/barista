<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Services\CertificateService;

class AdminCertificateController extends Controller {

    /**
     * Admin Certificates Management Hub
     */
    public function index(Request $request): void {
        $search = trim($request->input('q', ''));
        $status = trim($request->input('status', ''));
        $courseId = (int)$request->input('course_id', 0);
        $page = max(1, (int)$request->input('page', 1));
        $perPage = 15;
        $offset = ($page - 1) * $perPage;

        $kpis = CertificateService::getCertificateKpis();

        $where = ["1=1"];
        $params = [];

        if (!empty($search)) {
            $where[] = "(c.certificate_number LIKE :s1 OR c.student_name LIKE :s2 OR u.email LIKE :s3)";
            $params['s1'] = "%{$search}%";
            $params['s2'] = "%{$search}%";
            $params['s3'] = "%{$search}%";
        }

        if (!empty($status)) {
            $where[] = "c.status = :st";
            $params['st'] = $status;
        }

        if ($courseId > 0) {
            $where[] = "c.course_id = :cid";
            $params['cid'] = $courseId;
        }

        $whereStr = implode(' AND ', $where);

        $totalCount = (int)(Database::fetchValue(
            "SELECT COUNT(*) FROM certificates c JOIN users u ON c.user_id = u.id WHERE {$whereStr}",
            $params
        ) ?: 0);

        $certificates = Database::fetchAll(
            "SELECT c.*, u.email as student_email, u.student_id,
                    (SELECT COUNT(*) FROM certificate_verifications WHERE certificate_id = c.id) as verifications_count
             FROM certificates c
             JOIN users u ON c.user_id = u.id
             WHERE {$whereStr}
             ORDER BY c.issue_date DESC, c.id DESC
             LIMIT {$perPage} OFFSET {$offset}",
            $params
        );

        $courses = Database::fetchAll("SELECT id, title FROM courses ORDER BY title ASC");
        $users = Database::fetchAll("SELECT id, name, email, student_id FROM users WHERE role_id = 4 ORDER BY name ASC LIMIT 100");

        $pagination = [
            'total'        => $totalCount,
            'per_page'     => $perPage,
            'current_page' => $page,
            'last_page'    => max(1, (int)ceil($totalCount / $perPage))
        ];

        $this->render('admin/certificates/index', [
            'pageTitle'    => 'Certificates & Credentials Management',
            'kpis'         => $kpis,
            'certificates' => $certificates,
            'pagination'   => $pagination,
            'search'       => $search,
            'status'       => $status,
            'courseId'     => $courseId,
            'courses'      => $courses,
            'users'        => $users
        ], 'dashboard');
    }

    /**
     * Manually Issue a Certificate
     */
    public function issue(Request $request): void {
        $userId = (int)$request->input('user_id');
        $courseId = (int)$request->input('course_id');
        $gradeScore = $request->input('grade_score') ? (float)$request->input('grade_score') : 100.00;

        $res = CertificateService::issueManualCertificate($userId, $courseId, null, $gradeScore);
        if ($res['success']) {
            $this->flash('success', "Certificate {$res['certificate_number']} issued successfully.");
        } else {
            $this->flash('error', $res['message']);
        }
        $this->redirect('admin/certificates');
    }

    /**
     * Revoke a Certificate
     */
    public function revoke(Request $request, int $id): void {
        $reason = (string)$request->input('revocation_reason', 'Administrative revocation');
        $res = CertificateService::revokeCertificate($id, $reason);
        if ($res['success']) {
            $this->flash('success', $res['message']);
        } else {
            $this->flash('error', $res['message']);
        }
        $this->redirect('admin/certificates');
    }

    /**
     * Reissue / Restore a Revoked Certificate
     */
    public function reissue(Request $request, int $id): void {
        $res = CertificateService::reissueCertificate($id);
        if ($res['success']) {
            $this->flash('success', $res['message']);
        } else {
            $this->flash('error', $res['message']);
        }
        $this->redirect('admin/certificates');
    }

    /**
     * Export CSV Report
     */
    public function exportCsv(): void {
        $csv = CertificateService::exportCertificatesCsv();
        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="bba-certificates-' . date('Ymd-His') . '.csv"');
        echo $csv;
        exit;
    }
}
