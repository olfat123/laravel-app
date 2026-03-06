import React from 'react';
import { useState } from 'react';
import { useForm, usePage } from '@inertiajs/react';
import { useMemo } from 'react';
import { useEffect } from 'react';
import { router } from '@inertiajs/react';
import isEqual from 'lodash/isEqual';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CurrencyFormatter from '@/Components/CurrencyFormatter';
import Carousel from '@/Components/Carousel';
import ProductItem from '@/Components/App/ProductItem';
import { Head, Link } from '@inertiajs/react';
import { useTrans, useLocale } from '@/i18n';

function StarRating({ value, onChange, readonly = false }) {
    const [hovered, setHovered] = useState(0);
    return (
        <div className="flex gap-1">
            {[1, 2, 3, 4, 5].map((star) => (
                <button
                    key={star}
                    type="button"
                    disabled={readonly}
                    onClick={() => !readonly && onChange && onChange(star)}
                    onMouseEnter={() => !readonly && setHovered(star)}
                    onMouseLeave={() => !readonly && setHovered(0)}
                    className={`text-2xl transition-colors ${
                        (hovered || value) >= star
                            ? 'text-yellow-400'
                            : 'text-base-content/20'
                    } ${!readonly ? 'cursor-pointer hover:scale-110' : 'cursor-default'}`}
                >
                    ★
                </button>
            ))}
        </div>
    );
}

