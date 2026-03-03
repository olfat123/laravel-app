import React, { useState, useEffect } from "react";
import CurrencyFormatter from "@/Components/CurrencyFormatter";
import { Link, router } from "@inertiajs/react";
import { productRoute } from '@/Helper';

export default function CartItem({ item }) {
    const [qty, setQty] = useState(item.quantity);
    const [error, setError] = useState('');

    // Sync local qty when server updates item.quantity
    useEffect(() => {
        setQty(item.quantity);
    }, [item.quantity]);

    const updateQuantity = (newQty) => {
        const parsed = parseInt(newQty, 10);
        if (isNaN(parsed) || parsed < 1) return;
        setQty(parsed);
        setError('');
        router.put(route('cart.update', item.product_id), {
            quantity: parsed,
            option_ids: item.option_ids,
        }, {
            preserveScroll: true,
            onError: (errors) => {
                setQty(item.quantity); // revert on error
                setError(Object.values(errors)[0] || 'An error occurred.');
            },
        });
    };

    const onDeleteClick = () => {
        router.delete(route('cart.destroy', item.product_id), {
            data: { cart_item_id: item.id, option_ids: item.option_ids },
            preserveScroll: true,
        });
    };

    return (
        <div className="flex gap-6 p-3">
            <Link href={productRoute(item)} className="w-16 h-16 flex items-center justify-center">
                <img src={item.image} alt={item.title} className="max-w-full max-h-full" />
            </Link>
            <div className="flex-1 flex flex-col">
                <div className="flex-1">
                    <h3 className="mb-3 text-sm font-semibold">
                        <Link href={productRoute(item)}>{item.title}</Link>
                    </h3>
                    <div className="flex flex-col gap-1 text-sm mb-2">
                        {item.options.map((option) => (
                            <div key={option.id}>
                                <strong className="font-bold">{option.type.name}:</strong> {option.name}
                            </div>
                        ))}
                    </div>
                </div>
                <div className="flex justify-between items-center mt-4">
                    <div className="flex gap-2 items-center flex-wrap">
                        {/* Quantity stepper */}
                        <div className="flex items-center border border-base-300 rounded-lg overflow-hidden">
                            <button
                                type="button"
                                className="btn btn-ghost btn-sm px-2 rounded-none"
                                onClick={() => updateQuantity(qty - 1)}
                                disabled={qty <= 1}
                            >−</button>
                            <input
                                type="number"
                                min="1"
                                value={qty}
                                onChange={(e) => setQty(e.target.value)}
                                onBlur={(e) => updateQuantity(e.target.value)}
                                className="w-12 text-center bg-transparent border-0 focus:outline-none text-sm py-1"
                            />
                            <button
                                type="button"
                                className="btn btn-ghost btn-sm px-2 rounded-none"
                                onClick={() => updateQuantity(qty + 1)}
                            >+</button>
                        </div>
                        {error && <span className="text-error text-xs">{error}</span>}
                        <button onClick={onDeleteClick} className="btn btn-sm btn-ghost text-error">Remove</button>
                    </div>
                    <div className="font-bold text-lg">
                        <CurrencyFormatter amount={item.price * qty} />
                    </div>
                </div>
            </div>
        </div>
    );
}
