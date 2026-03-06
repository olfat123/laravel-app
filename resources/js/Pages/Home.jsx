import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import ProductItem from '@/Components/App/ProductItem';
import SectionHeader from '@/Components/App/SectionHeader';
import { useTrans, useLocale } from '@/i18n';

const DEPT_COLORS = [
    'from-violet-500 to-purple-600',
    'from-blue-500 to-cyan-600',
    'from-emerald-500 to-teal-600',
    'from-orange-500 to-amber-600',
    'from-rose-500 to-pink-600',
    'from-indigo-500 to-blue-600',
    'from-teal-500 to-green-600',
    'from-yellow-500 to-orange-600',
];

export default function Home({ departments, featuredProducts, mostSellingProducts, latestViewedProducts, latestPosts }) {
    const t = useTrans();
    const locale = useLocale();
    return (
        <AuthenticatedLayout>
            <Head title="Welcome" />

            {/* ── Hero ───────────────────────────────────────────── */}
            <section className="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
                {/* decorative blobs */}
                <div className="pointer-events-none absolute -top-32 -left-32 h-[600px] w-[600px] rounded-full bg-primary/20 blur-3xl" />
                <div className="pointer-events-none absolute -bottom-32 -right-32 h-[500px] w-[500px] rounded-full bg-secondary/20 blur-3xl" />

                <div className="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-28 md:py-40 text-center">
                    <span className="inline-block px-4 py-1.5 rounded-full bg-primary/20 text-primary text-xs font-semibold uppercase tracking-widest mb-6">
                        {t('home.hero.badge')}
                    </span>
                    <h1 className="text-4xl sm:text-5xl md:text-7xl font-extrabold text-white leading-tight">
                        {t('home.hero.headline')}<br />
                        <span className="text-transparent bg-clip-text bg-gradient-to-r from-primary to-secondary">
                            {t('home.hero.headline2')}
                        </span>
                    </h1>
                    <p className="mt-6 text-lg md:text-xl text-slate-400 max-w-2xl mx-auto">
                        {t('home.hero.subtext')}
                    </p>
                    <div className="mt-10 flex flex-wrap justify-center gap-4">
                        <Link href={route('shop')} className="btn btn-primary btn-lg px-10 shadow-lg shadow-primary/30">
                            {t('home.hero.cta_shop')}
                        </Link>
                        <a href="#departments" className="btn btn-outline btn-lg px-10 text-white border-white/30 hover:bg-white/10 hover:border-white/50">
                            {t('home.hero.cta_browse')}
                        </a>
                    </div>
                </div>
            </section>

            {/* ── Departments ────────────────────────────────────── */}
            {departments?.length > 0 && (
                <section id="departments" className="py-20 bg-base-100">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="text-center mb-12">
                            <h2 className="text-3xl md:text-4xl font-bold text-base-content">{t('home.departments.heading')}</h2>
                            <p className="mt-3 text-base-content/60">{t('home.departments.subtext')}</p>
                        </div>
                        <div className="grid grid-cols-2 sm:grid-cols-3 md:grid-cols-4 gap-4">
                            {departments.map((dept, i) => (
                                <Link
                                    key={dept.id}
                                    href={route('shop')}
                                    className="group relative overflow-hidden rounded-2xl aspect-square flex items-end p-5 shadow-sm hover:shadow-xl transition-all duration-300 hover:-translate-y-1"
                                >
                                    <div className={`absolute inset-0 bg-gradient-to-br ${DEPT_COLORS[i % DEPT_COLORS.length]} opacity-90 group-hover:opacity-100 transition-opacity`} />
                                    <div className="relative z-10">
                                        <p className="text-white font-bold text-lg leading-tight">{dept.name}</p>
                                        {dept.categories_count > 0 && (
                                            <p className="text-white/70 text-xs mt-1">{dept.categories_count} {t('home.departments.categories')}</p>
                                        )}
                                    </div>
                                    <div className="absolute top-4 right-4 w-10 h-10 rounded-full bg-white/20 flex items-center justify-center group-hover:scale-110 transition-transform">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 text-white" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                                        </svg>
                                    </div>
                                </Link>
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* ── Featured Products ───────────────────────────────── */}
            {featuredProducts?.data?.length > 0 && (
                <section className="py-20 bg-base-200">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <SectionHeader
                            heading={t('home.featured.heading')}
                            subtext={t('home.featured.subtext')}
                            viewAllHref={route('shop')}
                            viewAllLabel={t('home.featured.view_all')}
                        />
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            {featuredProducts.data.map((product) => (
                                <ProductItem product={product} key={product.id} />
                            ))}
                        </div>
                        <div className="mt-10 text-center sm:hidden">
                            <Link href={route('shop')} className="btn btn-outline btn-wide">{t('home.featured.view_all_mobile')}</Link>
                        </div>
                    </div>
                </section>
            )}

            {/* ── Best Sellers ────────────────────────────────────── */}
            {mostSellingProducts?.data?.length > 0 && (
                <section className="py-20 bg-base-100">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <SectionHeader
                            heading={t('home.best_sellers.heading')}
                            subtext={t('home.best_sellers.subtext')}
                            viewAllHref={route('shop')}
                            viewAllLabel={t('home.featured.view_all')}
                        />
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            {mostSellingProducts.data.map((product) => (
                                <ProductItem product={product} key={product.id} />
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* ── Recently Viewed ─────────────────────────────────── */}
            {latestViewedProducts?.data?.length > 0 && (
                <section className="py-20 bg-base-200">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <SectionHeader
                            heading={t('home.recently_viewed.heading')}
                            subtext={t('home.recently_viewed.subtext')}
                        />
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            {latestViewedProducts.data.map((product) => (
                                <ProductItem product={product} key={product.id} />
                            ))}
                        </div>
                    </div>
                </section>
            )}

            {/* ── Latest Blog Posts ───────────────────────────────── */}
            {latestPosts?.data?.length > 0 && (
                <section className="py-20 bg-base-100">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <SectionHeader
                            heading={t('home.blog.heading')}
                            subtext={t('home.blog.subtext')}
                            viewAllHref={route('blog.index')}
                            viewAllLabel={t('home.blog.view_all')}
                        />
                        <div className="grid grid-cols-1 md:grid-cols-3 gap-8">
                            {latestPosts.data.map(post => {
                                const title   = locale === 'ar' && post.title_ar   ? post.title_ar   : post.title;
                                const excerpt = locale === 'ar' && post.excerpt_ar ? post.excerpt_ar : post.excerpt;
                                return (
                                    <article key={post.id} className="card bg-base-200 shadow hover:shadow-lg transition-shadow overflow-hidden">
                                        {post.cover_thumb ? (
                                            <figure className="h-44 overflow-hidden">
                                                <img src={post.cover_thumb} alt={title} className="w-full h-full object-cover hover:scale-105 transition-transform duration-500" />
                                            </figure>
                                        ) : (
                                            <div className="h-44 bg-gradient-to-br from-primary/15 to-secondary/15" />
                                        )}
                                        <div className="card-body gap-2">
                                            {post.published_at && (
                                                <p className="text-xs text-base-content/40 uppercase tracking-wider">
                                                    {new Date(post.published_at).toLocaleDateString(
                                                        locale === 'ar' ? 'ar-EG' : 'en-US',
                                                        { year: 'numeric', month: 'short', day: 'numeric' }
                                                    )}
                                                </p>
                                            )}
                                            <h3 className="font-bold text-lg line-clamp-2 leading-snug">{title}</h3>
                                            {excerpt && <p className="text-base-content/60 text-sm line-clamp-2">{excerpt}</p>}
                                            <Link href={route('blog.show', post.slug)} className="mt-2 text-primary text-sm font-semibold hover:underline">
                                                {t('blog.read_more')} →
                                            </Link>
                                        </div>
                                    </article>
                                );
                            })}
                        </div>
                        <div className="mt-8 text-center sm:hidden">
                            <Link href={route('blog.index')} className="btn btn-outline btn-wide">{t('home.blog.view_all')}</Link>
                        </div>
                    </div>
                </section>
            )}

            {/* ── Vendor CTA ──────────────────────────────────────── */}
            <section className="py-20 bg-gradient-to-r from-primary to-secondary">
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 text-center">
                    <h2 className="text-3xl md:text-4xl font-extrabold text-white">{t('home.cta.heading')}</h2>
                    <p className="mt-4 text-white/80 text-lg max-w-xl mx-auto">
                        {t('home.cta.subtext')}
                    </p>
                    <div className="mt-8 flex flex-wrap justify-center gap-4">
                        <Link href={route('register')} className="btn btn-lg bg-white text-primary hover:bg-white/90 border-none shadow-lg px-10">
                            {t('home.cta.get_started')}
                        </Link>
                        <Link href={route('shop')} className="btn btn-lg btn-outline text-white border-white/50 hover:bg-white/10 hover:border-white px-10">
                            {t('home.cta.browse')}
                        </Link>
                    </div>
                </div>
            </section>
        </AuthenticatedLayout>
    );
}
