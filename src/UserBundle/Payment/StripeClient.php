<?php
/**
 * Created by PhpStorm.
 * User: AitAl
 * Date: 07/11/2018
 * Time: 23:48
 */

namespace UserBundle\Payment;
use Flosch\Bundle\StripeBundle\Stripe\StripeClient as BaseStripeClient;
use Stripe\Subscription;

class StripeClient extends BaseStripeClient
{
    public function __construct($stripeApiKey = 'sk_test_I1YLv1iydHXSyCY66Zo1FG0T')
    {
        parent::__construct($stripeApiKey);

        return $this;
    }

    public function retrieveSub($subId)
    {
        $sub = Subscription::retrieve($subId);
        return $sub;
    }
}