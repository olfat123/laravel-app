import React from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { CheckCircleIcon } from '@heroicons/react/24/outline';
import { useTrans } from '@/i18n';

export default function Success({ message }) {
    const t = useTrans();
    return (
        <AuthenticatedLayout>
            <Head title={t('checkout.success.title')} />

            <div className="flex flex-col items-center justify-center min-h-[60vh] text-center p-6">
                <CheckCircleIcon className="size-20 text-green-500 mb-4" />
                <h1 className="text-3xl font-bold mb-2">{t('checkout.success.heading')}</h1>
                <p className="text-gray-600 dark:text-gray-400 max-w-md mb-6">
                    {message || t('checkout.success.message')}
                </p>
                <Link href={route('home')} className="btn btn-primary rounded-full px-8">
                    {t('checkout.success.continue')}
                </Link>
            </div>
        </AuthenticatedLayout>
    );
}
