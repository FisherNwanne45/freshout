<?php
/**
 * Email Template System for Banking Platform
 * Handles professional HTML email rendering
 */

class EmailTemplate {
    private $bankName = 'Banking System';
    private $bankLogo = '';
    private $supportEmail = 'support@bank.com';
    private $siteUrl = '';
    private $palette = [
        'navy' => '#0d1f3c',
        'navy2' => '#0a162d',
        'gold' => '#c9a84c',
        'gold2' => '#dec58c',
        'light' => '#f5f7fa',
        'border' => '#d7e0ec',
        'success' => '#10b981',
        'danger' => '#ef4444',
        'muted' => '#6b7280',
    ];
    
    public function __construct($config = []) {
        if (!empty($config['bankName'])) $this->bankName = $config['bankName'];
        if (!empty($config['bankLogo'])) $this->bankLogo = $config['bankLogo'];
        if (!empty($config['supportEmail'])) $this->supportEmail = $config['supportEmail'];
        if (!empty($config['siteUrl'])) $this->siteUrl = $this->normalizeSiteUrl((string)$config['siteUrl']);
        if (!empty($config['palette']) && is_array($config['palette'])) {
            $this->palette = array_merge($this->palette, $config['palette']);
        }

        if ($this->siteUrl === '' || stripos($this->siteUrl, 'bank.example.com') !== false) {
            $resolvedUrl = $this->resolveRuntimeSiteUrl();
            if ($resolvedUrl !== '') {
                $this->siteUrl = $resolvedUrl;
            }
        }

        if ($this->siteUrl === '') {
            $this->siteUrl = 'https://bank.example.com';
        }
    }

    private function normalizeSiteUrl($url) {
        return rtrim(trim($url), '/');
    }

    private function resolveRuntimeSiteUrl() {
        $host = trim((string)($_SERVER['HTTP_HOST'] ?? ''));
        if ($host === '') {
            return '';
        }

        $https = strtolower((string)($_SERVER['HTTPS'] ?? ''));
        $scheme = ($https === 'on' || $https === '1') ? 'https' : 'http';

        $scriptPath = (string)($_SERVER['SCRIPT_NAME'] ?? '');
        $basePath = $scriptPath !== '' ? dirname($scriptPath) : '';
        if ($basePath === DIRECTORY_SEPARATOR || $basePath === '.') {
            $basePath = '';
        }

        $basePath = str_replace('\\', '/', $basePath);
        $basePath = preg_replace('#/user(?:/admin)?$#i', '', $basePath);
        $basePath = rtrim((string)$basePath, '/');

        return $this->normalizeSiteUrl($scheme . '://' . $host . ($basePath !== '' ? $basePath : ''));
    }
    
    public function renderTemplate($templateName, $data = []) {
        $method = 'template' . ucfirst(str_replace('_', '', $templateName));
        if (method_exists($this, $method)) {
            return $this->$method($data);
        }
        return $this->renderDefault($templateName, $data);
    }
    
