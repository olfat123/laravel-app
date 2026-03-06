import { Link, useForm, usePage } from '@inertiajs/react';
import CurrencyFormatter from '../CurrencyFormatter';
import { productRoute } from '@/Helper';
import { useTrans, useLocale } from '@/i18n';

export default function ProductItem({ product }) {
    const { auth, wishlistedProductIds = [] } = usePage().props;
    const form = useForm({ product_id: product.id, quantity: 1 });
    const wishlistForm = useForm({});
    const t = useTrans();
    const locale = useLocale();

    const title = (locale === 'ar' && product.title_ar) ? product.title_ar : product.title;
    const isWishlisted = wishlistedProductIds.includes(product.id);

    const addToCart = () => form.post(route('cart.store', product.id), { preserveScroll: true, preserveState: true });

    const toggleWishlist = (e) => {
        e.preventDefault();
        if (!auth?.user) { window.location.href = route('login'); return; }
        wishlistForm.post(route('account.wishlist.toggle', product.id), { preserveScroll: true, preserveState: true });
    };

    return (
        <div className="group card bg-base-100 border border-base-300 hover:border-base-400 shadow-sm hover:shadow-lg transition-all duration-300 overflow-hidden">

            {/* Image */}
            <Link href={route('product.show', product.slug)} className="relative block overflow-hidden aspect-square bg-base-200">
                {product.image_url ? (
                    <img
                        src={product.image_url}
                        alt={title}
                        className="w-full h-full object-cover group-hover:scale-105 transition-transform duration-500"
                    />
                ) : (
                    <div className="w-full h-full flex items-center justify-center text-base-content/20">
                        <svg xmlns="http://www.w3.org/2000/svg" className="h-12 w-12" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1} d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                        </svg>
                    </div>
                )}

                {/* Badges */}
                {product.is_on_sale && (
                    <span className="absolute top-3 left-3 badge badge-error text-white text-xs font-bold shadow">
                        {t('product_item.sale_badge')}
                    </span>
                )}
                {product.quantity < 1 && (
                    <span className="absolute inset-0 bg-base-100/70 flex items-center justify-center text-sm font-semibold text-base-content/60">
                        {t('product.out_of_stock')}
                    </span>
                )}
            </Link>

            {/* Wishlist button — sits on top-right of image */}
            <button
                onClick={toggleWishlist}
                className={`absolute top-2 right-2 z-10 btn btn-circle btn-xs shadow-md transition-all ${
                    isWishlisted
                        ? 'btn-error text-white'
                        : 'bg-base-100/90 text-base-content/50 hover:text-error hover:bg-base-100'
                }`}
                title={isWishlisted ? t('product_item.remove_wishlist') : t('product_item.add_wishlist')}
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-3.5 w-3.5" fill={isWishlisted ? 'currentColor' : 'none'} viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>

            {/* Card body */}
            <div className="p-4 flex flex-col gap-2">

                {/* Title */}
                <Link href={route('product.show', product.slug)} className="font-semibold text-sm leading-snug line-clamp-2 hover:text-primary transition-colors">
                    {title}
                </Link>

                {/* Store / Department */}
                <p className="text-xs text-base-content/50 truncate">
                    {product.user.store_slug ? (
                        <Link href={route('store.show', product.user.store_slug)} className="hover:text-primary transition-colors">
                            {product.user.name}
                        </Link>
                    ) : product.user.name}
                    {' · '}{product.department.name}
                </p>

                {/* Star rating */}
                {product.avg_rating > 0 && (
                    <div className="flex items-center gap-1.5">
                        <span className="text-warning text-xs leading-none">
                            {Array.from({ length: 5 }, (_, i) => i < Math.round(product.avg_rating) ? '★' : '☆').join('')}
                        </span>
                        <span className="text-xs text-base-content/40">
                            {product.avg_rating.toFixed(1)}{product.review_count > 0 && ` (${product.review_count})`}
                        </span>
                    </div>
                )}

                {/* Price + CTA */}
                <div className="flex items-center justify-between mt-auto pt-2 border-t border-base-200">
                    <div>
                        {product.is_on_sale ? (
                            <div className="flex items-baseline gap-1.5">
                                <span className="text-error font-bold text-sm">
                                    <CurrencyFormatter amount={product.sale_price} />
                                </span>
                                <span className="text-xs line-through text-base-content/40">
                                    <CurrencyFormatter amount={product.price} />
                                </span>
                            </div>
                        ) : (
                            <span className="font-bold text-sm">
                                <CurrencyFormatter amount={product.price} />
                            </span>
                        )}
                    </div>

                    {product.has_variations ? (
                        <Link href={productRoute(product, 'show')} className="btn btn-primary btn-xs">
                            {t('product_item.select_options')}
                        </Link>
                    ) : (
                        <button
                            onClick={addToCart}
                            disabled={product.quantity < 1 || form.processing}
                            className="btn btn-primary btn-xs"
                        >
                            {t('product_item.add_to_cart')}
                        </button>
                    )}
                </div>
            </div>
        </div>
    );
}

