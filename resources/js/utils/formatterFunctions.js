export const cuitDisplay = (data) => {
    return String(data).replace(/(\d{2})(\d{8})(\d{1})/, '$1-$2-$3');
}