export default function Show({ product, variationOptions, relatedProducts, reviews = [], canReview = false, userReview = null }) {
    const form = useForm({
        product_id: product.id,
        option_ids: Object.values(variationOptions || {}),
        quantity: 1,
        price: product.price | null
    });

    const reviewForm = useForm({ rating: 0, body: '' });

    const submitReview = (e) => {
        e.preventDefault();
        reviewForm.post(route('product.reviews.store', { product: product.slug }), {
            preserveScroll: true,
            onSuccess: () => reviewForm.reset(),
        });
    };

    const deleteReview = (reviewId) => {
        router.delete(route('product.reviews.destroy', { product: product.slug, review: reviewId }), {
            preserveScroll: true,
        });
    };
    const t = useTrans();
    const locale = useLocale();
    const productTitle = (locale === 'ar' && product.title_ar) ? product.title_ar : product.title;
    const productDesc = (locale === 'ar' && product.description_ar) ? product.description_ar : product.description;

    const {url} = usePage();

    const [selectedOptions, setSelectedOptions] = useState([]);
    const images = useMemo(() => {
        for (let typeId in selectedOptions) {
            const option = selectedOptions[typeId];
            if (option && option.images.length > 0) return option.images;
        }
        return product.images;
    }, [product,selectedOptions]);

    const arrayAreEquals = (arr1, arr2) => {
        if (arr1.length !== arr2.length) return false;
        return arr1.every((val, i) => val === arr2[i]);
    }
    const computedProduct = useMemo(() => {
        const selectedOptionIds = Object.values
            (selectedOptions)
            .map((op) => op.id)
            .sort();

        for (let variation of product.variations) {
            const optionIds = variation
            .variation_type_option_ids.sort();

            if(arrayAreEquals(selectedOptionIds, optionIds)) {
                return {
                    price: variation.price,
                    sale_price: variation.sale_price,
                    is_on_sale: variation.is_on_sale,
                    quantity: variation.quantity === null ? 1 : variation.quantity,
                }
            }
        }
        return {
            price: product.price,
            sale_price: product.sale_price,
            is_on_sale: product.is_on_sale,
            quantity: product.quantity === null ? 1 : product.quantity,
        };
    }, [product, selectedOptions]);

    useEffect(() => {
        if (!variationOptions) return;

        for (let type of product.variationTypes) {
            const selectedOptionId = variationOptions[type.id];
            const selectedId = selectedOptionId ? Number(selectedOptionId) : null;

            chooseOption(
                type.id,
                type.options.find((op) => op.id === selectedId) || type.options[0],
                false
            );
        }
    }, [variationOptions, product.variationTypes]);

    const getOptionIdsMap = (newOptions) => {
        return Object.fromEntries(
            Object.entries(newOptions)
                .map(([a, b]) => [a, b.id])
        )
    }

    const chooseOption = (typeId, option, updateRouter = true) => {
        setSelectedOptions((prevSelectedOptions) => {
            const newOptions = { 
                ...prevSelectedOptions,
                [typeId]: option 
            }
            
            if (updateRouter) {
                router.get(url, {
                    options: getOptionIdsMap(newOptions)
                }, { 
                    preserveState: true, 
                    preserveScroll: true 
                } );
            }

            return newOptions;
        });
    }

    const onQuantityChange = (e) => {
        form.setData('quantity', parseInt(e.target.value));
    }

    const addToCart = () => {
        form.post(route('cart.store', product.id), {
            preserveScroll: true,
            preserveState: true,
            onError: (errors) => {
                console.log(errors);
            },
            onSuccess: (response) => {
                console.log(response);  
            }
        });
    }

    const renderProductVariationTypes = () => {
        return (
            product.variationTypes.map((type) => (
                <div key={type.id}>
                    <p>{(locale === 'ar' && type.name_ar) ? type.name_ar : type.name}</p>
                    {type.type === 'image' && 
                        <div className="flex gap-2 mb-4">
                            {type.options.map((option) => (
                                <div onClick={() => chooseOption(type.id, option)} key={option.id}>
                                    {option.images?.length > 0 && <img src={option.images[0].thumb} 
                                    alt={(locale === 'ar' && option.name_ar) ? option.name_ar : option.name} className={'w-[50px] ' + (
                                        selectedOptions[type.id]?.id === option.id ? 
                                        'outline outline-4 outline-primary' : ''
                                    )}/>}
                                </div>
                            ))}
                        </div>
                    }
                    {type.type === 'radio' &&
                        <div className="flex join mb-4">
                            {type.options.map((option) => (
                                <input
                                    key={option.id}
                                    type="radio"
                                    name={`variation_type_${type.id}`}
                                    checked={selectedOptions[type.id]?.id === option.id}
                                    onChange={() => chooseOption(type.id, option)}
                                    className="join-item btn"
                                    aria-label={(locale === 'ar' && option.name_ar) ? option.name_ar : option.name}
                                />
                            ))}
                        </div>
                    }
                    
                </div>
            )
        ));
    }

    const renderAddToCartButton = () => {
        return (
            <div className="mb-8 flex gap-4">
                <input
                    type="number"
                    min="1"
                    max={computedProduct.quantity}
                    value={form.data.quantity}
                    onChange={onQuantityChange}
                    className="w-20 p-2 border rounded"
                />
                {computedProduct.quantity < 1 && (
                    <p className="text-red-500 mt-2">{t('product.out_of_stock')}</p>
                )}
                <button 
                    onClick={addToCart} 
                    disabled={computedProduct.quantity < 1}
                    className="btn btn-primary">{t('product.add_to_cart')}</button>
                
            </div>
        );
    }

    useEffect(() => {
        const idsMap = Object.fromEntries(
            Object.entries(selectedOptions)
                .map(([typeId, option]) => [typeId, option.id])
        );
        form.setData('option_ids', idsMap);
    }, [selectedOptions]);

    return (
        <AuthenticatedLayout>
            <Head title={productTitle} />

            <div className="container mx-auto py-8">
                <div className="grid gap-8 grid-cols-1 lg:grid-cols-12">
                    <div className="col-span-7">
                        <Carousel images={images} />
                    </div>
                    <div className="col-span-5">
                        <h1 className="text-2xl mb-8">{productTitle}</h1>
                        <div>
                            {computedProduct.is_on_sale ? (
                                <div className="flex items-center gap-3 mb-4">
                                    <div className="text-3xl font-semibold text-error">
                                        <CurrencyFormatter amount={computedProduct.sale_price} />
                                    </div>
                                    <div className="text-xl line-through text-gray-400">
                                        <CurrencyFormatter amount={computedProduct.price} />
                                    </div>
                                    <span className="badge badge-error text-white">{t('product_item.sale_badge')}</span>
                                </div>
                            ) : (
                                <div className="text-3xl font-semibold">
                                    <CurrencyFormatter amount={computedProduct.price} />
                                </div>
                            )}
                        </div>

                        {renderProductVariationTypes()}

                        {computedProduct.quantity != undefined &&
                        computedProduct.quantity < 10 &&
                            <div className="text-error my-4">
                                <span>{t('product.only_left', { count: computedProduct.quantity })}</span>
                            </div>
                        }
                        
                        <div className="mb-4">
                            
                        </div>
                        {renderAddToCartButton()}

                        <b className="text-xl">{t('product.about')}</b>
                        <div className="wysiwyg-output" dangerouslySetInnerHTML={{ __html: productDesc }}></div>
                    </div>
                </div>
            </div>

            {/* ── Reviews ────────────────────────────────────────── */}
            <section className="py-16 bg-base-100">
                <div className="container mx-auto px-4 max-w-4xl">
                    {/* Header + average */}
                    <div className="flex items-center gap-6 mb-10">
                        <div>
                            <h2 className="text-2xl font-bold text-base-content">{t('reviews.heading')}</h2>
                            {reviews.length > 0 && (
                                <p className="text-base-content/50 text-sm mt-1">
                                    {(reviews.reduce((s, r) => s + r.rating, 0) / reviews.length).toFixed(1)} / 5
                                    &nbsp;({reviews.length} {t('reviews.count_label')})
                                </p>
                            )}
                        </div>
                    </div>

                    {/* User's pending review notice */}
                    {userReview && (
                        <div className="alert alert-info mb-8 flex justify-between items-center">
                            <div>
                                <p className="font-semibold text-sm">{t('reviews.your_review')}</p>
                                <StarRating value={userReview.rating} readonly />
                                {userReview.body && <p className="text-sm mt-1 opacity-80">{userReview.body}</p>}
                                {!userReview.is_approved && (
                                    <p className="text-xs mt-1 opacity-60">{t('reviews.pending_approval')}</p>
                                )}
                            </div>
                            <button
                                onClick={() => deleteReview(userReview.id)}
                                className="btn btn-ghost btn-sm text-error"
                            >
                                {t('reviews.delete')}
                            </button>
                        </div>
                    )}

                    {/* Review submission form */}
                    {canReview && (
                        <div className="card bg-base-200 mb-10">
                            <div className="card-body">
                                <h3 className="card-title text-lg">{t('reviews.write_review')}</h3>
                                <form onSubmit={submitReview} className="space-y-4">
                                    <div>
                                        <label className="label"><span className="label-text font-medium">{t('reviews.your_rating')}</span></label>
                                        <StarRating
                                            value={reviewForm.data.rating}
                                            onChange={(v) => reviewForm.setData('rating', v)}
                                        />
                                        {reviewForm.errors.rating && (
                                            <p className="text-error text-sm mt-1">{reviewForm.errors.rating}</p>
                                        )}
                                    </div>
                                    <div>
                                        <label className="label"><span className="label-text font-medium">{t('reviews.comment_label')}</span></label>
                                        <textarea
                                            className="textarea textarea-bordered w-full"
                                            rows={4}
                                            placeholder={t('reviews.comment_placeholder')}
                                            value={reviewForm.data.body}
                                            onChange={(e) => reviewForm.setData('body', e.target.value)}
                                        />
                                        {reviewForm.errors.body && (
                                            <p className="text-error text-sm mt-1">{reviewForm.errors.body}</p>
                                        )}
                                    </div>
                                    {reviewForm.errors.review && (
                                        <p className="text-error text-sm">{reviewForm.errors.review}</p>
                                    )}
                                    <button
                                        type="submit"
                                        disabled={reviewForm.data.rating === 0 || reviewForm.processing}
                                        className="btn btn-primary"
                                    >
                                        {reviewForm.processing ? t('reviews.submitting') : t('reviews.submit_btn')}
                                    </button>
                                </form>
                            </div>
                        </div>
                    )}

                    {/* Approved reviews list */}
                    {reviews.length === 0 ? (
                        <p className="text-base-content/40 text-center py-12">{t('reviews.no_reviews')}</p>
                    ) : (
                        <div className="space-y-6">
                            {reviews.map((review) => (
                                <div key={review.id} className="card bg-base-200">
                                    <div className="card-body gap-2">
                                        <div className="flex items-center justify-between">
                                            <div>
                                                <p className="font-semibold text-base-content">{review.user.name}</p>
                                                <p className="text-xs text-base-content/40">
                                                    {new Date(review.created_at).toLocaleDateString(
                                                        locale === 'ar' ? 'ar-EG' : 'en-US',
                                                        { year: 'numeric', month: 'short', day: 'numeric' }
                                                    )}
                                                </p>
                                            </div>
                                            <StarRating value={review.rating} readonly />
                                        </div>
                                        {review.body && (
                                            <p className="text-base-content/70 text-sm leading-relaxed mt-1">{review.body}</p>
                                        )}
                                    </div>
                                </div>
                            ))}
                        </div>
                    )}
                </div>
            </section>

            {/* ── Related Products ───────────────────────────────── */}
            {relatedProducts?.data?.length > 0 && (
                <section className="py-16 bg-base-200">
                    <div className="container mx-auto px-4">
                        <h2 className="text-2xl font-bold text-base-content mb-8">
                            {t('product.related_products')}
                        </h2>
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                            {relatedProducts.data.map(p => (
                                <ProductItem key={p.id} product={p} />
                            ))}
                        </div>
                    </div>
                </section>
            )}
        </AuthenticatedLayout>
    );
}
