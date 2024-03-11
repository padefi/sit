import Lara from "@/presets/lara";
import Wind from "@/presets/wind";
import { reactive, watch } from "vue";

export default {
    install: (app) => {
        const _appState = reactive({ preset: "lara" });

        watch(
            () => _appState.preset,
            (newValue) => {
                if (newValue === "lara")
                    app.config.globalProperties.$primevue.config.pt = Lara;
                else if (newValue === "wind")
                    app.config.globalProperties.$primevue.config.pt = Wind;
            }
        );

        app.config.globalProperties.$appState = _appState;
    },
};
