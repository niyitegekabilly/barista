<?php

namespace App\Services;

use App\Core\Database;
use App\Models\Invitation;
use App\Models\User;
use App\Models\AuditLog;

class InvitationService {

    /**
     * Create and issue a new user invitation.
     */
    public static function createInvitation(array $data, int $invitedBy): array {
        $email = strtolower(trim($data['email']));
        $name = trim($data['name']);
        $roleId = (int)($data['role_id'] ?? 2);
        $cohortId = !empty($data['cohort_id']) ? (int)$data['cohort_id'] : null;

        // Check if user already exists
        if (User::findByEmail($email)) {
            return ['success' => false, 'message' => 'A user with this email address already has an account.'];
        }

        // Revoke any previous pending invitations for this email
        Database::query("UPDATE invitations SET status = 'revoked' WHERE email = :email AND status = 'pending'", ['email' => $email]);

        $token = bin2hex(random_bytes(32));
        $expiresAt = date('Y-m-d H:i:s', strtotime('+7 days'));

        $invitationId = Database::insert('invitations', [
            'email' => $email,
            'name' => $name,
            'role_id' => $roleId,
            'cohort_id' => $cohortId,
            'token' => $token,
            'status' => 'pending',
            'invited_by' => $invitedBy,
            'expires_at' => $expiresAt,
            'created_at' => date('Y-m-d H:i:s'),
        ]);

        $inviteUrl = url('invite/accept/' . $token);

        AuditLog::log('user_invited', 'invitation', $invitationId, [
            'email' => $email,
            'role_id' => $roleId,
            'cohort_id' => $cohortId,
            'invited_by' => $invitedBy
        ]);

        return [
            'success' => true,
            'invitation_id' => $invitationId,
            'token' => $token,
            'invite_url' => $inviteUrl,
            'message' => 'Invitation created successfully.'
        ];
    }

    /**
     * Accept invitation and initialize the user account with a secure password.
     */
    public static function acceptInvitation(string $token, string $password, ?string $phone = null): array {
        $invitation = Invitation::findByToken($token);
        if (!$invitation) {
            return ['success' => false, 'message' => 'This invitation link is invalid, expired, or has already been used.'];
        }

        if (strlen($password) < 8) {
            return ['success' => false, 'message' => 'Password must be at least 8 characters long.'];
        }

        // Create the user account
        $res = UserService::createUser([
            'name' => $invitation['name'],
            'email' => $invitation['email'],
            'password' => $password,
            'phone' => $phone,
            'role_id' => $invitation['role_id'],
            'cohort_id' => $invitation['cohort_id'],
            'status' => 'active'
        ]);

        if (!$res['success']) {
            return $res;
        }

        $userId = $res['user_id'];

        // Mark invitation as accepted
        Database::update('invitations', [
            'status' => 'accepted',
            'accepted_at' => date('Y-m-d H:i:s')
        ], ['id' => $invitation['id']]);

        AuditLog::log('invitation_accepted', 'user', $userId, [
            'email' => $invitation['email'],
            'invitation_id' => $invitation['id']
        ]);

        return [
            'success' => true,
            'user_id' => $userId,
            'message' => 'Account setup complete. You can now log in.'
        ];
    }
}
