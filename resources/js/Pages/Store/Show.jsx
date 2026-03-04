import { useState, useEffect, useCallback, useRef } from 'react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link, router } from '@inertiajs/react';
import ProductItem from '@/Components/App/ProductItem';
import ProductFilterPanel from '@/Components/App/ProductFilterPanel';
import { AdjustmentsHorizontalIcon, MapPinIcon, XMarkIcon } from '@heroicons/react/24/outline';

function useDebounce(value, delay = 400) {
    const [debounced, setDebounced] = useState(value);
    useEffect(() => {
        const t = setTimeout(() => setDebounced(value), delay);
        return () => clearTimeout(t);
    }, [value, delay]);
    return debounced;
}

const SORT_OPTIONS = [
    { value: 'newest',     label: 'Newest' },
    { value: 'price_asc',  label: 'Price: Low → High' },
    { value: 'price_desc', label: 'Price: High → Low' },
    { value: 'name_asc',   label: 'Name: A → Z' },
    { value: 'name_desc',  label: 'Name: Z → A' },
];

export default function StoreShow({ vendor, products, departments = [], filters }) {
    const f = (filters && !Array.isArray(filters)) ? filters : {};

    const [search, setSearch]             = useState(f.search        ?? '');
    const [departmentId, setDepartmentId] = useState(f.department_id ?? '');
    const [categoryId, setCategoryId]     = useState(f.category_id   ?? '');
    const [minPrice, setMinPrice]         = useState(f.min_price      ?? '');
    const [maxPrice, setMaxPrice]         = useState(f.max_price      ?? '');
    const [sort, setSort]                 = useState(f.sort           ?? 'newest');
    const [sidebarOpen, setSidebarOpen]   = useState(false);

    const debouncedSearch   = useDebounce(search);
    const debouncedMinPrice = useDebounce(minPrice);
    const debouncedMaxPrice = useDebounce(maxPrice);

    const isFirstRender = useRef(true);

    const categories = departmentId
        ? (departments.find(d => String(d.id) === String(departmentId))?.categories ?? [])
        : departments.flatMap(d => d.categories);

    const buildParams = useCallback(() => ({
        search:        debouncedSearch   || undefined,
        department_id: departmentId      || undefined,
        category_id:   categoryId        || undefined,
        min_price:     debouncedMinPrice || undefined,
        max_price:     debouncedMaxPrice || undefined,
        sort:          sort !== 'newest' ? sort : undefined,
    }), [debouncedSearch, departmentId, categoryId, debouncedMinPrice, debouncedMaxPrice, sort]);

    useEffect(() => {
        if (isFirstRender.current) { isFirstRender.current = false; return; }
        router.get(route('store.show', vendor.store_slug), buildParams(), { preserveState: true, replace: true });
    }, [debouncedSearch, departmentId, categoryId, debouncedMinPrice, debouncedMaxPrice, sort]);

    const handleDepartmentChange = (val) => {
        setDepartmentId(val);
        setCategoryId('');
    };

    const activeFilterCount = [
        search, departmentId, categoryId, minPrice, maxPrice, sort !== 'newest' ? sort : '',
    ].filter(Boolean).length;

    const clearAll = () => {
        setSearch(''); setDepartmentId(''); setCategoryId('');
        setMinPrice(''); setMaxPrice(''); setSort('newest');
    };

    const filterPanelProps = {
        search,        onSearchChange:     setSearch,
        departmentId,  onDepartmentChange: handleDepartmentChange,
        categoryId,    onCategoryChange:   setCategoryId,
        minPrice,      onMinPriceChange:   setMinPrice,
        maxPrice,      onMaxPriceChange:   setMaxPrice,
        departments,
        categories,
        stores: [],
        activeFilterCount,
        onClearAll: clearAll,
    };

    return (
        <AuthenticatedLayout>
            <Head title={vendor.store_name} />

            {/* ── Banner ── */}
            <div className="relative w-full bg-base-200 overflow-hidden" style={{ minHeight: '200px' }}>
                {vendor.banner_url ? (
                    <img
                        src={vendor.banner_url}
                        alt={`${vendor.store_name} banner`}
                        className="w-full object-cover"
                        style={{ maxHeight: '400px' }}
                    />
                ) : (
                    <div className="w-full flex items-center justify-center bg-gradient-to-br from-primary/20 to-secondary/20"
                         style={{ height: '220px' }}>
                        <span className="text-5xl font-extrabold text-base-content/20 select-none uppercase tracking-widest">
                            {vendor.store_name}
                        </span>
                    </div>
                )}
                <div className="absolute bottom-0 left-0 right-0 bg-gradient-to-t from-black/70 to-transparent px-6 pb-5 pt-10">
                    <h1 className="text-3xl font-bold text-white drop-shadow">{vendor.store_name}</h1>
                    {vendor.store_address && (
                        <p className="flex items-center gap-1 text-white/80 text-sm mt-1">
                            <MapPinIcon className="size-4 flex-shrink-0" />
                            {vendor.store_address}
                        </p>
                    )}
                </div>
            </div>

            {/* ── Store description ── */}
            {vendor.store_description && (
                <div className="bg-base-100 border-b border-base-300">
                    <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
                        <p className="text-base-content/70 text-sm leading-relaxed">{vendor.store_description}</p>
                    </div>
                </div>
            )}

            {/* ── Toolbar ── */}
            <div className="bg-base-200 border-b border-base-300">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4 flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3">
                    <p className="text-base-content/60 text-sm">
                        {products.meta?.total
                            ? `${products.meta.total} product${products.meta.total !== 1 ? 's' : ''} found`
                            : 'Browse products'}
                    </p>
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
                            Filters
                            {activeFilterCount > 0 && (
                                <span className="badge badge-primary badge-xs ml-1">{activeFilterCount}</span>
                            )}
                        </button>
                    </div>
                </div>
            </div>

            {/* ── Mobile filter drawer ── */}
            {sidebarOpen && (
                <div className="fixed inset-0 z-50 flex lg:hidden">
                    <div className="absolute inset-0 bg-black/40" onClick={() => setSidebarOpen(false)} />
                    <div className="relative ml-auto w-72 h-full bg-base-100 shadow-xl overflow-y-auto p-6 flex flex-col gap-4">
                        <div className="flex items-center justify-between mb-2">
                            <h2 className="text-lg font-bold">Filters</h2>
                            <button onClick={() => setSidebarOpen(false)}>
                                <XMarkIcon className="size-5" />
                            </button>
                        </div>
                        <ProductFilterPanel {...filterPanelProps} />
                    </div>
                </div>
            )}

            {/* ── Main layout ── */}
            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10 flex gap-8">

                {/* Desktop sidebar */}
                <aside className="hidden lg:block w-64 flex-shrink-0">
                    <div className="card bg-base-100 shadow-sm border border-base-300 p-5 sticky top-6">
                        <div className="flex items-center justify-between mb-4">
                            <h2 className="font-bold text-base">Filters</h2>
                            {activeFilterCount > 0 && (
                                <span className="badge badge-primary badge-sm">{activeFilterCount} active</span>
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
                            <p className="text-xl font-medium">No products found</p>
                            {activeFilterCount > 0 && (
                                <button onClick={clearAll} className="btn btn-ghost btn-sm mt-3 text-primary">
                                    Clear filters
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
