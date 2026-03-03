import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { CheckCircleIcon } from '@heroicons/react/24/outline';

export default function Success({ message }) {
    return (
        <AuthenticatedLayout>
            <Head title="Order Confirmed" />

            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-6">
                <CheckCircleIcon className="size-20 text-green-500 mb-4" />
                <h1 className="text-3xl font-bold mb-2">Order Confirmed!</h1>
                <p className="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                    {message || 'Thank you for your order. We will process it shortly.'}
                </p>
                <Link href={route('home')} className="btn btn-primary rounded-full px-8">
                    Continue Shopping
                </Link>
            </div>
        </AuthenticatedLayout>
    );
}
