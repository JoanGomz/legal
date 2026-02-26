import './bootstrap';
const cardSearch = () => import('./cardSearch.js');
function loadModuleIfElementExists(selector, moduleLoader) {
    if (document.querySelector(selector)) {
        return moduleLoader()
            .then(module => {
                if (typeof module.default === 'function') {
                    module.default();
                }
                return module;
            })
            .catch(error => console.error(`Error loading module for ${selector}:`, error));
    }
    return Promise.resolve(null);
}
document.addEventListener("DOMContentLoaded", function () {
    Promise.all([
        loadModuleIfElementExists('#searchInput', cardSearch),
    ]);
});
