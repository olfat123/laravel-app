import { Link, useForm } from '@inertiajs/react';
import React from 'react';
import CurrencyFormatter from '../CurrencyFormatter';
import { productRoute } from '@/Helper';

export default function ProductItem({ product }) {
    const form = useForm({
        product_id: product.id,
        quantity: 1,
    });

    const hasVariations = product.has_variations;

    const addToCart = () => {
        form.post(route('cart.store', product.id), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="card bg-base-100 shadow-xl">
            <Link href={route('product.show', product.slug)}>
                <figure>
                    <img 
                    src={product.image_url}
                    alt={product.title}
                    className="aspect-square object-cover" />
                </figure>
            </Link>
            <div className="card-body">
                <h2 className="card-title">{product.title}</h2>
                <p>
                    by <Link href="/" className="hover:underline">{product.user.name}</Link>
                </p>
                <p>
                    in <Link href="/" className="hover:underline">{product.department.name}</Link>
                </p>
                <div className="card-actions justify-between items-center mt-3">
                    {hasVariations ? (
                        <Link
                            href={productRoute(product, 'show')}
                            className="btn btn-primary"
                        >
                            Select options
                        </Link>
                    ) : (
                        <button
                            onClick={addToCart}
                            disabled={product.quantity < 1}
                            className="btn btn-primary"
                        >
                            Add to Cart
                        </button>
                    )}
                    <CurrencyFormatter amount={product.price} currency="USD" />
                </div>
            </div>
        </div>
    );
}