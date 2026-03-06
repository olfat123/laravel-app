import { Link } from '@inertiajs/react';

/**
 * Renders page-number pagination links from an Inertia paginator meta object.
 * Renders nothing when there is only one page.
 */
export default function Pagination({ meta, className = '' }) {
    if (!meta || meta.last_page <= 1) return null;

    return (
        <div className={`mt-10 flex justify-center gap-2 flex-wrap ${className}`}>
            {meta.links.map((link, i) => (
                <Link
                    key={i}
                    href={link.url ?? '#'}
                    className={`btn btn-sm ${link.active ? 'btn-primary' : 'btn-ghost'} ${
                        !link.url ? 'btn-disabled opacity-40' : ''
                    }`}
                    dangerouslySetInnerHTML={{ __html: link.label }}
                />
            ))}
        </div>
    );
}
