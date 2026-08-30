<?php

namespace App\Services;

use App\Core\Database;
use App\Models\User;
use App\Models\AuditLog;

class UserService {

    /**
     * Get system KPI statistics for IAM dashboard cards.
     */
    public static function getKpiStats(): array {
        $totalUsers = (int)(Database::fetchValue("SELECT COUNT(*) FROM users WHERE status != 'archived'") ?: 0);
        $activeLearners = (int)(Database::fetchValue("SELECT COUNT(DISTINCT user_id) FROM enrollments WHERE status = 'active'") ?: 0);
        $certifiedStudents = (int)(Database::fetchValue("SELECT COUNT(DISTINCT user_id) FROM certificates") ?: 0);
        $instructors = (int)(Database::fetchValue("SELECT COUNT(DISTINCT ur.user_id) FROM user_roles ur JOIN roles r ON ur.role_id = r.id WHERE r.slug = 'instructor'") ?: 0);
        $pendingInvites = (int)(Database::fetchValue("SELECT COUNT(*) FROM invitations WHERE status = 'pending' AND expires_at > NOW()") ?: 0);
        $suspendedOrLocked = (int)(Database::fetchValue("SELECT COUNT(*) FROM users WHERE status IN ('suspended', 'locked')") ?: 0);
        $newThisMonth = (int)(Database::fetchValue("SELECT COUNT(*) FROM users WHERE created_at >= DATE_FORMAT(NOW() ,'%Y-%m-01')") ?: 0);

        return [
            'total_users' => $totalUsers,
            'active_learners' => $activeLearners,
            'certified_students' => $certifiedStudents,
            'instructors' => $instructors,
            'pending_invites' => $pendingInvites,
            'suspended_or_locked' => $suspendedOrLocked,
            'new_this_month' => $newThisMonth,
        ];
    }

