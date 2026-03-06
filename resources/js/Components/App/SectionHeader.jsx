import { Link } from '@inertiajs/react';

/**
 * Standardised section header used throughout Home.jsx.
 *
 * Props:
 *  - heading   {string} main heading text
 *  - subtext   {string} optional sub-heading
 *  - viewAllHref {string} optional URL – renders a "View all" button on the right
 *  - viewAllLabel {string} label for the view-all link
 */
export default function SectionHeader({ heading, subtext, viewAllHref, viewAllLabel }) {
    return (
        <div className="flex items-end justify-between mb-12">
            <div>
                <h2 className="text-3xl md:text-4xl font-bold text-base-content">{heading}</h2>
                {subtext && <p className="mt-3 text-base-content/60">{subtext}</p>}
            </div>
            {viewAllHref && (
                <Link href={viewAllHref} className="btn btn-ghost gap-1 hidden sm:flex">
                    {viewAllLabel}
                    <svg xmlns="http://www.w3.org/2000/svg" className="h-4 w-4" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path strokeLinecap="round" strokeLinejoin="round" strokeWidth={2} d="M9 5l7 7-7 7" />
                    </svg>
                </Link>
            )}
        </div>
    );
}
