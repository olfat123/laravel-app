import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { XCircleIcon } from '@heroicons/react/24/outline';

export default function Failure({ message }) {
    return (
        <AuthenticatedLayout>
            <Head title="Payment Failed" />

            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-6">
                <XCircleIcon className="size-20 text-red-500 mb-4" />
                <h1 className="text-3xl font-bold mb-2">Payment Failed</h1>
                <p className="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                    {message || 'Something went wrong with your payment. Please try again.'}
                </p>
                <div className="flex gap-4">
                    <Link href={route('cart.index')} className="btn btn-outline rounded-full px-6">
                        Back to Cart
                    </Link>
                    <Link href={route('home')} className="btn btn-primary rounded-full px-6">
                        Continue Shopping
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
