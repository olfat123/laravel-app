import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import ProductItem from '@/Components/App/ProductItem';

export default function Shop({ products }) {
    return (
        <AuthenticatedLayout>
            <Head title="Shop" />

            {/* Page header */}
            <div className="bg-base-200 border-b border-base-300">
                <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-8">
                    <h1 className="text-3xl font-bold text-base-content">Shop</h1>
                    <p className="mt-1 text-base-content/60">Browse all products</p>
                </div>
            </div>

            <div className="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-10">
                {products.data.length === 0 ? (
                    <div className="text-center py-20 text-base-content/50">
                        <svg xmlns="http://www.w3.org/2000/svg" className="w-16 h-16 mx-auto mb-4 opacity-30" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={1.5} d="M20 7H4a1 1 0 00-1 1v10a1 1 0 001 1h16a1 1 0 001-1V8a1 1 0 00-1-1z" />
                        </svg>
                        <p className="text-xl font-medium">No products found</p>
                    </div>
                ) : (
                    <div className="grid grid-cols-1 gap-6 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4">
                        {products.data.map((product) => (
                            <ProductItem product={product} key={product.id} />
                        ))}
                    </div>
                )}

                {/* Pagination */}
                {products.meta && products.meta.last_page > 1 && (
                    <div className="mt-10 flex justify-center gap-2">
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
        </AuthenticatedLayout>
    );
}
