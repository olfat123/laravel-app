import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import { useTrans, useLocale } from '@/i18n';

function RelatedCard({ post }) {
    const locale = useLocale();
    const title  = locale === 'ar' && post.title_ar ? post.title_ar : post.title;

    return (
        <Link
            href={route('blog.show', post.slug)}
            className="group flex gap-4 p-4 rounded-xl hover:bg-base-200 transition-colors"
        >
            {post.cover_thumb ? (
                <img src={post.cover_thumb} alt={title} className="w-20 h-16 object-cover rounded-lg flex-shrink-0" />
            ) : (
                <div className="w-20 h-16 rounded-lg bg-gradient-to-br from-primary/20 to-secondary/20 flex-shrink-0" />
            )}
            <div>
                <p className="font-semibold text-sm line-clamp-2 group-hover:text-primary transition-colors">{title}</p>
                {post.published_at && (
                    <p className="text-xs text-base-content/40 mt-1">
                        {new Date(post.published_at).toLocaleDateString(locale === 'ar' ? 'ar-EG' : 'en-US', {
                            year: 'numeric', month: 'short', day: 'numeric',
                        })}
                    </p>
                )}
            </div>
        </Link>
    );
}

export default function BlogShow({ post, relatedPosts }) {
    const t      = useTrans();
    const locale = useLocale();

    const title   = locale === 'ar' && post.title_ar   ? post.title_ar   : post.title;
    const content = locale === 'ar' && post.content_ar ? post.content_ar : post.content;
    const excerpt = locale === 'ar' && post.excerpt_ar ? post.excerpt_ar : post.excerpt;
    const catName = post.category
        ? (locale === 'ar' && post.category.name_ar ? post.category.name_ar : post.category.name)
        : null;

    const related = Array.isArray(relatedPosts) ? relatedPosts : (relatedPosts?.data ?? []);

    return (
        <AuthenticatedLayout>
            <Head title={title} />

            {/* Article header */}
            <section className="relative overflow-hidden bg-gradient-to-br from-slate-900 via-slate-800 to-slate-900">
                <div className="pointer-events-none absolute inset-0 bg-primary/5" />
                <div className="relative max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-20 text-center">
                    {/* Back link */}
                    <Link
                        href={route('blog.index')}
                        className="inline-flex items-center gap-2 text-sm text-slate-400 hover:text-white mb-8 transition-colors"
                    >
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                        </svg>
                        {t('blog.back')}
                    </Link>

                    {/* Category + date */}
                    <div className="flex items-center justify-center gap-3 flex-wrap mb-4">
                        {catName && (
                            <Link
                                href={route('blog.index', { category: post.category.slug })}
                                className="badge badge-primary text-xs font-semibold px-3 py-2"
                            >
                                {catName}
                            </Link>
                        )}
                        {post.published_at && (
                            <p className="text-primary text-sm uppercase tracking-widest font-semibold">
                                {new Date(post.published_at).toLocaleDateString(locale === 'ar' ? 'ar-EG' : 'en-US', {
                                    year: 'numeric', month: 'long', day: 'numeric',
                                })}
                            </p>
                        )}
                    </div>

                    <h1 className="text-3xl sm:text-4xl md:text-5xl font-extrabold text-white leading-tight">
                        {title}
                    </h1>

                    {excerpt && (
                        <p className="mt-6 text-slate-400 text-lg max-w-2xl mx-auto leading-relaxed">
                            {excerpt}
                        </p>
                    )}

                    {post.author && (
                        <p className="mt-6 text-slate-500 text-sm">
                            {t('blog.by', { author: post.author })}
                        </p>
                    )}
                </div>
            </section>

            {/* Cover image */}
            {post.cover_url && (
                <div className="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 -mt-8 relative z-10">
                    <img
                        src={post.cover_url}
                        alt={title}
                        className="w-full max-h-[500px] object-cover rounded-2xl shadow-2xl"
                    />
                </div>
            )}

            {/* Content + sidebar */}
            <section className="py-16 bg-base-100">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div className="flex flex-col lg:flex-row gap-12">

                        {/* Main content */}
                        <article className="flex-1 min-w-0">
                            <div
                                className="prose prose-lg max-w-none prose-headings:text-base-content prose-a:text-primary prose-img:rounded-xl"
                                dir={locale === 'ar' ? 'rtl' : 'ltr'}
                                dangerouslySetInnerHTML={{ __html: content }}
                            />
                        </article>

                        {/* Sidebar */}
                        {related.length > 0 && (
                            <aside className="lg:w-72 flex-shrink-0">
                                <div className="sticky top-8">
                                    <h3 className="text-lg font-bold text-base-content mb-4 pb-3 border-b border-base-200">
                                        {t('blog.related_posts')}
                                    </h3>
                                    <div className="flex flex-col gap-1">
                                        {related.map(rp => (
                                            <RelatedCard key={rp.id} post={rp} />
                                        ))}
                                    </div>
                                </div>
                            </aside>
                        )}
                    </div>

                    {/* Back button */}
                    <div className="mt-16 pt-8 border-t border-base-200">
                        <Link
                            href={route('blog.index')}
                            className="btn btn-outline gap-2"
                        >
                            <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 19l-7-7 7-7" />
                            </svg>
                            {t('blog.back')}
                        </Link>
                    </div>
                </div>
            </section>
        </AuthenticatedLayout>
    );
}
