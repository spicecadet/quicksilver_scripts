<?php
/** Quicksilver script to send an email notification on creation of a multidev environment.
 *
 * @package MultidevCreationAlert
 */

namespace MultidevCreationAlert;

require 'vendor/autoload.php';

use SendGrid\Mail\From;
use SendGrid\Mail\To;
use SendGrid\Mail\Mail;
use MultidevCreationAlert\MultidevCreationAlert;
use Zendesk\API\HttpClient as ZendeskAPI;

// Send email via Sendgrid
$qs_alert = new MultidevCreationAlert();

$from = new From($qs_alert->getSecret('FROM_EMAIL'), $qs_alert->getSecret('FROM_USERNAME'));
$tos = [
    new To(
        $qs_alert->getSecret('TO_EMAIL'),
        $qs_alert->getSecret('TO_USERNAME'),
        [
            'subject' => "Pantheon Quicksilver Alert for $qs_alert->site_name",
            'dashboard-link' => $qs_alert->dashboard_link,
            'env-link' => $qs_alert->environment_link,
            'environment' => $qs_alert->site_env,
            'site-name' => $qs_alert->site_name,
            'user-email' => $qs_alert->user_email,
            'workflow-id' => $qs_alert->workflow_id
        ]
    )
];

$email = new Mail(
    $from,
    $tos
);
$email->setTemplateId($qs_alert->getSecret('SENDGRID_TEMPLATE_ID'));
$sendgrid = new \SendGrid($qs_alert->getSecret('SENDGRID_API_KEY'));

try {
    $response = $sendgrid->send($email);
    print $response->statusCode() . "\n";
    print_r($response->headers());
    print $response->body() . "\n";
} catch (Exception $e) {
    echo 'Caught exception: '.  $e->getMessage(). "\n";
}

// Zendesk Config
$subdomain = $qs_alert->getSecret('ZENDESK_SUBDOMAIN');
$username  = $qs_alert->getSecret('ZENDESK_USERNAME');
$token     = $qs_alert->getSecret('ZENDESK_API_TOKEN');

$client = new ZendeskAPI($subdomain);
$client->setAuth('basic', ['username' => $username, 'token' => $token]);

// Create a Zendesk Support ticket
$newTicket = $client->tickets()->create([
    'subject'  => "Quicksilver Support Request - NUS Multidev Environment Created for $qs_alert->site_name",
    'comment'  => [
        'body' => "A Multidev environment called $qs_alert->site_env has been created for the NUS site $qs_alert->site_name by $qs_alert->user_email. NUS requies IP Restrictions for all multidevs which will need to be enabled. This ticket has been created via Quicksilver."
    ],
    'priority' => 'normal'
]);
print_r($newTicket);
