export async function nominatim(data) {
    try {
        const endpoint = 'https://nominatim.openstreetmap.org/search';
        const params = {
            q: data,
            format: 'json',
            addressdetails: 1,
            limit: 50,
            type: 'address',
            countrycodes: 'ar'
        };
        const response = await fetch(`${endpoint}?${new URLSearchParams(params)}`);

        if (!response.ok) {
            throw new Error("Error fetching data: " + response.statusText);
        }

        return await response.json();
    } catch (error) {
        console.error(error);
        return [];
    }
}

/* export async function states() {
    try {
        const response = await fetch(
            "https://apis.datos.gob.ar/georef/api/provincias?&orden=nombre"
        );
        if (!response.ok) {
            throw new Error("Error fetching data: " + response.statusText);
        }
        const json = await response.json();
        return json.provincias;
    } catch (error) {
        console.error(error);
        return [];
    }
}

export async function districts(state) {
    try {
        const response = await fetch(
            "https://apis.datos.gob.ar/georef/api/localidades?provincia=" + state + "&orden=nombre&aplanar=true&campos=basico&max=100&inicio=0&exacto=true&formato=json"
        );
        if (!response.ok) {
            throw new Error("Error fetching data: " + response.statusText);
        }

        const json = await response.json();
        return json.localidades;
    } catch (error) {
        console.error(error);
        return [];
    }
}

export function municipio(provincia) {
    fetch(
        `https://apis.datos.gob.ar/georef/api/municipios?provincia=${provincia}&max=5`
    )
        .then((res) => (res.ok ? res.json() : Promise.reject(res)))
        .then((json) => {
            return json.municipios;
        })
        .catch((error) => {
            console.error(error);
        });
}

export function localidad(municipio) {
    fetch(
        `https://apis.datos.gob.ar/georef/api/localidades?municipio=${municipio}&max=5`
    )
        .then((res) => (res.ok ? res.json() : Promise.reject(res)))
        .then((json) => {
            return json.municipios;
        })
        .catch((error) => {
            console.error(error);
        });
} */
