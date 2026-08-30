<?php

namespace App\Controllers;

use App\Core\Controller;
use App\Core\Request;
use App\Services\CertificateService;
use App\Models\Certificate;

class CertificateController extends Controller {

    /**
     * Public Certificate Verification Portal
     */
    public function verify(Request $request, ?string $code = null): void {
        $searchCode = $code ?? $request->input('code', '');
        $verification = null;

        if (!empty($searchCode)) {
            $verification = CertificateService::verifyCertificate($searchCode);
        }

        $this->render('public/certificates/verify', [
            'pageTitle'    => 'Official Certificate Verification',
            'code'         => $searchCode,
            'verification' => $verification
        ], 'main');
    }

    /**
     * Standalone Printable Certificate View
     */
    public function print(Request $request, string $code): void {
        $cert = Certificate::findByNumber($code) ?? Certificate::findByHash($code);
        if (!$cert) {
            $this->abort(404);
            return;
        }

        $certificate = Certificate::findWithDetails((int)$cert['id']);

        $this->render('student/certificate-view', [
            'pageTitle'   => 'Certificate - ' . $certificate['certificate_number'],
            'certificate' => $certificate,
            'isPrintOnly' => true
        ], 'blank');
    }
}
