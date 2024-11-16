<?php
include 'secrets.php';

$switchboard += ['account' => array (
    'checkout' => 'checkout'
    ,'status' => 'status'
    ,'webhook' => 'webhook'
    )];

    $stripe = new \Stripe\StripeClient($stripeSecretKey);

function checkout()
{
global $stripe;

header('Content-Type: application/json');
_sanitizeRequest(['userId', 'plan']);

$YOUR_DOMAIN = 'http://localhost:5173';

$checkout_session = $stripe->checkout->sessions->create([
  'ui_mode' => 'embedded',
  'line_items' => [[
    # Provide the exact Price ID (e.g. pr_1234) of the product you want to sell
    'price' => 'price_1PmZDICed0bi1v5gbpefjX7Q',
    'quantity' => 1,
  ]],
  'mode' => 'subscription',
  'return_url' => $YOUR_DOMAIN . '/checkoutreturn?session_id={CHECKOUT_SESSION_ID}',
  'automatic_tax' => [
    'enabled' => true,
  ],
  'metadata' => [
    'plan' => 1
  ],
  'client_reference_id' => $_SESSION["user"]["userId"],
  //'customer' => $_SESSION["user"]["userId"],
  'customer_email' => $_SESSION["user"]["email"],
]);

    $_SESSION["stripeSessionId"] = $checkout_session['id'];

  echo json_encode(array('clientSecret' => $checkout_session->client_secret));
}

/*------------------------------------
* after checkout successful, call status to 
* - retrieve session
* - fulfill (update user db & session)
* - return user
*-------------------------------*/
function status()
{
global $stripe;

    header('Content-Type: application/json');
    try {
        $stripeSession = $stripe->checkout->sessions->retrieve($_POST['stripeSessionId'], ['expand' => ['line_items']]);

        if ($_SESSION['user']['userId'] != $stripeSession->client_reference_id) throw new Error("userId mismatch");

        if ($stripeSession->status == 'complete') {
            logger("Set user account plan to {$stripeSession->metadata->plan}");
            fulfill_checkout($stripeSession);
        }
        echo json_encode(['stripeSession' => $stripeSession, 'status' => _createStatus(0, "Session retrieved"), 'customer_email' => $stripeSession->customer_details->email]);
        http_response_code(200);
    } catch (Error $e) {
        http_response_code(500);
        echo json_encode(['status' => _createStatus(213, "$e->getMessage()")]);
    }
}