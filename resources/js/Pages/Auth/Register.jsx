import InputError from '@/Components/InputError';
import InputLabel from '@/Components/InputLabel';
import PrimaryButton from '@/Components/PrimaryButton';
import TextInput from '@/Components/TextInput';
import GuestLayout from '@/Layouts/GuestLayout';
import { Head, Link, useForm } from '@inertiajs/react';
import { useTrans } from '@/i18n';

export default function Register() {
    const { data, setData, post, processing, errors, reset } = useForm({
        role: 'customer',
        name: '',
        email: '',
        password: '',
        password_confirmation: '',
        store_name: '',
        store_address: '',
    });
    const t = useTrans();

    const isVendor = data.role === 'vendor';

    const submit = (e) => {
        e.preventDefault();
        post(route('register'), {
            onFinish: () => reset('password', 'password_confirmation'),
        });
    };

    return (
        <GuestLayout>
            <Head title={t('auth.register.title')} />

            <form onSubmit={submit}>
                {/* ── Role selector ─────────────────────────────── */}
                <div className="mb-6">
                    <InputLabel value={t('auth.register.register_as')} className="mb-3" />
                    <div className="grid grid-cols-2 gap-3">
                        {[
                            {
                                value: 'customer',
                                label: t('auth.register.customer'),
                                desc: t('auth.register.customer_desc'),
                                icon: (
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M15.75 6a3.75 3.75 0 11-7.5 0 3.75 3.75 0 017.5 0zM4.501 20.118a7.5 7.5 0 0114.998 0A17.933 17.933 0 0112 21.75c-2.676 0-5.216-.584-7.499-1.632z" />
                                    </svg>
                                ),
                            },
                            {
                                value: 'vendor',
                                label: t('auth.register.vendor'),
                                desc: t('auth.register.vendor_desc'),
                                icon: (
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={1.5}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d="M13.5 21v-7.5a.75.75 0 01.75-.75h3a.75.75 0 01.75.75V21m-4.5 0H2.36m11.14 0H18m0 0h3.64m-1.39 0V9.349m-16.5 11.65V9.35m0 0a3.001 3.001 0 003.75-.615A2.993 2.993 0 009.75 9.75c.896 0 1.7-.393 2.25-1.016a2.993 2.993 0 002.25 1.016c.896 0 1.7-.393 2.25-1.016a3.001 3.001 0 003.75.614m-16.5 0a3.004 3.004 0 01-.621-4.72L4.318 3.44A1.5 1.5 0 015.378 3h13.243a1.5 1.5 0 011.06.44l1.19 1.189a3 3 0 01-.621 4.72m-13.5 8.65h3.75a.75.75 0 00.75-.75V13.5a.75.75 0 00-.75-.75H6.75a.75.75 0 00-.75.75v3.75c0 .415.336.75.75.75z" />
                                    </svg>
                                ),
                            },
                        ].map(({ value, label, desc, icon }) => (
                            <button
                                key={value}
                                type="button"
                                onClick={() => setData('role', value)}
                                className={`flex flex-col items-center gap-2 p-4 rounded-xl border-2 text-center transition-all cursor-pointer
                                    ${data.role === value
                                        ? 'border-primary bg-primary/10 text-primary'
                                        : 'border-gray-200 hover:border-gray-300 text-gray-500 hover:text-gray-700'
                                    }`}
                            >
                                {icon}
                                <span className="font-semibold text-sm">{label}</span>
                                <span className="text-xs opacity-70">{desc}</span>
                            </button>
                        ))}
                    </div>
                    <InputError message={errors.role} className="mt-2" />
                </div>

                {/* ── Common fields ──────────────────────────────── */}
                <div>
                    <InputLabel htmlFor="name" value={t('checkout.full_name')} />
                    <TextInput
                        id="name"
                        name="name"
                        value={data.name}
                        className="mt-1 block w-full"
                        autoComplete="name"
                        isFocused={true}
                        onChange={(e) => setData('name', e.target.value)}
                        required
                    />
                    <InputError message={errors.name} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="email" value={t('auth.email')} />
                    <TextInput
                        id="email"
                        type="email"
                        name="email"
                        value={data.email}
                        className="mt-1 block w-full"
                        autoComplete="username"
                        onChange={(e) => setData('email', e.target.value)}
                        required
                    />
                    <InputError message={errors.email} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password" value={t('auth.password')} />
                    <TextInput
                        id="password"
                        type="password"
                        name="password"
                        value={data.password}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData('password', e.target.value)}
                        required
                    />
                    <InputError message={errors.password} className="mt-2" />
                </div>

                <div className="mt-4">
                    <InputLabel htmlFor="password_confirmation" value={t('auth.confirm_password')} />
                    <TextInput
                        id="password_confirmation"
                        type="password"
                        name="password_confirmation"
                        value={data.password_confirmation}
                        className="mt-1 block w-full"
                        autoComplete="new-password"
                        onChange={(e) => setData('password_confirmation', e.target.value)}
                        required
                    />
                    <InputError message={errors.password_confirmation} className="mt-2" />
                </div>

                {/* ── Vendor-only fields ─────────────────────────── */}
                {isVendor && (
                    <div className="mt-6 space-y-4 rounded-xl border border-primary/20 bg-primary/5 p-4">
                        <p className="text-xs font-semibold uppercase tracking-widest text-primary">{t('auth.register.store_details')}</p>

                        <div>
                            <InputLabel htmlFor="store_name" value={t('auth.register.store_name')} />
                            <TextInput
                                id="store_name"
                                name="store_name"
                                value={data.store_name}
                                className="mt-1 block w-full"
                                onChange={(e) => setData('store_name', e.target.value)}
                                required={isVendor}
                                placeholder="e.g. My Awesome Store"
                            />
                            <InputError message={errors.store_name} className="mt-2" />
                        </div>

                        <div>
                            <InputLabel htmlFor="store_address" value={t('auth.register.store_address')} />
                            <textarea
                                id="store_address"
                                name="store_address"
                                value={data.store_address}
                                rows={2}
                                onChange={(e) => setData('store_address', e.target.value)}
                                placeholder="Your store's address (optional)"
                                className="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 text-sm"
                            />
                            <InputError message={errors.store_address} className="mt-2" />
                        </div>

                        <p className="text-xs text-gray-500">
                            {t('auth.register.vendor_review')}
                        </p>
                    </div>
                )}

                <div className="mt-6 flex items-center justify-end">
                    <Link href={route('login')} className="link link-hover text-sm">
                        {t('auth.register.already_registered')}
                    </Link>
                    <PrimaryButton className="ms-4" disabled={processing}>
                        {isVendor ? t('auth.register.create_vendor') : t('auth.register.create_account')}
                    </PrimaryButton>
                </div>
            </form>
        </GuestLayout>
    );
}
