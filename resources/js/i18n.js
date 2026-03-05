import { usePage } from "@inertiajs/react";

/**
 * Returns a translation helper function `t(key, replacements?)`.
 * Replacements use {placeholder} syntax: t('nav.cart.items', { count: 3 })
 */
export function useTrans() {
    const { translations = {}, locale = "en" } = usePage().props;

    return function t(key, replacements = {}) {
        let string = translations[key] ?? key;
        Object.entries(replacements).forEach(([k, v]) => {
            string = string.replace(new RegExp(`\\{${k}\\}`, "g"), String(v));
        });
        return string;
    };
}

/** Returns the current active locale string, e.g. 'en' or 'ar'. */
export function useLocale() {
    return usePage().props.locale ?? "en";
}
