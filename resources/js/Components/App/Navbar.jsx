import { useMemo } from 'react';
import { Link, usePage } from '@inertiajs/react';
import CurrencyFormatter from '@/Components/CurrencyFormatter';
import { productRoute } from '@/Helper';
import { useTrans, useLocale } from '@/i18n';

export default function Navbar() {
    const { auth, totalQuantity, totalPrice, cartItems, availableLocales = ['en', 'ar'] } = usePage().props;
    const user = auth.user;
    const t = useTrans();
    const locale = useLocale();

    const initials = useMemo(() =>
        user?.name
            ?.split(' ')
            .map(n => n[0])
            .join('')
            .slice(0, 2)
            .toUpperCase() ?? '?',
        [user?.name]
    );

    const flatCartItems = useMemo(() => {
        if (!cartItems) return [];
        if (Array.isArray(cartItems)) return cartItems;
        return Object.values(cartItems).flatMap(v => Array.isArray(v.items) ? v.items : []);
    }, [cartItems]);

    return (
        <nav className="navbar bg-base-100/95 backdrop-blur-md border-b border-base-300 sticky top-0 z-50 shadow-sm">
            <div className="max-w-7xl w-full mx-auto px-4 sm:px-6 lg:px-8 flex items-center gap-2">

                {/* Brand + primary nav */}
                <div className="flex-1 flex items-center gap-1">
                    <Link href={route('home')} className="btn btn-ghost text-xl font-extrabold tracking-tight text-primary px-2">
                        {t('nav.brand')}
                    </Link>
                    <div className="hidden sm:flex items-center gap-1 ms-2">
                        <Link href={route('shop')} className="btn btn-ghost btn-sm font-medium">{t('nav.shop')}</Link>
                        <Link href={route('blog.index')} className="btn btn-ghost btn-sm font-medium">{t('nav.blog')}</Link>
                    </div>
                </div>

                {/* Right actions */}
                <div className="flex items-center gap-2">

                    {/* Cart dropdown */}
                    <div className="dropdown dropdown-end">
                        <button tabIndex={0} className="btn btn-ghost btn-circle relative">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                <path strokeLinecap="round" strokeLinejoin="round" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" />
                            </svg>
                            {totalQuantity > 0 && (
                                <span className="badge badge-primary badge-xs absolute -top-1 -right-1">{totalQuantity}</span>
                            )}
                        </button>

                        <div tabIndex={0} className="card card-compact dropdown-content bg-base-100 border border-base-300 shadow-xl z-50 mt-3 w-80 rounded-2xl">
                            <div className="card-body gap-3">
                                <div className="flex items-center justify-between">
                                    <span className="font-bold text-base">{t('nav.cart.items', { count: totalQuantity })}</span>
                                    <span className="text-primary font-semibold text-sm">
                                        {t('nav.cart.subtotal')} <CurrencyFormatter amount={totalPrice} />
                                    </span>
                                </div>

                                <div className="max-h-64 overflow-y-auto flex flex-col gap-1 -mx-1 px-1">
                                    {flatCartItems.length === 0 ? (
                                        <div className="py-6 text-center text-base-content/40 text-sm">{t('nav.cart.empty')}</div>
                                    ) : flatCartItems.map(item => (
                                        <Link key={item.id} href={productRoute(item)} className="flex gap-3 p-2 rounded-xl hover:bg-base-200 transition-colors">
                                            <img src={item.image} alt={item.title} className="w-12 h-12 rounded-lg object-cover border border-base-300 flex-shrink-0" />
                                            <div className="flex-1 min-w-0">
                                                <p className="text-sm font-medium truncate">{item.title}</p>
                                                <div className="flex items-center justify-between mt-1">
                                                    <span className="text-xs text-base-content/50">{t('nav.cart.qty')} {item.quantity}</span>
                                                    <CurrencyFormatter amount={item.price} />
                                                </div>
                                            </div>
                                        </Link>
                                    ))}
                                </div>

                                <Link href={route('cart.index')} className="btn btn-primary btn-sm w-full mt-1">
                                    {t('nav.cart.view')}
                                </Link>
                            </div>
                        </div>
                    </div>

                    {/* User menu */}
                    {user ? (
                        <div className="dropdown dropdown-end">
                            <button tabIndex={0} className="btn btn-ghost btn-circle">
                                <div className="w-9 h-9 rounded-full bg-primary text-primary-content flex items-center justify-center text-sm font-bold select-none">
                                    {initials}
                                </div>
                            </button>
                            <ul tabIndex={0} className="menu menu-sm dropdown-content bg-base-100 border border-base-300 shadow-xl rounded-2xl z-50 mt-3 w-52 p-2 gap-0.5">
                                <li>
                                    <Link href={route('account.index')} className="font-medium">
                                        {t('nav.my_account')}
                                    </Link>
                                </li>
                                <li>
                                    <Link href={route('profile.edit')}>
                                        {t('nav.profile')}
                                    </Link>
                                </li>
                                <div className="divider my-1" />
                                <li>
                                    <Link href={route('logout')} method="post" as="button" className="text-error">
                                        {t('nav.logout')}
                                    </Link>
                                </li>
                            </ul>
                        </div>
                    ) : (
                        <div className="flex items-center gap-2">
                            <Link href={route('login')} className="btn btn-ghost btn-sm">{t('nav.login')}</Link>
                            <Link href={route('register')} className="btn btn-primary btn-sm">{t('nav.register')}</Link>
                        </div>
                    )}

                    {/* Language switcher */}
                    {availableLocales.length > 1 && (
                        <div className="flex items-center border border-base-300 rounded-lg overflow-hidden">
                            {availableLocales.map(l =>
                                l === locale ? (
                                    <span key={l} className="px-2.5 py-1 text-xs font-bold bg-primary text-primary-content">
                                        {l.toUpperCase()}
                                    </span>
                                ) : (
                                    <Link key={l} href={route('language.switch', l)} className="px-2.5 py-1 text-xs font-medium hover:bg-base-200 transition-colors">
                                        {l.toUpperCase()}
                                    </Link>
                                )
                            )}
                        </div>
                    )}
                </div>
            </div>
        </nav>
    );
}
