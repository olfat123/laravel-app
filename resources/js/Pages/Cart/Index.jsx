import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, Link } from '@inertiajs/react';
import CurrencyFormatter from '@/Components/CurrencyFormatter';
import PrimaryButton from '@/Components/PrimaryButton';
import { CreditCardIcon } from '@heroicons/react/24/outline';
import CartItem from '@/Components/App/CartItem';

export default function Index({ cartItems, total_price, total_quantity, tax_rate, tax_amount, prices_include_tax, grand_total }) {
    const [updating, setUpdating] = useState(null);

    // Convert cartItems to array if it's an object, ensure it's always an array
    let cartItemsArray = [];
    if (Array.isArray(cartItems)) {
        cartItemsArray = cartItems;
    } else if (cartItems && typeof cartItems === 'object') {
        try {
            cartItemsArray = Object.values(cartItems);
        } catch (e) {
            cartItemsArray = [];
        }
    }

    return (
        <AuthenticatedLayout>
            <Head title="Shopping Cart" />

            <div className="container mx-auto p-8 flex flex-col gap-8 lg:flex-row gap-4">
                <div className="card w-full bg-white dark:bg-gray-800 order-2 lg:order-1">
                    <div className="card-body">
                        <h2 className="text-lg font-bold">Shopping Cart</h2>
                        <div className='my-4'>
                            {cartItemsArray.length === 0 && (
                                <p>Your cart is empty.</p>
                            )}
                            {cartItemsArray.map((cartItem) => (
                                <div key={cartItem.user.id}>
                                    <div className="flex items-center justify-between pb-2 border-b border-gray-300 mb-4">
                                        <Link href="/" className="underline">
                                            {cartItem.user.name}'s Store
                                        </Link>
                                        <div>
                                            <Link
                                                href={route('cart.checkout') + '?vendor_id=' + cartItem.user.id}
                                                className="btn btn-sm btn-ghost"
                                            >
                                                <CreditCardIcon className="size-6"/>
                                                Checkout this seller
                                            </Link>
                                        </div>
                                    </div>
                                    {(cartItem.items || []).map((item) => (
                                        <div key={item.id}>
                                            <CartItem
                                                item={item}
                                                updating={updating === `${item.id}_${JSON.stringify(item.option_ids)}`}
                                                // onUpdateQuantity={handleUpdateQuantity}
                                                // onRemoveItem={handleRemoveItem}
                                            />
                                            <div className="divider my-0"></div>
                                        </div>
                                    ))}
                                </div>
                            ))}
                        </div>
                    </div>
                </div>
                <div className="card flex-1 bg-white dark:bg-gray-800 lg:min-w-[260px] order-1 lg:order-2">
                    <div className="card-body">
                        <div className="flex justify-between text-sm mb-1">
                            <span>Subtotal ({total_quantity} items)</span>
                            <CurrencyFormatter amount={total_price}/>
                        </div>

                        {tax_rate > 0 && (
                            <div className="flex justify-between text-sm text-base-content/70 mb-1">
                                <span>
                                    Tax ({tax_rate}%{prices_include_tax ? ' incl.' : ''})
                                </span>
                                <span>{prices_include_tax ? 'Included' : <CurrencyFormatter amount={tax_amount} />}</span>
                            </div>
                        )}

                        <div className="divider my-1"></div>

                        <div className="flex justify-between font-bold text-base mb-3">
                            <span>Total</span>
                            <CurrencyFormatter amount={grand_total}/>
                        </div>

                        <Link href={route('cart.checkout')}>
                            <PrimaryButton className="rounded-full w-full justify-center">
                                <CreditCardIcon className="size-6"/>
                                Proceed to Checkout
                            </PrimaryButton>
                        </Link>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