    /**
     * Advanced Search, Filter, Sort and Paginate Users Directory.
     */
    public static function queryUsers(array $filters = []): array {
        $page = max(1, (int)($filters['page'] ?? 1));
        $perPage = max(5, min(100, (int)($filters['per_page'] ?? 25)));
        $offset = ($page - 1) * $perPage;

        $conditions = ["u.status != 'archived'"];
        $params = [];

        // Keyword Search (Name, Email, Phone, Student ID, Instructor ID)
        if (!empty($filters['q'])) {
            $q = '%' . trim($filters['q']) . '%';
            $conditions[] = "(u.name LIKE :q1 OR u.email LIKE :q2 OR u.student_id LIKE :q3 OR u.instructor_id LIKE :q4 OR p.phone LIKE :q5)";
            $params['q1'] = $q;
            $params['q2'] = $q;
            $params['q3'] = $q;
            $params['q4'] = $q;
            $params['q5'] = $q;
        }

        // Filter by Role
        if (!empty($filters['role'])) {
            $conditions[] = "EXISTS (SELECT 1 FROM user_roles ur2 JOIN roles r2 ON ur2.role_id = r2.id WHERE ur2.user_id = u.id AND (r2.slug = :role OR r2.id = :role_id))";
            $params['role'] = $filters['role'];
            $params['role_id'] = is_numeric($filters['role']) ? (int)$filters['role'] : 0;
        }

        // Filter by Status
        if (!empty($filters['status']) && $filters['status'] !== 'all') {
            $conditions[] = "u.status = :status";
            $params['status'] = $filters['status'];
        }

        // Filter by Cohort
        if (!empty($filters['cohort_id'])) {
            $conditions[] = "EXISTS (SELECT 1 FROM cohort_users cu2 WHERE cu2.user_id = u.id AND cu2.cohort_id = :cohort_id)";
            $params['cohort_id'] = (int)$filters['cohort_id'];
        }

        // Filter by Enrolled Course
        if (!empty($filters['course_id'])) {
            $conditions[] = "EXISTS (SELECT 1 FROM enrollments e2 WHERE e2.user_id = u.id AND e2.course_id = :course_id)";
            $params['course_id'] = (int)$filters['course_id'];
        }

        // Filter by Date Range
        if (!empty($filters['date_from'])) {
            $conditions[] = "u.created_at >= :date_from";
            $params['date_from'] = $filters['date_from'] . ' 00:00:00';
        }
        if (!empty($filters['date_to'])) {
            $conditions[] = "u.created_at <= :date_to";
            $params['date_to'] = $filters['date_to'] . ' 23:59:59';
        }

        $whereSql = implode(' AND ', $conditions);

        // Sorting
        $allowedSorts = [
            'name' => 'u.name',
            'email' => 'u.email',
            'created_at' => 'u.created_at',
            'last_login_at' => 'u.last_login_at',
            'status' => 'u.status',
            'student_id' => 'u.student_id'
        ];
        $sortBy = $allowedSorts[$filters['sort'] ?? 'created_at'] ?? 'u.created_at';
        $sortDir = strtoupper($filters['dir'] ?? 'DESC') === 'ASC' ? 'ASC' : 'DESC';

        // Count Total Records
        $countSql = "SELECT COUNT(DISTINCT u.id) as cnt
                     FROM users u
                     LEFT JOIN user_profiles p ON u.id = p.user_id
                     WHERE {$whereSql}";
        $totalRecords = (int)(Database::fetchValue($countSql, $params) ?: 0);

        // Fetch Paginated Records
        $sql = "SELECT u.id, u.name, u.email, u.role_id, u.student_id, u.instructor_id, u.status,
                       u.email_verified_at, u.last_login_at, u.created_at,
                       p.phone, p.avatar, p.city, p.country,
                       r.name as primary_role_name, r.slug as primary_role_slug,
                       COUNT(DISTINCT e.id) as enrollments_count,
                       COUNT(DISTINCT cert.id) as certificates_count
                FROM users u
                JOIN roles r ON u.role_id = r.id
                LEFT JOIN user_profiles p ON u.id = p.user_id
                LEFT JOIN enrollments e ON u.id = e.user_id
                LEFT JOIN certificates cert ON u.id = cert.user_id
                WHERE {$whereSql}
                GROUP BY u.id
                ORDER BY {$sortBy} {$sortDir}
                LIMIT {$perPage} OFFSET {$offset}";

        $users = Database::fetchAll($sql, $params);

        // Attach multi-roles and cohorts to each record
        foreach ($users as &$usr) {
            $usr['all_roles'] = User::getRoles((int)$usr['id']);
            $usr['cohorts'] = User::getCohorts((int)$usr['id']);
        }

        return [
            'data' => $users,
            'pagination' => [
                'total' => $totalRecords,
                'per_page' => $perPage,
                'current_page' => $page,
                'total_pages' => max(1, (int)ceil($totalRecords / $perPage)),
                'from' => $totalRecords > 0 ? $offset + 1 : 0,
                'to' => min($offset + $perPage, $totalRecords)
            ]
        ];
    }

