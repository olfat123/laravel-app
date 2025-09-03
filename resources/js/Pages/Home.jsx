import AuthenticatedLayout from '@/Layouts/AuthenticatedLayout';
import { Head, Link } from '@inertiajs/react';
import ProductItem from '@/Components/App/ProductItem';

export default function Home({ auth, laravelVersion, phpVersion, products }) {
    return (
        <AuthenticatedLayout>
            <Head title="Home" />
            <div className="grid grid-cols-1 gap-8 md:grid-cols-2 lg:grid-cols-3 p-8">
                {products.map((product) => (
                    <ProductItem product={product} key={product.id} />
                ))}
            </div>
            
        </AuthenticatedLayout>
    );
}
