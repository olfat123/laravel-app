import { useState } from 'react';

/**
 * Interactive or read-only 5-star rating widget.
 *
 * Props:
 *  - value    {number}   current rating (0-5)
 *  - onChange {function} called with the new rating on click (write mode)
 *  - readonly {boolean}  disables interaction
 *  - size     {string}   Tailwind text-size class, default 'text-2xl'
 */
export default function StarRating({ value = 0, onChange, readonly = false, size = 'text-2xl' }) {
    const [hovered, setHovered] = useState(0);

    return (
        <div className="flex gap-0.5">
            {[1, 2, 3, 4, 5].map((star) => (
                <button
                    key={star}
                    type="button"
                    disabled={readonly}
                    onClick={() => !readonly && onChange?.(star)}
                    onMouseEnter={() => !readonly && setHovered(star)}
                    onMouseLeave={() => !readonly && setHovered(0)}
                    className={`${size} leading-none transition-all ${
                        (hovered || value) >= star ? 'text-warning' : 'text-base-content/20'
                    } ${!readonly ? 'cursor-pointer hover:scale-125' : 'cursor-default'}`}
                >
                    ★
                </button>
            ))}
        </div>
    );
}
