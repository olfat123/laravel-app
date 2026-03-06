import { XMarkIcon, MagnifyingGlassIcon } from '@heroicons/react/24/outline';
import { useTrans } from '@/i18n';

/** Reusable labelled wrapper */
function FilterField({ label, children }) {
    return (
        <div className="flex flex-col gap-1.5">
            <span className="text-xs font-semibold uppercase tracking-wider text-base-content/40">{label}</span>
            {children}
        </div>
    );
}

/** Reusable select input */
function FilterSelect({ value, onChange, children }) {
    return (
        <select
            className="select select-bordered select-sm w-full bg-base-100"
            value={value}
            onChange={e => onChange(e.target.value)}
        >
            {children}
        </select>
    );
}

/**
 * ProductFilterPanel — pure presentational.
 * All state lives in the parent (Shop / Store/Show).
 */
export default function ProductFilterPanel({
    search,        onSearchChange,
    departmentId,  onDepartmentChange,
    categoryId,    onCategoryChange,
    storeId,       onStoreChange,
    minPrice,      onMinPriceChange,
    maxPrice,      onMaxPriceChange,
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
            <FilterField label={t('filter.search')}>
                <div className="relative">
                    <MagnifyingGlassIcon className="absolute left-3 top-1/2 -translate-y-1/2 size-3.5 text-base-content/30 pointer-events-none" />
                    <input
                        type="text"
                        className="input input-bordered input-sm w-full pl-8 bg-base-100"
                        placeholder={t('filter.search_placeholder')}
                        value={search}
                        onChange={e => onSearchChange(e.target.value)}
                    />
                </div>
            </FilterField>

            <div className="divider my-0" />

            {/* Department */}
            <FilterField label={t('filter.department')}>
                <FilterSelect value={departmentId} onChange={onDepartmentChange}>
                    <option value="">{t('filter.all_departments')}</option>
                    {departments.map(d => <option key={d.id} value={d.id}>{d.name}</option>)}
                </FilterSelect>
            </FilterField>

            {/* Category */}
            <FilterField label={t('filter.category')}>
                <FilterSelect value={categoryId} onChange={onCategoryChange}>
                    <option value="">{t('filter.all_categories')}</option>
                    {categories.map(c => <option key={c.id} value={c.id}>{c.name}</option>)}
                </FilterSelect>
            </FilterField>

            {/* Store (only on Shop page) */}
            {stores.length > 0 && (
                <FilterField label={t('filter.store')}>
                    <FilterSelect value={storeId} onChange={onStoreChange}>
                        <option value="">{t('filter.all_stores')}</option>
                        {stores.map(s => <option key={s.id} value={s.id}>{s.name}</option>)}
                    </FilterSelect>
                </FilterField>
            )}

            <div className="divider my-0" />

            {/* Price Range */}
            <FilterField label={t('filter.price_range')}>
                <div className="flex gap-2">
                    <input
                        type="number"
                        className="input input-bordered input-sm w-full bg-base-100"
                        placeholder={t('filter.min')}
                        min={0}
                        value={minPrice}
                        onChange={e => onMinPriceChange(e.target.value)}
                    />
                    <input
                        type="number"
                        className="input input-bordered input-sm w-full bg-base-100"
                        placeholder={t('filter.max')}
                        min={0}
                        value={maxPrice}
                        onChange={e => onMaxPriceChange(e.target.value)}
                    />
                </div>
            </FilterField>

            {/* Clear all */}
            {activeFilterCount > 0 && (
                <>
                    <div className="divider my-0" />
                    <button onClick={onClearAll} className="btn btn-ghost btn-sm text-error gap-1.5 w-full">
                        <XMarkIcon className="size-3.5" />
                        {t('filter.clear_all')}
                        <span className="badge badge-error badge-xs">{activeFilterCount}</span>
                    </button>
                </>
            )}
        </div>
    );
}

