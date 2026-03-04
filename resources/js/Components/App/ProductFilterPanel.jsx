import { XMarkIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';

/**
 * ProductFilterPanel
 *
 * A pure presentational filter panel.
 * All state is managed by the parent (Shop.jsx) and passed in as props.
 */
export default function ProductFilterPanel({
    search,
    onSearchChange,
    departmentId,
    onDepartmentChange,
    categoryId,
    onCategoryChange,
    storeId,
    onStoreChange,
    minPrice,
    onMinPriceChange,
    maxPrice,
    onMaxPriceChange,
    departments,
    categories,
    stores = [],
    activeFilterCount,
    onClearAll,
}) {
    return (
        <div className="flex flex-col gap-5">
            {/* Search */}
            <div>
                <label className="label label-text font-semibold mb-1">Search</label>
                <div className="relative">
                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-base-content/40" />
                    <input
                        type="text"
                        className="input input-bordered input-sm w-full pl-9"
                        placeholder="Search products…"
                        value={search}
                        onChange={e => onSearchChange(e.target.value)}
                    />
                </div>
            </div>

            {/* Department */}
            <div>
                <label className="label label-text font-semibold mb-1">Department</label>
                <select
                    className="select select-bordered select-sm w-full"
                    value={departmentId}
                    onChange={e => onDepartmentChange(e.target.value)}
                >
                    <option value="">All Departments</option>
                    {departments.map(d => (
                        <option key={d.id} value={d.id}>{d.name}</option>
                    ))}
                </select>
            </div>

            {/* Category */}
            <div>
                <label className="label label-text font-semibold mb-1">Category</label>
                <select
                    className="select select-bordered select-sm w-full"
                    value={categoryId}
                    onChange={e => onCategoryChange(e.target.value)}
                >
                    <option value="">All Categories</option>
                    {categories.map(c => (
                        <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                </select>
            </div>

            {/* Store */}
            {stores.length > 0 && (
                <div>
                    <label className="label label-text font-semibold mb-1">Store</label>
                    <select
                        className="select select-bordered select-sm w-full"
                        value={storeId}
                        onChange={e => onStoreChange(e.target.value)}
                    >
                        <option value="">All Stores</option>
                        {stores.map(s => (
                            <option key={s.id} value={s.id}>{s.name}</option>
                        ))}
                    </select>
                </div>
            )}

            {/* Price Range */}
            <div>
                <label className="label label-text font-semibold mb-1">Price Range</label>
                <div className="flex gap-2">
                    <input
                        type="number"
                        className="input input-bordered input-sm w-full"
                        placeholder="Min"
                        min={0}
                        value={minPrice}
                        onChange={e => onMinPriceChange(e.target.value)}
                    />
                    <input
                        type="number"
                        className="input input-bordered input-sm w-full"
                        placeholder="Max"
                        min={0}
                        value={maxPrice}
                        onChange={e => onMaxPriceChange(e.target.value)}
                    />
                </div>
            </div>

            {/* Clear all */}
            {activeFilterCount > 0 && (
                <button
                    onClick={onClearAll}
                    className="btn btn-ghost btn-sm text-error w-full"
                >
                    <XMarkIcon className="size-4" />
                    Clear all filters
                </button>
            )}
        </div>
    );
}