    /**
     * Get 360° Comprehensive User Profile Data.
     */
    public static function get360UserProfile(int $userId): ?array {
        $user = User::findWithProfile($userId);
        if (!$user) {
            return null;
        }

        // 1. Roles & Permissions
        $roles = User::getRoles($userId);
        $permissions = User::getPermissions($userId);
        $cohorts = User::getCohorts($userId);

        // 2. Course Enrollments with Progress
        $enrollmentsSql = "SELECT e.*, c.title as course_title, c.slug as course_slug, c.thumbnail,
                                  c.level, c.price, cat.name as category_name,
                                  (SELECT COUNT(*) FROM lessons l JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id) as total_lessons,
                                  (SELECT COUNT(*) FROM lesson_progress lp JOIN lessons l ON lp.lesson_id = l.id JOIN modules m ON l.module_id = m.id WHERE m.course_id = c.id AND lp.user_id = e.user_id AND lp.is_completed = 1) as completed_lessons
                           FROM enrollments e
                           JOIN courses c ON e.course_id = c.id
                           LEFT JOIN categories cat ON c.category_id = cat.id
                           WHERE e.user_id = :uid
                           ORDER BY e.enrolled_at DESC";
        $enrollments = Database::fetchAll($enrollmentsSql, ['uid' => $userId]);

        // Calculate progress percentage for each enrollment
        foreach ($enrollments as &$enr) {
            $total = (int)($enr['total_lessons'] ?? 0);
            $done = (int)($enr['completed_lessons'] ?? 0);
            $enr['progress_percentage'] = $total > 0 ? min(100, round(($done / $total) * 100)) : (int)($enr['progress'] ?? 0);
        }

        // 3. Certificates
        $certificatesSql = "SELECT cert.*, c.title as course_title, c.slug as course_slug
                            FROM certificates cert
                            JOIN courses c ON cert.course_id = c.id
                            WHERE cert.user_id = :uid
                            ORDER BY cert.issue_date DESC, cert.created_at DESC";
        $certificates = Database::fetchAll($certificatesSql, ['uid' => $userId]);

        // 4. Financial Orders
        $ordersSql = "SELECT o.*, 
                             (SELECT COUNT(*) FROM order_items oi WHERE oi.order_id = o.id) as items_count
                      FROM orders o
                      WHERE o.user_id = :uid
                      ORDER BY o.created_at DESC";
        $orders = Database::fetchAll($ordersSql, ['uid' => $userId]);
        $totalSpent = array_sum(array_column(array_filter($orders, fn($o) => $o['status'] === 'completed'), 'total_amount'));

        // 5. Instructor Stats (if applicable)
        $instructorStats = null;
        if (User::hasRole($userId, 'instructor') || $user['role_slug'] === 'instructor') {
            $coursesCreated = Database::fetchAll(
                "SELECT c.*, COUNT(DISTINCT e.id) as students_count, AVG(r.rating) as avg_rating
                 FROM courses c
                 LEFT JOIN enrollments e ON c.id = e.course_id
                 LEFT JOIN reviews r ON c.id = r.course_id
                 WHERE c.created_by = :uid
                 GROUP BY c.id
                 ORDER BY c.created_at DESC",
                ['uid' => $userId]
            );
            $totalStudentsTrained = (int)Database::fetchValue(
                "SELECT COUNT(DISTINCT e.user_id) FROM enrollments e JOIN courses c ON e.course_id = c.id WHERE c.created_by = :uid",
                ['uid' => $userId]
            );
            $instructorStats = [
                'courses' => $coursesCreated,
                'total_courses' => count($coursesCreated),
                'total_students_trained' => $totalStudentsTrained
            ];
        }

        // 6. Security Logins & Sessions
        $logins = \App\Models\UserLogin::getRecentForUser($userId, 10);

        // 7. Admin Notes
        $notes = \App\Models\UserNote::getForUser($userId);

        // 8. Audit Trail for this user
        $auditTrail = Database::fetchAll(
            "SELECT al.*, u2.name as performed_by_name
             FROM audit_logs al
             LEFT JOIN users u2 ON al.user_id = u2.id
             WHERE (al.entity_type = 'user' AND al.entity_id = :uid) OR al.user_id = :uid2
             ORDER BY al.created_at DESC LIMIT 25",
            ['uid' => $userId, 'uid2' => $userId]
        );

        return [
            'user' => $user,
            'roles' => $roles,
            'permissions' => $permissions,
            'cohorts' => $cohorts,
            'enrollments' => $enrollments,
            'certificates' => $certificates,
            'orders' => $orders,
            'total_spent' => $totalSpent,
            'instructor_stats' => $instructorStats,
            'logins' => $logins,
            'notes' => $notes,
            'audit_trail' => $auditTrail
        ];
    }

