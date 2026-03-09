<?php

namespace App\Observers;

use App\Enums\RolesEnum;
use App\Mail\NewOrderNotificationMail;
use App\Mail\OrderCreatedMail;
use App\Mail\OrderStatusChangedMail;
use App\Mail\OrderStatusChangedNotificationMail;
use App\Models\Order;
use App\Models\User;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Mail;

class OrderObserver
{
    /**
     * Handle the Order "created" event.
     * - Sends an order confirmation email to the customer in the current website locale.
     * - Sends a new-order notification to all admins and the vendor.
     */
    public function created(Order $order): void
    {
        $order->loadMissing('items.product', 'user', 'vendor');

        $currentLocale = App::getLocale();

        // 1. Customer confirmation (in current website locale)
        if ($order->user?->email) {
            Mail::to($order->user->email)
                ->send(new OrderCreatedMail($order, $currentLocale));
        }

        $notification = new NewOrderNotificationMail($order);

        // 2. All admin users
        $admins = User::role(RolesEnum::Admin->value)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(clone $notification);
        }

        // 3. Vendor (if different from the customer)
        if ($order->vendor?->email && $order->vendor_user_id !== $order->user_id) {
            Mail::to($order->vendor->email)->send(clone $notification);
        }
    }

    /**
     * Handle the Order "updated" event.
     * - Sends a status-change email to the customer in the current website locale.
     * - Sends a status-change notification to all admins and the vendor.
     */
    public function updated(Order $order): void
    {
        if (! $order->wasChanged('status')) {
            return;
        }

        $order->loadMissing('user', 'vendor');

        $oldStatus     = $order->getOriginal('status');
        $newStatus     = $order->status;
        $currentLocale = App::getLocale();

        // 1. Customer notification (in current website locale)
        if ($order->user?->email) {
            Mail::to($order->user->email)
                ->send(new OrderStatusChangedMail($order, $oldStatus, $newStatus, $currentLocale));
        }

        $notification = new OrderStatusChangedNotificationMail($order, $oldStatus, $newStatus);

        // 2. All admin users
        $admins = User::role(RolesEnum::Admin->value)->get();
        foreach ($admins as $admin) {
            Mail::to($admin->email)->send(clone $notification);
        }

        // 3. Vendor (if different from the customer)
        if ($order->vendor?->email && $order->vendor_user_id !== $order->user_id) {
            Mail::to($order->vendor->email)->send(clone $notification);
        }
    }
}