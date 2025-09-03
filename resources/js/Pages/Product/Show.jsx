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
import { Head } from '@inertiajs/react';

export default function Show({ product, variationOptions }) {
    console.log(product, variationOptions);
    const form = useForm({
        option_ids: [...variationOptions.map((option) => option.id)],
        quantity: 1,
        price: product.price | null
    });

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
                    quantity: variation.quantity === null ? 1 : variation.quantity,
                }
            }
        }
        return {
            price: product.price,
            quantity: product.quantity === null ? 1 : product.quantity,
        };
    }, [product, selectedOptions]);

    useEffect(() => {
        for ( let type of product.variationTypes) {
            const selectedOptionId = variationOptions[type.id ];
            chooseOption(
                type.id,
                type.options.find((op) => op.id === selectedOptionId) || type.options[0],
                false
            );
        }
    }, []);

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
                    <p>{type.name}</p>
                    {type.type === 'image' && 
                        <div className="flex gap-2 mb-4">
                            {type.options.map((option) => (
                                <div onClick={() => chooseOption(type.id, option)} key={option.id}>
                                    {option.images && <img src={option.images[0].thumb} 
                                    alt={option.name} className={'w-[50px] ' + (
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
                                    aria-label={option.name}
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
                <select value={form.data.quantity} 
                    onChange={onQuantityChange} 
                    className="select select-bordered w-full">
                    {Array.from({ 
                        length: Math.min(10, computedProduct.quantity) 
                    }).map((_, i) => (
                        <option key={i} value={i + 1}>Quantity: {i + 1}</option>
                    ))}
                </select>
                <button 
                    onClick={addToCart} 
                    disabled={computedProduct.quantity < 1}
                    className="btn btn-primary">Add to Cart</button>
                
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
            <Head title={product.title} />

            <div className="container mx-auto py-8">
                <div className="grid gap-8 grid-cols-1 lg:grid-cols-12">
                    <div className="col-span-7">
                        <Carousel images={images} />
                    </div>
                    <div className="col-span-5">
                        <h1 className="text-2xl mb-8">{product.title}</h1>
                        <div>
                            <div className="text-3xl font-semibold">
                                <CurrencyFormatter amount={computedProduct.price} />
                            </div>
                        </div>

                        {renderProductVariationTypes()}

                        {computedProduct.quantity != undefined &&
                        computedProduct.quantity < 10 &&
                            <div className="text-error my-4">
                                <span>Only {computedProduct.quantity} left in stock!</span>
                            </div>
                        }
                        
                        <div className="mb-4">
                            <label className="block mb-2 font-semibold">Quantity:</label>
                            <input
                                type="number"
                                min="1"
                                max={computedProduct.quantity}
                                value={form.data.quantity}
                                onChange={onQuantityChange}
                                className="w-20 p-2 border rounded"
                            />
                            {computedProduct.quantity < 1 && (
                                <p className="text-red-500 mt-2">This product is out of stock.</p>
                            )}
                        </div>
                        {renderAddToCartButton()}

                        <b className="text-xl">About the item</b>
                        <div className="wysiwyg-output" dangerouslySetInnerHTML={{ __html: product.description }}></div>
                    </div>
                </div>
            </div>
        </AuthenticatedLayout>
        

    );
}