    /**
     * Create a new user with profile and role assignments.
     */
    public static function createUser(array $data): array {
        $email = strtolower(trim($data['email']));
        if (User::findByEmail($email)) {
            return ['success' => false, 'message' => 'A user with this email address already exists.'];
        }

        $roleId = (int)($data['role_id'] ?? 2);
        $status = in_array($data['status'] ?? '', ['active', 'pending', 'suspended', 'locked', 'archived']) ? $data['status'] : 'active';
        $password = !empty($data['password']) ? password_hash($data['password'], PASSWORD_DEFAULT) : password_hash(bin2hex(random_bytes(8)), PASSWORD_DEFAULT);

        // Insert user record
        $userId = Database::insert('users', [
            'role_id' => $roleId,
            'name' => trim($data['name']),
            'email' => $email,
            'password' => $password,
            'status' => $status,
            'email_verified_at' => $status === 'active' ? date('Y-m-d H:i:s') : null,
            'created_at' => date('Y-m-d H:i:s'),
            'updated_at' => date('Y-m-d H:i:s'),
        ]);

        // Generate student or instructor ID if not provided
        $studentId = $data['student_id'] ?? null;
        $instructorId = $data['instructor_id'] ?? null;

        if ($roleId == 2 && empty($studentId)) {
            $studentId = User::generateStudentId($userId);
        } elseif ($roleId == 3 && empty($instructorId)) {
            $instructorId = User::generateInstructorId($userId);
        }

        Database::update('users', [
            'student_id' => $studentId,
            'instructor_id' => $instructorId
        ], ['id' => $userId]);

        // Create Profile
        Database::insert('user_profiles', [
            'user_id' => $userId,
            'phone' => $data['phone'] ?? null,
            'headline' => $data['headline'] ?? null,
            'bio' => $data['bio'] ?? null,
            'country' => $data['country'] ?? 'Rwanda',
            'city' => $data['city'] ?? 'Kigali',
            'language' => $data['language'] ?? 'en',
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        // Sync Roles
        $roleIds = !empty($data['roles']) && is_array($data['roles']) ? array_map('intval', $data['roles']) : [$roleId];
        User::syncRoles($userId, $roleIds, $roleId);

        // Sync Cohorts
        if (!empty($data['cohort_id'])) {
            User::syncCohorts($userId, [(int)$data['cohort_id']]);
        }

        AuditLog::log('user_created', 'user', $userId, [
            'name' => $data['name'],
            'email' => $email,
            'role_id' => $roleId,
            'status' => $status
        ]);

        return ['success' => true, 'user_id' => $userId];
    }

    /**
     * Update user details and profile.
     */
    public static function updateUser(int $userId, array $data): array {
        $user = User::find($userId);
        if (!$user) {
            return ['success' => false, 'message' => 'User not found.'];
        }

        $email = strtolower(trim($data['email']));
        $existing = User::findByEmail($email);
        if ($existing && $existing['id'] != $userId) {
            return ['success' => false, 'message' => 'Email address is already in use by another account.'];
        }

        $roleId = (int)($data['role_id'] ?? $user['role_id']);
        $status = in_array($data['status'] ?? '', ['active', 'pending', 'suspended', 'locked', 'archived']) ? $data['status'] : $user['status'];

        $updateFields = [
            'name' => trim($data['name']),
            'email' => $email,
            'role_id' => $roleId,
            'status' => $status,
            'student_id' => !empty($data['student_id']) ? trim($data['student_id']) : $user['student_id'],
            'instructor_id' => !empty($data['instructor_id']) ? trim($data['instructor_id']) : $user['instructor_id'],
            'updated_at' => date('Y-m-d H:i:s')
        ];

        // Optional password reset
        if (!empty($data['password'])) {
            $updateFields['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }

        Database::update('users', $updateFields, ['id' => $userId]);

        // Update or Insert Profile
        $profile = Database::fetchOne("SELECT id FROM user_profiles WHERE user_id = :uid", ['uid' => $userId]);
        $profileData = [
            'phone' => $data['phone'] ?? null,
            'headline' => $data['headline'] ?? null,
            'bio' => $data['bio'] ?? null,
            'country' => $data['country'] ?? 'Rwanda',
            'city' => $data['city'] ?? 'Kigali',
            'language' => $data['language'] ?? 'en',
            'updated_at' => date('Y-m-d H:i:s')
        ];

        if ($profile) {
            Database::update('user_profiles', $profileData, ['user_id' => $userId]);
        } else {
            $profileData['user_id'] = $userId;
            $profileData['created_at'] = date('Y-m-d H:i:s');
            Database::insert('user_profiles', $profileData);
        }

        // Update multi-roles if provided
        if (isset($data['roles']) && is_array($data['roles'])) {
            User::syncRoles($userId, array_map('intval', $data['roles']), $roleId);
        }

        // Update Cohorts if provided
        if (isset($data['cohort_ids']) && is_array($data['cohort_ids'])) {
            User::syncCohorts($userId, array_map('intval', $data['cohort_ids']));
        }

        AuditLog::log('user_updated', 'user', $userId, [
            'name' => $data['name'],
            'email' => $email,
            'role_id' => $roleId,
            'status' => $status
        ]);

        return ['success' => true, 'message' => 'User updated successfully.'];
    }

    /**
     * Execute Bulk Administrative Actions.
     */
    public static function executeBulkAction(string $action, array $userIds, array $payload = []): array {
        $userIds = array_values(array_filter(array_map('intval', $userIds)));
        if (empty($userIds)) {
            return ['success' => false, 'message' => 'No users selected.'];
        }

        $inClause = implode(',', $userIds);
        $count = count($userIds);

        switch ($action) {
            case 'activate':
                Database::query("UPDATE users SET status = 'active', email_verified_at = IFNULL(email_verified_at, NOW()) WHERE id IN ($inClause)");
                AuditLog::log('bulk_users_activated', 'user', null, ['count' => $count, 'ids' => $userIds]);
                return ['success' => true, 'message' => "Successfully activated {$count} user(s)."];

            case 'suspend':
                Database::query("UPDATE users SET status = 'suspended' WHERE id IN ($inClause)");
                AuditLog::log('bulk_users_suspended', 'user', null, ['count' => $count, 'ids' => $userIds]);
                return ['success' => true, 'message' => "Successfully suspended {$count} user(s)."];

            case 'lock':
                Database::query("UPDATE users SET status = 'locked' WHERE id IN ($inClause)");
                AuditLog::log('bulk_users_locked', 'user', null, ['count' => $count, 'ids' => $userIds]);
                return ['success' => true, 'message' => "Successfully locked {$count} user(s)."];

            case 'archive':
                Database::query("UPDATE users SET status = 'archived' WHERE id IN ($inClause)");
                AuditLog::log('bulk_users_archived', 'user', null, ['count' => $count, 'ids' => $userIds]);
                return ['success' => true, 'message' => "Successfully archived {$count} user(s)."];

            case 'assign_role':
                $roleId = (int)($payload['role_id'] ?? 0);
                if ($roleId <= 0) {
                    return ['success' => false, 'message' => 'Invalid role specified.'];
                }
                foreach ($userIds as $uid) {
                    User::syncRoles($uid, [$roleId], $roleId);
                }
                AuditLog::log('bulk_role_assigned', 'user', null, ['count' => $count, 'role_id' => $roleId]);
                return ['success' => true, 'message' => "Assigned role to {$count} user(s)."];

            case 'assign_cohort':
                $cohortId = (int)($payload['cohort_id'] ?? 0);
                if ($cohortId <= 0) {
                    return ['success' => false, 'message' => 'Invalid cohort specified.'];
                }
                foreach ($userIds as $uid) {
                    Database::query("INSERT IGNORE INTO cohort_users (cohort_id, user_id, role_in_cohort, enrolled_at) VALUES (:cid, :uid, 'student', NOW())", [
                        'cid' => $cohortId,
                        'uid' => $uid
                    ]);
                }
                AuditLog::log('bulk_cohort_assigned', 'user', null, ['count' => $count, 'cohort_id' => $cohortId]);
                return ['success' => true, 'message' => "Assigned {$count} user(s) to cohort."];

            case 'enroll_course':
                $courseId = (int)($payload['course_id'] ?? 0);
                if ($courseId <= 0) {
                    return ['success' => false, 'message' => 'Invalid course specified.'];
                }
                foreach ($userIds as $uid) {
                    Database::query("INSERT IGNORE INTO enrollments (user_id, course_id, enrolled_at, status, progress) VALUES (:uid, :cid, NOW(), 'active', 0)", [
                        'uid' => $uid,
                        'cid' => $courseId
                    ]);
                }
                AuditLog::log('bulk_course_enrolled', 'user', null, ['count' => $count, 'course_id' => $courseId]);
                return ['success' => true, 'message' => "Enrolled {$count} user(s) into course."];

            default:
                return ['success' => false, 'message' => 'Unknown bulk action.'];
        }
    }

    /**
     * Parse and validate CSV data for Import Preview.
     */
    public static function previewCsvImport(string $csvContent): array {
        $lines = array_filter(array_map('trim', explode("\n", $csvContent)));
        if (empty($lines)) {
            return ['success' => false, 'message' => 'CSV file is empty.'];
        }

        $headerLine = array_shift($lines);
        $headers = array_map(fn($h) => strtolower(trim($h, " \t\n\r\0\x0B\"'")), str_getcsv($headerLine));

        // Required fields: name, email
        if (!in_array('name', $headers) || !in_array('email', $headers)) {
            return [
                'success' => false,
                'message' => 'CSV must include at least "name" and "email" column headers.'
            ];
        }

        $rolesMap = [];
        foreach (Database::fetchAll("SELECT id, slug, name FROM roles") as $r) {
            $rolesMap[strtolower($r['slug'])] = (int)$r['id'];
            $rolesMap[strtolower($r['name'])] = (int)$r['id'];
        }

        $previewRows = [];
        $validCount = 0;
        $errorCount = 0;
        $seenEmails = [];

        foreach ($lines as $idx => $line) {
            if (empty(trim($line))) continue;
            $rowValues = str_getcsv($line);
            $rowData = [];
            foreach ($headers as $hIndex => $hKey) {
                $rowData[$hKey] = $rowValues[$hIndex] ?? '';
            }

            $name = trim($rowData['name'] ?? '');
            $email = strtolower(trim($rowData['email'] ?? ''));
            $phone = trim($rowData['phone'] ?? '');
            $role = strtolower(trim($rowData['role'] ?? 'student'));
            $status = strtolower(trim($rowData['status'] ?? 'active'));

            $errors = [];

            if (empty($name)) {
                $errors[] = 'Name is required';
            }
            if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
                $errors[] = 'Valid email is required';
            } elseif (isset($seenEmails[$email])) {
                $errors[] = 'Duplicate email in CSV';
            } else {
                $seenEmails[$email] = true;
                if (User::findByEmail($email)) {
                    $errors[] = 'Email already exists in database';
                }
            }

            $resolvedRoleId = $rolesMap[$role] ?? 2;
            $resolvedStatus = in_array($status, ['active', 'pending', 'suspended']) ? $status : 'active';

            $isValid = empty($errors);
            if ($isValid) $validCount++; else $errorCount++;

            $previewRows[] = [
                'row_number' => $idx + 2,
                'name' => $name,
                'email' => $email,
                'phone' => $phone,
                'role' => $role,
                'role_id' => $resolvedRoleId,
                'status' => $resolvedStatus,
                'is_valid' => $isValid,
                'errors' => $errors
            ];
        }

        return [
            'success' => true,
            'total_rows' => count($previewRows),
            'valid_count' => $validCount,
            'error_count' => $errorCount,
            'rows' => $previewRows
        ];
    }

    /**
     * Process Validated CSV Rows into Database.
     */
    public static function processCsvImport(array $rows, int $defaultCohortId = 0): array {
        $imported = 0;
        $failed = 0;

        foreach ($rows as $row) {
            if (empty($row['is_valid'])) {
                $failed++;
                continue;
            }

            $res = static::createUser([
                'name' => $row['name'],
                'email' => $row['email'],
                'phone' => $row['phone'] ?? null,
                'role_id' => (int)($row['role_id'] ?? 2),
                'status' => $row['status'] ?? 'active',
                'cohort_id' => $defaultCohortId > 0 ? $defaultCohortId : null
            ]);

            if ($res['success']) {
                $imported++;
            } else {
                $failed++;
            }
        }

        AuditLog::log('csv_users_imported', 'user', null, [
            'imported' => $imported,
            'failed' => $failed
        ]);

        return [
            'success' => true,
            'imported' => $imported,
            'failed' => $failed
        ];
    }

    /**
     * Export Filtered Users to CSV format.
     */
    public static function exportUsersCsv(array $filters = []): string {
        $filters['per_page'] = 10000;
        $filters['page'] = 1;
        $result = static::queryUsers($filters);
        $users = $result['data'] ?? [];

        $output = fopen('php://temp', 'r+');
        fputcsv($output, [
            'ID',
            'Name',
            'Email',
            'Phone',
            'Role',
            'Student ID',
            'Instructor ID',
            'Status',
            'Cohort(s)',
            'Enrollments Count',
            'Certificates Count',
            'Registered Date',
            'Last Login'
        ]);

        foreach ($users as $u) {
            $cohortNames = implode(', ', array_column($u['cohorts'] ?? [], 'name'));
            fputcsv($output, [
                $u['id'],
                $u['name'],
                $u['email'],
                $u['phone'] ?? '',
                $u['primary_role_name'] ?? '',
                $u['student_id'] ?? '',
                $u['instructor_id'] ?? '',
                strtoupper($u['status']),
                $cohortNames,
                $u['enrollments_count'] ?? 0,
                $u['certificates_count'] ?? 0,
                date('Y-m-d H:i', strtotime($u['created_at'])),
                $u['last_login_at'] ? date('Y-m-d H:i', strtotime($u['last_login_at'])) : 'Never'
            ]);
        }

        rewind($output);
        $csv = stream_get_contents($output);
        fclose($output);

        return $csv;
    }
}
