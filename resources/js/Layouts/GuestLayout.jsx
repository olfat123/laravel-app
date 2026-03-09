import ApplicationLogo from '@/Components/ApplicationLogo';
import { Link, usePage } from '@inertiajs/react';
import Navbar from '@/Components/App/Navbar';

export default function GuestLayout({ children }) {
    const { app_logo } = usePage().props;

    return (
        <>
            <Navbar />
            <div className="flex min-h-screen flex-col items-center bg-gray-100 pt-6 sm:justify-center sm:pt-0">
                <div>
                    <Link href="/">
                        {app_logo
                            ? <img src={`/storage/${app_logo}`} alt="Logo" className="h-20 w-auto object-contain" />
                            : <ApplicationLogo className="h-20 w-20 fill-current text-gray-500" />
                        }
                    </Link>
                </div>

                <div className="mt-6 w-full overflow-hidden bg-white px-6 py-4 shadow-md sm:max-w-md sm:rounded-lg">
                    {children}
                </div>
            </div>
        </>
    );
}
