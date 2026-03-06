import { usePage } from '@inertiajs/react';
import Navbar from '@/Components/App/Navbar';
import Footer from '@/Components/App/Footer';

export default function AuthenticatedLayout({ children }) {
    const { error, success } = usePage().props;

    return (
        <div className="min-h-screen bg-base-100 flex flex-col">
            <Navbar />

            {error && (
                <div role="alert" className="alert alert-error mx-auto mt-4 max-w-7xl w-full px-4 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{error}</span>
                </div>
            )}

            {success && (
                <div role="alert" className="alert alert-success mx-auto mt-4 max-w-7xl w-full px-4 rounded-xl">
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-5 w-5 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                    <span>{success}</span>
                </div>
            )}

            <main className="flex-1">{children}</main>
            <Footer />
        </div>
    );
}

