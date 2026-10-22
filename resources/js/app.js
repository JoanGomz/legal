import "./bootstrap";
import * as Ably from "ably";
// import "emoji-picker-element";
const cardSearch = () => import("./cardSearch.js");
function loadModuleIfElementExists(selector, moduleLoader) {
    if (document.querySelector(selector)) {
        return moduleLoader()
            .then((module) => {
                if (typeof module.default === "function") {
                    module.default();
                }
                return module;
            })
            .catch((error) =>
                console.error(`Error loading module for ${selector}:`, error),
            );
    }
    return Promise.resolve(null);
}
document.addEventListener("DOMContentLoaded", function () {
    Promise.all([loadModuleIfElementExists("#searchInput", cardSearch)]);
});
window.ably = new Ably.Realtime(window.ablyApiKey);
if ("serviceWorker" in navigator) {
    navigator.serviceWorker
        .register("/sw.js")
        .then((registration) => {
            console.log("Service Worker registrado:");
        })
        .catch((error) => {
            console.error("Error registrando Service Worker:", error);
        });
}
