import axios from "axios";

export async function states() {
    try {
        const response = await fetch(
            "https://apis.datos.gob.ar/georef/api/provincias"
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
/* 
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
