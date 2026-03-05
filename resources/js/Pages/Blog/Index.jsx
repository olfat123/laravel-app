import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import { useTrans, useLocale } from '@/i18n';

function PostCard({ post }) {
    const t = useTrans();
    const locale = useLocale();

    const title      = locale === 'ar' && post.title_ar   ? post.title_ar   : post.title;
    const excerpt    = locale === 'ar' && post.excerpt_ar ? post.excerpt_ar : post.excerpt;
    const catName    = post.category
        ? (locale === 'ar' && post.category.name_ar ? post.category.name_ar : post.category.name)
        : null;

    return (
        <article className="card bg-base-100 shadow-md hover:shadow-xl transition-shadow duration-300 flex flex-col overflow-hidden">
            {post.cover_thumb ? (
                <figure className="relative overflow-hidden h-48">
                    <img
                        src={post.cover_thumb}
                        alt={title}
                        className="w-full h-full object-cover transition-transform duration-500 hover:scale-105"
                    />
                </figure>
            ) : (
                <div className="h-48 bg-gradient-to-br from-primary/20 to-secondary/20 flex items-center justify-center">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12 text-base-content/20" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                    </svg>
                </div>
            )}

            <div className="card-body flex-1 flex flex-col gap-3">
                <div className="flex items-center gap-2 flex-wrap">
                    {catName && (
                        <button
                            onClick={() => router.get(route('blog.index'), { category: post.category.slug })}
                            className="badge badge-primary badge-outline text-xs cursor-pointer hover:badge-primary"
                        >
                            {catName}
                        </button>
                    )}
                    {post.published_at && (
                        <p className="text-xs text-base-content/50 uppercase tracking-wider">
                            {new Date(post.published_at).toLocaleDateString(locale === 'ar' ? 'ar-EG' : 'en-US', {
                                year: 'numeric', month: 'long', day: 'numeric',
                            })}
                        </p>
                    )}
                </div>

                <h2 className="card-title text-lg font-bold leading-snug line-clamp-2">{title}</h2>

                {excerpt && (
                    <p className="text-base-content/60 text-sm leading-relaxed line-clamp-3">{excerpt}</p>
                )}

                {post.author && (
                    <p className="text-xs text-base-content/40 mt-auto">{t('blog.by', { author: post.author })}</p>
                )}

                <div className="card-actions mt-2">
                    <Link href={route('blog.show', post.slug)} className="btn btn-primary btn-sm gap-1">
                        {t('blog.read_more')}
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                        </svg>
                    </Link>
                </div>
            </div>
        </article>
    );
}

export default function BlogIndex({ posts, categories, activeCategory }) {
    const t      = useTrans();
    const locale = useLocale();

    const items = posts?.data ?? [];
    const meta  = posts?.meta ?? {};

    function filterCategory(slug) {
        router.get(route('blog.index'), slug ? { category: slug } : {}, { preserveScroll: false });
    }

    return (
        <AuthenticatedLayout>
            <Head title={t('blog.page_title')} />

            {/* Hero banner */}
            <section className="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900 py-24 text-center">
                <div className="pointer-events-none absolute -top-24 -left-24 h-[400px] w-[400px] rounded-full bg-primary/20 blur-3xl" />
                <div className="pointer-events-none absolute -bottom-24 -right-24 h-[350px] w-[350px] rounded-full bg-secondary/20 blur-3xl" />
                <div className="relative max-w-3xl mx-auto px-4">
                    <span className="inline-block px-4 py-1 rounded-full bg-primary/20 text-primary text-xs font-semibold uppercase tracking-widest mb-4">
                        {t('blog.badge')}
                    </span>
                    <h1 className="text-4xl md:text-6xl font-extrabold text-white">{t('blog.heading')}</h1>
                    <p className="mt-4 text-slate-400 text-lg max-w-xl mx-auto">{t('blog.subtitle')}</p>
                </div>
            </section>

            {/* Category filter tabs */}
            {categories?.length > 0 && (
                <div className="bg-base-200 border-b border-base-300 sticky top-0 z-10">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                        <div className="flex gap-1 overflow-x-auto py-3 scrollbar-none">
                            <button
                                onClick={() => filterCategory(null)}
                                className={`btn btn-sm flex-shrink-0 ${!activeCategory ? 'btn-primary' : 'btn-ghost'}`}
                            >
                                {t('blog.all_categories')}
                            </button>
                            {categories.map(cat => {
                                const label = locale === 'ar' && cat.name_ar ? cat.name_ar : cat.name;
                                return (
                                    <button
                                        key={cat.id}
                                        onClick={() => filterCategory(cat.slug)}
                                        className={`btn btn-sm flex-shrink-0 ${activeCategory === cat.slug ? 'btn-primary' : 'btn-ghost'}`}
                                    >
                                        {label}
                                    </button>
                                );
                            })}
                        </div>
                    </div>
                </div>
            )}

            {/* Posts grid */}
            <section className="py-16 bg-base-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    {items.length === 0 ? (
                        <div className="text-center py-24 text-base-content/40">
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-16 w-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <p className="text-xl">{t('blog.no_posts')}</p>
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-8">
                            {items.map(post => <PostCard key={post.id} post={post} />)}
                        </div>
                    )}

                    {/* Pagination */}
                    {meta?.last_page > 1 && (
                        <div className="mt-12 flex justify-center gap-2">
                            {meta.links?.map((link, i) => (
                                link.url ? (
                                    <Link
                                        key={i}
                                        href={link.url}
                                        className={`btn btn-sm ${link.active ? 'btn-primary' : 'btn-ghost'}`}
                                        dangerouslySetInnerHTML={{ __html: link.label }}
                                    />
                                ) : (
                                    <span key={i} className="btn btn-sm btn-disabled" dangerouslySetInnerHTML={{ __html: link.label }} />
                                )
                            ))}
                        </div>
                    )}
                </div>
            </section>
        </AuthenticatedLayout>
    );
}

