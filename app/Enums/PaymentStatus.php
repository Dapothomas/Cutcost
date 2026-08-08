<?php

namespace App\Enums;

enum PaymentStatus: string
{
    case Pending = 'pending';
    case Paid = 'paid';
    case Waived = 'waived';
    case Failed = 'failed';

    public function label(): string
    {
        return match ($this) {
            self::Pending => 'Awaiting payment',
            self::Paid => 'Paid',
            self::Waived => 'No payment required',
            self::Failed => 'Payment failed',
        };
    }

    public function isPaid(): bool
    {
        return $this === self::Paid || $this === self::Waived;
    }
}
