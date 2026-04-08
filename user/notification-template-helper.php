<?php

if (!function_exists('notification_template_catalog')) {
    function notification_template_catalog()
    {
        return [
            'registration_welcome' => [
                'name' => 'Registration Welcome',
                'description' => 'Welcome message after account registration or account creation by admin.',
                'default_subject' => 'Welcome to {{bank_name}}',
            ],
            'debit_alert' => [
                'name' => 'Debit Alert',
                'description' => 'Debit and transfer completion alerts.',
                'default_subject' => 'Debit Alert: Transfer Initiated',
            ],
            'application_approved' => [
                'name' => 'Application Approved',
                'description' => 'Sent when an account application is approved.',
                'default_subject' => 'Application Approved',
            ],
            'application_declined' => [
                'name' => 'Application Declined',
                'description' => 'Sent when an account application is declined.',
                'default_subject' => 'Application Declined',
            ],
            'ticket_created' => [
                'name' => 'Support Ticket Created',
                'description' => 'Confirmation after user opens a support ticket.',
                'default_subject' => 'Support Ticket Confirmation',
            ],
            'loan_alert' => [
                'name' => 'Loan Alert',
                'description' => 'Loan application submission and update alerts.',
                'default_subject' => 'Loan Application Received',
            ],
            'transaction_alert' => [
                'name' => 'Transaction Alert',
                'description' => 'General transaction alerts sent from admin/user actions.',
                'default_subject' => 'Transaction Alert',
            ],
            'otp_code' => [
                'name' => 'OTP Code',
                'description' => 'One-time-password messages for login, transfer, and verification.',
                'default_subject' => 'Your OTP Code',
            ],
            'admin_new_application' => [
                'name' => 'Admin New Application',
                'description' => 'Admin alert when a new application/account submission is received.',
                'default_subject' => 'New Application from Client',
            ],
            'admin_application_attachment' => [
                'name' => 'Admin Application Attachment',
                'description' => 'Admin alert when identity document attachment is sent.',
                'default_subject' => 'Identity Document of Last Applicant',
            ],
            'contact_form_submission' => [
                'name' => 'Contact Form Submission',
                'description' => 'Website contact form submission received from frontend.',
                'default_subject' => 'Website Contact: {{department}}',
            ],
        ];
    }
}

if (!function_exists('notification_template_render_override')) {
    function notification_template_render_override($templateType, array $templateData, $subject, callable $getSetting, array $context = [])
    {
        $templateType = strtolower(trim((string)$templateType));
        if ($templateType === '') {
            return ['used' => false, 'subject' => (string)$subject, 'body' => ''];
        }

        $subjectKey = 'notify_tpl_subject_' . $templateType;
        $bodyKey = 'notify_tpl_body_' . $templateType;

        $rawSubjectTemplate = trim((string)$getSetting($subjectKey, ''));
        $rawBodyTemplate = trim((string)$getSetting($bodyKey, ''));

        if ($rawSubjectTemplate === '' && $rawBodyTemplate === '') {
            return ['used' => false, 'subject' => (string)$subject, 'body' => ''];
        }

        $rawTokens = notification_template_collect_tokens($templateType, $templateData, $subject, $context, false);
        $htmlTokens = notification_template_collect_tokens($templateType, $templateData, $subject, $context, true);

        $compiledSubject = (string)$subject;
        if ($rawSubjectTemplate !== '') {
            $compiledSubject = notification_template_replace_tokens($rawSubjectTemplate, $rawTokens);
            if (trim($compiledSubject) === '') {
                $compiledSubject = (string)$subject;
            }
        }

        $compiledBody = '';
        if ($rawBodyTemplate !== '') {
            $bodyContent = notification_template_replace_tokens($rawBodyTemplate, $htmlTokens);
            $compiledBody = notification_template_wrap_html($compiledSubject, $bodyContent, $context);
        }

        return [
            'used' => true,
            'subject' => $compiledSubject,
            'body' => $compiledBody,
        ];
    }
}

