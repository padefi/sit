export function dropdownClasses(value)  {
    return {
        'filled': value && value !== '' || value === 0,
    }
};

export function dropdownLabelClasses(value)  {
    return {
        '!top-[-2px]': value !== '',
    }
};