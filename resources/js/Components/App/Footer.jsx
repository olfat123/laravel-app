import { Link } from '@inertiajs/react';
import { useTrans } from '@/i18n';

export default function Footer() {
    const year = new Date().getFullYear();
    const t = useTrans();

    return (
        <footer className="bg-slate-900 text-slate-300">
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                {/* Top grid */}
                <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-10 py-16">
                    {/* Brand */}
                    <div className="lg:col-span-1">
                        <Link href={route('home')} className="text-white text-2xl font-extrabold tracking-tight">
                            {t('footer.brand')}
                        </Link>
                        <p className="mt-4 text-sm leading-relaxed text-slate-400">
                            {t('footer.tagline')}
                        </p>
                        {/* Social icons */}
                        <div className="mt-6 flex gap-3">
                            {[
                                { label: 'Facebook', href: '#', icon: 'M18 2h-3a5 5 0 00-5 5v3H7v4h3v8h4v-8h3l1-4h-4V7a1 1 0 011-1h3z' },
                                { label: 'Instagram', href: '#', icon: 'M16 11.37A4 4 0 1112.63 8 4 4 0 0116 11.37zm1.5-4.87h.01M6.5 19.5h11a3 3 0 003-3v-11a3 3 0 00-3-3h-11a3 3 0 00-3 3v11a3 3 0 003 3z' },
                                { label: 'Twitter', href: '#', icon: 'M23 3a10.9 10.9 0 01-3.14 1.53 4.48 4.48 0 00-7.86 3v1A10.66 10.66 0 013 4s-4 9 5 13a11.64 11.64 0 01-7 2c9 5 20 0 20-11.5a4.5 4.5 0 00-.08-.83A7.72 7.72 0 0023 3z' },
                            ].map(({ label, href, icon }) => (
                                <a
                                    key={label}
                                    href={href}
                                    aria-label={label}
                                    className="w-9 h-9 flex items-center justify-center rounded-full bg-slate-800 hover:bg-primary hover:text-white text-slate-400 transition-colors"
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                        <path strokeLinecap="round" strokeLinejoin="round" d={icon} />
                                    </svg>
                                </a>
                            ))}
                        </div>
                    </div>

                    {/* Quick links */}
                    <div>
                        <h3 className="text-white text-sm font-semibold uppercase tracking-widest mb-5">{t('footer.quick_links')}</h3>
                        <ul className="space-y-3 text-sm">
                            <li><Link href={route('home')} className="hover:text-white transition-colors">{t('footer.home')}</Link></li>
                            <li><Link href={route('shop')} className="hover:text-white transition-colors">{t('footer.shop')}</Link></li>
                            <li><Link href={route('cart.index')} className="hover:text-white transition-colors">{t('footer.cart')}</Link></li>
                        </ul>
                    </div>

                    {/* Account */}
                    <div>
                        <h3 className="text-white text-sm font-semibold uppercase tracking-widest mb-5">{t('footer.account')}</h3>
                        <ul className="space-y-3 text-sm">
                            <li><Link href={route('login')} className="hover:text-white transition-colors">{t('footer.login')}</Link></li>
                            <li><Link href={route('register')} className="hover:text-white transition-colors">{t('footer.register')}</Link></li>
                            <li><Link href={route('profile.edit')} className="hover:text-white transition-colors">{t('footer.my_profile')}</Link></li>
                        </ul>
                    </div>

                    {/* Contact / info */}
                    <div>
                        <h3 className="text-white text-sm font-semibold uppercase tracking-widest mb-5">{t('footer.contact')}</h3>
                        <ul className="space-y-3 text-sm">
                            <li className="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mt-0.5 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z" />
                                </svg>
                                <span>support@store.com</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mt-0.5 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z" />
                                </svg>
                                <span>+20 100 000 0000</span>
                            </li>
                            <li className="flex items-start gap-2">
                                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4 mt-0.5 shrink-0 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                                    <path strokeLinecap="round" strokeLinejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                                </svg>
                                <span>Cairo, Egypt</span>
                            </li>
                        </ul>
                    </div>
                </div>

                {/* Bottom bar */}
                <div className="border-t border-slate-800 py-6 flex flex-col sm:flex-row items-center justify-between gap-3 text-sm text-slate-500">
                    <p>{t('footer.copyright', { year })}</p>
                    <div className="flex gap-5">
                        <a href="#" className="hover:text-slate-300 transition-colors">{t('footer.privacy')}</a>
                        <a href="#" className="hover:text-slate-300 transition-colors">{t('footer.terms')}</a>
                    </div>
                </div>
            </div>
        </footer>
    );
}
