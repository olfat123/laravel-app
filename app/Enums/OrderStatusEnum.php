<?php

namespace App\Enums;

enum OrderStatusEnum: string
{
    case Pending    = 'pending';
    case Processing = 'processing';
    case Paid       = 'paid';
    case Shipped    = 'shipped';
    case Completed  = 'completed';
    case Refunded   = 'refunded';
    case Failed     = 'failed';
    case Delivered  = 'delivered';
    case Cancelled  = 'cancelled';

    public static function labels(): array
    {
        return [
            self::Pending->value    => __('Pending'),
            self::Processing->value => __('Processing'),
            self::Paid->value       => __('Paid'),
            self::Shipped->value    => __('Shipped'),
            self::Completed->value  => __('Completed'),
            self::Refunded->value   => __('Refunded'),
            self::Failed->value     => __('Failed'),
            self::Delivered->value  => __('Delivered'),
            self::Cancelled->value  => __('Cancelled'),
        ];
    }
}