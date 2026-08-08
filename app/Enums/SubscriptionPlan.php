<?php

namespace App\Enums;

enum SubscriptionPlan: string
{
    case Starter = 'starter';
    case Shop = 'shop';
    case Studio = 'studio';

    public function label(): string
    {
        return match ($this) {
            self::Starter => 'Starter',
            self::Shop => 'Shop',
            self::Studio => 'Studio',
        };
    }

    public function priceLabel(): string
    {
        return match ($this) {
            self::Starter => '£10',
            self::Shop => '£25',
            self::Studio => '£59',
        };
    }

    public function stripePriceId(): ?string
    {
        $priceId = config("stripe.prices.{$this->value}");

        return filled($priceId) ? $priceId : null;
    }

    public function maxBarbers(): ?int
    {
        return match ($this) {
            self::Starter => 1,
            self::Shop => 5,
            self::Studio => null,
        };
    }

    /**
     * @return array<int, array{value: string, label: string, price: string, description: string, features: array<int, string>}>
     */
    public static function options(): array
    {
        return [
            [
                'value' => self::Starter->value,
                'label' => self::Starter->label(),
                'price' => self::Starter->priceLabel(),
                'description' => 'For solo barbers getting organised.',
                'features' => [
                    '1 barber seat',
                    'Client CRM & notes',
                    'Private booking link',
                ],
            ],
            [
                'value' => self::Shop->value,
                'label' => self::Shop->label(),
                'price' => self::Shop->priceLabel(),
                'description' => 'Everything a busy shop floor needs.',
                'features' => [
                    'Up to 5 barbers',
                    'Team scheduling',
                    'Client self-booking',
                ],
            ],
            [
                'value' => self::Studio->value,
                'label' => self::Studio->label(),
                'price' => self::Studio->priceLabel(),
                'description' => 'For multi-chair salons and growing teams.',
                'features' => [
                    'Unlimited barbers',
                    'Priority support',
                    'Dedicated onboarding',
                ],
            ],
        ];
    }
}
