import React, { useState } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, useForm, Link } from '@inertiajs/react';
import CurrencyFormatter from '@/Components/CurrencyFormatter';
import InputLabel from '@/Components/InputLabel';
import TextInput from '@/Components/TextInput';
import InputError from '@/Components/InputError';
import PrimaryButton from '@/Components/PrimaryButton';
import { CreditCardIcon, TruckIcon } from '@heroicons/react/24/outline';
import axios from 'axios';

export default function Index({ checkoutItems, total_price, vendor_id }) {
    const { data, setData, post, processing, errors } = useForm({
        shipping_name: '',
        shipping_phone: '',
        shipping_address: '',
        shipping_city: '',
        shipping_state: '',
        shipping_country: '',
        shipping_zip: '',
        payment_method: 'cod',
        vendor_id: vendor_id || '',
        coupon_code: '',
    });

    const [couponCode, setCouponCode]       = useState('');
    const [couponData, setCouponData]       = useState(null);
    const [couponError, setCouponError]     = useState('');
    const [couponLoading, setCouponLoading] = useState(false);

    const appliedTotal = couponData ? couponData.final_total : total_price;

    const applyCoupon = async () => {
        if (!couponCode.trim()) return;
        setCouponLoading(true);
        setCouponError('');
        setCouponData(null);
        try {
            const response = await axios.post(route('coupon.apply'), {
                code: couponCode,
                vendor_id: vendor_id || null,
            });
            setCouponData(response.data);
            setData('coupon_code', response.data.code);
        } catch (err) {
            setCouponError(err.response?.data?.error ?? 'Invalid coupon code.');
        } finally {
            setCouponLoading(false);
        }
    };

    const removeCoupon = () => {
        setCouponData(null);
        setCouponCode('');
        setCouponError('');
        setData('coupon_code', '');
    };

    const handleSubmit = (e) => {
        e.preventDefault();
        post(route('cart.place-order'));
    };

    return (
        <AuthenticatedLayout>
            <Head title="Checkout" />

            <div className="container mx-auto p-6 max-w-5xl">
                <h1 className="text-2xl font-bold mb-6">Checkout</h1>

                <form onSubmit={handleSubmit} className="flex flex-col lg:flex-row gap-6">
                    {/* Left column — Shipping & Payment */}
                    <div className="flex-1 flex flex-col gap-6">

                        {/* Shipping Address */}
                        <div className="card bg-white dark:bg-gray-800 shadow">
                            <div className="card-body">
                                <h2 className="text-lg font-semibold mb-4">Shipping Address</h2>

                                <div className="grid grid-cols-1 sm:grid-cols-2 gap-4">
                                    <div>
                                        <InputLabel htmlFor="shipping_name" value="Full Name" />
                                        <TextInput
                                            id="shipping_name"
                                            className="mt-1 block w-full"
                                            value={data.shipping_name}
                                            onChange={(e) => setData('shipping_name', e.target.value)}
                                            placeholder="John Doe"
                                            required
                                        />
                                        <InputError message={errors.shipping_name} className="mt-1" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="shipping_phone" value="Phone Number" />
                                        <TextInput
                                            id="shipping_phone"
                                            className="mt-1 block w-full"
                                            value={data.shipping_phone}
                                            onChange={(e) => setData('shipping_phone', e.target.value)}
                                            placeholder="+20 1xx xxx xxxx"
                                            required
                                        />
                                        <InputError message={errors.shipping_phone} className="mt-1" />
                                    </div>

                                    <div className="sm:col-span-2">
                                        <InputLabel htmlFor="shipping_address" value="Street Address" />
                                        <TextInput
                                            id="shipping_address"
                                            className="mt-1 block w-full"
                                            value={data.shipping_address}
                                            onChange={(e) => setData('shipping_address', e.target.value)}
                                            placeholder="123 Main St, Apt 4"
                                            required
                                        />
                                        <InputError message={errors.shipping_address} className="mt-1" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="shipping_city" value="City" />
                                        <TextInput
                                            id="shipping_city"
                                            className="mt-1 block w-full"
                                            value={data.shipping_city}
                                            onChange={(e) => setData('shipping_city', e.target.value)}
                                            placeholder="Cairo"
                                            required
                                        />
                                        <InputError message={errors.shipping_city} className="mt-1" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="shipping_state" value="State / Governorate" />
                                        <TextInput
                                            id="shipping_state"
                                            className="mt-1 block w-full"
                                            value={data.shipping_state}
                                            onChange={(e) => setData('shipping_state', e.target.value)}
                                            placeholder="Cairo Governorate"
                                        />
                                        <InputError message={errors.shipping_state} className="mt-1" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="shipping_country" value="Country" />
                                        <TextInput
                                            id="shipping_country"
                                            className="mt-1 block w-full"
                                            value={data.shipping_country}
                                            onChange={(e) => setData('shipping_country', e.target.value)}
                                            placeholder="Egypt"
                                            required
                                        />
                                        <InputError message={errors.shipping_country} className="mt-1" />
                                    </div>

                                    <div>
                                        <InputLabel htmlFor="shipping_zip" value="ZIP / Postal Code" />
                                        <TextInput
                                            id="shipping_zip"
                                            className="mt-1 block w-full"
                                            value={data.shipping_zip}
                                            onChange={(e) => setData('shipping_zip', e.target.value)}
                                            placeholder="12345"
                                        />
                                        <InputError message={errors.shipping_zip} className="mt-1" />
                                    </div>
                                </div>
                            </div>
                        </div>

                        {/* Payment Method */}
                        <div className="card bg-white dark:bg-gray-800 shadow">
                            <div className="card-body">
                                <h2 className="text-lg font-semibold mb-4">Payment Method</h2>

                                <div className="flex flex-col gap-3">
                                    {/* Paymob Credit Card */}
                                    <label
                                        className={`flex items-center gap-4 p-4 rounded-lg border-2 cursor-pointer transition-colors ${
                                            data.payment_method === 'paymob_cc'
                                                ? 'border-blue-500 bg-blue-50 dark:bg-blue-900/20'
                                                : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="paymob_cc"
                                            checked={data.payment_method === 'paymob_cc'}
                                            onChange={(e) => setData('payment_method', e.target.value)}
                                            className="radio radio-primary"
                                        />
                                        <CreditCardIcon className="size-6 text-blue-600" />
                                        <div>
                                            <p className="font-medium">Credit / Debit Card</p>
                                            <p className="text-sm text-gray-500">Secure payment via Paymob</p>
                                        </div>
                                    </label>

                                    {/* Cash on Delivery */}
                                    <label
                                        className={`flex items-center gap-4 p-4 rounded-lg border-2 cursor-pointer transition-colors ${
                                            data.payment_method === 'cod'
                                                ? 'border-green-500 bg-green-50 dark:bg-green-900/20'
                                                : 'border-gray-200 dark:border-gray-600 hover:border-gray-300'
                                        }`}
                                    >
                                        <input
                                            type="radio"
                                            name="payment_method"
                                            value="cod"
                                            checked={data.payment_method === 'cod'}
                                            onChange={(e) => setData('payment_method', e.target.value)}
                                            className="radio radio-success"
                                        />
                                        <TruckIcon className="size-6 text-green-600" />
                                        <div>
                                            <p className="font-medium">Cash on Delivery</p>
                                            <p className="text-sm text-gray-500">Pay when your order arrives</p>
                                        </div>
                                    </label>
                                </div>

                                <InputError message={errors.payment_method} className="mt-2" />
                            </div>
                        </div>
                    </div>

                    {/* Right column — Order Summary */}
                    <div className="lg:w-80">
                        <div className="card bg-white dark:bg-gray-800 shadow sticky top-6">
                            <div className="card-body">
                                <h2 className="text-lg font-semibold mb-4">Order Summary</h2>

                                {(checkoutItems || []).map((group) => (
                                    <div key={group.user?.id} className="mb-4">
                                        <p className="text-sm font-medium text-gray-500 mb-2">
                                            {group.user?.name}'s Store
                                        </p>
                                        {(group.items || []).map((item) => (
                                            <div key={item.id} className="flex justify-between text-sm py-1">
                                                <span className="truncate max-w-[180px]">
                                                    {item.title} × {item.quantity}
                                                </span>
                                                <span className="ml-2 whitespace-nowrap">
                                                    <CurrencyFormatter amount={item.price * item.quantity} />
                                                </span>
                                            </div>
                                        ))}
                                    </div>
                                ))}

                                <div className="divider my-2"></div>

                                {/* Coupon Code */}
                                {!couponData ? (
                                    <div className="mb-3">
                                        <p className="text-sm font-medium mb-1 text-base-content/70">Have a coupon?</p>
                                        <div className="flex gap-2">
                                            <input
                                                type="text"
                                                className="input input-bordered input-sm flex-1 uppercase"
                                                placeholder="ENTER CODE"
                                                value={couponCode}
                                                onChange={e => setCouponCode(e.target.value.toUpperCase())}
                                                onKeyDown={e => e.key === 'Enter' && (e.preventDefault(), applyCoupon())}
                                            />
                                            <button
                                                type="button"
                                                className="btn btn-sm btn-outline"
                                                onClick={applyCoupon}
                                                disabled={couponLoading || !couponCode.trim()}
                                            >
                                                {couponLoading ? <span className="loading loading-spinner loading-xs" /> : 'Apply'}
                                            </button>
                                        </div>
                                        {couponError && <p className="text-error text-xs mt-1">{couponError}</p>}
                                    </div>
                                ) : (
                                    <div className="flex items-center justify-between bg-success/10 border border-success/30 rounded-lg px-3 py-2 mb-3">
                                        <div>
                                            <span className="badge badge-success badge-sm mr-2">{couponData.code}</span>
                                            <span className="text-sm text-success font-medium">Coupon applied!</span>
                                        </div>
                                        <button type="button" onClick={removeCoupon} className="btn btn-ghost btn-xs text-error">✕</button>
                                    </div>
                                )}

                                {/* Subtotal */}
                                {couponData && (
                                    <>
                                        <div className="flex justify-between text-sm text-base-content/70 mb-1">
                                            <span>Subtotal</span>
                                            <CurrencyFormatter amount={total_price} />
                                        </div>
                                        <div className="flex justify-between text-sm text-success mb-1">
                                            <span>Discount ({couponData.code})</span>
                                            <span>- <CurrencyFormatter amount={couponData.discount_amount} /></span>
                                        </div>
                                    </>
                                )}

                                <div className="flex justify-between font-bold text-base">
                                    <span>Total</span>
                                    <CurrencyFormatter amount={appliedTotal} />
                                </div>

                                <PrimaryButton
                                    type="submit"
                                    className="w-full mt-4 justify-center"
                                    disabled={processing}
                                >
                                    {processing
                                        ? 'Processing...'
                                        : data.payment_method === 'paymob_cc'
                                        ? 'Pay with Card'
                                        : 'Place Order'}
                                </PrimaryButton>

                                <Link
                                    href={route('cart.index')}
                                    className="block text-center mt-3 text-sm text-gray-500 hover:underline"
                                >
                                    ← Back to cart
                                </Link>
                            </div>
                        </div>
                    </div>
                </form>
            </div>
        </AuthenticatedLayout>
    );
}