    private function getHeader() {
        return <<<HTML
        <!DOCTYPE html>
        <html>
        <head>
            <meta charset="UTF-8">
            <meta name="viewport" content="width=device-width, initial-scale=1.0">
            <style>
                body { font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, 'Helvetica Neue', Arial, sans-serif; line-height: 1.6; color: #333; }
                .container { max-width: 600px; margin: 0 auto; background: #ffffff; }
                .header { background: {$this->palette['navy']}; padding: 30px; text-align: center; color: white; }
                .header img { max-height: 50px; margin-bottom: 10px; }
                .header h1 { margin: 10px 0; font-size: 24px; font-weight: 600; }
                .content { padding: 30px; }
                .section { margin-bottom: 24px; }
                .section h2 { font-size: 16px; color: {$this->palette['navy']}; margin-bottom: 12px; border-bottom: 2px solid {$this->palette['gold']}; padding-bottom: 8px; }
                .section p { margin: 10px 0; color: #555; }
                .highlight { background: {$this->palette['light']}; padding: 15px; border-left: 4px solid {$this->palette['gold']}; margin: 15px 0; }
                .details-table { width: 100%; border-collapse: collapse; margin: 15px 0; }
                .details-table tr { border-bottom: 1px solid {$this->palette['light']}; }
                .details-table td { padding: 10px; }
                .details-table td:first-child { font-weight: 600; color: {$this->palette['navy']}; width: 30%; }
                .btn { display: inline-block; padding: 12px 24px; background: {$this->palette['navy']}; color: white; text-decoration: none; border-radius: 4px; margin: 10px 0; }
                .btn:hover { background: {$this->palette['navy2']}; }
                .footer { background: {$this->palette['light']}; padding: 20px; text-align: center; font-size: 12px; color: {$this->palette['muted']}; border-top: 1px solid #ddd; }
                .success { color: {$this->palette['success']}; }
                .danger { color: {$this->palette['danger']}; }
            </style>
        </head>
        <body>
        <div class="container">
            <div class="header">
                {$this->getBankLogo()}
                <h1>{$this->bankName}</h1>
            </div>
            <div class="content">
HTML;
    }
    
    private function getFooter() {
        $year = date('Y');
        return <<<HTML
            </div>
            <div class="footer">
                <p>&copy; {$year} {$this->bankName}. All rights reserved.</p>
                <p>Questions? Contact us at <a href="mailto:{$this->supportEmail}">{$this->supportEmail}</a></p>
                <p><a href="{$this->siteUrl}" style="color:{$this->palette['navy']};text-decoration:none;">Visit our website</a></p>
            </div>
        </div>
        </body>
        </html>
HTML;
    }
    
    private function getBankLogo() {
        if (!empty($this->bankLogo)) {
            return "<img src=\"{$this->bankLogo}\" alt=\"{$this->bankName}\" />";
        }
        return "";
    }
    
    // ========== TEMPLATES ==========
    
    public function templateRegistrationWelcome($data) {
        $fname = htmlspecialchars($data['fname'] ?? '');
        $lname = htmlspecialchars($data['lname'] ?? '');
        $accNo = htmlspecialchars($data['acc_no'] ?? '');
        $typeLabel = trim((string)($data['type_label'] ?? $data['type'] ?? ''));
        if ($typeLabel === '') {
            $typeLabel = 'Account';
        }
        $type = htmlspecialchars($typeLabel);
        $currency = htmlspecialchars($data['currency'] ?? 'USD');
        $balance = $data['balance'] ?? 0;
        $balanceFormatted = number_format((float)$balance, 2);
        
        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>Welcome to {$this->bankName}</h2>
                    <p>Dear {$fname} {$lname},</p>
                    <p>Your account has been successfully created! We're excited to have you join our banking family.</p>
                </div>
                
                <div class="section">
                    <h2>Account Details</h2>
                    <table class="details-table">
                        <tr>
                            <td>Account Number:</td>
                            <td><strong>{$accNo}</strong></td>
                        </tr>
                        <tr>
                            <td>Account Type:</td>
                            <td>{$type}</td>
                        </tr>
                        <tr>
                            <td>Currency:</td>
                            <td>{$currency}</td>
                        </tr>
                        <tr>
                            <td>Opening Balance:</td>
                            <td>{$currency} {$balanceFormatted}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="highlight">
                    <strong>Next Steps:</strong>
                    <p>You can now log in to your account using your account number and password. Start enjoying our banking services!</p>
                </div>
                
                <div style="text-align:center;">
                    <a href="{$this->siteUrl}/user/login.php" class="btn">Log In to Your Account</a>
                </div>
                
                <div class="section">
                    <p>If you have any questions or need assistance, please don't hesitate to contact our support team.</p>
                    <p><strong>Best regards,</strong><br>{$this->bankName} Team</p>
                </div>
HTML . $this->getFooter();
    }
    
    public function templateDebitAlert($data) {
        $fname = htmlspecialchars($data['fname'] ?? ($data['name'] ?? 'Customer'));
        $amount = htmlspecialchars(($data['currency'] ?? '') . ' ' . ($data['amount'] ?? '0'));
        $description = htmlspecialchars($data['description'] ?? 'Transaction');
        $balance = htmlspecialchars(($data['currency'] ?? '') . ' ' . ($data['balance'] ?? '0'));
        $date = htmlspecialchars($data['date'] ?? date('Y-m-d H:i:s'));
        
        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2 style="color:{$this->palette['danger']};">Debit Alert</h2>
                    <p>Dear {$fname},</p>
                    <p>A debit transaction has been processed on your account.</p>
                </div>
                
                <div class="highlight">
                    <table class="details-table" style="margin:0;">
                        <tr>
                            <td>Transaction:</td>
                            <td>{$description}</td>
                        </tr>
                        <tr>
                            <td>Amount Debited:</td>
                            <td><strong style="color:{$this->palette['danger']};">-{$amount}</strong></td>
                        </tr>
                        <tr>
                            <td>Remaining Balance:</td>
                            <td><strong>{$balance}</strong></td>
                        </tr>
                        <tr>
                            <td>Transaction Date:</td>
                            <td>{$date}</td>
                        </tr>
                    </table>
                </div>
                
                <div class="section">
                    <p><strong>Did you make this transaction?</strong> If this transaction is unauthorized, please contact us immediately.</p>
                    <p>Account Security: Never share your account details or OTP with anyone. {$this->bankName} will never ask for your full password via email.</p>
                </div>
HTML . $this->getFooter();
    }
    
    public function templateApplicationApproved($data) {
        $fname = htmlspecialchars($data['fname'] ?? '');
        $accNo = htmlspecialchars($data['acc_no'] ?? '');
        
        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2 style="color:{$this->palette['success']};">✓ Account Application Approved</h2>
                    <p>Dear {$fname},</p>
                    <p>Congratulations! Your account application has been reviewed and <strong>approved</strong>.</p>
                </div>
                
                <div class="highlight">
                    <p><strong>Your Account Number:</strong> {$accNo}</p>
                    <p>You can now use this account number along with your password to access your account.</p>
                </div>
                
                <div class="section">
                    <h2>Next Steps:</h2>
                    <ol style="margin-left:20px;">
                        <li>Log in to your account using your account number and password</li>
                        <li>Complete your profile setup</li>
                        <li>Set up your preferred communication channels</li>
                        <li>Start using our banking services</li>
                    </ol>
                </div>
                
                <div style="text-align:center;">
                    <a href="{$this->siteUrl}/user/login.php" class="btn">Access Your Account</a>
                </div>
                
                <div class="section">
                    <p>Welcome to {$this->bankName}! We're thrilled to serve you.</p>
                </div>
HTML . $this->getFooter();
    }
    
    public function templateApplicationDeclined($data) {
        $fname = htmlspecialchars($data['fname'] ?? '');
        $reason = htmlspecialchars($data['reason'] ?? 'Does not meet current criteria');
        
        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2 style="color:{$this->palette['danger']};">Account Application Status</h2>
                    <p>Dear {$fname},</p>
                    <p>Thank you for your interest in {$this->bankName}. We have reviewed your application carefully.</p>
                </div>
                
                <div class="highlight" style="border-left-color:{$this->palette['danger']};">
                    <p><strong>Application Status: Not Approved</strong></p>
                    <p><strong>Reason:</strong> {$reason}</p>
                </div>
                
                <div class="section">
                    <h2>What Now?</h2>
                    <p>While we cannot approve your application at this time, we encourage you to:</p>
                    <ul style="margin-left:20px;">
                        <li>Review the reason for declining your application</li>
                        <li>Address any concerns and reapply in the future</li>
                        <li>Contact us for more information: {$this->supportEmail}</li>
                    </ul>
                </div>
                
                <div class="section">
                    <p>We appreciate your understanding and hope to serve you in the future.</p>
                </div>
HTML . $this->getFooter();
    }
    
    public function templateTicketCreated($data) {
        $fname = htmlspecialchars($data['fname'] ?? '');
        $ticketId = htmlspecialchars($data['ticket_id'] ?? '');
        $subject = htmlspecialchars($data['subject'] ?? '');
        $date = htmlspecialchars($data['creation_date'] ?? ($data['date'] ?? date('Y-m-d')));
        
        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>Support Ticket Created</h2>
                    <p>Dear {$fname},</p>
                    <p>Your support ticket has been successfully created and assigned to our support team.</p>
                </div>
                
                <div class="highlight">
                    <table class="details-table" style="margin:0;">
                        <tr>
                            <td>Ticket ID:</td>
                            <td><strong>{$ticketId}</strong></td>
                        </tr>
                        <tr>
                            <td>Subject:</td>
                            <td>{$subject}</td>
                        </tr>
                        <tr>
                            <td>Created:</td>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td><span class="success">Open</span></td>
                        </tr>
                    </table>
                </div>
                
                <div class="section">
                    <p><strong>Reference Your Ticket ID:</strong> Please include your ticket ID ({$ticketId}) in all future correspondence about this issue.</p>
                    <p>Our support team typically responds within 24 hours.</p>
                </div>
HTML . $this->getFooter();
    }
    
    public function templateTransactionAlert($data) {
        $type = htmlspecialchars($data['transaction_type'] ?? ($data['type'] ?? 'Transaction'));
        $amount = htmlspecialchars(($data['currency'] ?? '') . ' ' . ($data['amount'] ?? '0'));
        $description = htmlspecialchars($data['description'] ?? '');
        $status = htmlspecialchars($data['status'] ?? 'Completed');
        $statusClass = ($status === 'Completed') ? 'success' : 'danger';
        
        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>Transaction Notification</h2>
                    <p>You have received a {$type} notification.</p>
                </div>
                
                <div class="highlight">
                    <table class="details-table" style="margin:0;">
                        <tr>
                            <td>Type:</td>
                            <td>{$type}</td>
                        </tr>
                        <tr>
                            <td>Amount:</td>
                            <td><strong>{$amount}</strong></td>
                        </tr>
                        <tr>
                            <td>Description:</td>
                            <td>{$description}</td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td><span class="{$statusClass}">{$status}</span></td>
                        </tr>
                    </table>
                </div>
                
                <div class="section">
                    <p>If you have questions about this transaction, please log in to your account or contact our support team.</p>
                </div>
HTML . $this->getFooter();
    }

    public function templateLoanAlert($data) {
        $fname = htmlspecialchars($data['fname'] ?? 'Customer');
        $loanId = htmlspecialchars($data['loan_id'] ?? 'N/A');
        $purpose = htmlspecialchars($data['purpose'] ?? 'Loan Application');
        $amount = htmlspecialchars($data['amount'] ?? 'Requested');
        $date = htmlspecialchars($data['creation_date'] ?? date('Y-m-d H:i:s'));

        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>Loan Application Received</h2>
                    <p>Dear {$fname},</p>
                    <p>Your loan application has been submitted successfully and is now under review.</p>
                </div>

                <div class="highlight">
                    <table class="details-table" style="margin:0;">
                        <tr>
                            <td>Application ID:</td>
                            <td><strong>{$loanId}</strong></td>
                        </tr>
                        <tr>
                            <td>Purpose:</td>
                            <td>{$purpose}</td>
                        </tr>
                        <tr>
                            <td>Requested Amount:</td>
                            <td>{$amount}</td>
                        </tr>
                        <tr>
                            <td>Submitted On:</td>
                            <td>{$date}</td>
                        </tr>
                        <tr>
                            <td>Status:</td>
                            <td><span class="success">In Review</span></td>
                        </tr>
                    </table>
                </div>

                <div class="section">
                    <p>Our team typically responds within 24 hours with the next steps.</p>
                </div>
HTML . $this->getFooter();
    }

    public function templateOtpCode($data) {
        $fname = htmlspecialchars($data['fname'] ?? 'Customer');
        $otp = htmlspecialchars($data['otp'] ?? '');
        $expiryMin = htmlspecialchars((string)($data['expiry_min'] ?? '10'));

        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>One-Time Password (OTP)</h2>
                    <p>Dear {$fname},</p>
                    <p>Use the one-time code below to continue your secure action:</p>
                </div>

                <div class="highlight" style="text-align:center;">
                    <p style="font-size:30px;letter-spacing:6px;font-weight:700;color:#0f1f3c;margin:0;">{$otp}</p>
                </div>

                <div class="section">
                    <p>This code expires in <strong>{$expiryMin} minutes</strong>.</p>
                    <p>For your security, never share this OTP with anyone.</p>
                </div>
HTML . $this->getFooter();
    }

    public function templateAdminnewapplication($data) {
        $fname = htmlspecialchars($data['fname'] ?? '');
        $lname = htmlspecialchars($data['lname'] ?? '');
        $email = htmlspecialchars($data['email'] ?? '');
        $phone = htmlspecialchars($data['phone'] ?? '');
        $type = htmlspecialchars($data['type'] ?? '');
        $currency = htmlspecialchars($data['currency'] ?? '');
        $work = htmlspecialchars($data['work'] ?? '');
        $addr = htmlspecialchars(trim((string)($data['addr'] ?? '') . ' ' . (string)($data['city'] ?? '') . ' ' . (string)($data['state'] ?? '') . ' ' . (string)($data['nation'] ?? '') . ' ' . (string)($data['zip'] ?? '')));
        $uname = htmlspecialchars($data['uname'] ?? '');
        $accNo = htmlspecialchars($data['acc_no'] ?? '');

        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>New Application from Client</h2>
                    <p>A new account/application submission has been received.</p>
                </div>

                <div class="highlight">
                    <table class="details-table" style="margin:0;">
                        <tr><td>Name:</td><td><strong>{$fname} {$lname}</strong></td></tr>
                        <tr><td>Email:</td><td>{$email}</td></tr>
                        <tr><td>Phone:</td><td>{$phone}</td></tr>
                        <tr><td>Account Type:</td><td>{$type}</td></tr>
                        <tr><td>Currency:</td><td>{$currency}</td></tr>
                        <tr><td>Occupation:</td><td>{$work}</td></tr>
                        <tr><td>Address:</td><td>{$addr}</td></tr>
                        <tr><td>Username:</td><td>{$uname}</td></tr>
                        <tr><td>Account Number:</td><td>{$accNo}</td></tr>
                    </table>
                </div>
HTML . $this->getFooter();
    }

    public function templateAdminapplicationattachment($data) {
        $fname = htmlspecialchars($data['fname'] ?? '');
        $lname = htmlspecialchars($data['lname'] ?? '');
        $email = htmlspecialchars($data['email'] ?? '');
        $accNo = htmlspecialchars($data['acc_no'] ?? '');

        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>Identity Document Attachment</h2>
                    <p>The latest applicant identity document is attached to this email.</p>
                </div>

                <div class="highlight">
                    <p><strong>Applicant:</strong> {$fname} {$lname}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Account Number:</strong> {$accNo}</p>
                </div>
HTML . $this->getFooter();
    }

    public function templateContactformsubmission($data) {
        $department = htmlspecialchars($data['department'] ?? 'General Inquiry');
        $name = htmlspecialchars($data['name'] ?? '');
        $email = htmlspecialchars($data['email'] ?? '');
        $phone = htmlspecialchars($data['phone'] ?? '');
        $comments = htmlspecialchars($data['comments'] ?? '');
        $commentsFormatted = nl2br($comments);

        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>New Contact Form Submission</h2>
                </div>

                <div class="highlight">
                    <p><strong>Department:</strong> {$department}</p>
                    <p><strong>Name:</strong> {$name}</p>
                    <p><strong>Email:</strong> {$email}</p>
                    <p><strong>Phone:</strong> {$phone}</p>
                </div>

                <div class="section">
                    <p><strong>Message:</strong></p>
                    <p>{$commentsFormatted}</p>
                </div>
HTML . $this->getFooter();
    }
    
    private function renderDefault($templateName, $data) {
        // Fallback template
        return $this->getHeader() . <<<HTML
                <div class="section">
                    <h2>{$templateName}</h2>
                    <p>Message from {$this->bankName}</p>
                </div>
HTML . $this->getFooter();
    }
}
?>
