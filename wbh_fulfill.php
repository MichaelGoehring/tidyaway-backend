<?php

function fulfill_checkout($session_id) {
    // Set your secret key. Remember to switch to your live secret key in production.
    // See your keys here: https://dashboard.stripe.com/apikeys
    \Stripe\Stripe::setApiKey('sk_test_51OI703Ced0bi1v5g2XvTrgBek4DzbO0exkGSy6zEznJVPSEyjAnOJc8WEnJfDuLurmf5M1GYIKFOw3LSPPVU8l9L00Np4Awm0d');
  
    // TODO: Log the string "Fulfilling Checkout Session $session_id"
  
    // TODO: Make this function safe to run multiple times,
    // even concurrently, with the same session ID
  
    // TODO: Make sure fulfillment hasn't already been
    // peformed for this Checkout Session
  
    // Retrieve the Checkout Session from the API with line_items expanded
    $checkout_session = $stripe->checkout->sessions->retrieve($session_id, [
      'expand' => ['line_items'],
    ]);
  
    // Check the Checkout Session's payment_status property
    // to determine if fulfillment should be peformed
    if ($checkout_session->payment_status != 'unpaid') {
      // TODO: Perform fulfillment of the line items
  
      // TODO: Record/save fulfillment status for this
      // Checkout Session
    }
  }

  // Use the secret provided by Stripe CLI for local testing
// or your webhook endpoint's secret.
$endpoint_secret = 'whsec_...';

$payload = @file_get_contents('php://input');
$sig_header = $_SERVER['HTTP_STRIPE_SIGNATURE'];
$event = null;

try {
  $event = \Stripe\Webhook::constructEvent(
    $payload, $sig_header, $endpoint_secret
  );
} catch(\UnexpectedValueException $e) {
  // Invalid payload
  http_response_code(400);
  exit();
} catch(\Stripe\Exception\SignatureVerificationException $e) {
  // Invalid signature
  http_response_code(400);
  exit();
}

if (
  $event->type == 'checkout.session.completed'
  || $event->type == 'checkout.session.async_payment_succeeded'
) {
  fulfill_checkout($event->data->object->id);
}

http_response_code(200);