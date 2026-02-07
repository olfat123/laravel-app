import React, {useState} from "react";
import CurrencyFormatter from "@/Components/CurrencyFormatter";
import { Link, router, useForm } from "@inertiajs/react";
import TextInput from "@/Components/TextInput";
import { productRoute } from '@/Helper';

export default function CartItem({ item, updating, onUpdateQuantity, onRemoveItem }) {
    const [error, setError] = useState('');
    const onDeleteClick = () => {
        router.delete(route('cart.destroy', item.product_id), {
            data: {
                option_ids: item.option_ids,
            },
            preserveScroll: true,
        });
    };
    const handleQuantityChange = (event) => {
        setError('');
        router.put(route('cart.update', item.product_id), {
            quantity: event.target.value,
            option_ids: item.option_ids,
        }, {
            preserveScroll: true,
            onError: (errors) => {
                setError(Object.values(errors)[0] || 'An error occurred.');
            }
        });
    };

    return (
        <div className="flex gap-6 p-3">
            <Link href={productRoute(item)}
                  className="w-16 h-16 flex items-center justify-center">
                <img
                    src={item.image}
                    alt={item.title}
                    className="max-w-full max-h-full"
                />
            </Link>
            <div className="flex-1 flex flex-col">
                <div className="flex-1">
                    <h3 className="mb-3 text-sm font-semibold">
                        <Link href={productRoute(item)}>
                            {item.title}
                        </Link>
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
                    <div className="flex gap-2 items-center">
                        <div className="text-sm">Quantity:</div>
                        <div className={error ? 'tooltip tooltip-open tooltip-error' : ''} data-tip={error}>
                            <TextInput
                                type="number"
                                min="1"
                                defaultValue={item.quantity}
                                onBlur={handleQuantityChange}
                                className="w-16 input-sm"></TextInput>
                        </div>
                        <button onClick={() => onDeleteClick()}
                            className="btn btn-sm btn-ghost">Delete</button>
                        <button className="btn btn-sm btn-ghost" > Save for Later</button>
                    </div>
                    <div className="font-bold text-lg">
                        <CurrencyFormatter amount={item.price * item.quantity} />
                    </div>
                </div>
            </div>
        </div>
    );
}
