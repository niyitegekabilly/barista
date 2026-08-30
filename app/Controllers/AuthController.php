<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Services\AuthService;

class AuthController extends Controller
{
    private AuthService $auth;

    public function __construct()
    {
        parent::__construct();
        $this->auth = new AuthService();
    }

    public function showLogin(): void
    {
        if (auth()) {
            $this->redirect('/');
        }
        $this->render('auth/login', [], 'auth');
    }

    public function login(): void
    {
        $email    = $this->request->input('email');
        $password = $this->request->input('password');
        $remember = $this->request->input('remember_me') === 'on' || $this->request->input('remember_me') === '1';

        $result = $this->auth->login($email, $password, $remember);

        if ($result['success']) {
            $user = $result['user'];
            $this->flash('success', 'Welcome back, ' . $user['name'] . '!');

            // Redirect by role
            switch ($user['role_slug']) {
                case 'super_admin':
                case 'admin':
                    $this->redirect('/admin/dashboard');
                    break;
                case 'instructor':
                    $this->redirect('/instructor/dashboard');
                    break;
                case 'reviewer':
                    $this->redirect('/admin/courses');
                    break;
                default:
                    $this->redirect('/student/dashboard');
            }
        } else {
            $this->flash('error', $result['message']);
            $this->redirect('/login');
        }
    }

    public function showRegister(): void
    {
        if (auth()) {
            $this->redirect('/');
        }
        $this->render('auth/register', [], 'auth');
    }

    public function register(): void
    {
        $errors = $this->validate($this->request->all(), [
            'name'                  => 'required|min:2|max:100',
            'email'                 => 'required|email',
            'phone'                 => 'required|min:10',
            'city'                  => 'required|min:2',
            'password'              => 'required|min:8',
            'password_confirmation' => 'required',
            'agree_terms'           => 'required',
        ]);

        if (!empty($errors)) {
            foreach ($errors as $err) {
                $this->flash('error', $err);
            }
            $this->redirect('/register');
            return;
        }

        if ($this->request->input('password') !== $this->request->input('password_confirmation')) {
            $this->flash('error', 'Passwords do not match.');
            $this->redirect('/register');
            return;
        }

        // Register the user account
        $result = $this->auth->register([
            'name'     => $this->request->input('name'),
            'email'    => $this->request->input('email'),
            'password' => $this->request->input('password'),
            'phone'    => $this->request->input('phone'),
        ]);

        if ($result['success']) {
            // Get the newly created user to store additional profile info
            $user = $this->db()->fetchOne("SELECT id FROM users WHERE email = ?", [$this->request->input('email')]);

            if ($user) {
                // Store additional admission information in user_profiles
                $interests = $this->request->input('interests', []);
                $this->db()->query(
                    "UPDATE user_profiles SET phone=?, city=?, headline=?, language=?
                     WHERE user_id=?",
                    [
                        $this->request->input('phone'),
                        $this->request->input('city'),
                        $this->request->input('headline'),
                        session('locale', 'en'),
                        $user['id']
                    ]
                );

                // Store admission details as JSON in a new admission_details column (if needed)
                // For now we can use social_links JSON column
                $admissionData = [
                    'education_level' => $this->request->input('education_level'),
                    'experience_level' => $this->request->input('experience_level'),
                    'interests' => $interests,
                    'subscribed_updates' => $this->request->input('subscribe_updates') ? 1 : 0,
                    'applied_at' => date('Y-m-d H:i:s'),
                ];

                $this->db()->query(
                    "UPDATE user_profiles SET social_links=? WHERE user_id=?",
                    [json_encode($admissionData), $user['id']]
                );
            }

            $this->flash('success', 'Welcome to Beyond Barista Academy! Please log in to start your learning journey.');
            $this->redirect('/login');
        } else {
            $this->flash('error', $result['message']);
            $this->redirect('/register');
        }
    }

    public function showForgotPassword(): void
    {
        $this->render('auth/forgot-password', [], 'auth');
    }

    public function sendResetLink(): void
    {
        $email  = $this->request->input('email');
        $result = $this->auth->sendPasswordResetLink($email);

        if ($result['success']) {
            $this->flash('success', 'If that email exists in our system, a reset link has been sent.');
        } else {
            $this->flash('error', $result['message']);
        }
        $this->redirect('/forgot-password');
    }

    public function logout(): void
    {
        $this->auth->logout();
        $this->flash('success', 'You have been logged out.');
        $this->redirect('/login');
    }
}
