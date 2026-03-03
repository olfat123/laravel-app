import ApplicationLogo from '@/Components/ApplicationLogo';
import Dropdown from '@/Components/Dropdown';
import NavLink from '@/Components/NavLink';
import ResponsiveNavLink from '@/Components/ResponsiveNavLink';
import { Link, usePage } from '@inertiajs/react';
import { useState } from 'react';
import Navbar from '@/Components/App/Navbar';
import Footer from '@/Components/App/Footer';

export default function AuthenticatedLayout({ header, children }) {
    const props = usePage().props;
    const user = props.auth.user;

    const [showingNavigationDropdown, setShowingNavigationDropdown] =
        useState(false);

    return (
        <div className="min-h-screen bg-base-100 flex flex-col">
            <Navbar />

            {props.error && (
                <div className="bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded relative mx-auto mt-4 max-w-7xl" role="alert">
                    <strong className="font-bold">Error! </strong>
                    <span className="block sm:inline">{props.error}</span>
                </div>
            )}

            {props.success && (
                <div className="bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded relative mx-auto mt-4 max-w-7xl" role="alert">
                    <span className="block sm:inline">{props.success}</span>
                </div>
            )}

            {/* {header && (
                <header className="bg-white shadow">
                    <div className="mx-auto max-w-7xl px-4 py-6 sm:px-6 lg:px-8">
                        {header}
                    </div>
                </header>
            )} */}

            <main className="flex-1">{children}</main>
            <Footer />
        </div>
    );
}
