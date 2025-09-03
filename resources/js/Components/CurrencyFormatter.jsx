import React from 'react';

export default function CurrencyFormatter(
    { 
        amount, 
        currency = 'USD', 
        locale = 'en-US' 
    }) {
    const formattedAmount = new Intl.NumberFormat(locale, {
        style: 'currency',
        currency: currency,
    }).format(amount);

    return <span className="text-2xl">{formattedAmount}</span>;
}