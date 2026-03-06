import React from 'react'
import { Link } from '@inertiajs/react'
import { usePage } from '@inertiajs/react'
import CurrencyFormatter from '@/Components/CurrencyFormatter';
import { productRoute } from '@/Helper';
import { useTrans, useLocale } from '@/i18n';

export default function Navbar() {
    const { auth, totalQuantity, totalPrice, cartItems, availableLocales = ['en', 'ar'] } = usePage().props;
    const user = auth.user;
    const t = useTrans();
    const locale = useLocale();
    
    // Flatten cartItems if it's grouped by vendor
    const flatCartItems = React.useMemo(() => {
        if (!cartItems) return [];
        if (Array.isArray(cartItems)) return cartItems;
        
        // If cartItems is an object grouped by vendor, flatten it
        return Object.values(cartItems).flatMap(vendor => 
            Array.isArray(vendor.items) ? vendor.items : []
        );
    }, [cartItems]);
  return (
    <div className="navbar bg-base-100 shadow-sm">
        <div className="flex-1 gap-2">
            <Link href={route('home')} className="btn btn-ghost text-xl font-bold">{t('nav.brand')}</Link>
            <Link href={route('shop')} className="btn btn-ghost btn-sm hidden sm:inline-flex">{t('nav.shop')}</Link>
            <Link href={route('blog.index')} className="btn btn-ghost btn-sm hidden sm:inline-flex">{t('nav.blog')}</Link>
        </div>
        <div className="flex flex-none gap-4">
            <div className="dropdown dropdown-end">
                <div tabIndex={0} role="button" className="btn btn-ghost btn-circle">
                    <div className="indicator">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"> <path strokeLinecap="round" strokeLinejoin="round" strokeWidth="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z" /> </svg>
                        <span className="badge badge-sm indicator-item">{totalQuantity}</span>
                    </div>
                </div>
                <div
                    tabIndex={0}
                    className="card card-compact dropdown-content bg-base-100 z-1 mt-3 w-[300px] shadow">
                    <div className="card-body">
                        <span className="text-lg font-bold">{t('nav.cart.items', { count: totalQuantity })}</span>
                        <span className="text-info">{t('nav.cart.subtotal')} <CurrencyFormatter amount={totalPrice} /></span>

                        <div className={'my-4 max-h-[300px] overflow-auto'}>
                            {flatCartItems.length === 0 && (
                                <div className={'py-2 text-gray-500 text-center'}>{t('nav.cart.empty')}</div>
                            )}
                            {flatCartItems.map((item) => (
                                <div key={item.id} className="flex gap-4 p-3">
                                    <Link href={productRoute(item)}
                                        className="w-16 h-16 flex items-center justify-center">
                                        <img
                                            src={item.image}
                                            alt={item.title}
                                            className="max-w-full max-h-full"
                                        />
                                    </Link>
                                    <div className="flex-1">
                                        <h3 className="mb-3 font-semibold">
                                            <Link href={productRoute(item)}>
                                                {item.title}
                                            </Link>
                                        </h3>
                                        <div className="flex justify-between text-sm">
                                            <div>{t('nav.cart.qty')} {item.quantity}</div>
                                            <div>
                                                <CurrencyFormatter amount={item.price} />
                                            </div>
                                        </div>
                                    </div>
                                </div>
                            ))}
                        </div>

                        <div className="card-actions">
                            <Link href={route('cart.index')} className="btn btn-primary btn-block">{t('nav.cart.view')}</Link>
                        </div>
                    </div>
                </div>
            </div>
            {user && (
                <div className="dropdown dropdown-end">
                    <div tabIndex={0} role="button" className="btn btn-ghost btn-circle avatar">
                        <div className="w-10 rounded-full">
                        <img
                            alt="Tailwind CSS Navbar component"
                            src="https://img.daisyui.com/images/stock/photo-1534528741775-53994a69daeb.webp" />
                        </div>
                    </div>
                    <ul
                        tabIndex={0}
                        className="menu menu-sm dropdown-content bg-base-100 rounded-box z-1 mt-3 w-52 p-2 shadow">
                        <li>
                            <Link href={route('profile.edit')} className="justify-between">
                                {t('nav.profile')}
                            </Link>
                        </li>
                        <li>
                            <Link href={route('account.index')} className="justify-between">
                                {t('nav.my_account')}
                                <span className="badge badge-sm badge-primary">{t('nav.orders_badge')}</span>
                            </Link>
                        </li>
                        <li>
                            <Link href={route('logout')} method="post" as="button">{t('nav.logout')}</Link>
                        </li>
                    </ul>
                </div>
            )}
            {!user && (<>
                <Link href={route('login')} className="btn">{t('nav.login')}</Link>
                <Link href={route('register')} className='btn btn-primary'>{t('nav.register')}</Link>
                </>
            )}
            {/* Language switcher */}
            {availableLocales.length > 1 && (
                <div className="flex items-center gap-1">
                    {availableLocales.map((l) =>
                        l === locale ? (
                            <span key={l} className="btn btn-sm btn-primary pointer-events-none">
                                {l.toUpperCase()}
                            </span>
                        ) : (
                            <Link
                                key={l}
                                href={route('language.switch', l)}
                                className="btn btn-ghost btn-sm"
                            >
                                {l.toUpperCase()}
                            </Link>
                        )
                    )}
                </div>
            )}
        </div>
    </div>
  )
}