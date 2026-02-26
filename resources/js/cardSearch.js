export default function () {
    const searchInput = document.getElementById("searchInput");
    const cardRows = document.querySelectorAll(".div");

    // El código original del buscador
    searchInput.addEventListener("keyup", function () {
        const searchTerm = this.value.toLowerCase();

        // Filtrar tarjetas
        cardRows.forEach((card) => {
            // Obtener elementos específicos dentro de cada tarjeta
            const name =
                card.querySelector("h2")?.textContent.toLowerCase() || "";
            const guard_name =
                card.querySelector("p")?.textContent.toLowerCase() || "";
            const description =
                card.querySelector(".text-sm")?.textContent.toLowerCase() || "";
            const id =
                card
                    .querySelector(".absolute.top-3.right-3")
                    ?.textContent.toLowerCase() || "";
            const metadata =
                card.querySelector(".border-t")?.textContent.toLowerCase() ||
                "";

            // También considerar todo el texto como respaldo
            const allText = card.textContent.toLowerCase();

            // Buscar en cada campo importante o en todo el texto
            if (
                name.includes(searchTerm) ||
                description.includes(searchTerm) ||
                id.includes(searchTerm) ||
                metadata.includes(searchTerm) ||
                allText.includes(searchTerm)
            ) {
                card.style.display = ""; // Mostrar tarjeta
            } else {
                card.style.display = "none"; // Ocultar tarjeta
            }
        });
    });
};