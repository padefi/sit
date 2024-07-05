export function dropdownClasses(value)  {
    return {
        'filled': value !== '' && value,
    }
};

export function dropdownLabelClasses(value)  {
    return {
        '!top-[-2px]': value !== '',
    }
};