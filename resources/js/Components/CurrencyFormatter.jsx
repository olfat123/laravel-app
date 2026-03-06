import React from 'react';
import { usePage } from '@inertiajs/react';

export default function CurrencyFormatter({ amount, currency, locale }) {
    const { currency: siteCurrency = 'USD', currencyLocale: siteLocale = 'en-US' } = usePage().props;

    const resolvedCurrency = currency ?? siteCurrency;
    const resolvedLocale   = locale   ?? siteLocale;

    const formattedAmount = new Intl.NumberFormat(resolvedLocale, {
        style: 'currency',
        currency: resolvedCurrency,
    }).format(amount);

    return <span className="text-l font-semibold">{formattedAmount}</span>;
}
