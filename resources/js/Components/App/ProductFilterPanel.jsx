import { XMarkIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { useTrans } from '@/i18n';

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
    const t = useTrans();
    return (
        <div className="flex flex-col gap-5">
            {/* Search */}
            <div>
                <label className="label label-text font-semibold mb-1">{t('filter.search')}</label>
                <div className="relative">
                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 size-4 text-base-content/40" />
                    <input
                        type="text"
                        className="input input-bordered input-sm w-full pl-9"
                        placeholder={t('filter.search_placeholder')}
                        value={search}
                        onChange={e => onSearchChange(e.target.value)}
                    />
                </div>
            </div>

            {/* Department */}
            <div>
                <label className="label label-text font-semibold mb-1">{t('filter.department')}</label>
                <select
                    className="select select-bordered select-sm w-full"
                    value={departmentId}
                    onChange={e => onDepartmentChange(e.target.value)}
                >
                    <option value="">{t('filter.all_departments')}</option>
                    {departments.map(d => (
                        <option key={d.id} value={d.id}>{d.name}</option>
                    ))}
                </select>
            </div>

            {/* Category */}
            <div>
                <label className="label label-text font-semibold mb-1">{t('filter.category')}</label>
                <select
                    className="select select-bordered select-sm w-full"
                    value={categoryId}
                    onChange={e => onCategoryChange(e.target.value)}
                >
                    <option value="">{t('filter.all_categories')}</option>
                    {categories.map(c => (
                        <option key={c.id} value={c.id}>{c.name}</option>
                    ))}
                </select>
            </div>

            {/* Store */}
            {stores.length > 0 && (
                <div>
                    <label className="label label-text font-semibold mb-1">{t('filter.store')}</label>
                    <select
                        className="select select-bordered select-sm w-full"
                        value={storeId}
                        onChange={e => onStoreChange(e.target.value)}
                    >
                        <option value="">{t('filter.all_stores')}</option>
                        {stores.map(s => (
                            <option key={s.id} value={s.id}>{s.name}</option>
                        ))}
                    </select>
                </div>
            )}

            {/* Price Range */}
            <div>
                <label className="label label-text font-semibold mb-1">{t('filter.price_range')}</label>
                <div className="flex gap-2">
                    <input
                        type="number"
                        className="input input-bordered input-sm w-full"
                        placeholder={t('filter.min')}
                        min={0}
                        value={minPrice}
                        onChange={e => onMinPriceChange(e.target.value)}
                    />
                    <input
                        type="number"
                        className="input input-bordered input-sm w-full"
                        placeholder={t('filter.max')}
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
                    {t('filter.clear_all')}
                </button>
            )}
        </div>
    );
}
