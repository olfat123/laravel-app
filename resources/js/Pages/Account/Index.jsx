import { Head, Link, useForm, usePage, router } from '@inertiajs/react';
import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import CurrencyFormatter from '@/Components/CurrencyFormatter';
import ProductItem from '@/Components/App/ProductItem';
import { useState } from 'react';
import { useTrans, useLocale } from '@/i18n';

const STATUS_COLORS = {
    pending:    'badge-warning',
    processing: 'badge-info',
    paid:       'badge-info',
    shipped:    'badge-primary',
    delivered:  'badge-success',
    completed:  'badge-success',
    cancelled:  'badge-error',
    refunded:   'badge-ghost',
    failed:     'badge-error',
};

const CANCELLABLE_STATUSES = ['pending', 'processing'];

// ─── Orders Tab ──────────────────────────────────────────────────────────────
function OrdersTab({ orders }) {
    const [expanded, setExpanded] = useState(null);
    const t = useTrans();
    const locale = useLocale();
    const itemTitle = (item) => (locale === 'ar' && item.product?.title_ar) ? item.product.title_ar : item.product?.title;

    if (!orders.length) {
        return (
            <div className="text-center py-16">
                <div className="text-6xl mb-4">📦</div>
                <h3 className="text-xl font-semibold mb-2">{t('account.orders.empty_title')}</h3>
                <p className="text-base-content/60 mb-6">{t('account.orders.empty_sub')}</p>
                <Link href={route('shop')} className="btn btn-primary">{t('account.browse_shop')}</Link>
            </div>
        );
    }

    return (
        <div className="space-y-4">
            {orders.map((order) => (
                <div key={order.id} className="card bg-base-100 border border-base-300 shadow-sm">
                    {/* Order header */}
                    <div
                        className="card-body cursor-pointer"
                        onClick={() => setExpanded(expanded === order.id ? null : order.id)}
                    >
                        <div className="flex flex-wrap items-center justify-between gap-3">
                            <div className="flex items-center gap-3">
                                <span className="font-bold text-base-content/50 text-sm">{t('account.orders.order_label')}</span>
                                <span className="font-bold">#{order.id}</span>
                                <span className={`badge badge-sm ${STATUS_COLORS[order.status] ?? 'badge-ghost'}`}>
                                    {order.status}
                                </span>
                            </div>
                            <div className="flex items-center gap-4">
                                <span className="text-sm text-base-content/60">{order.created_at}</span>
                                <CurrencyFormatter amount={order.total_price} currency="EGP" locale="en-EG" />
                                <svg
                                    xmlns="http://www.w3.org/2000/svg"
                                    className={`h-5 w-5 transition-transform ${expanded === order.id ? 'rotate-180' : ''}`}
                                    fill="none" viewBox="0 0 24 24" stroke="currentColor"
                                >
                                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 9l-7 7-7-7" />
                                </svg>
                            </div>
                        </div>

                        {/* Item thumbnails preview */}
                        <div className="flex gap-2 mt-2 flex-wrap">
                            {order.items.slice(0, 4).map((item) => (
                                <div key={item.id} className="relative">
                                    {item.product?.image_url ? (
                                        <img
                                            src={item.product.image_url}
                                            alt={itemTitle(item)}
                                            className="w-12 h-12 rounded-lg object-cover border border-base-300"
                                        />
                                    ) : (
                                        <div className="w-12 h-12 rounded-lg bg-base-200 flex items-center justify-center text-base-content/40 text-xs">img</div>
                                    )}
                                    {item.quantity > 1 && (
                                        <span className="absolute -top-1 -right-1 badge badge-xs badge-primary">×{item.quantity}</span>
                                    )}
                                </div>
                            ))}
                            {order.items.length > 4 && (
                                <div className="w-12 h-12 rounded-lg bg-base-200 flex items-center justify-center text-sm font-semibold">
                                    +{order.items.length - 4}
                                </div>
                            )}
                        </div>
                    </div>

                    {/* Expandable details */}
                    {expanded === order.id && (
                        <div className="border-t border-base-300 px-6 py-4 space-y-4">
                            {/* Items list */}
                            <div>
                                <h4 className="font-semibold mb-3 text-sm uppercase tracking-wide text-base-content/60">{t('account.orders.items_heading')}</h4>
                                <div className="space-y-3">
                                    {order.items.map((item) => (
                                        <div key={item.id} className="flex items-center gap-3">
                                            {item.product?.image_url ? (
                                                <img src={item.product.image_url} alt={itemTitle(item)} className="w-14 h-14 rounded-lg object-cover" />
                                            ) : (
                                                <div className="w-14 h-14 rounded-lg bg-base-200" />
                                            )}
                                            <div className="flex-1 min-w-0">
                                                {item.product ? (
                                                    <Link href={route('product.show', item.product.slug)} className="font-medium hover:underline truncate block">
                                                        {itemTitle(item)}
                                                    </Link>
                                                ) : (
                                                    <span className="font-medium text-base-content/50">{t('account.orders.product_removed')}</span>
                                                )}
                                                <span className="text-sm text-base-content/60">{t('account.orders.qty')} {item.quantity}</span>
                                            </div>
                                            <CurrencyFormatter amount={item.price * item.quantity} currency="EGP" locale="en-EG" />
                                        </div>
                                    ))}
                                </div>
                            </div>

                            {/* Shipping info */}
                            {order.shipping_address && (
                                <div>
                                    <h4 className="font-semibold mb-1 text-sm uppercase tracking-wide text-base-content/60">{t('account.orders.shipped_to')}</h4>
                                    <p className="text-sm">
                                        {order.shipping_name} — {order.shipping_address}, {order.shipping_city}, {order.shipping_country}
                                    </p>
                                </div>
                            )}

                            {/* Payment */}
                            <div className="flex items-center justify-between pt-2 border-t border-base-300">
                                <span className="text-sm text-base-content/60 capitalize">
                                    {t('account.orders.payment')}: {order.payment_method ?? 'N/A'}
                                </span>
                                <div className="flex items-center gap-2 font-semibold">
                                    <span>{t('account.orders.total')}:</span>
                                    <CurrencyFormatter amount={order.total_price} currency="EGP" locale="en-EG" />
                                </div>
                            </div>

                            {/* Actions */}
                            <div className="flex flex-wrap gap-2 pt-2 border-t border-base-300">
                                <button
                                    className="btn btn-sm btn-outline btn-primary gap-2"
                                    onClick={() => router.post(route('account.orders.reorder', order.id))}
                                >
                                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15" />
                                    </svg>
                                    Reorder
                                </button>

                                {CANCELLABLE_STATUSES.includes(order.status) && (
                                    <button
                                        className="btn btn-sm btn-outline btn-error gap-2"
                                        onClick={() => {
                                            if (confirm(t('account.orders.cancel_confirm', { id: order.id }))) {
                                                router.post(route('account.orders.cancel', order.id));
                                            }
                                        }}
                                    >
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M6 18L18 6M6 6l12 12" />
                                        </svg>
                                        {t('account.orders.cancel_btn')}
                                    </button>
                                )}
                            </div>
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}

// ─── Favourites Tab ───────────────────────────────────────────────────────────
function FavouritesTab({ wishlist }) {
    const t = useTrans();
    if (!wishlist.length) {
        return (
            <div className="text-center py-16">
                <div className="text-6xl mb-4">🤍</div>
                <h3 className="text-xl font-semibold mb-2">{t('account.favourites.empty_title')}</h3>
                <p className="text-base-content/60 mb-6">{t('account.favourites.empty_sub')}</p>
                <Link href={route('shop')} className="btn btn-primary">{t('account.browse_shop')}</Link>
            </div>
        );
    }

    return (
        <div className="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
            {wishlist.map((product) => (
                <ProductItem key={product.id} product={product} />
            ))}
        </div>
    );
}

// ─── Addresses Tab ────────────────────────────────────────────────────────────
function AddressesTab({ addresses }) {
    const [showForm, setShowForm] = useState(false);
    const [editingId, setEditingId] = useState(null);
    const t = useTrans();

    const emptyForm = { name: '', phone: '', address: '', city: '', state: '', country: 'Egypt', zip: '', is_default: false };

    const createForm = useForm({ ...emptyForm });
    const editForm = useForm({ ...emptyForm });
    const deleteForm = useForm({});
    const defaultForm = useForm({});

    const startEdit = (addr) => {
        setEditingId(addr.id);
        editForm.setData({
            name: addr.name,
            phone: addr.phone ?? '',
            address: addr.address,
            city: addr.city,
            state: addr.state ?? '',
            country: addr.country,
            zip: addr.zip ?? '',
            is_default: addr.is_default,
        });
    };

    const cancelEdit = () => {
        setEditingId(null);
        editForm.reset();
    };

    const submitCreate = (e) => {
        e.preventDefault();
        createForm.post(route('account.addresses.store'), {
            preserveScroll: true,
            onSuccess: () => { createForm.reset(); setShowForm(false); },
        });
    };

    const submitEdit = (e, id) => {
        e.preventDefault();
        editForm.put(route('account.addresses.update', id), {
            preserveScroll: true,
            onSuccess: () => cancelEdit(),
        });
    };

    const handleDelete = (id) => {
        if (!confirm('Delete this address?')) return;
        deleteForm.delete(route('account.addresses.delete', id), { preserveScroll: true });
    };

    const setDefault = (id) => {
        defaultForm.post(route('account.addresses.default', id), { preserveScroll: true });
    };

    const AddressForm = ({ form, onSubmit, onCancel, submitLabel }) => (
        <form onSubmit={onSubmit} className="grid grid-cols-1 sm:grid-cols-2 gap-4 p-4 bg-base-200 rounded-xl">
            <div className="form-control">
                <label className="label"><span className="label-text">{t('checkout.full_name')} *</span></label>
                <input className="input input-bordered" value={form.data.name} onChange={e => form.setData('name', e.target.value)} required />
                {form.errors.name && <span className="text-error text-xs mt-1">{form.errors.name}</span>}
            </div>
            <div className="form-control">
                <label className="label"><span className="label-text">{t('checkout.phone')}</span></label>
                <input className="input input-bordered" value={form.data.phone} onChange={e => form.setData('phone', e.target.value)} />
            </div>
            <div className="form-control sm:col-span-2">
                <label className="label"><span className="label-text">{t('checkout.street_address')} *</span></label>
                <input className="input input-bordered" value={form.data.address} onChange={e => form.setData('address', e.target.value)} required />
                {form.errors.address && <span className="text-error text-xs mt-1">{form.errors.address}</span>}
            </div>
            <div className="form-control">
                <label className="label"><span className="label-text">{t('checkout.city')} *</span></label>
                <input className="input input-bordered" value={form.data.city} onChange={e => form.setData('city', e.target.value)} required />
                {form.errors.city && <span className="text-error text-xs mt-1">{form.errors.city}</span>}
            </div>
            <div className="form-control">
                <label className="label"><span className="label-text">{t('checkout.state')}</span></label>
                <input className="input input-bordered" value={form.data.state} onChange={e => form.setData('state', e.target.value)} />
            </div>
            <div className="form-control">
                <label className="label"><span className="label-text">{t('checkout.country')} *</span></label>
                <input className="input input-bordered" value={form.data.country} onChange={e => form.setData('country', e.target.value)} required />
            </div>
            <div className="form-control">
                <label className="label"><span className="label-text">{t('checkout.zip')}</span></label>
                <input className="input input-bordered" value={form.data.zip} onChange={e => form.setData('zip', e.target.value)} />
            </div>
            <div className="form-control sm:col-span-2 flex-row items-center gap-2">
                <input
                    type="checkbox"
                    className="checkbox checkbox-primary"
                    checked={form.data.is_default}
                    onChange={e => form.setData('is_default', e.target.checked)}
                    id={`default-${submitLabel}`}
                />
                <label htmlFor={`default-${submitLabel}`} className="label-text cursor-pointer">{t('account.addresses.set_default_label')}</label>
            </div>
            <div className="sm:col-span-2 flex gap-2">
                <button type="submit" className="btn btn-primary" disabled={form.processing}>{submitLabel}</button>
                <button type="button" className="btn btn-ghost" onClick={onCancel}>{t('account.addresses.cancel')}</button>
            </div>
        </form>
    );

    return (
        <div className="space-y-4">
            {/* Add new address button */}
            {!showForm && (
                <button onClick={() => setShowForm(true)} className="btn btn-outline btn-primary gap-2">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M12 4v16m8-8H4" />
                    </svg>
                    {t('account.addresses.add_new')}
                </button>
            )}

            {showForm && (
                <div className="card bg-base-100 border border-base-300 shadow-sm">
                    <div className="card-body">
                        <h3 className="card-title text-base">{t('account.addresses.new_address')}</h3>
                        <AddressForm
                            form={createForm}
                            onSubmit={submitCreate}
                            onCancel={() => { setShowForm(false); createForm.reset(); }}
                            submitLabel={t('account.addresses.save')}
                        />
                    </div>
                </div>
            )}

            {/* Address list */}
            {!addresses.length && !showForm && (
                <div className="text-center py-16">
                    <div className="text-6xl mb-4">📍</div>
                    <h3 className="text-xl font-semibold mb-2">{t('account.addresses.empty_title')}</h3>
                    <p className="text-base-content/60">{t('account.addresses.empty_sub')}</p>
                </div>
            )}

            {addresses.map((addr) => (
                <div key={addr.id} className="card bg-base-100 border border-base-300 shadow-sm">
                    {editingId === addr.id ? (
                        <div className="card-body">
                            <h3 className="card-title text-base">{t('account.addresses.edit_address')}</h3>
                            <AddressForm
                                form={editForm}
                                onSubmit={(e) => submitEdit(e, addr.id)}
                                onCancel={cancelEdit}
                                submitLabel={t('account.addresses.update')}
                            />
                        </div>
                    ) : (
                        <div className="card-body">
                            <div className="flex items-start justify-between gap-3">
                                <div>
                                    <div className="flex items-center gap-2 mb-1">
                                        <span className="font-semibold">{addr.name}</span>
                                        {addr.is_default && <span className="badge badge-primary badge-sm">{t('account.addresses.default_badge')}</span>}
                                    </div>
                                    {addr.phone && <p className="text-sm text-base-content/70">{addr.phone}</p>}
                                    <p className="text-sm">{addr.address}</p>
                                    <p className="text-sm">{addr.city}{addr.state ? `, ${addr.state}` : ''}, {addr.country}{addr.zip ? ` ${addr.zip}` : ''}</p>
                                </div>
                                <div className="flex gap-2 flex-shrink-0">
                                    {!addr.is_default && (
                                        <button
                                            onClick={() => setDefault(addr.id)}
                                            className="btn btn-xs btn-ghost"
                                            title={t('account.addresses.set_default')}
                                        >
                                            {t('account.addresses.set_default')}
                                        </button>
                                    )}
                                    <button onClick={() => startEdit(addr)} className="btn btn-xs btn-ghost btn-square" title="Edit">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z" />
                                        </svg>
                                    </button>
                                    <button onClick={() => handleDelete(addr.id)} className="btn btn-xs btn-ghost btn-square text-error" title="Delete">
                                        <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16" />
                                        </svg>
                                    </button>
                                </div>
                            </div>
                        </div>
                    )}
                </div>
            ))}
        </div>
    );
}

// ─── Main Page ────────────────────────────────────────────────────────────────
export default function AccountIndex({ orders, wishlist, addresses }) {
    const { auth } = usePage().props;
    const user = auth.user;
    const t = useTrans();

    const [activeTab, setActiveTab] = useState('orders');

    const tabs = [
        {
            key: 'orders',
            label: t('account.tab_orders'),
            count: orders.length,
            icon: (
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M16 11V7a4 4 0 00-8 0v4M5 9h14l1 12H4L5 9z" />
                </svg>
            ),
        },
        {
            key: 'favourites',
            label: t('account.tab_favourites'),
            count: wishlist.length,
            icon: (
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z" />
                </svg>
            ),
        },
        {
            key: 'addresses',
            label: t('account.tab_addresses'),
            count: addresses.length,
            icon: (
                <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z" />
                    <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M15 11a3 3 0 11-6 0 3 3 0 016 0z" />
                </svg>
            ),
        },
    ];

    // Avatar initials
    const initials = user.name
        .split(' ')
        .map((n) => n[0])
        .join('')
        .slice(0, 2)
        .toUpperCase();

    return (
        <AuthenticatedLayout>
            <Head title={t('account.page_title')} />

            <div className="max-w-7xl mx-auto px-4 py-8">
                <div className="flex flex-col lg:flex-row gap-6">
                    {/* Sidebar */}
                    <aside className="lg:w-72 flex-shrink-0">
                        {/* Profile card */}
                        <div className="card bg-base-100 shadow-md mb-4">
                            <div className="card-body items-center text-center py-8">
                                <div className="avatar placeholder mb-3">
                                    <div className="bg-primary text-primary-content rounded-full w-20 text-2xl font-bold flex items-center justify-center">
                                        <span>{initials}</span>
                                    </div>
                                </div>
                                <h2 className="card-title text-lg">{user.name}</h2>
                                <p className="text-sm text-base-content/60">{user.email}</p>
                                <Link href={route('profile.edit')} className="btn btn-sm btn-ghost mt-2">
                                    {t('account.edit_profile')}
                                </Link>
                            </div>
                        </div>

                        {/* Nav */}
                        <div className="card bg-base-100 shadow-md">
                            <ul className="menu p-2">
                                {tabs.map((tab) => (
                                    <li key={tab.key}>
                                        <button
                                            onClick={() => setActiveTab(tab.key)}
                                            className={`flex items-center gap-3 ${activeTab === tab.key ? 'active' : ''}`}
                                        >
                                            {tab.icon}
                                            <span className="flex-1 text-left">{tab.label}</span>
                                            {tab.count > 0 && (
                                                <span className="badge badge-sm badge-primary">{tab.count}</span>
                                            )}
                                        </button>
                                    </li>
                                ))}
                            </ul>
                        </div>
                    </aside>

                    {/* Main content */}
                    <main className="flex-1 min-w-0">
                        {/* Tab header */}
                        <div className="flex items-center justify-between mb-6">
                            <h1 className="text-2xl font-bold">
                                {tabs.find((t) => t.key === activeTab)?.label}
                            </h1>
                            {/* Mobile tab switcher */}
                            <div className="tabs tabs-boxed lg:hidden">
                                {tabs.map((tab) => (
                                    <button
                                        key={tab.key}
                                        onClick={() => setActiveTab(tab.key)}
                                        className={`tab ${activeTab === tab.key ? 'tab-active' : ''}`}
                                    >
                                        {tab.icon}
                                    </button>
                                ))}
                            </div>
                        </div>

                        {activeTab === 'orders' && <OrdersTab orders={orders} />}
                        {activeTab === 'favourites' && <FavouritesTab wishlist={wishlist} />}
                        {activeTab === 'addresses' && <AddressesTab addresses={addresses} />}
                    </main>
                </div>
            </div>
        </AuthenticatedLayout>
    );
}
