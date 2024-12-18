import { format } from "@formkit/tempo";
export const cuitDisplay = (data) => {
    return String(data).replace(/(\d{2})(\d{8})(\d{1})/, "$1-$2-$3");
};

export const currencyNumber = (data) => {
    return new Intl.NumberFormat("es-AR", {
        style: "currency",
        currency: "ARS",
    }).format(data);
};

export const percentNumber = (data) => {
    return new Intl.NumberFormat("es-ES", {
        style: "percent",
        minimumFractionDigits: 2,
        maximumFractionDigits: 2,
    }).format(data / 100);
};

export const roundedNumber = (data) => {
    const rounded = Math.round(data * 100) / 100;
    return Math.abs(rounded) < 0.001 ? 0 : rounded;
};

export const addDate = (date, days) => {
    const result = new Date(date);
    result.setDate(result.getDate() + days);
    return result;
};

export const dateFormat = (data) => {
    return format(data, "DD/MM/YYYY", "es");
};

export const dateTimeFormat = (data) => {
    return format(data, "DD/MM/YYYY HH:mm:ss", "es");
};

export const invoiceNumberFormat = (data, length) => {
    return String(data).padStart(length, "0");
};
