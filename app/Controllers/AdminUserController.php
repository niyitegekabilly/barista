<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Core\Response;
use App\Core\Database;
use App\Models\User;
use App\Models\Role;
use App\Models\Permission;
use App\Models\Cohort;
use App\Models\UserNote;
use App\Models\Course;
use App\Models\Invitation;
use App\Models\AuditLog;
use App\Services\UserService;
use App\Services\InvitationService;

class AdminUserController extends Controller {

    /**
     * Users Directory with KPI cards, multi-criteria filters, and bulk operations.
     */
    public function index(Request $request): void {
        $filters = [
            'q' => $request->input('q', ''),
            'role' => $request->input('role', ''),
            'status' => $request->input('status', ''),
            'cohort_id' => $request->input('cohort_id', ''),
            'course_id' => $request->input('course_id', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
            'sort' => $request->input('sort', 'created_at'),
            'dir' => $request->input('dir', 'DESC'),
            'page' => $request->input('page', 1),
            'per_page' => $request->input('per_page', 25)
        ];

        $kpis = UserService::getKpiStats();
        $result = UserService::queryUsers($filters);
        
        $roles = Role::all();
        $cohorts = Cohort::all();
        $courses = Course::all();

        $this->render('admin/users/index', [
            'pageTitle' => 'Users & Access Management',
            'users' => $result['data'],
            'pagination' => $result['pagination'],
            'kpis' => $kpis,
            'filters' => $filters,
            'roles' => $roles,
            'cohorts' => $cohorts,
            'courses' => $courses
        ], 'dashboard');
    }

    /**
     * 360° Comprehensive User Profile.
     */
    public function show(Request $request, int $id): void {
        $data = UserService::get360UserProfile($id);
        if (!$data) {
            $this->flash('danger', 'User not found.');
            $this->redirect('admin/users');
            return;
        }

        $allRoles = Role::all();
        $allPermissions = Permission::allGroupedByModule();
        $allCohorts = Cohort::all();
        $allCourses = Course::all();

        $this->render('admin/users/show', array_merge($data, [
            'pageTitle' => 'User Profile — ' . ($data['user']['name'] ?? 'User'),
            'allRoles' => $allRoles,
            'allPermissions' => $allPermissions,
            'allCohorts' => $allCohorts,
            'allCourses' => $allCourses
        ]), 'dashboard');
    }

    /**
     * Show Create User Form.
     */
    public function create(Request $request): void {
        $roles = Role::all();
        $cohorts = Cohort::all();

        $this->render('admin/users/create', [
            'pageTitle' => 'Create New User',
            'roles' => $roles,
            'cohorts' => $cohorts
        ], 'dashboard');
    }

    /**
     * Store New User.
     */
    public function store(Request $request): void {
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role_id' => $request->input('role_id', 2),
            'roles' => $request->input('roles', []),
            'status' => $request->input('status', 'active'),
            'password' => $request->input('password'),
            'student_id' => $request->input('student_id'),
            'instructor_id' => $request->input('instructor_id'),
            'cohort_id' => $request->input('cohort_id'),
            'headline' => $request->input('headline'),
            'bio' => $request->input('bio'),
            'country' => $request->input('country', 'Rwanda'),
            'city' => $request->input('city', 'Kigali'),
        ];

        $res = UserService::createUser($data);

        if (!$res['success']) {
            $this->flash('danger', $res['message'] ?? 'Failed to create user.');
            $this->redirect('admin/users/create');
            return;
        }

        $this->flash('success', 'User account created successfully.');
        $this->redirect('admin/users/' . $res['user_id']);
    }

    /**
     * Show Edit User Form.
     */
    public function edit(Request $request, int $id): void {
        $user = User::findWithProfile($id);
        if (!$user) {
            $this->flash('danger', 'User not found.');
            $this->redirect('admin/users');
            return;
        }

        $roles = Role::all();
        $cohorts = Cohort::all();

        $this->render('admin/users/edit', [
            'pageTitle' => 'Edit User — ' . $user['name'],
            'user' => $user,
            'roles' => $roles,
            'cohorts' => $cohorts
        ], 'dashboard');
    }

    /**
     * Update User.
     */
    public function update(Request $request, int $id): void {
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'phone' => $request->input('phone'),
            'role_id' => $request->input('role_id'),
            'roles' => $request->input('roles', []),
            'status' => $request->input('status'),
            'password' => $request->input('password'),
            'student_id' => $request->input('student_id'),
            'instructor_id' => $request->input('instructor_id'),
            'cohort_ids' => $request->input('cohort_ids', []),
            'headline' => $request->input('headline'),
            'bio' => $request->input('bio'),
            'country' => $request->input('country'),
            'city' => $request->input('city'),
        ];

