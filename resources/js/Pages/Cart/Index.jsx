import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, router, Link } from '@inertiajs/react';
import CurrencyFormatter from '@/Components/CurrencyFormatter';
import PrimaryButton from '@/Components/PrimaryButton';
import { CreditCardIcon } from '@heroicons/react/24/outline';
import CartItem from '@/Components/App/CartItem';

export default function Index({ csrf_token, cartItems, total_price, total_quantity }) {
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
        <AuthenticatedLayout csrf_token={csrf_token} >
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
                                            <form action={route('cart.checkout')} method="POST">
                                                <input type="hidden" name="_token" value={csrf_token} />
                                                <input type="hidden" name="vendor_id" value={cartItem.user.id} />
                                                <button className="btn btn-sm btn-ghost">
                                                    <CreditCardIcon className="size-6"/>
                                                    Pay Only for this seller
                                                </button>
                                            </form>
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
                        Subtotal ({total_quantity} items): 
                        <CurrencyFormatter amount={total_price}/>
                        <form action={route('cart.checkout')} method="POST">
                            <input type="hidden" name="_token" value={csrf_token} />
                            <PrimaryButton className="rounded-full" >   
                                <CreditCardIcon className="size-6"/>
                                Proceed to Checkout
                            </PrimaryButton>
                        </form>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
