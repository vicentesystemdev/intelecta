import { useEffect, useLayoutEffect, useState } from 'react';

const THEME_KEY = 'intelecta-theme';
const THEME_EVENT = 'intelecta-theme-change';

const getStoredTheme = () => {
    if (typeof window === 'undefined') {
        return 'light';
    }

    return window.localStorage.getItem(THEME_KEY) === 'dark'
        ? 'dark'
        : 'light';
};

const applyTheme = (theme) => {
    if (typeof document === 'undefined') {
        return;
    }

    document.documentElement.classList.toggle('dark', theme === 'dark');
    document.documentElement.style.colorScheme = theme;
};

export default function useTheme() {
    const [theme, setThemeState] = useState(getStoredTheme);
    const useBrowserLayoutEffect =
        typeof window === 'undefined' ? useEffect : useLayoutEffect;

    useBrowserLayoutEffect(() => {
        applyTheme(theme);
    }, [theme]);

    useEffect(() => {
        const syncTheme = (event) => {
            const nextTheme =
                event.type === 'storage' ? event.newValue : event.detail;

            if (nextTheme === 'light' || nextTheme === 'dark') {
                setThemeState(nextTheme);
            }
        };

        window.addEventListener('storage', syncTheme);
        window.addEventListener(THEME_EVENT, syncTheme);

        return () => {
            window.removeEventListener('storage', syncTheme);
            window.removeEventListener(THEME_EVENT, syncTheme);
        };
    }, []);

    const setTheme = (nextTheme) => {
        const normalizedTheme = nextTheme === 'dark' ? 'dark' : 'light';

        window.localStorage.setItem(THEME_KEY, normalizedTheme);
        applyTheme(normalizedTheme);
        setThemeState(normalizedTheme);
        window.dispatchEvent(
            new CustomEvent(THEME_EVENT, { detail: normalizedTheme }),
        );
    };

    return {
        theme,
        isDark: theme === 'dark',
        setTheme,
        toggleTheme: () => setTheme(theme === 'dark' ? 'light' : 'dark'),
    };
}