        $res = UserService::updateUser($id, $data);

        if (!$res['success']) {
            $this->flash('danger', $res['message']);
            $this->redirect('admin/users/' . $id . '/edit');
            return;
        }

        $this->flash('success', 'User updated successfully.');
        $this->redirect('admin/users/' . $id);
    }

    /**
     * Send Account Invitation.
     */
    public function sendInvite(Request $request): void {
        $data = [
            'name' => $request->input('name'),
            'email' => $request->input('email'),
            'role_id' => $request->input('role_id', 2),
            'cohort_id' => $request->input('cohort_id')
        ];

        $res = InvitationService::createInvitation($data, auth_id() ?: 1);

        if ($request->isAjax()) {
            Response::json($res, $res['success'] ? 200 : 400);
        }

        if ($res['success']) {
            $this->flash('success', 'Invitation issued! Onboarding URL: ' . $res['invite_url']);
        } else {
            $this->flash('danger', $res['message']);
        }

        $this->redirect('admin/users');
    }

    /**
     * Process Bulk Actions.
     */
    public function bulkAction(Request $request): void {
        $action = $request->input('bulk_action');
        $userIds = $request->input('user_ids', []);
        
        if (is_string($userIds)) {
            $userIds = explode(',', $userIds);
        }

        $payload = [
            'role_id' => $request->input('bulk_role_id'),
            'cohort_id' => $request->input('bulk_cohort_id'),
            'course_id' => $request->input('bulk_course_id'),
        ];

        $res = UserService::executeBulkAction($action, $userIds, $payload);

        if ($request->isAjax()) {
            Response::json($res, $res['success'] ? 200 : 400);
        }

        $this->flash($res['success'] ? 'success' : 'danger', $res['message']);
        $this->redirect('admin/users');
    }

    /**
     * Preview CSV Import.
     */
    public function importCsvPreview(Request $request): void {
        if (!isset($_FILES['csv_file']) || $_FILES['csv_file']['error'] !== UPLOAD_ERR_OK) {
            Response::json(['success' => false, 'message' => 'Please select a valid CSV file.'], 400);
        }

        $csvContent = file_get_contents($_FILES['csv_file']['tmp_name']);
        $preview = UserService::previewCsvImport($csvContent);

        Response::json($preview, $preview['success'] ? 200 : 400);
    }

    /**
     * Execute CSV Import.
     */
    public function importCsvProcess(Request $request): void {
        $rows = $request->input('rows', []);
        $cohortId = (int)$request->input('cohort_id', 0);

        if (empty($rows) || !is_array($rows)) {
            Response::json(['success' => false, 'message' => 'No valid rows to import.'], 400);
        }

        $res = UserService::processCsvImport($rows, $cohortId);
        Response::json($res);
    }

    /**
     * Export Filtered Users as CSV.
     */
    public function exportCsv(Request $request): void {
        $filters = [
            'q' => $request->input('q', ''),
            'role' => $request->input('role', ''),
            'status' => $request->input('status', ''),
            'cohort_id' => $request->input('cohort_id', ''),
            'course_id' => $request->input('course_id', ''),
            'date_from' => $request->input('date_from', ''),
            'date_to' => $request->input('date_to', ''),
            'sort' => $request->input('sort', 'created_at'),
            'dir' => $request->input('dir', 'DESC')
        ];

        $csv = UserService::exportUsersCsv($filters);
        $filename = 'bba_users_' . date('Y-m-d_His') . '.csv';

        header('Content-Type: text/csv');
        header('Content-Disposition: attachment; filename="' . $filename . '"');
        header('Pragma: no-cache');
        header('Expires: 0');

        echo $csv;
        exit;
    }

    /**
     * Add Administrative Note.
     */
    public function addNote(Request $request, int $id): void {
        $note = trim($request->input('note', ''));
        $type = $request->input('type', 'general');

        if (!empty($note)) {
            UserNote::create([
                'user_id' => $id,
                'author_id' => auth_id() ?: 1,
                'note' => $note,
                'type' => $type,
                'created_at' => date('Y-m-d H:i:s')
            ]);

            AuditLog::log('user_note_added', 'user', $id, ['type' => $type]);
            $this->flash('success', 'Admin note added.');
        }

        $this->redirect('admin/users/' . $id . '#tab-notes');
    }

    /**
     * Manual Course Enrollment for User.
     */
    public function enrollCourse(Request $request, int $id): void {
        $courseId = (int)$request->input('course_id');
        if ($courseId > 0) {
            Database::query("INSERT IGNORE INTO enrollments (user_id, course_id, enrolled_at, status, progress) VALUES (:uid, :cid, NOW(), 'active', 0)", [
                'uid' => $id,
                'cid' => $courseId
            ]);
            AuditLog::log('manual_course_enrolled', 'user', $id, ['course_id' => $courseId]);
            $this->flash('success', 'User enrolled in course.');
        }

        $this->redirect('admin/users/' . $id . '#tab-learning');
    }

    /**
     * Drop / Cancel User Course Enrollment.
     */
    public function dropCourse(Request $request, int $id, int $courseId): void {
        Database::query("DELETE FROM enrollments WHERE user_id = :uid AND course_id = :cid", [
            'uid' => $id,
            'cid' => $courseId
        ]);
        AuditLog::log('course_enrollment_dropped', 'user', $id, ['course_id' => $courseId]);
        $this->flash('success', 'Course enrollment removed.');
        $this->redirect('admin/users/' . $id . '#tab-learning');
    }

    /**
     * Trigger Password Reset.
     */
    public function resetPassword(Request $request, int $id): void {
        $user = User::find($id);
        if (!$user) {
            $this->flash('danger', 'User not found.');
            $this->redirect('admin/users');
            return;
        }

        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+2 hours'));

        Database::insert('password_resets', [
            'email' => $user['email'],
            'token' => $token,
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ]);

        AuditLog::log('admin_password_reset_issued', 'user', $id, ['email' => $user['email']]);

        $resetUrl = url('forgot-password?token=' . $token);
        $this->flash('success', "Password reset link generated: {$resetUrl}");
        $this->redirect('admin/users/' . $id . '#tab-security');
    }

    /**
     * Roles and Permissions Matrix Index.
     */
    public function rolesIndex(Request $request): void {
        $roles = Role::allWithCounts();
        $permissions = Permission::allGroupedByModule();

        $this->render('admin/users/roles', [
            'pageTitle' => 'Roles & Permissions Matrix',
            'roles' => $roles,
            'permissions' => $permissions
        ], 'dashboard');
    }

    /**
     * Update Role Permissions Matrix.
     */
    public function roleUpdate(Request $request, int $id): void {
        $permissionIds = $request->input('permissions', []);
        if (is_array($permissionIds)) {
            Role::syncPermissions($id, array_map('intval', $permissionIds));
            AuditLog::log('role_permissions_updated', 'role', $id, ['permissions_count' => count($permissionIds)]);
            $this->flash('success', 'Role permissions updated successfully.');
        }

        $this->redirect('admin/roles');
    }

    /**
     * Cohorts Index & Management.
     */
    public function cohortsIndex(Request $request): void {
        $cohorts = Cohort::allWithMemberCount();

        $this->render('admin/users/cohorts', [
            'pageTitle' => 'Cohorts & Training Batches',
            'cohorts' => $cohorts
        ], 'dashboard');
    }

    /**
     * Store Cohort.
     */
    public function cohortStore(Request $request): void {
        $name = trim($request->input('name', ''));
        $code = strtoupper(trim($request->input('code', '')));

        if (!empty($name) && !empty($code)) {
            Cohort::create([
                'name' => $name,
                'code' => $code,
                'start_date' => $request->input('start_date') ?: null,
                'end_date' => $request->input('end_date') ?: null,
                'max_students' => (int)($request->input('max_students', 25)),
                'status' => $request->input('status', 'upcoming'),
                'description' => $request->input('description', ''),
                'created_at' => date('Y-m-d H:i:s')
            ]);

            AuditLog::log('cohort_created', 'cohort', null, ['name' => $name, 'code' => $code]);
            $this->flash('success', 'Training cohort created successfully.');
        } else {
            $this->flash('danger', 'Cohort Name and Batch Code are required.');
        }

        $this->redirect('admin/cohorts');
    }

    /**
     * View Cohort Members.
     */
    public function cohortShow(Request $request, int $id): void {
        $cohort = Cohort::find($id);
        if (!$cohort) {
            $this->flash('danger', 'Cohort not found.');
            $this->redirect('admin/cohorts');
            return;
        }

        $members = Cohort::getMembers($id);

        $this->render('admin/users/cohort-show', [
            'pageTitle' => 'Cohort: ' . $cohort['name'],
            'cohort' => $cohort,
            'members' => $members
        ], 'dashboard');
    }
}
