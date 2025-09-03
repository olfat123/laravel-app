import { Link } from '@inertiajs/react';
import React from 'react';
import CurrencyFormatter from '../CurrencyFormatter';

export default function ProductItem({ product }) {
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
                    <button className="btn btn-primary">Add to Cart</button>
                    <CurrencyFormatter amount={product.price} currency="USD" />
                </div>
            </div>
        </div>
    );
}