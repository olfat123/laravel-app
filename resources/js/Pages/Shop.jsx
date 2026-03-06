import { useState, useEffect, useCallback, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ProductItem from '@/Components/App/ProductItem';
import ProductFilterPanel from '@/Components/App/ProductFilterPanel';
import { AdjustmentsHorizontalIcon, XMarkIcon } from '@heroicons/react/24/outline';
import { useTrans } from '@/i18n';

function useDebounce(value, delay = 400) {
    const [debounced, setDebounced] = useState(value);
    useEffect(() => {
        const t = setTimeout(() => setDebounced(value), delay);
        return () => clearTimeout(t);
    }, [value, delay]);
    return debounced;
}

export default function Shop({ products, departments = [], stores = [], filters }) {
    // PHP serializes an empty array as `[]` in JSON, not `{}`, so guard against
    // filters being a JS array or null/undefined before reading its properties.
    const f = (filters && !Array.isArray(filters)) ? filters : {};
    const t = useTrans();

    const SORT_OPTIONS = [
        { value: 'newest',     label: t('shop.sort.newest') },
        { value: 'price_asc',  label: t('shop.sort.price_asc') },
        { value: 'price_desc', label: t('shop.sort.price_desc') },
        { value: 'name_asc',   label: t('shop.sort.name_asc') },
        { value: 'name_desc',  label: t('shop.sort.name_desc') },
        { value: 'top_rated',  label: t('shop.sort.top_rated') },
    ];
    const [search, setSearch]             = useState(f.search        ?? '');
    const [departmentId, setDepartmentId] = useState(f.department_id ?? '');
    const [categoryId, setCategoryId]     = useState(f.category_id   ?? '');
    const [storeId, setStoreId]           = useState(f.store_id       ?? '');
    const [minPrice, setMinPrice]         = useState(f.min_price      ?? '');
    const [maxPrice, setMaxPrice]         = useState(f.max_price      ?? '');
    const [sort, setSort]                 = useState(f.sort           ?? 'newest');
    const [sidebarOpen, setSidebarOpen]   = useState(false);

    const debouncedSearch   = useDebounce(search);
    const debouncedMinPrice = useDebounce(minPrice);
    const debouncedMaxPrice = useDebounce(maxPrice);

    const isFirstRender = useRef(true);

    // Derive categories from the selected department
    const categories = departmentId
        ? (departments.find(d => String(d.id) === String(departmentId))?.categories ?? [])
        : departments.flatMap(d => d.categories);

    const buildParams = useCallback(() => ({
        search:        debouncedSearch   || undefined,
        department_id: departmentId      || undefined,
        category_id:   categoryId        || undefined,
        store_id:      storeId           || undefined,
        min_price:     debouncedMinPrice || undefined,
        max_price:     debouncedMaxPrice || undefined,
        sort:          sort !== 'newest' ? sort : undefined,
    }), [debouncedSearch, departmentId, categoryId, storeId, debouncedMinPrice, debouncedMaxPrice, sort]);

    useEffect(() => {
        if (isFirstRender.current) { isFirstRender.current = false; return; }
        router.get(route('shop'), buildParams(), { preserveState: true, replace: true });
    }, [debouncedSearch, departmentId, categoryId, storeId, debouncedMinPrice, debouncedMaxPrice, sort]);

    const handleDepartmentChange = (val) => {
        setDepartmentId(val);
        setCategoryId(''); // reset category when department changes
    };

    const activeFilterCount = [
        search, departmentId, categoryId, storeId, minPrice, maxPrice, sort !== 'newest' ? sort : '',
    ].filter(Boolean).length;

    const clearAll = () => {
        setSearch(''); setDepartmentId(''); setCategoryId('');
        setStoreId(''); setMinPrice(''); setMaxPrice(''); setSort('newest');
    };

    /** Shared props forwarded to the filter panel */
    const filterPanelProps = {
        search,        onSearchChange:     setSearch,
        departmentId,  onDepartmentChange: handleDepartmentChange,
        categoryId,    onCategoryChange:   setCategoryId,
        storeId,       onStoreChange:      setStoreId,
        minPrice,      onMinPriceChange:   setMinPrice,
        maxPrice,      onMaxPriceChange:   setMaxPrice,
        departments,
        categories,
        stores,
        activeFilterCount,
        onClearAll: clearAll,
    };

    return (
        <AuthenticatedLayout>
            <Head title="Shop" />

            {/* Page header */}
            <div className="bg-base-200 border-b border-base-300">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <div>
                        <h1 className="text-3xl font-bold text-base-content">{t('shop.heading')}</h1>
                        <p className="mt-1 text-base-content/60">
                            {products.meta?.total
                                ? t('shop.products_found', { count: products.meta.total })
                                : t('shop.browse_all')}
                        </p>
                    </div>

                    {/* Sort + mobile filter toggle */}
                    <div className="flex items-center gap-3">
                        <select
                            className="select select-bordered select-sm"
                            value={sort}
                            onChange={e => setSort(e.target.value)}
                        >
                            {SORT_OPTIONS.map(o => (
                                <option key={o.value} value={o.value}>{o.label}</option>
                            ))}
                        </select>

                        <button
                            className="btn btn-outline btn-sm lg:hidden flex items-center gap-1"
                            onClick={() => setSidebarOpen(true)}
                        >
                            <AdjustmentsHorizontalIcon className="size-4" />
                            {t('shop.filters')}
                            {activeFilterCount > 0 && (
                                <span className="badge badge-primary badge-xs ml-1">{activeFilterCount}</span>
                            )}
                        </button>
                    </div>
                </div>
            </div>

            {/* Mobile filter drawer */}
            {sidebarOpen && (
                <div className="fixed inset-0 z-50 flex lg:hidden">
                    <div className="absolute inset-0 bg-black/40" onClick={() => setSidebarOpen(false)} />
                    <div className="relative ml-auto w-72 h-full bg-base-100 shadow-xl overflow-y-auto p-6 flex flex-col gap-4">
                        <div className="flex items-center justify-between mb-2">
                            <h2 className="text-lg font-bold">{t('shop.filters')}</h2>
                            <button onClick={() => setSidebarOpen(false)}>
                                <XMarkIcon className="size-5" />
                            </button>
                        </div>
                        <ProductFilterPanel {...filterPanelProps} />
                    </div>
                </div>
            )}

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex gap-8">

                {/* Desktop sidebar */}
                <aside className="hidden lg:block w-64 flex-shrink-0">
                    <div className="card bg-base-100 shadow-sm border border-base-300 p-5 sticky top-6">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="font-bold text-base">{t('shop.filters')}</h2>
                            {activeFilterCount > 0 && (
                                <span className="badge badge-primary badge-sm">{activeFilterCount} {t('shop.active')}</span>
                            )}
                        </div>
                        <ProductFilterPanel {...filterPanelProps} />
                    </div>
                </aside>

                {/* Products grid */}
                <div className="flex-1 min-w-0">
                    {products.data.length === 0 ? (
                        <div className="text-center py-20 text-base-content/50">
                            <svg xmlns="http://www.w3.org/2000/svg" className="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20 7H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1z" />
                            </svg>
                            <p className="text-xl font-medium">{t('shop.no_products')}</p>
                            {activeFilterCount > 0 && (
                                <button onClick={clearAll} className="btn btn-ghost btn-sm mt-3 text-primary">
                                    {t('shop.clear_filters')}
                                </button>
                            )}
                        </div>
                    ) : (
                        <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 xl:grid-cols-4">
                            {products.data.map(product => (
                                <ProductItem product={product} key={product.id} />
                            ))}
                        </div>
                    )}

                    {/* Pagination */}
                    {products.meta && products.meta.last_page > 1 && (
                        <div className="mt-10 flex justify-center gap-2 flex-wrap">
                            {products.meta.links.map((link, i) => (
                                <Link
                                    key={i}
                                    href={link.url ?? '#'}
                                    className={`btn btn-sm ${link.active ? 'btn-primary' : 'btn-ghost'} ${!link.url ? 'btn-disabled opacity-40' : ''}`}
                                    dangerouslySetInnerHTML={{ __html: link.label }}
                                />
                            ))}
                        </div>
                    )}
                </div>
            </div>
        </AuthenticatedLayout>
    );
}

