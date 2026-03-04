export function useFormatters() {

    function formatCurrency(amount) {
        if (amount === null || amount === undefined) return '$0.00';

        return new Intl.NumberFormat('es-MX', {
            style: 'currency',
            currency: 'MXN',
            minimumFractionDigits: 2
        }).format(amount); 
    }

    function capitalize(str) {
        if (!str) return ''; 
        return str.charAt(0).toUpperCase() + str.slice(1); 
    }

    return {
        formatCurrency,
        capitalize
    }
}