<?php

namespace App\Domain\Subscriptions\Enums;

enum UpSubscriptionStatus: string
{
    case Pending = 'pending';
    case Active = 'active';
    case Expired = 'expired';
    case Cancelled = 'cancelled';
    case Suspended = 'suspended';
    case PaymentFailed = 'payment_failed';
}
