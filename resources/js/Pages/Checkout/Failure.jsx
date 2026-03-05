import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { XCircleIcon } from '@heroicons/react/24/outline';
import { useTrans } from '@/i18n';

export default function Failure({ message }) {
    const t = useTrans();
    return (
        <AuthenticatedLayout>
            <Head title={t('checkout.failure.title')} />

            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-6">
                <XCircleIcon className="size-20 text-red-500 mb-4" />
                <h1 className="text-3xl font-bold mb-2">{t('checkout.failure.heading')}</h1>
                <p className="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                    {message || t('checkout.failure.message')}
                </p>
                <div className="flex gap-4">
                    <Link href={route('cart.index')} className="btn btn-outline rounded-full px-6">
                        {t('checkout.back_to_cart')}
                    </Link>
                    <Link href={route('home')} className="btn btn-primary rounded-full px-6">
                        {t('checkout.success.continue')}
                    </Link>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
