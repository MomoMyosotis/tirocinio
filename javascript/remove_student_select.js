document.addEventListener("DOMContentLoaded", function () {
    const selects = document.querySelectorAll(".custom-select");

    function aggiornaOpzioni() {
        let selezionati = new Set();

        // Trova tutti i valori selezionati
        selects.forEach(select => {
            if (select.value) {
                selezionati.add(select.value);
            }
        });

        // Mostra/Nasconde le opzioni
        selects.forEach(select => {
            Array.from(select.options).forEach(option => {
                if (option.value === "") return; // Non nascondere l'opzione vuota
                option.hidden = selezionati.has(option.value) && select.value !== option.value;
            });
        });
    }

    // Aggiungi listener a ogni select
    selects.forEach(select => {
        select.addEventListener("change", aggiornaOpzioni);
    });
});