export const validateEmail = (value) => {
    return /^\w+([\.-]?\w+)*@\w+([\.-]?\w+)*(\.\w{2,3})+$/.test(value);
};

export const validatePhoneNumber = (value) => {
    return /^[0-9-]{6,15}$/.test(value);
}

export const validateAccountNumber = (value) => {
    return /^[0-9/]{6,10}$/.test(value);
}

/* CBU validation */
export const validateCBU = (value) => {
    if (!/^[0-9]{22}$/.test(value)) {
        return false;
    }

    const cod_bcra = value.substring(0, 3);
    const sucursalNumber = value.substring(3, 7);
    const accountNumber = value.substring(8, 21);
    const validate1 = firstPart(cod_bcra, sucursalNumber);
    const validate2 = secondPart(accountNumber);

    const finalNumber = cod_bcra + sucursalNumber + validate1 + accountNumber + validate2;

    return finalNumber === value;
};

const firstPart = (a, b) => {
    let value =
        a[0] * 7 +
        a[1] * 1 +
        a[2] * 3 +
        b[0] * 9 +
        b[1] * 7 +
        b[2] * 1 +
        b[3] * 3;

    return (value = (10 - (value % 10)) % 10);
};

const secondPart = (c) => {
    let value =
        c[0] * 3 +
        c[1] * 9 +
        c[2] * 7 +
        c[3] * 1 +
        c[4] * 3 +
        c[5] * 9 +
        c[6] * 7 +
        c[7] * 1 +
        c[8] * 3 +
        c[9] * 9 +
        c[10] * 7 +
        c[11] * 1 +
        c[12] * 3;

    return (value = (10 - (value % 10)) % 10);
};
/* CBU validation */

/* Alias validation */
export const validateAlias = (value) => {
    return /^[a-zA-Z0-9_.-]{6,22}$/.test(value);
};
/* Alias validation */
