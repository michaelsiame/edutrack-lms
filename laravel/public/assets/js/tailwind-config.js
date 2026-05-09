/**
 * Shared Tailwind CSS Configuration for Edutrack LMS
 * This ensures consistent colors across all templates
 */

tailwind.config = {
    theme: {
        extend: {
            colors: {
                primary: {
                    50: '#EBF4FF',
                    100: '#D6E9FF',
                    200: '#B3D9FF',
                    300: '#80C3FF',
                    400: '#4DA8FF',
                    500: '#2E70DA',
                    600: '#1E4A8A',
                    700: '#1A3D73',
                    800: '#15305C',
                    900: '#0F2445'
                },
                secondary: {
                    50: '#FFF9EB',
                    100: '#FFF3D6',
                    200: '#FFE8B3',
                    300: '#FFD980',
                    400: '#F9C455',
                    500: '#F6B745',
                    600: '#D99E2E',
                    700: '#B5821F',
                    800: '#91661A',
                    900: '#785218'
                },
                success: {
                    50: '#ECFDF5',
                    100: '#D1FAE5',
                    200: '#A7F3D0',
                    300: '#6EE7B7',
                    400: '#34D399',
                    500: '#10B981',
                    600: '#059669',
                    700: '#047857',
                    800: '#065F46',
                    900: '#064E3B'
                },
                warning: {
                    50: '#FFFBEB',
                    100: '#FEF3C7',
                    200: '#FDE68A',
                    300: '#FCD34D',
                    400: '#FBBF24',
                    500: '#F59E0B',
                    600: '#D97706',
                    700: '#B45309',
                    800: '#92400E',
                    900: '#78350F'
                },
                danger: {
                    50: '#FEF2F2',
                    100: '#FEE2E2',
                    200: '#FECACA',
                    300: '#FCA5A5',
                    400: '#F87171',
                    500: '#EF4444',
                    600: '#DC2626',
                    700: '#B91C1C',
                    800: '#991B1B',
                    900: '#7F1D1D'
                }
            },
            fontFamily: {
                sans: ['Inter', 'system-ui', '-apple-system', 'BlinkMacSystemFont', 'Segoe UI', 'Roboto', 'sans-serif'],
            },
            boxShadow: {
                'soft': '0 2px 15px -3px rgba(0, 0, 0, 0.07), 0 10px 20px -2px rgba(0, 0, 0, 0.04)',
                'card': '0 0 0 1px rgba(0,0,0,0.03), 0 2px 8px rgba(0,0,0,0.04)',
                'card-hover': '0 0 0 1px rgba(0,0,0,0.03), 0 12px 24px rgba(0,0,0,0.08)',
            }
        }
    }
};

// Make config available globally if needed
if (typeof module !== 'undefined' && module.exports) {
    module.exports = tailwind.config;
}
