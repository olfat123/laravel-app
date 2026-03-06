import { Link, useForm, usePage } from '@inertiajs/react';
import React from 'react';
import CurrencyFormatter from '../CurrencyFormatter';
import { productRoute } from '@/Helper';
import { useTrans, useLocale } from '@/i18n';

export default function ProductItem({ product }) {
    const { auth, wishlistedProductIds = [] } = usePage().props;

    const form = useForm({ product_id: product.id, quantity: 1 });
    const wishlistForm = useForm({});
    const t = useTrans();
    const locale = useLocale();
    const productTitle = (locale === 'ar' && product.title_ar) ? product.title_ar : product.title;

    const hasVariations = product.has_variations;
    const isWishlisted = wishlistedProductIds.includes(product.id);

    const addToCart = () => {
        form.post(route('cart.store', product.id), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    const toggleWishlist = (e) => {
        e.preventDefault();
        if (!auth?.user) {
            window.location.href = route('login');
            return;
        }
        wishlistForm.post(route('account.wishlist.toggle', product.id), {
            preserveScroll: true,
            preserveState: true,
        });
    };

    return (
        <div className="card bg-base-100 shadow-xl relative">
            <button
                onClick={toggleWishlist}
                className={`absolute top-3 right-3 z-10 btn btn-circle btn-sm ${
                    isWishlisted ? 'btn-error text-white' : 'btn-ghost bg-base-100/80'
                }`}
                title={isWishlisted ? 'Remove from favourites' : 'Add to favourites'}
            >
                <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill={isWishlisted ? 'currentColor' : 'none'} viewBox="0 0 24 24" stroke="currentColor" strokeWidth={2}>
                    <path strokeLinecap="round" strokeLinejoin="round" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            </button>

            <Link href={route('product.show', product.slug)} className="relative block">
                <figure>
                    <img
                        src={product.image_url}
                        alt={productTitle}
                        className="aspect-square object-cover"
                    />
                </figure>
                {product.is_on_sale && (
                    <span className="absolute top-3 left-3 badge badge-error text-white font-semibold">{t('product_item.sale_badge')}</span>
                )}
            </Link>
            <div className="card-body">
                <h2 className="card-title">{productTitle}</h2>
                <p>
                    {t('product_item.by')}{' '}
                    {product.user.store_slug ? (
                        <Link href={route('store.show', product.user.store_slug)} className="hover:underline">
                            {product.user.name}
                        </Link>
                    ) : (
                        <span>{product.user.name}</span>
                    )}
                </p>
                <p>
                    {t('product_item.in')} <Link href="/" className="hover:underline">{product.department.name}</Link>
                </p>

                {/* Star rating */}
                {product.avg_rating > 0 && (
                    <div className="flex items-center gap-1 mt-1">
                        <span className="text-warning text-sm leading-none">
                            {Array.from({ length: 5 }, (_, i) => (
                                i < Math.round(product.avg_rating) ? '★' : '☆'
                            )).join('')}
                        </span>
                        <span className="text-xs text-base-content/50">
                            {product.avg_rating.toFixed(1)}
                            {product.review_count > 0 && ` (${product.review_count})`}
                        </span>
                    </div>
                )}

                <div className="card-actions justify-between items-center mt-3">
                    {hasVariations ? (
                        <Link href={productRoute(product, 'show')} className="btn btn-primary">
                            {t('product_item.select_options')}
                        </Link>
                    ) : (
                        <button
                            onClick={addToCart}
                            disabled={product.quantity < 1}
                            className="btn btn-primary"
                        >
                            {t('product_item.add_to_cart')}
                        </button>
                    )}
                    {product.is_on_sale ? (
                        <div className="flex items-center gap-2">
                            <span className="text-error font-semibold">
                                <CurrencyFormatter amount={product.sale_price} />
                            </span>
                            <span className="text-sm line-through text-gray-400">
                                <CurrencyFormatter amount={product.price} />
                            </span>
                        </div>
                    ) : (
                        <CurrencyFormatter amount={product.price} />
                    )}
                </div>
            </div>
        </div>
    );
}
