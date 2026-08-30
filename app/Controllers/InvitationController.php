<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Models\Invitation;
use App\Services\InvitationService;

class InvitationController extends Controller {

    /**
     * Show Accept Invitation / Onboarding Screen.
     */
    public function showAccept(Request $request, string $token): void {
        $invitation = Invitation::findByToken($token);
        if (!$invitation) {
            $this->render('auth/invalid-invite', [
                'pageTitle' => 'Invitation Expired or Invalid'
            ], 'layouts/auth');
            return;
        }

        $this->render('auth/accept-invite', [
            'pageTitle' => 'Welcome to Beyond Barista Academy — Set Up Your Account',
            'invitation' => $invitation,
            'token' => $token
        ], 'layouts/auth');
    }

    /**
     * Process Invitation Acceptance and Password Setup.
     */
    public function processAccept(Request $request, string $token): void {
        $password = $request->input('password', '');
        $passwordConfirm = $request->input('password_confirmation', '');
        $phone = $request->input('phone');

        if ($password !== $passwordConfirm) {
            $this->flash('danger', 'Passwords do not match.');
            $this->redirect('invite/accept/' . $token);
            return;
        }

        $res = InvitationService::acceptInvitation($token, $password, $phone);

        if (!$res['success']) {
            $this->flash('danger', $res['message']);
            $this->redirect('invite/accept/' . $token);
            return;
        }

        $this->flash('success', 'Your account has been set up successfully! Please sign in with your credentials.');
        $this->redirect('login');
    }
}