if (!function_exists('notification_template_collect_tokens')) {
    function notification_template_collect_tokens($templateType, array $templateData, $subject, array $context, $escapeHtml)
    {
        $normalize = function ($value) use ($escapeHtml) {
            $str = trim((string)$value);
            if ($escapeHtml) {
                return htmlspecialchars($str, ENT_QUOTES, 'UTF-8');
            }
            return $str;
        };

        $tokens = [
            'template_type' => $normalize($templateType),
            'subject' => $normalize($subject),
            'bank_name' => $normalize($context['bank_name'] ?? 'Banking System'),
            'support_email' => $normalize($context['support_email'] ?? 'support@bank.com'),
            'site_url' => $normalize($context['site_url'] ?? ''),
            'year' => $normalize(date('Y')),
            'today' => $normalize(date('Y-m-d')),
        ];

        foreach ($templateData as $key => $value) {
            if (!is_scalar($value) && $value !== null) {
                continue;
            }
            $safeKey = strtolower(trim((string)$key));
            if ($safeKey === '' || !preg_match('/^[a-z0-9_]+$/', $safeKey)) {
                continue;
            }
            $tokens[$safeKey] = $normalize($value ?? '');
        }

        if (!isset($tokens['name'])) {
            $fname = trim((string)($templateData['fname'] ?? $templateData['name'] ?? ''));
            $lname = trim((string)($templateData['lname'] ?? ''));
            $fullName = trim($fname . ' ' . $lname);
            if ($fullName !== '') {
                $tokens['name'] = $normalize($fullName);
            }
        }

        return $tokens;
    }
}

if (!function_exists('notification_template_replace_tokens')) {
    function notification_template_replace_tokens($template, array $tokens)
    {
        $replacements = [];
        foreach ($tokens as $key => $value) {
            $replacements['{{' . $key . '}}'] = (string)$value;
        }
        return strtr((string)$template, $replacements);
    }
}

if (!function_exists('notification_template_wrap_html')) {
    function notification_template_wrap_html($subject, $bodyContent, array $context)
    {
        $bodyContent = trim((string)$bodyContent);
        if ($bodyContent === '') {
            return '';
        }

        if (stripos($bodyContent, '<html') !== false || stripos($bodyContent, '<!doctype') !== false) {
            return $bodyContent;
        }

        $bankName = htmlspecialchars((string)($context['bank_name'] ?? 'Banking System'), ENT_QUOTES, 'UTF-8');
        $supportEmail = htmlspecialchars((string)($context['support_email'] ?? 'support@bank.com'), ENT_QUOTES, 'UTF-8');
        $siteUrl = htmlspecialchars((string)($context['site_url'] ?? '#'), ENT_QUOTES, 'UTF-8');
        $subjectSafe = htmlspecialchars((string)$subject, ENT_QUOTES, 'UTF-8');
        $year = date('Y');

        return "<!DOCTYPE html>\n"
            . "<html><head><meta charset=\"UTF-8\"><meta name=\"viewport\" content=\"width=device-width, initial-scale=1.0\"></head>\n"
            . "<body style=\"margin:0;background:#f5f7fa;font-family:Arial,sans-serif;color:#1f2937;\">\n"
            . "<table role=\"presentation\" width=\"100%\" cellpadding=\"0\" cellspacing=\"0\" style=\"background:#f5f7fa;padding:24px 0;\"><tr><td align=\"center\">\n"
            . "<table role=\"presentation\" width=\"600\" cellpadding=\"0\" cellspacing=\"0\" style=\"max-width:600px;background:#ffffff;border:1px solid #dbe3ee;border-radius:12px;overflow:hidden;\">\n"
            . "<tr><td style=\"background:#0d1f3c;color:#ffffff;padding:20px 24px;font-size:20px;font-weight:700;\">{$bankName}</td></tr>\n"
            . "<tr><td style=\"padding:24px;\">\n"
            . "<h2 style=\"margin:0 0 16px;font-size:20px;color:#0d1f3c;\">{$subjectSafe}</h2>\n"
            . $bodyContent
            . "</td></tr>\n"
            . "<tr><td style=\"padding:18px 24px;background:#f9fbff;border-top:1px solid #dbe3ee;color:#6b7280;font-size:12px;\">\n"
            . "<div>Need help? <a href=\"mailto:{$supportEmail}\" style=\"color:#0d1f3c;text-decoration:none;\">{$supportEmail}</a></div>\n"
            . "<div style=\"margin-top:4px;\"><a href=\"{$siteUrl}\" style=\"color:#0d1f3c;text-decoration:none;\">Visit website</a></div>\n"
            . "<div style=\"margin-top:8px;\">&copy; {$year} {$bankName}</div>\n"
            . "</td></tr></table></td></tr></table></body></html>";
    }
}

