<?php

namespace App\Enums;

enum Role: string
{
    case Owner = 'owner';
    case Barber = 'barber';

    public function label(): string
    {
        return match ($this) {
            self::Owner => 'Shop owner',
            self::Barber => 'Stylist',
        };
    }
}