if (!function_exists('notification_template_default_overrides')) {
    function notification_template_default_overrides()
    {
        return [
            'registration_welcome' => [
                'subject' => 'Welcome to {{bank_name}}',
                'body' => '<p>Dear {{name}},</p><p>Your account has been successfully created and is ready to use.</p><p><strong>Account Number:</strong> {{acc_no}}<br><strong>Account Type:</strong> {{type_label}}<br><strong>Currency:</strong> {{currency}}</p><p>You can now sign in and start banking online.</p>',
            ],
            'debit_alert' => [
                'subject' => 'Debit Alert: {{description}}',
                'body' => '<p>Dear {{name}},</p><p>A debit transaction was processed on your account.</p><p><strong>Amount:</strong> {{currency}} {{amount}}<br><strong>Description:</strong> {{description}}<br><strong>Date:</strong> {{date}}<br><strong>Balance:</strong> {{currency}} {{balance}}</p><p>If you did not authorize this transaction, contact support immediately.</p>',
            ],
            'application_approved' => [
                'subject' => 'Application Approved - {{bank_name}}',
                'body' => '<p>Dear {{fname}},</p><p>Your account application has been approved.</p><p><strong>Account Number:</strong> {{acc_no}}</p><p>You can now log in and access your banking dashboard.</p>',
            ],
            'application_declined' => [
                'subject' => 'Application Status Update - {{bank_name}}',
                'body' => '<p>Dear {{fname}},</p><p>We have completed the review of your application and it was not approved at this time.</p><p><strong>Reason:</strong> {{reason}}</p><p>Please contact support for more details.</p>',
            ],
            'ticket_created' => [
                'subject' => 'Support Ticket Created: {{ticket_id}}',
                'body' => '<p>Dear {{fname}},</p><p>Your support ticket has been created successfully.</p><p><strong>Ticket ID:</strong> {{ticket_id}}<br><strong>Subject:</strong> {{subject}}<br><strong>Created:</strong> {{creation_date}}</p><p>Our support team will respond shortly.</p>',
            ],
            'loan_alert' => [
                'subject' => 'Loan Application Received - {{loan_id}}',
                'body' => '<p>Dear {{fname}},</p><p>Your loan application has been submitted and is currently under review.</p><p><strong>Application ID:</strong> {{loan_id}}<br><strong>Purpose:</strong> {{purpose}}<br><strong>Requested Amount:</strong> {{amount}}<br><strong>Submitted On:</strong> {{creation_date}}</p>',
            ],
            'transaction_alert' => [
                'subject' => 'Transaction Alert: {{transaction_type}}',
                'body' => '<p>Hello {{name}},</p><p>A transaction update is available on your account.</p><p><strong>Type:</strong> {{transaction_type}}<br><strong>Amount:</strong> {{currency}} {{amount}}<br><strong>Description:</strong> {{description}}<br><strong>Status:</strong> {{status}}</p>',
            ],
            'otp_code' => [
                'subject' => 'Your One-Time Password (OTP)',
                'body' => '<p>Dear {{name}},</p><p>Your one-time verification code is:</p><p style="font-size:28px;letter-spacing:6px;font-weight:700;margin:12px 0;">{{otp}}</p><p>This code expires in {{expiry_min}} minutes. Do not share this code with anyone.</p>',
            ],
            'admin_new_application' => [
                'subject' => 'New Application from Client - {{fname}} {{lname}}',
                'body' => '<p>A new client application was submitted.</p><p><strong>Name:</strong> {{fname}} {{lname}}<br><strong>Email:</strong> {{email}}<br><strong>Phone:</strong> {{phone}}<br><strong>Account Type:</strong> {{type}}<br><strong>Currency:</strong> {{currency}}<br><strong>Occupation:</strong> {{work}}<br><strong>Address:</strong> {{addr}} {{city}} {{state}} {{nation}} {{zip}}<br><strong>Username:</strong> {{uname}}</p>',
            ],
            'admin_application_attachment' => [
                'subject' => 'Identity Document of Last Applicant - {{fname}} {{lname}}',
                'body' => '<p>The identity document for the latest applicant is attached to this email.</p><p><strong>Applicant:</strong> {{fname}} {{lname}}<br><strong>Email:</strong> {{email}}<br><strong>Account Number:</strong> {{acc_no}}</p>',
            ],
            'contact_form_submission' => [
                'subject' => 'Website Contact: {{department}}',
                'body' => '<p><strong>New Contact Form Submission</strong></p><p><strong>Department:</strong> {{department}}<br><strong>Name:</strong> {{name}}<br><strong>Email:</strong> {{email}}<br><strong>Phone:</strong> {{phone}}</p><p><strong>Message:</strong><br>{{comments}}</p>',
            ],
        ];
    }
}
